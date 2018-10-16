jQuery(document).ready(function(t) {
    function show_alert(content) {
        t(".cap_update_alert").html(content);
        t(".cap_update_alert").fadeIn(1000, function() {
            t(".cap_update_alert").fadeOut(3000)
        });
    }

    function show_waiting(d) {
        d ? t(".cap_update_waiting").html(loading_gif) : t(".cap_update_waiting").html("");
    }

    function format_date(year, month, date) {
        if (month < 10) month = "0" + month;
        if (date < 10) date = "0" + date;
        return month + "/" + date + "/" + year;
    }

    function format_time(hour, minute, second) {
        if (hour < 10) hour = "0" + hour;
        if (minute < 10) minute = "0" + minute;
        if (second < 10) second = "0" + second;
        return hour + ":" + minute + ":" + second;
    }

    function set_dates() {
        t("#from_date").prop("disabled", true);
        t("#to_date").prop("disabled", true);
        var date = new Date;
        var cur_date = new Date(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate());
        var formatted_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
        if ("date_today" == date_type) {
            from_date = formatted_date;
            to_date = formatted_date;
        }
        else if ("date_yesterday" == date_type) {
            cur_date.setDate(cur_date.getDate() - 1);
            from_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
            to_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
        }
        else if ("date_thisweek" == date_type) {
            var r = cur_date.getDate() + 1;
            0 == cur_date.getDay() ? r -= 7 : r -= cur_date.getDay();
            cur_date.setDate(r);
            from_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
            to_date = formatted_date;
        }
        else if ("date_thismonth" == date_type) {
            from_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, 1);
            to_date = formatted_date;
        }
        else if ("date_thisyear" == date_type) {
            from_date = format_date(cur_date.getFullYear(), 1, 1);
            to_date = formatted_date;
        }
        else if ("date_lastweek" == date_type) {
            r = cur_date.getDate() + 1 - 7;
            0 == cur_date.getDay() ? r -= 7 : r -= cur_date.getDay();
            cur_date.setDate(r);
            from_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
            r = cur_date.getDate() + 6;
            cur_date.setDate(r);
            to_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
        }
        else if ("date_custom" == date_type) {
            from_date = "";
            to_date = "";
            t("#from_date").prop("disabled", false);
            t("#to_date").prop("disabled", false);
        }
        t("#from_date").val(from_date);
        t("#to_date").val(to_date);
    }

    function get_cap_update_list() {
        show_waiting(true);
        t.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/crm_list.php",
            data: {},
            success: function(e) {
                show_waiting(false);
                if ("error" == e) {
                    show_alert("Cannot load CRM site information.");
                }
                else if ("no_cookie" == e) {
                    window.location.href = "../../admin/login.php";
                }
                else {
                    var crm_list = jQuery.parseJSON(e);

                    if ("" == t("#from_date").val()) {
                        show_alert("Please select FROM DATE.")
                    }
                    else if ("" == t("#to_date").val()) {
                        show_alert("Please select TO DATE.")
                    }
                    else {
                        show_waiting(true);
                        t.ajax({
                            type: "GET",
                            url: "../daemon/ajax_admin/dashboard_sales_all.php",
                            data: {
                                crm_list: crm_list,
                                from_date: t("#from_date").val(),
                                to_date: t("#to_date").val()
                            },
                            success: function(data) {
                                show_waiting(false);

                                crm_sales_goal = jQuery.parseJSON(data);

                                show_waiting(true);
                                t(".table_affiliation_body").html("");
                                t.ajax({
                                    type: "GET",
                                    url: "../daemon/ajax_admin/cap_update_list.php",
                                    data: {},
                                    success: function(e) {
                                        show_waiting(false);
                                        if ("no_cookie" === e)
                                            return void (window.location.href = "../../admin/login.php");

                                        cap_update_list = jQuery.parseJSON(e);

                                        var html = "";

                                        var affiliate_goal_id = -1;
                                        for (var i = 0; i < cap_update_list.length; i++) {
                                            var affiliate_goal = cap_update_list[i];
                                            // ["6", "2", "3", "200", "Full Zoom Media", "12,58", "Vital X", "Falcor CRM", "2500"]
                                            if (affiliate_goal_id != affiliate_goal[1]) {
                                                if (-1 !== affiliate_goal_id)
                                                    html += '</div></div></div>';
                                                affiliate_goal_id = affiliate_goal[1];

                                                html += '<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 c_item"><div>';
                                                html += '<h4 style="color: #6772e5;"><b>' + affiliate_goal[4] + '</b></h4>';
                                                if (null == affiliate_goal[5])
                                                    html += '<p>AFIDS:</p>';
                                                else
                                                    html += '<p>AFIDS: ' + affiliate_goal[5] + '</p>';

                                                html += '<h4 style="color: #6772e5">Sales Progress</h4>';
                                                html += '<div class="row c_cnt_header">';
                                                html += '<div style="color: #6772e5" class="col-lg-4 col-md-4 col-sm-4 col-xs-4">OFFER</div>';
                                                html += '<div style="color: #6772e5" class="col-lg-3 col-md-3 col-sm-3 col-xs-3">PROGRESS</div>';
                                                html += '<div style="color: #6772e5" class="col-lg-5 col-md-5 col-sm-5 col-xs-5">LAST UPDATED</div>';
                                                html += '</div>';
                                                html += '<div class="c_cnt_list">';
                                            }
                                            html += '<div class="row">';
                                            html += '<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">' + affiliate_goal[6] + '</div>';
                                            html += '<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3" id="capgoal_' + affiliate_goal[1] + '_' + affiliate_goal[2] + '">0/' + affiliate_goal[3] + '</div>';
                                            html += '<div class="col-lg-5 col-md-5 col-sm-5 col-xs-5" id="updated_' + affiliate_goal[1] + '_' + affiliate_goal[2] + '"></div>';
                                            html += '</div>';
                                        }
                                        t(".div_cap_update_body").html(html);

                                        for (i = 0; i < crm_list.length; i++) {
                                            get_cap_update_goal_list(crm_list[i][0]);
                                        }
                                    },
                                    failure: function(t) {
                                        show_waiting(false);
                                        show_alert("Cannot load Affiliate Sales Goal information.")
                                    }
                                });
                            },
                            failure: function() {
                                show_alert("Cannot load sales information.")
                            }
                        })
                    }
                }
            },
            failure: function() {
                show_waiting(false);
                show_alert("Cannot load CRM site information.");
            }
        });
    }

    function get_cap_update_goal_list(crm_id) {
        t.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/cap_update_goal_list.php",
            data: {
                crm_id: crm_id,
                from_date: t("#from_date").val(),
                to_date: t("#to_date").val()
            },
            success: function(e) {
                var goal = jQuery.parseJSON(e);

                if (goal[0] == 'error')
                {
                    show_alert('Cannot load sales information of ' + goal[1]);
                }
                else if (goal[0] == 'no_cookie')
                {
                    window.location.href = '../../admin/login.php';
                }
                else {
                    goals.push(goal);
                    for (var i = 0; i < cap_update_list.length; i++) {
                        var affiliate_goal = cap_update_list[i];
                        if (goal[1] == affiliate_goal[7]) {
                            var count = 0;
                            var afids = affiliate_goal[5].split(',');
                            var campaign_ids = affiliate_goal[10].split(',');
                            for (var k = 0; k < goal[2].length; k++) {
                                var campaign_prospects = goal[2][k];
                                for (var l = 0; l < campaign_ids.length; l++) {
                                    if ("step1" === campaign_ids[l].split('_')[0]) {
                                        var campaign_id = campaign_ids[l].split('_')[1];
                                        if (campaign_id == campaign_prospects[0]) {
                                            for (var m = 0; m < campaign_prospects[1].length; m++) {
                                                for (var n = 0; n < afids.length; n++) {
                                                    if (campaign_prospects[1][m][0] == afids[n]) {
                                                        count += campaign_prospects[1][m][2];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $("#capgoal_" + affiliate_goal[1] + '_' + affiliate_goal[2]).html(
                                '[' + count.toString() + '/' + affiliate_goal[3] + ']'
                            );

                            var est = new Date(goal[3].replace(' ', 'T'));
                            est.setHours(est.getHours() - 4);
                            $("#updated_" + affiliate_goal[1] + '_' + affiliate_goal[2]).html(
                                format_date(est.getFullYear(), est.getMonth() + 1, est.getDate()) + ' ' +
                                format_time(est.getHours(), est.getMinutes(), est.getSeconds())
                            );
                        }
                    }
                }
            },
            failure: function() {
                show_waiting(false);
                show_alert("Cannot load sales information.");
            }
        });
    }

    function get_export_result() {
        var affiliate_goal_id = -1;
        var text_result = '';
        for (var i = 0; i < cap_update_list.length; i++) {
            var affiliate_goal = cap_update_list[i];
            if (affiliate_goal_id != affiliate_goal[1]) {
                affiliate_goal_id = affiliate_goal[1];
                if (i != 0) text_result += '\n\n';
                text_result += "To " + affiliate_goal[4] + '\n\n';
                text_result += "Here are remaining caps for the week\n\n";
            }
            for (var j = 0; j < goals.length; j++) {
                var goal = goals[j];
                if (goal[1] == affiliate_goal[7]) {
                    var count = 0;
                    var afids = affiliate_goal[5].split(',');
                    var campaign_ids = affiliate_goal[10].split(',');
                    for (var k = 0; k < goal[2].length; k++) {
                        var campaign_prospects = goal[2][k];
                        for (var l = 0; l < campaign_ids.length; l++) {
                            if ("step1" === campaign_ids[l].split('_')[0]) {
                                var campaign_id = campaign_ids[l].split('_')[1];
                                if (campaign_id == campaign_prospects[0]) {
                                    for (var m = 0; m < campaign_prospects[1].length; m++) {
                                        for (var n = 0; n < afids.length; n++) {
                                            if (campaign_prospects[1][m][0] == afids[n]) {
                                                count += campaign_prospects[1][m][2];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    text_result += affiliate_goal[6] + ' ';
                    text_result += (parseInt(affiliate_goal[3]) - count).toString() + ' remaining ';
                    text_result += '(' + affiliate_goal[3] + ' for the week)\n';
                }
            }
        }
        var data = new Blob([text_result], {type: 'text/plain'});
        var textFile = window.URL.createObjectURL(data);
        var link = document.getElementById('downloadlink');
        link.setAttribute('download', 'cap_result_' +
            t("#from_date").val().replace(new RegExp('/', 'g'), '-') + '_' +
            t("#to_date").val().replace(new RegExp('/', 'g'), '-') + '.txt');
        link.href = textFile;
    }

    t(".input-daterange").datepicker({});
    t(".date_dropdown_menu li").on("click", function(e) {
        var r = t(this).text();
        date_type = t(this).find("a").attr("id");
        t(".date_toggle_button").html(r + ' <span class="caret"></span>');
        set_dates();
    });
    t(".cap_search_button").click(function() {
        get_cap_update_list();
    });
    t(".btn_cap_export").click(function() {
        get_export_result();
    });


    var loading_gif = '<img src="../images/loading.gif" style="width:22px;height:22px;">';
    var from_date = "";
    var to_date = "";
    var cap_update_list = null;
    var crm_sales_goal = null;
    var goals = [];
    var date_type = "date_thisweek";

    set_dates();
    get_cap_update_list();
});
