jQuery(document).ready(function(t) {
    var e;
    var loading_gif = '<img src="../images/loading.gif" style="width:22px;height:22px;">';
    var r = '<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color: #ffa5a5"></span>';
    var crm_list = null;
    var i = -1;
    var kkcrm_active_list = null;
    var s = -1;
    var date_type = "date_thisweek";
    var from_date = "";
    var to_date = "";
    var dashboard_columns = "";
    var crm_positions = "";

    function h(e, a) {
        if (e == "sales") {
            t(".dashboard_sales_alert").html(a);
            t(".dashboard_sales_alert").fadeIn(1000, function() {
                t(".dashboard_sales_alert").fadeOut(3000)
            });
        }
        else if (e == "setting") {
            t(".setting_edit_alert").html(a);
            t(".setting_edit_alert").fadeIn(1000, function () {
                t(".setting_edit_alert").fadeOut(3000)
            });
        }
    }

    function show_loading_status(e, r, d) {
        if (e == "sales") {
            d ? t(".dashboard_sales_waiting").html(loading_gif) : t(".dashboard_sales_waiting").html("")
        }
        else if (e == "crm") {
            d ? (t("#crm1_" + r + "_0").html(loading_gif),
                t("#crm2_" + r + "_0").html(""),
                t("#crm3_" + r + "_0").html(""),
                t("#crm4_" + r + "_0").html(""),
                t("#crm5_" + r + "_0").html(""),
                t("#crm6_" + r + "_0").html(""),
                t("#crm7_" + r + "_0").html(""),
                t("#crm8_" + r + "_0").html(""),
                t("#crm9_" + r + "_0").html(""),
                t("#crm10_" + r + "_0").html(""),
                t(".subrow_" + r).each(function(e) {
                    t(this).remove()
                })) : t("#crm1_" + r + "_0").html("")
        }
        else if (e == "kkcrm") {
            d ? (t("#kkcrm1_" + r + "_0").html(loading_gif),
                t("#kkcrm2_" + r + "_0").html(""),
                t("#kkcrm3_" + r + "_0").html(""),
                t("#kkcrm4_" + r + "_0").html(""),
                t("#kkcrm5_" + r + "_0").html(""),
                t("#kkcrm6_" + r + "_0").html(""),
                t("#kkcrm7_" + r + "_0").html(""),
                t("#kkcrm8_" + r + "_0").html(""),
                t("#kkcrm9_" + r + "_0").html(""),
                t("#kkcrm10_" + r + "_0").html("")) : t("#kkcrm1_" + r + "_0").html("")
        }
    }

    function show_headers() {
        if (dashboard_columns != "")
            for (var e = dashboard_columns.split(","), a = 1; a <= 8; a++) {
                t(".table_overall th:nth-child(" + a + ")").hide();
                t(".table_overall td:nth-child(" + a + ")").hide();
                t(".table_dashboard th:nth-child(" + (a + 3) + ")").hide();
                t(".table_dashboard td:nth-child(" + (a + 3) + ")").hide();

                for (var r = 0; r < e.length; r++) {
                    if (a == e[r]) {
                        t(".table_overall th:nth-child(" + a + ")").show();
                        t(".table_overall td:nth-child(" + a + ")").show();
                        t(".table_dashboard th:nth-child(" + (a + 3) + ")").show();
                        t(".table_dashboard td:nth-child(" + (a + 3) + ")").show();
                        break
                    }
                }
            }
    }

    function b() {
        show_loading_status("sales", "", true);
        t.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/crm_list.php",
            data: {},
            success: function(e) {
                show_loading_status("sales", "", false);
                if ("error" == e) {
                    h("sales", "Cannot load CRM site information.");
                }
                else if ("no_cookie" == e) {
                    window.location.href = "../../admin/login.php";
                }
                else {
                    crm_list = jQuery.parseJSON(e);
                    show_loading_status("sales", "", true);
                    t.ajax({
                        type: "GET",
                        url: "../daemon/ajax_admin/konnektive/crm_active_list.php",
                        data: {},
                        success: function(e) {
                            show_loading_status("sales", "", false);
                            if ("error" == e) {

                            }
                            else if ("no_cookie" == e)
                                return void (window.location.href = "../../admin/login.php");
                            else
                                kkcrm_active_list = jQuery.parseJSON(e);
                            var a = "";
                            if ("" == crm_positions) {
                                for (var r = 0; r < crm_list.length; r++)
                                    a += '<tr id="row_' + crm_list[r][0] + '" class="crm_row" style="border-top: 1px solid #00b9ab !important"><td>' + (r + 1) + "</td>";
                                    a += '<td><span id="ll' + crm_list[r][0] + '" class="payment_badge payment_badge_blue crm_name_row">' + crm_list[r][1] + "</span></td>";
                                    a += '<td id="crm0_' + crm_list[r][0] + '_0">-</td>';
                                    a += '<td id="crm1_' + crm_list[r][0] + '_0"></td>';
                                    a += '<td id="crm2_' + crm_list[r][0] + '_0"></td>';
                                    a += '<td id="crm3_' + crm_list[r][0] + '_0"></td>';
                                    a += '<td id="crm4_' + crm_list[r][0] + '_0"></td>';
                                    a += '<td id="crm5_' + crm_list[r][0] + '_0"></td>';
                                    a += '<td id="crm6_' + crm_list[r][0] + '_0"></td>';
                                    a += '<td id="crm7_' + crm_list[r][0] + '_0"></td>';
                                    a += '<td id="crm8_' + crm_list[r][0] + '_0"></td>';
                                    a += '<td id="crm9_' + crm_list[r][0] + '_0"></td>';
                                    a += '<td id="crm10_' + crm_list[r][0] + '_0"></td>';
                                    a += '<td><button type="button" id="setting_' + crm_list[r][0] + '" class="btn btn-link btn-sm btn_setting" data-toggle="modal" data-target="#setting_edit_modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span></button></td>';
                                    a += '<td><button type="button" id="refresh_' + crm_list[r][0] + '" class="btn btn-link btn-sm btn_refresh"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button></td>';
                                    a += "</tr>";
                                if (null != kkcrm_active_list)
                                    for (var r = 0; r < kkcrm_active_list.length; r++)
                                        a += '<tr id="kkrow_' + kkcrm_active_list[r][0] + '" class="kkcrm_row" style="border-top: 1px solid #00b9ab !important"><td>' + (crm_list.length + r + 1) + "</td>";
                                        a += '<td><span id="kk' + kkcrm_active_list[r][0] + '" class="payment_badge payment_badge_red crm_name_row">' + kkcrm_active_list[r][1] + "</span></td>";
                                        a += '<td id="kkcrm0_' + kkcrm_active_list[r][0] + '_0">-</td>';
                                        a += '<td id="kkcrm1_' + kkcrm_active_list[r][0] + '_0"></td>';
                                        a += '<td id="kkcrm2_' + kkcrm_active_list[r][0] + '_0"></td>';
                                        a += '<td id="kkcrm3_' + kkcrm_active_list[r][0] + '_0"></td>';
                                        a += '<td id="kkcrm4_' + kkcrm_active_list[r][0] + '_0"></td>';
                                        a += '<td id="kkcrm5_' + kkcrm_active_list[r][0] + '_0"></td>';
                                        a += '<td id="kkcrm6_' + kkcrm_active_list[r][0] + '_0"></td>';
                                        a += '<td id="kkcrm7_' + kkcrm_active_list[r][0] + '_0"></td>';
                                        a += '<td id="kkcrm8_' + kkcrm_active_list[r][0] + '_0"></td>';
                                        a += '<td id="kkcrm9_' + kkcrm_active_list[r][0] + '_0"></td>';
                                        a += '<td id="kkcrm10_' + kkcrm_active_list[r][0] + '_0"></td>';
                                        a += "<td></td>";
                                        a += '<td><button type="button" id="refresh_' + kkcrm_active_list[r][0] + '" class="btn btn-link btn-sm btn_kkrefresh"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button></td>';
                                        a += "</tr>";
                            } else {
                                var i = 0;
                                var position_ids = crm_positions.split(",");
                                for (var r = 0; r < position_ids.length; r++) {
                                    var s = position_ids[r].substring(0, 2);
                                    var l = position_ids[r].substring(2);
                                    if ("ll" == s) {
                                        for (var o = 0; o < crm_list.length; o++)
                                            if (l == crm_list[o][0]) {
                                                a += '<tr id="row_' + l + '" class="crm_row" style="border-top: 1px solid #00b9ab !important"><td>' + ++i + "</td>";
                                                a += '<td><span id="ll' + l + '" class="payment_badge payment_badge_blue crm_name_row">' + crm_list[o][1] + "</span></td>";
                                                a += '<td id="crm0_' + l + '_0">-</td>';
                                                a += '<td id="crm1_' + l + '_0"></td>';
                                                a += '<td id="crm2_' + l + '_0"></td>';
                                                a += '<td id="crm3_' + l + '_0"></td>';
                                                a += '<td id="crm4_' + l + '_0"></td>';
                                                a += '<td id="crm5_' + l + '_0"></td>';
                                                a += '<td id="crm6_' + l + '_0"></td>';
                                                a += '<td id="crm7_' + l + '_0"></td>';
                                                a += '<td id="crm8_' + l + '_0"></td>';
                                                a += '<td id="crm9_' + l + '_0"></td>';
                                                a += '<td id="crm10_' + l + '_0"></td>';
                                                a += '<td><button type="button" id="setting_' + l + '" class="btn btn-link btn-sm btn_setting" data-toggle="modal" data-target="#setting_edit_modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span></button></td>';
                                                a += '<td><button type="button" id="refresh_' + l + '" class="btn btn-link btn-sm btn_refresh"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button></td>';
                                                a += "</tr>";
                                                break
                                            }
                                    } else if (null != kkcrm_active_list)
                                        for (var o = 0; o < kkcrm_active_list.length; o++)
                                            if (l == kkcrm_active_list[o][0]) {
                                                a += '<tr id="kkrow_' + l + '" class="kkcrm_row" style="border-top: 1px solid #00b9ab !important"><td>' + ++i + "</td>";
                                                a += '<td><span id="kk' + l + '" class="payment_badge payment_badge_red crm_name_row">' + kkcrm_active_list[o][1] + "</span></td>";
                                                a += '<td id="kkcrm0_' + l + '_0">-</td>';
                                                a += '<td id="kkcrm1_' + l + '_0"></td>';
                                                a += '<td id="kkcrm2_' + l + '_0"></td>';
                                                a += '<td id="kkcrm3_' + l + '_0"></td>';
                                                a += '<td id="kkcrm4_' + l + '_0"></td>';
                                                a += '<td id="kkcrm5_' + l + '_0"></td>';
                                                a += '<td id="kkcrm6_' + l + '_0"></td>';
                                                a += '<td id="kkcrm7_' + l + '_0"></td>';
                                                a += '<td id="kkcrm8_' + l + '_0"></td>';
                                                a += '<td id="kkcrm9_' + l + '_0"></td>';
                                                a += '<td id="kkcrm10_' + l + '_0"></td>';
                                                a += "<td></td>";
                                                a += '<td><button type="button" id="refresh_' + l + '" class="btn btn-link btn-sm btn_kkrefresh"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button></td>';
                                                a += "</tr>";
                                                break
                                            }
                                }
                                for (var r = 0; r < crm_list.length; r++) {
                                    for (var _ = !1, o = 0; o < position_ids.length; o++) {
                                        var s = position_ids[o].substring(0, 2);
                                        var l = position_ids[o].substring(2);
                                        if ("ll" == s && l == crm_list[r][0]) {
                                            _ = true;
                                            break
                                        }
                                    }
                                    _ || (a += '<tr id="row_' + crm_list[r][0] + '" class="crm_row" style="border-top: 1px solid #00b9ab !important"><td>' + (r + 1) + "</td>",
                                        a += '<td><span id="ll' + crm_list[r][0] + '" class="payment_badge payment_badge_blue crm_name_row">' + crm_list[r][1] + "</span></td>",
                                        a += '<td id="crm0_' + crm_list[r][0] + '_0">-</td>',
                                        a += '<td id="crm1_' + crm_list[r][0] + '_0"></td>',
                                        a += '<td id="crm2_' + crm_list[r][0] + '_0"></td>',
                                        a += '<td id="crm3_' + crm_list[r][0] + '_0"></td>',
                                        a += '<td id="crm4_' + crm_list[r][0] + '_0"></td>',
                                        a += '<td id="crm5_' + crm_list[r][0] + '_0"></td>',
                                        a += '<td id="crm6_' + crm_list[r][0] + '_0"></td>',
                                        a += '<td id="crm7_' + crm_list[r][0] + '_0"></td>',
                                        a += '<td id="crm8_' + crm_list[r][0] + '_0"></td>',
                                        a += '<td id="crm9_' + crm_list[r][0] + '_0"></td>',
                                        a += '<td id="crm10_' + crm_list[r][0] + '_0"></td>',
                                        a += '<td><button type="button" id="setting_' + crm_list[r][0] + '" class="btn btn-link btn-sm btn_setting" data-toggle="modal" data-target="#setting_edit_modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span></button></td>',
                                        a += '<td><button type="button" id="refresh_' + crm_list[r][0] + '" class="btn btn-link btn-sm btn_refresh"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button></td>',
                                        a += "</tr>")
                                }
                                if (null != kkcrm_active_list)
                                    for (var r = 0; r < kkcrm_active_list.length; r++) {
                                        for (var _ = !1, o = 0; o < position_ids.length; o++) {
                                            var s = position_ids[o].substring(0, 2);
                                            var l = position_ids[o].substring(2);
                                            if ("kk" == s && l == kkcrm_active_list[r][0]) {
                                                _ = true;
                                                break
                                            }
                                        }
                                        _ || (a += '<tr id="kkrow_' + kkcrm_active_list[r][0] + '" class="kkcrm_row" style="border-top: 1px solid #00b9ab !important"><td>' + (crm_list.length + r + 1) + "</td>",
                                            a += '<td><span id="kk' + kkcrm_active_list[r][0] + '" class="payment_badge payment_badge_red crm_name_row">' + kkcrm_active_list[r][1] + "</span></td>",
                                            a += '<td id="kkcrm0_' + kkcrm_active_list[r][0] + '_0">-</td>',
                                            a += '<td id="kkcrm1_' + kkcrm_active_list[r][0] + '_0"></td>',
                                            a += '<td id="kkcrm2_' + kkcrm_active_list[r][0] + '_0"></td>',
                                            a += '<td id="kkcrm3_' + kkcrm_active_list[r][0] + '_0"></td>',
                                            a += '<td id="kkcrm4_' + kkcrm_active_list[r][0] + '_0"></td>',
                                            a += '<td id="kkcrm5_' + kkcrm_active_list[r][0] + '_0"></td>',
                                            a += '<td id="kkcrm6_' + kkcrm_active_list[r][0] + '_0"></td>',
                                            a += '<td id="kkcrm7_' + kkcrm_active_list[r][0] + '_0"></td>',
                                            a += '<td id="kkcrm8_' + kkcrm_active_list[r][0] + '_0"></td>',
                                            a += '<td id="kkcrm9_' + kkcrm_active_list[r][0] + '_0"></td>',
                                            a += '<td id="kkcrm10_' + kkcrm_active_list[r][0] + '_0"></td>',
                                            a += "<td></td>",
                                            a += '<td><button type="button" id="refresh_' + kkcrm_active_list[r][0] + '" class="btn btn-link btn-sm btn_kkrefresh"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button></td>',
                                            a += "</tr>")
                                    }
                            }
                            t(".table_dashboard_sales_body").html(a);
                            show_headers();
                            if ("date_custom" == date_type) {
                                for (var r = 0; r < crm_list.length; r++)
                                    f(crm_list[r][0], crm_list[r][7]);
                            }
                            else {
                                for (var r = 0; r < crm_list.length; r++)
                                    show_loading_status("crm", crm_list[r][0], true);
                                get_dashboard_sales_db(crm_list);
                            }
                            if (null != kkcrm_active_list)
                                for (var r = 0; r < kkcrm_active_list.length; r++)
                                    g(kkcrm_active_list[r][0], kkcrm_active_list[r][7])
                        },
                        failure: function(e) {
                            show_loading_status("sales", "", false);
                            h("sales", "Cannot load Konnective CRM account information.");
                        }
                    })
                }
            },
            failure: function(t) {
                show_loading_status("sales", "", !1),
                    h("sales", "Cannot load CRM site information.")
            }
        })
    }

    function f(e, a) {
        if ("" == t("#from_date").val()) {
            h("sales", "Please select FROM DATE.")
        }
        else if ("" == t("#to_date").val()) {
            h("sales", "Please select TO DATE.")
        }
        else {
            show_loading_status("crm", e, true);
            t.ajax({
                type: "GET",
                url: "../daemon/ajax_admin/dashboard_sales.php",
                data: {
                    crm_id: e,
                    crm_goal: a,
                    date_type: date_type,
                    from_date: t("#from_date").val(),
                    to_date: t("#to_date").val()
                },
                success: function(e) {
                    var a = jQuery.parseJSON(e);
                    if ("error" == a[0]) {
                        h("sales", "Cannot load sales information.");
                        return void t("#crm1_" + a[1] + "_0").html(r);
                    }
                    if ("no_result" == a[0]) {
                        h("sales", "No result.");
                        return void t("#crm1_" + a[1] + "_0").html(r);
                    }
                    if ("no_cookie" == a[0]) {
                        window.location.href = "../../admin/login.php"
                    }
                    else {
                        for (var d = 0; d < a[3].length; d++) {
                            var label_type = a[3][d][0];
                            var label_name = a[3][d][1];
                            var crm_goal = a[3][d][2];
                            if ("0" != label_type) {
                                var l = "";
                                l += '<tr class="subrow_' + a[1] + '">';
                                l += '<td style="border-top:none"></td>';
                                l += '<td style="border-top:none"></td>';
                                l += '<td id="crm0_' + a[1] + "_" + label_type + '">' + label_name + "</td>";
                                l += '<td id="crm1_' + a[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm2_' + a[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm3_' + a[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm4_' + a[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm5_' + a[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm6_' + a[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm7_' + a[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm8_' + a[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm9_' + a[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm10_' + a[1] + "_" + label_type + '"></td>';
                                l += "<td></td>";
                                l += "<td></td>";
                                l += "</tr>";
                                t("#row_" + a[1]).closest("tr").after(l);
                            }
                            var step1 = parseFloat(a[3][d][3]);
                            var step2 = parseFloat(a[3][d][4]);
                            var tablet_step1 = parseFloat(a[3][d][5]);
                            var prepaid = parseFloat(a[3][d][6]);
                            var step1_nonpp = parseFloat(a[3][d][7]);
                            var step2_nonpp = parseFloat(a[3][d][8]);
                            var order_page = parseFloat(a[3][d][9]);
                            var order_count = parseFloat(a[3][d][10]);
                            var decline = parseFloat(a[3][d][11]);
                            var gross_order = parseFloat(a[3][d][12]);
                            var goal = parseFloat(a[2]);

                            if ("0" == label_type) {
                                var w = '<div class="bar-main-container"><div id="bar_' + a[1] + '" class="bar-percentage">0</div><div class="bar-container"><div class="bar"></div></div></div>';
                                t("#crm9_" + a[1] + "_" + label_type).html(w)
                            } else
                                goal = crm_goal;

                            t("#crm1_" + a[1] + "_" + label_type).html(step1);
                            t("#crm2_" + a[1] + "_" + label_type).html(step2);
                            if (0 != step1) {
                                var x = 100 * step2 / step1;
                                t("#crm3_" + a[1] + "_" + label_type).html(x.toFixed(2))
                            } else
                                t("#crm3_" + a[1] + "_" + label_type).html("0");

                            t("#crm4_" + a[1] + "_" + label_type).html(tablet_step1);
                            if (tablet_step1 + step1 != 0) {
                                var F = 100 * tablet_step1 / (tablet_step1 + step1);
                                t("#crm5_" + a[1] + "_" + label_type).html(F.toFixed(2));
                            } else
                                t("#crm5_" + a[1] + "_" + label_type).html("0");

                            t("#crm6_" + a[1] + "_" + label_type).html(prepaid);
                            if (0 != order_count) {
                                var j = order_page / order_count;
                                t("#crm7_" + a[1] + "_" + label_type).html(j.toFixed(2));
                            } else
                                t("#crm7_" + a[1] + "_" + label_type).html("0");

                            if (0 != gross_order) {
                                var D = decline / gross_order;
                                t("#crm8_" + a[1] + "_" + label_type).html(D.toFixed(2))
                            } else
                                t("#crm8_" + a[1] + "_" + label_type).html("0");

                            t("#crm10_" + a[1] + "_" + label_type).html(step1 + " / " + goal);
                            var M = 0;
                            if (goal > 0 && (M = Math.round(100 * step1 / goal)),
                                "0" == label_type) {
                                var C = t("#bar_" + a[1]);
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
                                }),
                                    k()
                            } else
                                t("#crm9_" + a[1] + "_" + label_type).html(M + "%");
                            show_headers();
                        }
                    }
                },
                failure: function(e) {
                    h("sales", "Cannot load sales information.")
                }
            })
        }
    }

    function get_dashboard_sales_db(crm_list) {
        if ("" == t("#from_date").val()) {
            h("sales", "Please select FROM DATE.")
        }
        else if ("" == t("#to_date").val()) {
            h("sales", "Please select TO DATE.")
        }
        else {
            t.ajax({
                type: "GET",
                url: "../daemon/ajax_admin/dashboard_sales_all.php",
                data: {
                    crm_list: crm_list,
                    from_date: t("#from_date").val(),
                    to_date: t("#to_date").val()
                },
                success: function(e) {
                    var results = jQuery.parseJSON(e);
                    for (var i = 0; i < results.length; i++) {
                        var a = jQuery.parseJSON(results[i]);
                        if ("error" == a[0]) {
                            h("sales", "Cannot load sales information.");
                            t("#crm1_" + a[1] + "_0").html(r);
                            continue;
                        }
                        if ("no_result" == a[0]) {
                            h("sales", "No result.");
                            t("#crm1_" + a[1] + "_0").html(r);
                            continue;
                        }
                        if ("no_cookie" == a[0]) {
                            window.location.href = "../../admin/login.php"
                        }
                        else {
                            for (var d = 0; d < a[3].length; d++) {
                                var label_type = a[3][d][0];
                                var label_name = a[3][d][1];
                                var crm_goal = a[3][d][2];
                                if ("0" != label_type) {
                                    var l = "";
                                    l += '<tr class="subrow_' + a[1] + '">';
                                    l += '<td style="border-top:none"></td>';
                                    l += '<td style="border-top:none"></td>';
                                    l += '<td id="crm0_' + a[1] + "_" + label_type + '">' + label_name + "</td>";
                                    l += '<td id="crm1_' + a[1] + "_" + label_type + '"></td>';
                                    l += '<td id="crm2_' + a[1] + "_" + label_type + '"></td>';
                                    l += '<td id="crm3_' + a[1] + "_" + label_type + '"></td>';
                                    l += '<td id="crm4_' + a[1] + "_" + label_type + '"></td>';
                                    l += '<td id="crm5_' + a[1] + "_" + label_type + '"></td>';
                                    l += '<td id="crm6_' + a[1] + "_" + label_type + '"></td>';
                                    l += '<td id="crm7_' + a[1] + "_" + label_type + '"></td>';
                                    l += '<td id="crm8_' + a[1] + "_" + label_type + '"></td>';
                                    l += '<td id="crm9_' + a[1] + "_" + label_type + '"></td>';
                                    l += '<td id="crm10_' + a[1] + "_" + label_type + '"></td>';
                                    l += "<td></td>";
                                    l += "<td></td>";
                                    l += "</tr>";
                                    t("#row_" + a[1]).closest("tr").after(l);
                                }
                                var step1 = parseFloat(a[3][d][3]);
                                var step2 = parseFloat(a[3][d][4]);
                                var tablet = parseFloat(a[3][d][5]);
                                var prepaid = parseFloat(a[3][d][6]);
                                var step1_nonpp = parseFloat(a[3][d][7]);
                                var step2_nonpp = parseFloat(a[3][d][8]);
                                var order_page = parseFloat(a[3][d][9]);
                                var order_count = parseFloat(a[3][d][10]);
                                var decline = parseFloat(a[3][d][11]);
                                var gross_order = parseFloat(a[3][d][12]);
                                var goal = parseFloat(a[2]);

                                if ("0" == label_type) {
                                    var w = '<div class="bar-main-container"><div id="bar_' + a[1] + '" class="bar-percentage">0</div><div class="bar-container"><div class="bar"></div></div></div>';
                                    t("#crm9_" + a[1] + "_" + label_type).html(w)
                                } else
                                    goal = crm_goal;

                                t("#crm1_" + a[1] + "_" + label_type).html(step1);
                                t("#crm2_" + a[1] + "_" + label_type).html(step2);
                                if (0 != step1) {
                                    var x = 100 * step2 / step1;
                                    t("#crm3_" + a[1] + "_" + label_type).html(x.toFixed(2))
                                } else
                                    t("#crm3_" + a[1] + "_" + label_type).html("0");

                                t("#crm4_" + a[1] + "_" + label_type).html(tablet);
                                if (tablet + step2_nonpp != 0) {
                                    var F = 100 * tablet / (tablet + step2_nonpp);
                                    t("#crm5_" + a[1] + "_" + label_type).html(F.toFixed(2));
                                } else
                                    t("#crm5_" + a[1] + "_" + label_type).html("0");

                                t("#crm6_" + a[1] + "_" + label_type).html(prepaid);
                                if (0 != order_count) {
                                    var j = order_page / order_count;
                                    t("#crm7_" + a[1] + "_" + label_type).html(j.toFixed(2));
                                } else
                                    t("#crm7_" + a[1] + "_" + label_type).html("0");

                                if (0 != gross_order) {
                                    var D = decline / gross_order;
                                    t("#crm8_" + a[1] + "_" + label_type).html(D.toFixed(2))
                                } else
                                    t("#crm8_" + a[1] + "_" + label_type).html("0");

                                t("#crm10_" + a[1] + "_" + label_type).html(step1 + " / " + goal);
                                var M = 0;
                                if (goal > 0 && (M = Math.round(100 * step1 / goal)),
                                    "0" == label_type) {
                                    var C = t("#bar_" + a[1]);
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
                                    }),
                                        k()
                                } else
                                    t("#crm9_" + a[1] + "_" + label_type).html(M + "%");
                                show_headers();
                            }
                        }
                    }
                },
                failure: function(e) {
                    h("sales", "Cannot load sales information.")
                }
            })
        }
    }

    function g(e, a) {
        if ("" == t("#from_date").val()) {
            h("sales", "Please select FROM DATE.")
        }
        else if ("" == t("#to_date").val()) {
            h("sales", "Please select TO DATE.")
        }
        else {
            show_loading_status("kkcrm", e, !0);
            t.ajax({
                type: "GET",
                url: "../daemon/ajax_admin/konnektive/dashboard_sales.php",
                data: {
                    crm_id: e,
                    crm_goal: a,
                    from_date: t("#from_date").val(),
                    to_date: t("#to_date").val()
                },
                success: function(e) {
                    var a = jQuery.parseJSON(e);
                    if ("error" == a[0])
                        return h("sales", "Cannot load konnektive sales information."),
                            void t("#kkcrm1_" + a[1] + "_0").html(r);
                    if ("no_cookie" != a[0]) {
                        var d = parseFloat(a[3][0])
                            , i = parseFloat(a[2])
                            , n = '<div class="bar-main-container"><div id="kkbar_' + a[1] + '" class="bar-percentage">0</div><div class="bar-container"><div class="bar"></div></div></div>';
                        t("#kkcrm9_" + a[1] + "_0").html(n),
                            t("#kkcrm1_" + a[1] + "_0").html(a[3][0]),
                            t("#kkcrm2_" + a[1] + "_0").html(a[3][1]),
                            t("#kkcrm3_" + a[1] + "_0").html(a[3][2]),
                            t("#kkcrm4_" + a[1] + "_0").html(a[3][3]),
                            t("#kkcrm5_" + a[1] + "_0").html(a[3][4]),
                            t("#kkcrm6_" + a[1] + "_0").html(a[3][5]),
                            t("#kkcrm7_" + a[1] + "_0").html(a[3][6]),
                            t("#kkcrm8_" + a[1] + "_0").html(a[3][7]),
                            t("#kkcrm10_" + a[1] + "_0").html(d + " / " + i);
                        var s = 0;
                        i > 0 && (s = Math.round(100 * d / i));
                        var l = t("#kkbar_" + a[1]);
                        t({
                            countNum: 0
                        }).animate({
                            countNum: s
                        }, {
                            duration: 2e3,
                            easing: "linear",
                            step: function() {
                                var t = Math.round(this.countNum) + "%";
                                l.text(t) && l.siblings().children().css("width", t)
                            }
                        })
                    } else
                        window.location.href = "../../admin/login.php"
                },
                failure: function(t) {
                    h("sales", "Cannot load konnektive sales information.")
                }
            })
        }
    }

    function k() {
        var e = 0;
        var a = 0;
        var r = 0;
        var d = 0;
        var i = 0;
        var n = 0;
        var s = 0;
        var l = 0;
        var o = 0;
        var _ = 0;
        var c = 0;
        t(".crm_row").each(function(d) {
            var c = t(this).prop("id").substring(4);
            if (!isNaN(t("#crm1_" + c + "_0").html())) {
                a += parseInt(t("#crm1_" + c + "_0").html()),
                    r += parseInt(t("#crm2_" + c + "_0").html()),
                    i += parseInt(t("#crm4_" + c + "_0").html()),
                    n += parseFloat(t("#crm5_" + c + "_0").html()),
                    s += parseInt(t("#crm6_" + c + "_0").html()),
                    l += parseFloat(t("#crm7_" + c + "_0").html()),
                    o += parseFloat(t("#crm8_" + c + "_0").html());
                var m = t("#crm10_" + c + "_0").html()
                    , h = m.indexOf(" / ");
                _ += parseInt(m.substr(h + 3)),
                    e++
            }
        }),
        _ > 0 && (c = (100 * a / _).toFixed(2)),
        a > 0 && (d = (100 * r / a).toFixed(2)),
        e > 0 && (t("#all1").html(a),
            t("#all2").html(r),
            t("#all3").html(d),
            t("#all4").html(i),
            t("#all5").html((n / e).toFixed(2)),
            t("#all6").html(s),
            t("#all7").html((l / e).toFixed(2)),
            t("#all8").html((o / e).toFixed(2)),
            t("#all9").html(c),
            t("#all10").html(a + " / " + _));
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

    function bulk_update_crm_goal(ids, goals) {
        show_loading_status("sales", "", true);
        t.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/setting_crm_goal.php",
            data: {
                crm_ids: ids,
                crm_goals: goals
            },
            success: function(e) {
                show_loading_status("sales", "", false);
                if ("error" == e) {
                    h("sales", "Cannot update CRM Sales Goal.");
                }
                else if ("no_cookie" == e) {
                    window.location.href = "../../admin/login.php";
                }
                else {
                    b();
                }
            },
            failure: function(t) {
                show_loading_status("sales", "", !1);
                h("sales", "Cannot update CRM Sales Goal.");
            }
        })
    }

    function refresh() {
        t.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/dashboard_refresh.php",
            data: {
                date_type: date_type
            },
            success: function(e) {
                if (1 == e) {
                    b();
                }
                else if ("no_cookie" == e) {
                    window.location.href = "../../admin/login.php";
                }
            },
            failure: function(t) {

            }
        })
    }


    crm_positions = t("#crm_positions").html();
    set_dates();
    show_loading_status("sales", "", true);
    t.ajax({
        type: "GET",
        url: "../daemon/ajax_admin/dashboard_columns_get.php",
        data: {},
        success: function(t) {
            show_loading_status("sales", "", false);
            var e = jQuery.parseJSON(t);
            if ("success" == e[0])
                dashboard_columns = e[1];
            else if ("no_cookie" == e[0])
                return void (window.location.href = "../../admin/login.php");
            b();
        },
        failure: function(t) {
            show_loading_status("sales", "", false);
            h("sales", "Cannot load columns for dashboard.");
        }
    });
    t(".input-daterange").datepicker({});
    t(".date_dropdown_menu li").on("click", function(e) {
        var a = t(this).text();
        date_type = t(this).find("a").attr("id");
        t(".date_toggle_button").html(a + ' <span class="caret"></span>');
        set_dates();
    }),
    t(".sales_search_button").click(function() {
        b();
    }),
    t(".btn_crm_position").click(function() {
        var e = "";
        var a = "";
        var r = 0;
        t(".crm_name_row").each(function(d) {
            var i = t(this).prop("id");
            var n = t(this).html();
            a += '<li style="margin-bottom: 10px"><span class="payment_badge payment_badge_grey">' + ++r + "</span></li>",
                "ll" == i.substring(0, 2) ? e += '<li id="' + i + '" class="position_row" style="cursor: move; margin-bottom: 10px"><span class="payment_badge payment_badge_blue">' + n + "</span></li>" : e += '<li id="' + i + '" class="position_row" style="cursor: move; margin-bottom: 10px"><span class="payment_badge payment_badge_red">' + n + "</span></li>"
        });
        t("#crm_number_ul").html(a);
        t("#crm_number_ul").disableSelection();
        t("#crm_position_ul").html(e);
        t("#crm_position_ul").sortable();
        t("#crm_position_ul").disableSelection();
        t("#crm_position_modal").modal("toggle");
    }),
    t(".modal_btn_crm_position").click(function() {
        var e, a = "";
        t(".position_row").each(function(e) {
            var r = t(this).prop("id");
            a += "" == a ? "" : ",",
                a += r
        }),
            e = a,
            show_loading_status("sales", "", !0),
            t.ajax({
                type: "GET",
                url: "../daemon/ajax_admin/crm_position_set.php",
                data: {
                    crm_positions: e
                },
                success: function(a) {
                    if (show_loading_status("sales", "", !1),
                        "success" == a)
                        crm_positions = e,
                            t("#crm_positions").html(crm_positions),
                            b();
                    else {
                        if ("no_cookie" == a)
                            return void (window.location.href = "../../admin/login.php");
                        h("sales", "Cannot save CRM positions.")
                    }
                },
                failure: function(t) {
                    show_loading_status("sales", "", !1),
                        h("sales", "Cannot save CRM positions.")
                }
            }),
            t("#crm_position_modal").modal("toggle")
    });

    t(".btn_quick_edit").click(function() {
        var html = "";
        for (var i = 0; i < crm_list.length; i++) {
            html += '<div class="row" style="margin-bottom:5px;">';
            html += '<div class="col-xs-4 modal_input_label">' + crm_list[i][1] + "</div>";
            html += '<div class="col-xs-8"><input type="text" id="editgoal_' + crm_list[i][0] + '" class="form-control input-sm edit_goals" value="' + crm_list[i][7] + '"></div>';
            html += "</div>";
        }
        t(".quick_edit_body").html(html);
    });
    t(".modal_btn_apply_goal").click(function() {
        var ids = "";
        var goals = "";
        t(".edit_goals").each(function() {
            "" != ids && (ids += ",");
            "" != goals && (goals += ",");
            ids += t(this).prop("id").substring(9);
            "" == t(this).val() ? goals += "0" : goals += t(this).val();
        });
        t("#quick_edit_modal").modal("toggle");
        bulk_update_crm_goal(ids, goals);
    });

    t(".btn_refresh_all").click(function() {
        b();
    });
    t(".table_dashboard_sales_body").on("click", ".btn_refresh", function(e) {
        i = t(this).prop("id").substring(8);
        for (var a = 0; a < crm_list.length; a++)
            if (crm_list[a][0] == i)
                return void f(i, crm_list[a][7])
    }),
    t(".table_dashboard_sales_body").on("click", ".btn_kkrefresh", function(e) {
        s = t(this).prop("id").substring(8);
        for (var a = 0; a < kkcrm_active_list.length; a++)
            if (kkcrm_active_list[a][0] == s)
                return void g(s, kkcrm_active_list[a][7])
    }),
    t(".table_dashboard_sales_body").on("click", ".btn_setting", function(a) {
        var r;
        i = t(this).prop("id").substring(8),
            r = i,
            t.ajax({
                type: "GET",
                url: "../daemon/ajax_admin/setting_alert_list_by_cid.php",
                data: {
                    crm_id: r
                },
                success: function(a) {
                    var r = jQuery.parseJSON(a);
                    if ("error" != r[0])
                        if ("no_cookie" != r[0]) {
                            var d = (e = r[2]).length
                                , i = "";
                            i += '<div class="row" style="margin-bottom:5px;">',
                                i += '<div class="col-xs-6 modal_input_label"><label>Alert Level Management</label></div>',
                                i += "</div>";
                            for (var n = 0; n < d; n++)
                                "1" == e[n][8] && (i += '<div class="row" style="margin-bottom:5px;">',
                                    i += '<div class="col-xs-6 modal_input_label">' + e[n][3] + "</div>",
                                    i += '<div class="col-xs-6"><input type="text" class="form-control input-sm edit_level_' + e[n][2] + '" value="' + e[n][4] + '"></div>',
                                    i += "</div>");
                            t(".modal_setting_alert_body").html(i)
                        } else
                            window.location.href = "../../admin/login.php";
                    else
                        h("setting", "Cannot load alert level information.")
                },
                failure: function(t) {
                    h("setting", "Cannot load alert level information.")
                }
            });
        for (var n = 0; n < crm_list.length; n++)
            if (crm_list[n][0] == i)
                return t(".edit_crm_name").val(crm_list[n][1]),
                    t(".edit_crm_url").val(crm_list[n][2]),
                    t(".edit_crm_username").val(crm_list[n][3]),
                    t(".edit_api_username").val(crm_list[n][5]),
                    t(".edit_sales_goal").val(crm_list[n][7]),
                    void ("1" == crm_list[n][8] ? t(".edit_crm_paused").prop("checked", !0) : t(".edit_crm_paused").prop("checked", !1))
    }),
    t(".modal_btn_setting_edit").click(function() {
        if ("" == t(".edit_crm_name").val())
            return h("setting", "Please input CRM Name."),
                void t(".edit_crm_name").focus();
        if ("" == t(".edit_crm_url").val())
            return h("setting", "Please input CRM Site URL."),
                void t(".edit_crm_url").focus();
        if ("" == t(".edit_crm_username").val())
            return h("setting", "Please input CRM User Name."),
                void t(".edit_crm_username").focus();
        if ("" == t(".edit_api_username").val())
            return h("setting", "Please input API User Name."),
                void t(".edit_api_username").focus();
        if ("" == t(".edit_sales_goal").val())
            return h("setting", "Please input Sales Goal."),
                void t(".edit_sales_goal").focus();
        for (var a = 0; a < e.length; a++)
            if ("1" == e[a][8] && "" == t(".edit_level_" + e[a][2]).val())
                return h("setting", "Please input Alert level."),
                    void t(".edit_level_" + e[a][2]).focus();
        t("#setting_edit_modal").modal("toggle"),
            function() {
                var e = 0;
                t(".edit_crm_paused").prop("checked") && (e = 1);
                t.ajax({
                    type: "GET",
                    url: "../daemon/ajax_admin/setting_crm_edit.php",
                    data: {
                        crm_id: i,
                        crm_name: t(".edit_crm_name").val(),
                        crm_url: t(".edit_crm_url").val(),
                        crm_username: t(".edit_crm_username").val(),
                        api_username: t(".edit_api_username").val(),
                        sales_goal: t(".edit_sales_goal").val(),
                        crm_paused: e
                    },
                    success: function(t) {
                        if ("success" == t)
                            b();
                        else {
                            if ("no_cookie" == t)
                                return void (window.location.href = "../../admin/login.php");
                            h("sales", "CRM information cannot be changed.")
                        }
                    },
                    failure: function(t) {
                        show_loading_status(!1),
                            h("sales", "CRM information cannot be changed.")
                    }
                })
            }();
        for (a = 0; a < e.length; a++)
            "1" == e[a][8] && (r = e[a][2],
                d = i,
                n = t(".edit_level_" + e[a][2]).val(),
                s = "0",
                t.ajax({
                    type: "GET",
                    url: "../daemon/ajax_admin/setting_alert_edit.php",
                    data: {
                        type: r,
                        crm_id: d,
                        level1: n,
                        level2: s
                    },
                    success: function(t) {
                        if ("error" == t)
                            h("sales", "Alert level cannot be changed.");
                        else if ("no_cookie" == t)
                            return void (window.location.href = "../../admin/login.php")
                    },
                    failure: function(t) {
                        h("sales", "Alert level cannot be changed.")
                    }
                }));
        var r, d, n, s
    }),
    t(".dashboard_alert_body").on("click", ".btn_alert", function(e) {
        var a = t(this).prop("id").substring(6);
        -1 != t(this).html().indexOf("glyphicon-triangle-bottom") ? (t(this).html('<span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>'),
            t("#abody_" + a).slideUp("1000", function() {
                t("#atitle_" + a).css("border-bottom", "1px solid #ebccd1"),
                    t("#atitle_" + a).css("background", "#f9f2f4"),
                    t("#abody_" + a).css("background", "#f9f2f4"),
                    t("#acontent_" + a).css("background", "#f9f2f4")
            })) : (t(this).html('<span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true" style="color: #ffa5a5"></span>'),
            t("#abody_" + a).slideDown("1000"),
            t("#atitle_" + a).css("border-bottom", "none"),
            t("#atitle_" + a).css("background", "#fff"),
            t("#abody_" + a).css("background", "#fff"),
            t("#acontent_" + a).css("background", "#fff"))
    });

    setInterval(function () {
        // refresh();
        b();
    }, 600000);
});
