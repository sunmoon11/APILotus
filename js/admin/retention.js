jQuery(document).ready(function(t) {
    function get_selected_crm() {
        if (t(".crm_dropdown_list").length > 0) {
            crm_id = t(".crm_dropdown_list").prop("id");
            crm_name = t(".crm_dropdown_list").html();
        }
    }
    function r(e) {
        t(".retention_alert").html(e),
            t(".retention_alert").fadeIn(1e3, function() {
                t(".retention_alert").fadeOut(3e3)
            })
    }
    function a(e, r, a) {
        "list" == e && (a ? t(".retention_waiting").html(c) : t(".retention_waiting").html(""))
    }
    function d(e) {
        t(".aff_item_by_" + e).each(function(e) {
            t(this).remove()
        }),
            t("#waittr_" + e).remove(),
            t(".subaff_item_by_" + e).each(function(e) {
                t(this).remove()
            })
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
    }
    function format_date(year, month, date) {
        if (month < 10) month = "0" + month;
        if (date < 10) date = "0" + date;
        return month + "/" + date + "/" + year;
    }
    function i(e) {
        k = (new Date).getTime();
        if ("" == t("#from_date").val()) {
            r("Please select FROM DATE.");
        }
        else if ("" == t("#to_date").val()) {
            r("Please select TO DATE.");
        }
        else {
            a("list", "", !0);
            t.ajax({
                type: "GET",
                url: "../daemon/ajax_admin/retention_list.php",
                data: {
                    user_token: k,
                    crm_id: crm_id,
                    from_date: t("#from_date").val(),
                    to_date: t("#to_date").val(),
                    cycle: cycle,
                    delete: e
                },
                success: function(e) {
                    a("list", "", !1);
                    var d = jQuery.parseJSON(e);
                    if ("error" == d[0])
                        r("Cannot load retention information.");
                    else if ("no_cookie" == d[0])
                        return void (window.location.href = "../../admin/login.php");
                    else {
                        x = d[3].cycle;
                        var o = d[3].report.length;
                        var n = "";
                        var i = "";
                        var l = "";
                        n = "<tr>";
                        n += '<th rowspan="2" style="vertical-align:middle"><button type="button" class="btn btn-link btn-sm btn_campaign_head"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></button></th>';
                        n += '<th rowspan="2" style="vertical-align:middle"></th>';
                        n += '<th rowspan="2" style="vertical-align:middle">Campaign (ID) Name</th>';
                        n += '<th colspan="6" style="border-left: 1px solid #dadada">Initial Cycle</th>';
                        for (s = 1; s < x; s++)
                            n += '<th colspan="6" style="border-left: 1px solid #dadada">Subscription Cycle ' + s + "</th>";
                        n += "</tr>";
                        n += "<tr>";
                        n += '<th style="border-left: 1px solid #dadada">Gross Orders</th>';
                        n += "<th>Net Approved</th>";
                        n += "<th>Void/Full Refund</th>";
                        n += "<th>Partial Refund</th>";
                        n += "<th>Void/Refund Revenue</th>";
                        n += "<th>Approval Rate</th>";
                        for (s = 1; s < x; s++)
                            n += '<th style="border-left: 1px solid #dadada">Gross Orders</th>',
                                n += "<th>Net Approved</th>",
                                n += "<th>Void/Full Refund</th>",
                                n += "<th>Partial Refund</th>",
                                n += "<th>Void/Refund Revenue</th>",
                                n += "<th>Conversion</th>";
                        n += "</tr>";
                        t(".table_retention_head").html(n);
                        if (0 == o)
                            return void r("There is no any retention information.");
                        n = "";
                        for (var s = 0; s < o; s++) {
                            n += '<tr id="camprow_' + d[3].report[s][0] + '" class="camp_tr">',
                                "-1" == d[3].report[s][0] ? (n += "<td></td>",
                                    n += "<td></td>",
                                    n += "<td>" + (i = "<b>") + "Total" + (l = "</b>") + "</td>") : (i = "",
                                    l = "",
                                    "yes" == d[3].report[s][2 + 6 * x] ? n += '<td><button type="button" class="btn btn-link btn-sm btn_campaign_expand" id="camp_' + d[3].report[s][0] + '"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></button></td>' : n += "<td></td>",
                                    n += "<td></td>",
                                    n += "<td>(" + d[3].report[s][0] + ") " + d[3].report[s][1] + "</td>"),
                                n += '<td style="border-left: 1px solid #dadada">' + i + d[3].report[s][2] + l + "</td>",
                                n += "<td>" + i + d[3].report[s][3] + l + "</td>",
                                n += "<td>" + i + d[3].report[s][4] + l + "</td>",
                                n += "<td>" + i + d[3].report[s][5] + l + "</td>",
                                n += "<td>" + i + "$" + d[3].report[s][6] + l + "</td>",
                                n += "<td>" + i + d[3].report[s][7] + "%" + l + "</td>";
                            for (var p = 1; p < x; p++)
                                n += '<td style="border-left: 1px solid #dadada">' + i + d[3].report[s][6 * p + 2] + l + "</td>",
                                    n += "<td>" + i + d[3].report[s][6 * p + 3] + l + "</td>",
                                    n += "<td>" + i + d[3].report[s][6 * p + 4] + l + "</td>",
                                    n += "<td>" + i + d[3].report[s][6 * p + 5] + l + "</td>",
                                    n += "<td>" + i + "$" + d[3].report[s][6 * p + 6] + l + "</td>",
                                    n += "<td>" + i + d[3].report[s][6 * p + 7] + "%" + l + "</td>";
                            n += "</tr>"
                        }
                        t(".table_retention_body").html(n)
                    }
                },
                failure: function(t) {
                    a("list", "", !1),
                        r("Cannot load retention information.")
                }
            })
        }
    }
    function l(e, a) {
        p = !0,
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
                    p = !1;
                    var r = jQuery.parseJSON(e);
                    if ("error" != r[0])
                        if ("no_cookie" != r[0]) {
                            var a = ""
                                , d = r[2];
                            x = r[3].cycle;
                            for (var o = 0; o < r[3].report.length; o++) {
                                a = '<tr id="affrow_' + d + "_" + r[3].report[o][0] + '" class="aff_item_by_' + d + '">',
                                    a += '<td style="border-top:none"></td>',
                                    a += '<td id="affmark_' + d + "_" + r[3].report[o][0] + '">' + c + "</td>",
                                    a += "<td>(" + r[3].report[o][0] + ") " + r[3].report[o][1] + "</td>",
                                    a += '<td style="border-left: 1px solid #dadada">' + r[3].report[o][2] + "</td>",
                                    a += "<td>" + r[3].report[o][3] + "</td>",
                                    a += "<td>" + r[3].report[o][4] + "</td>",
                                    a += "<td>" + r[3].report[o][5] + "</td>",
                                    a += "<td>$" + r[3].report[o][6] + "</td>",
                                    a += "<td>" + r[3].report[o][7] + "%</td>";
                                for (var n = 1; n < x; n++)
                                    a += '<td style="border-left: 1px solid #dadada">' + r[3].report[o][6 * n + 2] + "</td>",
                                        a += "<td>" + r[3].report[o][6 * n + 3] + "</td>",
                                        a += "<td>" + r[3].report[o][6 * n + 4] + "</td>",
                                        a += "<td>" + r[3].report[o][6 * n + 5] + "</td>",
                                        a += "<td>$" + r[3].report[o][6 * n + 6] + "</td>",
                                        a += "<td>" + r[3].report[o][6 * n + 7] + "%</td>";
                                a += "</tr>",
                                    t("#camprow_" + d).closest("tr").after(a),
                                    t("#waittr_" + d).remove(),
                                    s(d, r[3].report[o][0], x, 0 == o, "1")
                            }
                        } else
                            window.location.href = "../../admin/login.php";
                    else
                        t("#waittd_" + r[2]).html(_)
                },
                failure: function(t) {
                    p = !1,
                        r("Cannot load affiliate information.")
                }
            })
    }
    function s(e, r, a, d, o) {
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
    var p = !1;
    var c = '<img src="../images/loading.gif" style="width:22px;height:22px;">';
    var _ = '<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color: #ffa5a5"></span>';
    var f = '<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>';
    var h = '<span class="glyphicon glyphicon-minus-sign" aria-hidden="true" style="color: #ffa5a5"></span>';
    var u = '<span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true" style="color: #ffa5a5"></span>';
    var crm_id = -1;
    var crm_name = "";
    var date_type = "date_thisweek";
    var from_date = "";
    var to_date = "";
    var cycle = 1;
    var x = 1;
    var k = "";
    get_selected_crm();
    set_dates();
    i("1");
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
        i("1")
    });
    t(".table_retention_body").on("click", ".btn_campaign_expand", function(e) {
        if (!p) {
            var r = ""
                , a = -1 != t(this).html().indexOf("glyphicon-plus-sign")
                , o = t(this).prop("id").substring(5);
            d(o),
                a ? (t(this).html(h),
                    r = '<tr id="waittr_' + o + '">',
                    r += '<td style="border-top:none"></td>',
                    r += "<td></td>",
                    r += "<td>" + c + "</td>",
                    r += '<td id="waittd_' + o + '" colspan="' + 6 * x + '"></td>',
                    r += "</tr>",
                    t(this).closest("tr").after(r),
                    l(o, "1")) : t(this).html(f)
        }
    });
    t(".table_retention_head").on("click", ".btn_campaign_head", function(e) {
        if (!p) {
            var r = "";
            var a = -1 != t(this).html().indexOf("glyphicon-plus-sign");
            t(".btn_campaign_expand").each(function(e) {
                var o = t(this).prop("id").substring(5);
                d(o);
                a ? (t(this).html(h),
                    r = '<tr id="waittr_' + o + '">',
                    r += '<td style="border-top:none"></td>',
                    r += "<td></td>",
                    r += "<td>" + c + "</td>",
                    r += '<td id="waittd_' + o + '" colspan="' + 6 * x + '"></td>',
                    r += "</tr>",
                    t(this).closest("tr").after(r),
                    l(o, "1")) : t(this).html(f)
            });
            a ? t(this).html(h) : t(this).html(f);
        }
    });
});
