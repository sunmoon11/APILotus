jQuery(document).ready(function(t) {
    function show_alert(e, a) {
        t(".cap_update_alert").html(a);
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

    function set_dates() {
        t("#from_date").prop("disabled", true);
        t("#to_date").prop("disabled", true);
        var cur_date = new Date;
        var formatted_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());

        var r = cur_date.getDate() + 1;
        0 == cur_date.getDay() ? r -= 7 : r -= cur_date.getDay();
        cur_date.setDate(r);
        from_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
        r = cur_date.getDate() + 6;
        cur_date.setDate(r);
        to_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());

        t("#cap_date").val(formatted_date);
        t("#from_date").val(from_date);
        t("#to_date").val(to_date);
    }

    function get_cap_update_list() {
        show_waiting(true);
        t(".table_affiliation_body").html("");
        if ("" == t("#from_date").val()) {
            show_alert("Please select FROM DATE.");
        }
        else if ("" == t("#to_date").val()) {
            show_alert("Please select TO DATE.");
        }
        else {
            t.ajax({
                type: "GET",
                url: "../daemon/ajax_admin/cap_update_list.php",
                data: {
                    from_date: $("#from_date").val(),
                    to_date : $("#to_date").val()
                },
                success: function(e) {
                    show_waiting(false);
                    if ("no_cookie" === e)
                        return void (window.location.href = "../../admin/login.php");

                    var results = jQuery.parseJSON(e);
                    var html = "";
                    var affiliate_goal_id = -1;
                    for (var i = 0; i < results.length; i++) {
                        var affiliate_goal = results[i];
                        // ["6", "2", "3", "200", "Full Zoom Media", "12,58", "Vital X", "Falcor CRM", "2500"]
                        if (affiliate_goal_id != affiliate_goal[1]) {
                            affiliate_goal_id = affiliate_goal[1];
                            html += '<tr>';
                            html += '<td><span class="payment_badge payment_badge_blue">' + affiliate_goal[4] + '</span></td>';
                            if (null == affiliate_goal[5])
                                html += '<td></td>';
                            else
                                html += '<td>' + affiliate_goal[5] + '</td>';
                            html += '<td></td>';
                            html += '<td></td>';
                            html += '<td></td>';
                            html += '<td></td>';
                            html += '</tr>';
                        }
                        html += '<tr>';
                        html += "<td></td>";
                        html += "<td></td>";
                        html += "<td>" + affiliate_goal[6] + "</td>";
                        html += "<td>" + affiliate_goal[7] + '(' + affiliate_goal[8] + ')' + "</td>";

                        html += '<td><div class="bar-main-container"><div id="bar_' + affiliate_goal[0] + '" class="bar-percentage">0%</div><div class="bar-container"><div class="bar"></div></div></div></td>';
                        html += "<td>" + '0/' + affiliate_goal[3] + "</td>";

                        // html += '<td><button type="button" id="setting_' + affiliate_goal[0] + '" class="btn btn-link btn-sm btn_setting" data-toggle="modal" data-target="#setting_edit_modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span></button></td>';
                        html += '<td><button type="button" id="refresh_' + affiliate_goal[0] + '" class="btn btn-link btn-sm btn_refresh"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button></td>';
                        html += '</tr>';
                    }
                    t(".table_cap_update_body").html(html);

                    for (i = 0; i < results.length; i++) {
                        var affiliate_goal = results[i];
                        var M = Math.round(100 * 217 / affiliate_goal[3]);
                        var C = t("#bar_" + affiliate_goal[0]);
                        t({
                            countNum: M
                        }).animate({
                            countNum: M
                        }, {
                            duration: 2e3,
                            easing: "linear",
                            step: function () {
                                var t = this.countNum + "%";
                                C.text(t) && C.siblings().children().css("width", t)
                            }
                        });
                    }
                },
                failure: function(t) {
                    show_waiting(false);
                    show_alert("Cannot load Affiliate Sales Goal information.")
                }
            });
        }
    }


    t("#from_date").datepicker({});
    t("#to_date").datepicker({});
    t("#cap_date").datepicker({});
    t("#cap_date").change(function () {
        var cur_date = new Date($(this).val());
        var r = cur_date.getDate() + 1;
        0 == cur_date.getDay() ? r -= 7 : r -= cur_date.getDay();
        cur_date.setDate(r);
        from_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
        r = cur_date.getDate() + 6;
        cur_date.setDate(r);
        to_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
        t("#from_date").val(from_date);
        t("#to_date").val(to_date);
    });
    t(".sales_search_button").click(function() {
        get_cap_update_list();
    });


    var loading_gif = '<img src="../images/loading.gif" style="width:22px;height:22px;">';
    var r = '<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color: #ffa5a5"></span>';
    var from_date = "";
    var to_date = "";

    set_dates();
    get_cap_update_list();
});
