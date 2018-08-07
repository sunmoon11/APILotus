jQuery(document).ready(function(t) {
    function get_selected_crm() {
        if (t(".crm_dropdown_list").length > 0) {
            crm_id = t(".crm_dropdown_list").prop("id");
            crm_name = t(".crm_dropdown_list").html();
        }
    }
    function r(e) {
        t(".retention_alert").html(e);
        t(".retention_alert").fadeIn(1e3, function() {
            t(".retention_alert").fadeOut(3e3)
        });
    }
    function show_status(e, r, a) {
        "list" == e && (a ? t(".retention_waiting").html(c) : t(".retention_waiting").html(""));
    }
    function d(e) {
        t(".aff_item_by_" + e).each(function(e) {
            t(this).remove();
        });
        t("#waittr_" + e).remove();
        t(".subaff_item_by_" + e).each(function(e) {
            t(this).remove();
        });
    }
    function set_dates() {
        t("#from_date").prop("disabled", true);
        t("#to_date").prop("disabled", true);
        var cur_date = new Date;
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
        } else if ("date_thismonth" == date_type) {
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
        } else if ("date_custom" == date_type) {
            from_date = "";
            to_date = "";
            t("#from_date").prop("disabled", false);
            t("#to_date").prop("disabled", false);
        }
        t("#from_date").val(from_date);
        t("#to_date").val(to_date);
        t("#from_date").val('07/08/2018');
        t("#to_date").val('07/14/2018');
    }
    function format_date(year, month, date) {
        if (month < 10) month = "0" + month;
        if (date < 10) date = "0" + date;
        return month + "/" + date + "/" + year;
    }
    function get_rebill(e) {
        k = (new Date).getTime();
        if ("" == t("#from_date").val()) {
            r("Please select FROM DATE.");
        }
        else if ("" == t("#to_date").val()) {
            r("Please select TO DATE.");
        }
        else {
            show_status("list", "", !0);
            t.ajax({
                type: "GET",
                url: "../daemon/ajax_admin/rebill_list.php",
                data: {
                    user_token: k,
                    crm_id: crm_id,
                    from_date: t("#from_date").val(),
                    to_date: t("#to_date").val(),
                    cycle: cycle,
                    delete: e
                },
                success: function(e) {
                    show_status("list", "", !1);
                    var data = jQuery.parseJSON(e);
                    if ("error" == data[0])
                        r("Cannot load retention information.");
                    else if ("no_cookie" == data[0])
                        return void (window.location.href = "../../admin/login.php");
                    else {
                        var total_length = data[3].length;
                        var result_html = "<tr>";
                        result_html += '<th rowspan="2" style="vertical-align:middle"><button type="button" class="btn btn-link btn-sm btn_campaign_head"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></button></th>';
                        result_html += '<th rowspan="2" style="vertical-align:middle"></th>';
                        result_html += '<th rowspan="2" style="vertical-align:middle">' + crm_name + '</th>';
                        result_html += '<th colspan="6" style="border-left: 1px solid #dadada">Initial Cycle</th>';
                        for (var cycle_id = 1; cycle_id < cycle; cycle_id++)
                            result_html += '<th colspan="6" style="border-left: 1px solid #dadada">Subscription Cycle ' + cycle_id + "</th>";
                        result_html += "</tr>";
                        result_html += "<tr>";
                        result_html += '<th style="border-left: 1px solid #dadada">Gross Orders</th>';
                        result_html += "<th>Net Approved</th>";
                        result_html += "<th>Void/Full Refund</th>";
                        result_html += "<th>Partial Refund</th>";
                        result_html += "<th>Void/Refund Revenue</th>";
                        result_html += "<th>Approval Rate</th>";
                        for (cycle_id = 1; cycle_id < cycle; cycle_id++) {
                            result_html += '<th style="border-left: 1px solid #dadada">Gross Orders</th>';
                            result_html += "<th>Net Approved</th>";
                            result_html += "<th>Void/Full Refund</th>";
                            result_html += "<th>Partial Refund</th>";
                            result_html += "<th>Void/Refund Revenue</th>";
                            result_html += "<th>Conversion</th>";
                        }
                        result_html += "</tr>";
                        t(".table_retention_head").html(result_html);

                        if (0 == total_length)
                            return void r("There is no any retention information.");

                        var br = "";
                        var _br = "";
                        result_html = "";
                        for (var campaign_id = 0; campaign_id < total_length; campaign_id++) {
                            var campaign = data[3][campaign_id][0];

                            result_html += '<tr id="camprow_' + campaign[0] + '" class="camp_tr">';
                            if ("-1" == campaign[0]) {
                                result_html += "<td></td>";
                                result_html += "<td></td>";
                                result_html += "<td>" + (br = "<b>") + "Total" + (_br = "</b>") + "</td>";
                            }
                            else {
                                "yes" == campaign[2 + 6 * x] ? result_html += '<td><button type="button" class="btn btn-link btn-sm btn_campaign_expand" id="camp_' + campaign[0] + '">' + minus_sign + '</button></td>' : result_html += "<td></td>";
                                result_html += "<td></td>";
                                result_html += "<td>(" + campaign[0] + ") " + campaign[1] + "</td>";
                                result_html += '<td style="border-left: 1px solid #dadada">' + campaign[2] + "</td>";
                                result_html += "<td>" + campaign[3] + "</td>";
                                result_html += "<td>" + campaign[4] + "</td>";
                                result_html += "<td>" + campaign[5] + "</td>";
                                result_html += "<td>" + "$" + campaign[6] + "</td>";
                                result_html += "<td>" + campaign[7] + "%" + "</td>";
                            }
                            for (cycle_id = 1; cycle_id < cycle; cycle_id++) {
                                result_html += '<td style="border-left: 1px solid #dadada">' + br + campaign[6 * cycle_id + 2] + _br + "</td>";
                                result_html += "<td>" + br + campaign[6 * cycle_id + 3] + _br + "</td>";
                                result_html += "<td>" + br + campaign[6 * cycle_id + 4] + _br + "</td>";
                                result_html += "<td>" + br + campaign[6 * cycle_id + 5] + _br + "</td>";
                                result_html += "<td>" + br + "$" + campaign[6 * cycle_id + 6] + _br + "</td>";
                                result_html += "<td>" + br + campaign[6 * cycle_id + 7] + "%" + _br + "</td>";
                            }
                            result_html += "</tr>";

                            var affiliates = data[3][campaign_id][1];
                            for (var affiliate_id = 0; affiliate_id < affiliates.length; affiliate_id++) {
                                var affiliate = affiliates[affiliate_id][0];
                                if (affiliate[2] <= 10) // Remove all of the rows that have 10 or less Gross Orders
                                    continue;

                                var a = '<tr id="affrow_' + campaign[0] + "_" + affiliate[0] + '" class="aff_item_by_' + campaign[0] + '">';
                                a += '<td style="border-top:none"></td>';
                                a += '<td id="affmark_' + campaign[0] + "_" + affiliate[0] + '">' + u + "</td>";
                                a += "<td>(" + affiliate[0] + ") " + affiliate[1] + "</td>";
                                a += '<td style="border-left: 1px solid #dadada">' + affiliate[2] + "</td>";
                                a += "<td>" + affiliate[3] + "</td>";
                                a += "<td>" + affiliate[4] + "</td>";
                                a += "<td>" + affiliate[5] + "</td>";
                                a += "<td>$" + affiliate[6] + "</td>";
                                a += "<td>" + affiliate[7] + "%</td>";
                                for (cycle_id = 1; cycle_id < cycle; cycle_id++) {
                                    a += '<td style="border-left: 1px solid #dadada">' + affiliate[6 * cycle_id + 2] + "</td>";
                                    a += "<td>" + affiliate[6 * cycle_id + 3] + "</td>";
                                    a += "<td>" + affiliate[6 * cycle_id + 4] + "</td>";
                                    a += "<td>" + affiliate[6 * cycle_id + 5] + "</td>";
                                    a += "<td>$" + affiliate[6 * cycle_id + 6] + "</td>";
                                    a += "<td>" + affiliate[6 * cycle_id + 7] + "%</td>";
                                }
                                a += "</tr>";
                                result_html += a;

                                var sub_affiliates = affiliates[affiliate_id][1];
                                sub_affiliates.length > 0 && 0 === affiliate_id && t("#affrow_" + campaign[0] + "_" + affiliate[0]).css("border-bottom", "none");
                                for (var sub_affiliate_id = 0; sub_affiliate_id < sub_affiliates.length; sub_affiliate_id++) {
                                    var sub_affiliate = sub_affiliates[sub_affiliate_id];
                                    if (sub_affiliate[2] <= 10) // Remove all of the rows that have 10 or less Gross Orders
                                        continue;

                                    var o = '<tr id="subaff_' + sub_affiliate[0] + '" class="subaff_item_by_' + campaign[0] + " esubaff_" + campaign[0] + "_" + affiliate[0] + '">';
                                    o += '<td style="border-top:none"></td>';
                                    o += '<td style="border-top:none"></td>';
                                    o += "<td>(" + sub_affiliate[0] + ") " + sub_affiliate[1] + "</td>";
                                    o += '<td style="border-left: 1px solid #dadada">' + sub_affiliate[2] + "</td>";
                                    o += "<td>" + sub_affiliate[3] + "</td>";
                                    o += "<td>" + sub_affiliate[4] + "</td>";
                                    o += "<td>" + sub_affiliate[5] + "</td>";
                                    o += "<td>$" + sub_affiliate[6] + "</td>";
                                    o += "<td>" + sub_affiliate[7] + "%</td>";
                                    for (cycle_id = 1; cycle_id < cycle; cycle_id++) {
                                        o += '<td style="border-left: 1px solid #dadada">' + sub_affiliate[6 * cycle_id + 2] + "</td>";
                                        o += "<td>" + sub_affiliate[6 * cycle_id + 3] + "</td>";
                                        o += "<td>" + sub_affiliate[6 * cycle_id + 4] + "</td>";
                                        o += "<td>" + sub_affiliate[6 * cycle_id + 5] + "</td>";
                                        o += "<td>$" + sub_affiliate[6 * cycle_id + 6] + "</td>";
                                        o += "<td>" + sub_affiliate[6 * cycle_id + 7] + "%</td>";
                                    }
                                    o += "</tr>";
                                    result_html += o;
                                }
                            }
                        }
                        t(".table_retention_body").html(result_html);
                    }
                },
                failure: function(t) {
                    show_status("list", "", !1);
                    r("Cannot load retention information.");
                }
            })
        }
    }
    function retention_aid(e, a) {
        loading = true;
        t.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/retention_aid.php",
            data: {
                user_token: k,
                crm_id: crm_id,
                campaign_id: e,
                from_date: t("#from_date").val(),
                to_date: t("#to_date").val(),
                cycle: cycle,
                delete: a
            },
            success: function(e) {
                loading = !1;
                var r = jQuery.parseJSON(e);
                if ("error" == r[0]) {
                    t("#waittd_" + r[2]).html(_);
                }
                else if ("no_cookie" == r[0]) {
                    window.location.href = "../../admin/login.php";
                }
                else {
                    var a = "";
                    var d = r[2];
                    x = r[3].cycle;
                    for (var o = 0; o < r[3].report.length; o++) {
                        a = '<tr id="affrow_' + d + "_" + r[3].report[o][0] + '" class="aff_item_by_' + d + '">';
                        a += '<td style="border-top:none"></td>';
                        a += '<td id="affmark_' + d + "_" + r[3].report[o][0] + '">' + c + "</td>";
                        a += "<td>(" + r[3].report[o][0] + ") " + r[3].report[o][1] + "</td>";
                        a += '<td style="border-left: 1px solid #dadada">' + r[3].report[o][2] + "</td>";
                        a += "<td>" + r[3].report[o][3] + "</td>";
                        a += "<td>" + r[3].report[o][4] + "</td>";
                        a += "<td>" + r[3].report[o][5] + "</td>";
                        a += "<td>$" + r[3].report[o][6] + "</td>";
                        a += "<td>" + r[3].report[o][7] + "%</td>";
                        for (var n = 1; n < x; n++) {
                            a += '<td style="border-left: 1px solid #dadada">' + r[3].report[o][6 * n + 2] + "</td>";
                            a += "<td>" + r[3].report[o][6 * n + 3] + "</td>";
                            a += "<td>" + r[3].report[o][6 * n + 4] + "</td>";
                            a += "<td>" + r[3].report[o][6 * n + 5] + "</td>";
                            a += "<td>$" + r[3].report[o][6 * n + 6] + "</td>";
                            a += "<td>" + r[3].report[o][6 * n + 7] + "%</td>";
                        }
                        a += "</tr>";
                        t("#camprow_" + d).closest("tr").after(a);
                        t("#waittr_" + d).remove();
                        retention_sub_aid(d, r[3].report[o][0], x, 0 == o, "1");
                    }
                }
            },
            failure: function(t) {
                loading = false;
                r("Cannot load affiliate information.");
            }
        })
    }
    function retention_sub_aid(e, r, a, d, o) {
        t.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/retention_sub_aid.php",
            data: {
                user_token: k,
                crm_id: crm_id,
                campaign_id: e,
                affiliate_id: r,
                from_date: t("#from_date").val(),
                to_date: t("#to_date").val(),
                cycle: cycle,
                delete: o
            },
            success: function(e) {
                var r = jQuery.parseJSON(e);
                if ("error" != r[0])
                    if ("no_cookie" != r[0]) {
                        var o = ""
                            , n = r[2]
                            , i = r[3];
                        t("#affmark_" + n + "_" + i).html(u),
                        r[4].report.length > 0 && d && t("#affrow_" + n + "_" + i).css("border-bottom", "none");
                        for (var l = 0; l < r[4].report.length; l++) {
                            o = '<tr id="subaff_' + r[4].report[l][0] + '" class="subaff_item_by_' + n + " esubaff_" + n + "_" + i + '">',
                                o += '<td style="border-top:none"></td>',
                                o += '<td style="border-top:none"></td>',
                                o += "<td>(" + r[4].report[l][0] + ") " + r[4].report[l][1] + "</td>",
                                o += '<td style="border-left: 1px solid #dadada">' + r[4].report[l][2] + "</td>",
                                o += "<td>" + r[4].report[l][3] + "</td>",
                                o += "<td>" + r[4].report[l][4] + "</td>",
                                o += "<td>" + r[4].report[l][5] + "</td>",
                                o += "<td>$" + r[4].report[l][6] + "</td>",
                                o += "<td>" + r[4].report[l][7] + "%</td>";
                            for (var s = 1; s < a; s++)
                                o += '<td style="border-left: 1px solid #dadada">' + r[4].report[l][6 * s + 2] + "</td>",
                                    o += "<td>" + r[4].report[l][6 * s + 3] + "</td>",
                                    o += "<td>" + r[4].report[l][6 * s + 4] + "</td>",
                                    o += "<td>" + r[4].report[l][6 * s + 5] + "</td>",
                                    o += "<td>$" + r[4].report[l][6 * s + 6] + "</td>",
                                    o += "<td>" + r[4].report[l][6 * s + 7] + "%</td>";
                            o += "</tr>",
                                t("#affrow_" + n + "_" + i).closest("tr").after(o)
                        }
                    } else
                        window.location.href = "../../admin/login.php";
                else
                    t("#affmark_" + r[2] + "_" + r[2]).html(_)
            },
            failure: function(t) {}
        })
    }
    var loading = !1;
    var c = '<img src="../images/loading.gif" style="width:22px;height:22px;">';
    var _ = '<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color: #ffa5a5"></span>';
    var f = '<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>';
    var minus_sign = '<span class="glyphicon glyphicon-minus-sign" aria-hidden="true" style="color: #ffa5a5"></span>';
    var u = '<span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true" style="color: #ffa5a5"></span>';
    var crm_id = -1;
    var crm_name = "";
    var date_type = "date_custom";
    var from_date = "";
    var to_date = "";
    var cycle = 2;
    var x = 2;
    var k = "";
    var active_crms = t("#active_crms").html();

    get_selected_crm();
    set_dates();
    get_rebill("1");
    t(".crm_dropdown_menu li").on("click", function(e) {
        crm_name = t(this).text(),
            crm_id = t(this).find("a").attr("id"),
            t(".crm_toggle_button").html(crm_name + ' <span class="caret"></span>')
    });
    t(".input-daterange").datepicker({});
    t(".date_dropdown_menu li").on("click", function(e) {
        var r = t(this).text();
        date_type = t(this).find("a").attr("id"),
            t(".date_toggle_button").html(r + ' <span class="caret"></span>'),
            set_dates()
    });
    t(".btn_export").click(function() {
        var e = "./export_quick_retention.php?type=retention&user_token=" + k + "&from_date=" + t("#from_date").val() + "&to_date=" + t("#to_date").val() + "&crm_id=" + crm_id + "&crm_name=" + crm_name;
        window.location.href = e
    });
    t(".cycle_dropdown_menu li").on("click", function(e) {
        cycle = t(this).text(),
            cycle_id = t(this).prop("id").substring(6),
            t(".cycle_toggle_button").html(cycle + ' <span class="caret"></span>')
    });
    t(".retention_search_button").click(function() {
        get_rebill("1")
    });
    t(".table_retention_body").on("click", ".btn_campaign_expand", function(e) {
        if (!loading) {
            var r = "";
            var a = -1 != t(this).html().indexOf("glyphicon-plus-sign");
            var o = t(this).prop("id").substring(5);
            d(o);
            if (a) {
                t(this).html(minus_sign);
                r = '<tr id="waittr_' + o + '">';
                r += '<td style="border-top:none"></td>';
                r += "<td></td>";
                r += "<td>" + c + "</td>";
                r += '<td id="waittd_' + o + '" colspan="' + 6 * x + '"></td>';
                r += "</tr>";
                t(this).closest("tr").after(r);
                retention_aid(o, "1");
            }
            else {
                t(this).html(f);
            }
        }
    });
    t(".table_retention_head").on("click", ".btn_campaign_head", function(e) {
        if (!loading) {
            var r = "";
            var a = -1 != t(this).html().indexOf("glyphicon-plus-sign");
            t(".btn_campaign_expand").each(function(e) {
                var o = t(this).prop("id").substring(5);
                d(o);
                a ? (t(this).html(minus_sign),
                    r = '<tr id="waittr_' + o + '">',
                    r += '<td style="border-top:none"></td>',
                    r += "<td></td>",
                    r += "<td>" + c + "</td>",
                    r += '<td id="waittd_' + o + '" colspan="' + 6 * x + '"></td>',
                    r += "</tr>",
                    t(this).closest("tr").after(r),
                    retention_aid(o, "1")) : t(this).html(f)
            });
            a ? t(this).html(minus_sign) : t(this).html(f);
        }
    });
});
