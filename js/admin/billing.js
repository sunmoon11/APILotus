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
        if ("date_thisweek" == date_type) {
            var r = cur_date.getDate() + 1;
            0 == cur_date.getDay() ? r -= 7 : r -= cur_date.getDay();
            cur_date.setDate(r);
            from_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
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
        else {
            let date_selected = date_type.split('_')[1];
            from_date = date_selected.split('-')[0];
            to_date = date_selected.split('-')[1];
            from_date = from_date.substring(0, 2) + '/' + from_date.substring(3, 5) + '/00' + from_date.substring(6);
            to_date = to_date.substring(0, 2) + '/' + to_date.substring(3, 5) + '/00' + to_date.substring(6);
        }
        t("#from_date").val(from_date);
        t("#to_date").val(to_date);
    }

    function get_billing_list() {
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
                            url: "../daemon/ajax_admin/billing_list.php",
                            data: {},
                            success: function(e) {
                                show_waiting(false);
                                if ("no_cookie" === e)
                                    return void (window.location.href = "../../admin/login.php");

                                billing_list = jQuery.parseJSON(e);

                                let html = "";

                                let affiliate_id = -1;
                                for (let i = 0; i < billing_list.length; i++) {
                                    let billing = billing_list[i];
                                    // ["6", "2", "3", "200", "Full Zoom Media", "12,58", "Vital X", "Falcor CRM", "2500"]
                                    if (affiliate_id !== billing['affiliate_id']) {
                                        if (-1 !== affiliate_id)
                                            html += '</div></div></div>';
                                        affiliate_id = billing['affiliate_id'];

                                        html += '<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 c_item"><div>';
                                        html += '<h4 style="color: #6772e5;"><b>' + billing['affiliate_name'] + '</b></h4>';
                                        if (null == billing['afid'])
                                            html += '<p>AFIDS:</p>';
                                        else
                                            html += '<p>AFIDS: ' + billing['afid'] + '</p>';
                                        html += '<p style="margin-top: 5px;" id="tti_' + billing['affiliate_id'] + '">Total To Invoice: $ 0.00</p>';

                                        html += '<h4 style="color: #6772e5">Sales Progress</h4>';
                                        html += '<div class="row c_cnt_header">';
                                        html += '<div style="color: #6772e5; text-align: center;" class="col-lg-4 col-md-4 col-sm-4 col-xs-4">OFFER</div>';
                                        html += '<div style="color: #6772e5" class="col-lg-2 col-md-2 col-sm-2 col-xs-2">SALES</div>';
                                        html += '<div style="color: #6772e5" class="col-lg-3 col-md-3 col-sm-3 col-xs-3">CPA</div>';
                                        html += '<div style="color: #6772e5" class="col-lg-3 col-md-3 col-sm-3 col-xs-3">TOTAL</div>';
                                        html += '</div>';
                                        html += '<div class="c_cnt_list">';
                                    }
                                    html += '<div class="row">';
                                    html += '<div style="text-align: center" class="col-lg-4 col-md-4 col-sm-4 col-xs-4">' + billing['offer_name'] + '</div>';
                                    html += '<div style="text-align: center" class="col-lg-2 col-md-2 col-sm-2 col-xs-2" id="capgoal_' + billing['affiliate_id'] + '_' + billing['offer_id'] + '"></div>';
                                    if (null == billing['s1_payout'] || 0 == billing['s1_payout'])
                                        html += '<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3" id="cpa_' + billing['affiliate_id'] + '_' + billing['offer_id'] + '">$ ' + billing['s1_payout_'] + '.00</div>';
                                    else
                                        html += '<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3" id="cpa_' + billing['affiliate_id'] + '_' + billing['offer_id'] + '"><b>$ ' + billing['s1_payout'] + '.00</b></div>';
                                    html += '<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3" id="total_' + billing['affiliate_id'] + '_' + billing['offer_id'] + '"></div>';
                                    html += '</div>';
                                }
                                t(".div_cap_update_body").html(html);

                                for (let i = 0; i < crm_list.length; i++) {
                                    get_billing_goal_list(crm_list[i][0]);
                                }
                            },
                            failure: function() {
                                show_waiting(false);
                                show_alert("Cannot load Affiliate Sales Goal information.")
                            }
                        });
                    }
                }
            },
            failure: function() {
                show_waiting(false);
                show_alert("Cannot load CRM site information.");
            }
        });
    }

    function get_billing_goal_list(crm_id) {
        t.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/billing_goal_list.php",
            data: {
                crm_id: crm_id,
                from_date: t("#from_date").val(),
                to_date: t("#to_date").val()
            },
            success: function(e) {
                let goal = jQuery.parseJSON(e);

                if (goal[0] === 'error') {
                    show_alert('Cannot load sales information of ' + goal[1]);
                }
                else if (goal[0] === 'no_cookie') {
                    window.location.href = '../../admin/login.php';
                }
                else {
                    for (let i = 0; i < billing_list.length; i++) {
                        let billing = billing_list[i];
                        if (goal[1] == billing['crm_id']) {
                            let count = 0;
                            let afids = billing['afid'].split(',');
                            let campaign_ids = billing['campaign_ids'].split(',');
                            for (let k = 0; k < goal[2].length; k++) {
                                let campaign_prospects = goal[2][k];
                                for (let l = 0; l < campaign_ids.length; l++) {
                                    if ("step1" === campaign_ids[l].split('_')[0]) {
                                        let campaign_id = campaign_ids[l].split('_')[1];
                                        if (campaign_id == campaign_prospects[0]) {
                                            for (let m = 0; m < campaign_prospects[1].length; m++) {
                                                for (let n = 0; n < afids.length; n++) {
                                                    if (campaign_prospects[1][m][0] == afids[n]) {
                                                        count += campaign_prospects[1][m][2];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            billing['sales'] = count;
                            $("#capgoal_" + billing['affiliate_id'] + '_' + billing['offer_id']).html(count ? count.toString() : '');
                            let total = 0;
                            if (null == billing['s1_payout'] || 0 == billing['s1_payout'])
                                total = billing['s1_payout_'];
                            else
                                total = billing['s1_payout'];
                            $("#total_" + billing['affiliate_id'] + '_' + billing['offer_id']).html(
                                '$ ' + (count ? ((count * total).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')) : '-')
                            );

                            let tti = parseFloat($("#tti_" + billing['affiliate_id']).html().substring(20).replace(',', ''));
                            tti += parseFloat(count * total);
                            $("#tti_" + billing['affiliate_id']).html('Total To Invoice: $ ' + tti.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
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

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async function get_export_result() {
        let affiliate_id = -1;
        let result;
        for (let i = 0; i < billing_list.length; i++) {
            let billing = billing_list[i];
            if (affiliate_id !== billing['affiliate_id']) {
                if (0 !== i) {
                    result['tti'] = '$ ' + (result['tti'].toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                    window.location.href = "./export_billing.php?from_date=" + t("#from_date").val() + "&to_date=" + t("#to_date").val() + "&data=" + JSON.stringify(result);
                    await sleep(2000);
                }
                result = {};
                result['affiliate_name'] = billing['affiliate_name'];
                result['afid'] = billing['afid'];
                result['weekof'] = t("#from_date").val() + '-' + t("#to_date").val();
                result['tti'] = 0;
                result['offers'] = [];
                affiliate_id = billing['affiliate_id'];
            }

            let cpa = 0;
            if (null == billing['s1_payout'] || 0 == billing['s1_payout'])
                cpa = billing['s1_payout_'];
            else
                cpa = billing['s1_payout'];
            result['offers'].push({
                'offer': billing['offer_name'],
                'sales': billing['sales'],
                'cpa': '$ ' + cpa + '.00',
                'total': '$ ' + (billing['sales'] ? ((billing['sales'] * cpa).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')) : '-'),
            });

            result['tti'] += parseFloat(billing['sales'] * cpa);
        }
        result['tti'] = '$ ' + (result['tti'].toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
        await sleep(1000);
        window.location.href = "./export_billing.php?from_date=" + t("#from_date").val() + "&to_date=" + t("#to_date").val() + "&data=" + JSON.stringify(result);
    }

    t(".input-daterange").datepicker({});
    t(".date_dropdown_menu li").on("click", function(e) {
        let r = t(this).text();
        date_type = t(this).find("a").attr("id");
        t(".date_toggle_button").html(r + ' <span class="caret"></span>');
        set_dates();
    });
    t(".cap_search_button").click(function() {
        get_billing_list();
    });
    t(".btn_billing_export").click(function() {
        let result = get_export_result();
    });


    let loading_gif = '<img src="../images/loading.gif" style="width:22px;height:22px;">';
    let from_date = "";
    let to_date = "";
    let billing_list = null;
    let date_type = "date_thisweek";

    set_dates();
    get_billing_list();
});
