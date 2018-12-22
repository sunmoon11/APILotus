jQuery(document).ready(function(t) {
    let e;
    let loading_gif = '<img src="../images/loading.gif" style="width:22px;height:22px;">';
    let remove_icon = '<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color: #ffa5a5"></span>';
    let crm_list = null;
    let i = -1;
    let s = -1;
    let date_type = "date_thisweek";
    let from_date = "";
    let to_date = "";
    let dashboard_columns = "";
    let crm_positions = "";

    function show_alert(type, content) {
        if (type == "sales") {
            t(".dashboard_sales_alert").html(content);
            t(".dashboard_sales_alert").fadeIn(1000, function() {
                t(".dashboard_sales_alert").fadeOut(3000)
            });
        }
        else if (type == "setting") {
            t(".setting_edit_alert").html(content);
            t(".setting_edit_alert").fadeIn(1000, function () {
                t(".setting_edit_alert").fadeOut(3000)
            });
        }
    }

    function show_waiting(type, crm_id, show) {
        if (type == "sales") {
            show ? t(".dashboard_sales_waiting").html(loading_gif) : t(".dashboard_sales_waiting").html("");
        }
        else if (type == "crm") {
            show ? (t("#crm1_" + crm_id + "_0").html(loading_gif),
                t("#crm2_" + crm_id + "_0").html(""),
                t("#crm3_" + crm_id + "_0").html(""),
                t("#crm4_" + crm_id + "_0").html(""),
                t("#crm5_" + crm_id + "_0").html(""),
                t("#crm6_" + crm_id + "_0").html(""),
                t("#crm61_" + crm_id + "_0").html(""),
                // t("#crm62_" + crm_id + "_0").html(""),
                t("#crm7_" + crm_id + "_0").html(""),
                t("#crm8_" + crm_id + "_0").html(""),
                t("#crm9_" + crm_id + "_0").html(""),
                t("#crm10_" + crm_id + "_0").html(""),
                t(".subrow_" + crm_id).each(function(e) {
                    t(this).remove()
                })) : t("#crm1_" + crm_id + "_0").html("");
        }
        else if (type == "kkcrm") {
            show ? (t("#kkcrm1_" + crm_id + "_0").html(loading_gif),
                t("#kkcrm2_" + crm_id + "_0").html(""),
                t("#kkcrm3_" + crm_id + "_0").html(""),
                t("#kkcrm4_" + crm_id + "_0").html(""),
                t("#kkcrm5_" + crm_id + "_0").html(""),
                t("#kkcrm6_" + crm_id + "_0").html(""),
                t("#kkcrm7_" + crm_id + "_0").html(""),
                t("#kkcrm8_" + crm_id + "_0").html(""),
                t("#kkcrm9_" + crm_id + "_0").html(""),
                t("#kkcrm10_" + crm_id + "_0").html("")) : t("#kkcrm1_" + crm_id + "_0").html("");
        }
    }

    function show_headers() {
        if (dashboard_columns != "") {
            let columns = dashboard_columns.split(",");
            for (let i = 1; i <= 8; i++) {
                t(".table_overall th:nth-child(" + i + ")").hide();
                t(".table_overall td:nth-child(" + i + ")").hide();
                t(".table_dashboard th:nth-child(" + (i + 3) + ")").hide();
                t(".table_dashboard td:nth-child(" + (i + 3) + ")").hide();

                for (let j = 0; j < columns.length; j++) {
                    if (i == columns[j]) {
                        t(".table_overall th:nth-child(" + i + ")").show();
                        t(".table_overall td:nth-child(" + i + ")").show();
                        t(".table_dashboard th:nth-child(" + (i + 3) + ")").show();
                        t(".table_dashboard td:nth-child(" + (i + 3) + ")").show();
                        break
                    }
                }
            }
        }
    }

    function show_result() {
        show_waiting("sales", "", true);
        t.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/crm_list.php",
            data: {},
            success: function(response) {
                show_waiting("sales", "", false);
                if ("error" === response) {
                    show_alert("sales", "Cannot load CRM site information.");
                }
                else if ("no_cookie" === response) {
                    window.location.href = "../../admin/login.php";
                }
                else {
                    crm_list = jQuery.parseJSON(response);

                    let html = "";
                    if ("" === crm_positions) {
                        for (let r = 0; r < crm_list.length; r++) {
                            html += '<tr id="row_' + crm_list[r][0] + '" class="crm_row" style="border-top: 1px solid #00b9ab !important"><td>' + (r + 1) + "</td>";
                            html += '<td><span id="ll' + crm_list[r][0] + '" class="payment_badge payment_badge_blue crm_name_row">' + crm_list[r][1] + "</span></td>";
                            html += '<td id="crm0_' + crm_list[r][0] + '_0">-</td>';
                            html += '<td id="crm1_' + crm_list[r][0] + '_0"></td>';
                            html += '<td id="crm2_' + crm_list[r][0] + '_0"></td>';
                            html += '<td id="crm3_' + crm_list[r][0] + '_0"></td>';
                            html += '<td id="crm4_' + crm_list[r][0] + '_0"></td>';
                            html += '<td id="crm5_' + crm_list[r][0] + '_0"></td>';
                            html += '<td id="crm6_' + crm_list[r][0] + '_0"></td>';
                            html += '<td id="crm7_' + crm_list[r][0] + '_0"></td>';
                            html += '<td id="crm8_' + crm_list[r][0] + '_0"></td>';
                            html += '<td id="crm61_' + crm_list[r][0] + '_0"></td>';
                            // html += '<td id="crm62_' + crm_list[r][0] + '_0"></td>';
                            html += '<td id="crm9_' + crm_list[r][0] + '_0"></td>';
                            html += '<td id="crm10_' + crm_list[r][0] + '_0"></td>';
                            html += '<td id="updated_' + crm_list[r][0] + '_0"></td>';
                            html += '<td><button type="button" id="setting_' + crm_list[r][0] + '" class="btn btn-link btn-sm btn_setting" data-toggle="modal" data-target="#setting_edit_modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span></button></td>';
                            html += '<td><button type="button" id="refresh_' + crm_list[r][0] + '" class="btn btn-link btn-sm btn_refresh"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button></td>';
                            html += "</tr>";
                        }
                    }
                    else {
                        let i = 0;
                        let position_ids = crm_positions.split(",");
                        for (let r = 0; r < position_ids.length; r++) {
                            let s = position_ids[r].substring(0, 2);
                            let l = position_ids[r].substring(2);
                            if ("ll" === s) {
                                for (let o = 0; o < crm_list.length; o++) {
                                    if (l === crm_list[o][0]) {
                                        html += '<tr id="row_' + l + '" class="crm_row" style="border-top: 1px solid #00b9ab !important"><td>' + ++i + "</td>";
                                        html += '<td><span id="ll' + l + '" class="payment_badge payment_badge_blue crm_name_row">' + crm_list[o][1] + "</span></td>";
                                        html += '<td id="crm0_' + l + '_0">-</td>';
                                        html += '<td id="crm1_' + l + '_0"></td>';
                                        html += '<td id="crm2_' + l + '_0"></td>';
                                        html += '<td id="crm3_' + l + '_0"></td>';
                                        html += '<td id="crm4_' + l + '_0"></td>';
                                        html += '<td id="crm5_' + l + '_0"></td>';
                                        html += '<td id="crm6_' + l + '_0"></td>';
                                        html += '<td id="crm7_' + l + '_0"></td>';
                                        html += '<td id="crm8_' + l + '_0"></td>';
                                        html += '<td id="crm61_' + l + '_0"></td>';
                                        // html += '<td id="crm62_' + l + '_0"></td>';
                                        html += '<td id="crm9_' + l + '_0"></td>';
                                        html += '<td id="crm10_' + l + '_0"></td>';
                                        html += '<td id="updated_' + l + '_0"></td>';
                                        html += '<td><button type="button" id="setting_' + l + '" class="btn btn-link btn-sm btn_setting" data-toggle="modal" data-target="#setting_edit_modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span></button></td>';
                                        html += '<td><button type="button" id="refresh_' + l + '" class="btn btn-link btn-sm btn_refresh"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button></td>';
                                        html += "</tr>";
                                        break;
                                    }
                                }
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
                            _ || (html += '<tr id="row_' + crm_list[r][0] + '" class="crm_row" style="border-top: 1px solid #00b9ab !important"><td>' + (r + 1) + "</td>",
                                html += '<td><span id="ll' + crm_list[r][0] + '" class="payment_badge payment_badge_blue crm_name_row">' + crm_list[r][1] + "</span></td>",
                                html += '<td id="crm0_' + crm_list[r][0] + '_0">-</td>',
                                html += '<td id="crm1_' + crm_list[r][0] + '_0"></td>',
                                html += '<td id="crm2_' + crm_list[r][0] + '_0"></td>',
                                html += '<td id="crm3_' + crm_list[r][0] + '_0"></td>',
                                html += '<td id="crm4_' + crm_list[r][0] + '_0"></td>',
                                html += '<td id="crm5_' + crm_list[r][0] + '_0"></td>',
                                html += '<td id="crm6_' + crm_list[r][0] + '_0"></td>',
                                html += '<td id="crm7_' + crm_list[r][0] + '_0"></td>',
                                html += '<td id="crm8_' + crm_list[r][0] + '_0"></td>',
                                html += '<td id="crm61_' + crm_list[r][0] + '_0"></td>',
                                // html += '<td id="crm62_' + crm_list[r][0] + '_0"></td>',
                                html += '<td id="crm9_' + crm_list[r][0] + '_0"></td>',
                                html += '<td id="crm10_' + crm_list[r][0] + '_0"></td>',
                                html += '<td id="updated_' + crm_list[r][0] + '_0"></td>',
                                html += '<td><button type="button" id="setting_' + crm_list[r][0] + '" class="btn btn-link btn-sm btn_setting" data-toggle="modal" data-target="#setting_edit_modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span></button></td>',
                                html += '<td><button type="button" id="refresh_' + crm_list[r][0] + '" class="btn btn-link btn-sm btn_refresh"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button></td>',
                                html += "</tr>")
                        }
                    }
                    t(".table_dashboard_sales_body").html(html);
                    show_headers();
                    if ("date_custom" == date_type) {
                        for (let r = 0; r < crm_list.length; r++)
                            get_dahsboard_sales(crm_list[r][0], crm_list[r][7]);
                    }
                    else {
                        for (let r = 0; r < crm_list.length; r++)
                            show_waiting("crm", crm_list[r][0], true);
                        get_dashboard_sales_db(crm_list);
                    }
                }
            },
            failure: function() {
                show_waiting("sales", "", false);
                show_alert("sales", "Cannot load CRM site information.")
            }
        })
    }

    function get_dahsboard_sales(e, a) {
        if ("" == t("#from_date").val()) {
            show_alert("sales", "Please select FROM DATE.")
        }
        else if ("" == t("#to_date").val()) {
            show_alert("sales", "Please select TO DATE.")
        }
        else {
            show_waiting("crm", e, true);
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
                    let sales = jQuery.parseJSON(e);
                    if ("error" == sales[0]) {
                        show_alert("sales", "Cannot load sales information.");
                        return void t("#crm1_" + sales[1] + "_0").html(remove_icon);
                    }
                    if ("no_result" == sales[0]) {
                        // show_alert("sales", "No result.");
                        return void t("#crm1_" + sales[1] + "_0").html('No result');
                    }
                    if ("no_cookie" == sales[0]) {
                        window.location.href = "../../admin/login.php"
                    }
                    else {
                        for (var d = 0; d < sales[3].length; d++) {
                            var label_type = sales[3][d][0];
                            var label_name = sales[3][d][1];
                            var crm_goal = sales[3][d][2];
                            if ("0" != label_type) {
                                var l = "";
                                l += '<tr class="subrow_' + sales[1] + '">';
                                l += '<td style="border-top:none"></td>';
                                l += '<td style="border-top:none"></td>';
                                l += '<td id="crm0_' + sales[1] + "_" + label_type + '">' + label_name + "</td>";
                                l += '<td id="crm1_' + sales[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm2_' + sales[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm3_' + sales[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm4_' + sales[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm5_' + sales[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm6_' + sales[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm7_' + sales[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm8_' + sales[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm61_' + sales[1] + "_" + label_type + '"></td>';
                                // l += '<td id="crm62_' + sales[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm9_' + sales[1] + "_" + label_type + '"></td>';
                                l += '<td id="crm10_' + sales[1] + "_" + label_type + '"></td>';
                                l += "<td></td>";
                                l += "<td></td>";
                                l += "</tr>";
                                t("#row_" + sales[1]).closest("tr").after(l);
                            }
                            var step1 = parseFloat(sales[3][d][3]);
                            var step2 = parseFloat(sales[3][d][4]);
                            var tablet_step1 = parseFloat(sales[3][d][5]);
                            var prepaid = parseFloat(sales[3][d][6]);
                            var prepaid_step1 = parseFloat(sales[3][d]['prepaid_step1']);
                            var prepaid_step2 = parseFloat(sales[3][d]['prepaid_step2']);
                            var step1_nonpp = parseFloat(sales[3][d][7]);
                            var step2_nonpp = parseFloat(sales[3][d][8]);
                            var order_page = parseFloat(sales[3][d][9]);
                            var order_count = parseFloat(sales[3][d][10]);
                            var decline = parseFloat(sales[3][d][11]);
                            var gross_order = parseFloat(sales[3][d][12]);
                            var goal = parseFloat(sales[2]);

                            if ("0" == label_type) {
                                var w = '<div class="bar-main-container"><div id="bar_' + sales[1] + '" class="bar-percentage">0</div><div class="bar-container"><div class="bar"></div></div></div>';
                                t("#crm9_" + sales[1] + "_" + label_type).html(w)
                            } else
                                goal = crm_goal;

                            t("#crm1_" + sales[1] + "_" + label_type).html(step1);
                            t("#crm2_" + sales[1] + "_" + label_type).html(step2);
                            if (0 != step1) {
                                var x = 100 * step2 / step1;
                                t("#crm3_" + sales[1] + "_" + label_type).html(x.toFixed(2))
                            } else
                                t("#crm3_" + sales[1] + "_" + label_type).html("0");

                            t("#crm4_" + sales[1] + "_" + label_type).html(tablet_step1);
                            if (tablet_step1 + step1 != 0) {
                                var F = 100 * tablet_step1 / (tablet_step1 + step1);
                                t("#crm5_" + sales[1] + "_" + label_type).html(F.toFixed(2));
                            } else
                                t("#crm5_" + sales[1] + "_" + label_type).html("0");

                            t("#crm6_" + sales[1] + "_" + label_type).html(prepaid_step1);
                            if (prepaid != 0)
                                t("#crm61_" + sales[1] + "_" + label_type).html((prepaid_step1 * 100 / prepaid).toFixed(2));
                            else
                                t("#crm61_" + sales[1] + "_" + label_type).html("0");
                            // t("#crm62_" + sales[1] + "_" + label_type).html((prepaid_step2 * 100 / prepaid).toFixed(2));
                            if (0 != order_count) {
                                var j = order_page / order_count;
                                t("#crm7_" + sales[1] + "_" + label_type).html(j.toFixed(2));
                            } else
                                t("#crm7_" + sales[1] + "_" + label_type).html("0");

                            if (0 != gross_order) {
                                var D = decline / gross_order;
                                t("#crm8_" + sales[1] + "_" + label_type).html(D.toFixed(2))
                            } else
                                t("#crm8_" + sales[1] + "_" + label_type).html("0");

                            t("#crm10_" + sales[1] + "_" + label_type).html(step1 + " / " + goal);
                            var M = 0;
                            if (goal > 0 && (M = Math.round(100 * step1 / goal)),
                                "0" == label_type) {
                                var C = t("#bar_" + sales[1]);
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
                                    calculate_total()
                            } else
                                t("#crm9_" + sales[1] + "_" + label_type).html(M + "%");
                            show_headers();
                        }
                    }
                },
                failure: function(e) {
                    show_alert("sales", "Cannot load sales information.")
                }
            })
        }
    }

    function get_dashboard_sales_db(crm_list) {
        if ("" === t("#from_date").val()) {
            show_alert("sales", "Please select FROM DATE.")
        }
        else if ("" === t("#to_date").val()) {
            show_alert("sales", "Please select TO DATE.")
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
                    let results = jQuery.parseJSON(e);
                    for (let i = 0; i < results.length; i++) {
                        let sales = jQuery.parseJSON(results[i]);
                        if ("error" === sales[0]) {
                            show_alert("sales", "Cannot load sales information.");
                            t("#crm1_" + sales[1] + "_0").html(remove_icon);
                        }
                        else if ("no_result" === sales[0]) {
                            // show_alert("sales", "No result.");
                            t("#crm1_" + sales[1] + "_0").html('No result');
                        }
                        else if ("no_cookie" === sales[0]) {
                            window.location.href = "../../admin/login.php"
                        }
                        else {
                            for (let i = 0; i < sales[3].length; i++) {
                                let label_type = sales[3][i][0];
                                let label_name = sales[3][i][1];
                                let crm_goal = sales[3][i][2];
                                if ("0" !== label_type) {
                                    let html = "";
                                    html += '<tr class="subrow_' + sales[1] + '">';
                                    html += '<td style="border-top:none"></td>';
                                    html += '<td style="border-top:none"></td>';
                                    html += '<td id="crm0_' + sales[1] + "_" + label_type + '">' + label_name + "</td>";
                                    html += '<td id="crm1_' + sales[1] + "_" + label_type + '"></td>';
                                    html += '<td id="crm2_' + sales[1] + "_" + label_type + '"></td>';
                                    html += '<td id="crm3_' + sales[1] + "_" + label_type + '"></td>';
                                    html += '<td id="crm4_' + sales[1] + "_" + label_type + '"></td>';
                                    html += '<td id="crm5_' + sales[1] + "_" + label_type + '"></td>';
                                    html += '<td id="crm6_' + sales[1] + "_" + label_type + '"></td>';
                                    html += '<td id="crm7_' + sales[1] + "_" + label_type + '"></td>';
                                    html += '<td id="crm8_' + sales[1] + "_" + label_type + '"></td>';
                                    html += '<td id="crm61_' + sales[1] + "_" + label_type + '"></td>';
                                    // html += '<td id="crm62_' + sales[1] + "_" + label_type + '"></td>';
                                    html += '<td id="crm9_' + sales[1] + "_" + label_type + '"></td>';
                                    html += '<td id="crm10_' + sales[1] + "_" + label_type + '"></td>';
                                    html += "<td></td>";
                                    html += "<td></td>";
                                    html += "</tr>";
                                    t("#row_" + sales[1]).closest("tr").after(html);
                                }
                                let step1 = parseFloat(sales[3][i][3]);
                                let step2 = parseFloat(sales[3][i][4]);
                                let tablet = parseFloat(sales[3][i][5]);
                                let prepaid = parseFloat(sales[3][i][6]);
                                let prepaid_step1 = parseFloat(sales[3][i]['prepaid_step1']);
                                let prepaid_step2 = parseFloat(sales[3][i]['prepaid_step2']);
                                let step1_nonpp = parseFloat(sales[3][i][7]);
                                let step2_nonpp = parseFloat(sales[3][i][8]);
                                let order_page = parseFloat(sales[3][i][9]);
                                let order_count = parseFloat(sales[3][i][10]);
                                let decline = parseFloat(sales[3][i][11]);
                                let gross_order = parseFloat(sales[3][i][12]);
                                let goal = parseFloat(sales[2]);

                                if ("0" === label_type) {
                                    let html = '<div class="bar-main-container"><div id="bar_' + sales[1] + '" class="bar-percentage">0</div><div class="bar-container"><div class="bar"></div></div></div>';
                                    t("#crm9_" + sales[1] + "_" + label_type).html(html)
                                } else {
                                    goal = crm_goal;
                                }

                                t("#crm1_" + sales[1] + "_" + label_type).html(step1);
                                t("#crm2_" + sales[1] + "_" + label_type).html(step2);
                                if (0 != step1) {
                                    var x = 100 * step2 / step1;
                                    t("#crm3_" + sales[1] + "_" + label_type).html(x.toFixed(2))
                                } else
                                    t("#crm3_" + sales[1] + "_" + label_type).html("0");

                                t("#crm4_" + sales[1] + "_" + label_type).html(tablet);
                                if (tablet + step2_nonpp != 0) {
                                    var F = 100 * tablet / (tablet + step2_nonpp);
                                    t("#crm5_" + sales[1] + "_" + label_type).html(F.toFixed(2));
                                } else
                                    t("#crm5_" + sales[1] + "_" + label_type).html("0");

                                t("#crm6_" + sales[1] + "_" + label_type).html(prepaid_step1);
                                if (prepaid != 0)
                                    t("#crm61_" + sales[1] + "_" + label_type).html((prepaid_step1 * 100 / prepaid).toFixed(2));
                                else
                                    t("#crm61_" + sales[1] + "_" + label_type).html("0");
                                // t("#crm62_" + sales[1] + "_" + label_type).html((prepaid_step2 * 100 / prepaid).toFixed(2));
                                if (0 != order_count) {
                                    var j = order_page / order_count;
                                    t("#crm7_" + sales[1] + "_" + label_type).html(j.toFixed(2));
                                } else
                                    t("#crm7_" + sales[1] + "_" + label_type).html("0");

                                if (0 != gross_order) {
                                    var D = decline / gross_order;
                                    t("#crm8_" + sales[1] + "_" + label_type).html(D.toFixed(2))
                                } else
                                    t("#crm8_" + sales[1] + "_" + label_type).html("0");

                                t("#crm10_" + sales[1] + "_" + label_type).html(step1 + " / " + goal);
                                t("#updated_" + sales[1] + "_" + label_type).html(sales[3][i].timestamp);

                                let goal_percent = goal > 0 ? Math.round(100 * step1 / goal) : 0;
                                if ("0" === label_type) {
                                    let goal_div = t("#bar_" + sales[1]);
                                    t({
                                        countNum: goal_percent
                                    }).animate({
                                        countNum: goal_percent
                                    }, {
                                        duration: 2e3,
                                        easing: "linear",
                                        step: function () {
                                            let t = this.countNum + "%";
                                            goal_div.text(t) && goal_div.siblings().children().css("width", t)
                                        }
                                    });
                                    calculate_total();
                                } else {
                                    t("#crm9_" + sales[1] + "_" + label_type).html(goal_percent + "%");
                                }
                                show_headers();
                            }
                        }
                    }
                },
                failure: function(e) {
                    show_alert("sales", "Cannot load sales information.")
                }
            })
        }
    }

    function g(e, a) {
        if ("" == t("#from_date").val()) {
            show_alert("sales", "Please select FROM DATE.")
        }
        else if ("" == t("#to_date").val()) {
            show_alert("sales", "Please select TO DATE.")
        }
        else {
            show_waiting("kkcrm", e, !0);
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
                        return show_alert("sales", "Cannot load konnektive sales information."),
                            void t("#kkcrm1_" + a[1] + "_0").html(remove_icon);
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
                    show_alert("sales", "Cannot load konnektive sales information.")
                }
            })
        }
    }

    function calculate_total() {
        let count = 0;
        let step1 = 0;
        let step2 = 0;
        let tablet = 0;
        let tablet_percent = 0;
        let prepaids = 0;
        let order_percent = 0;
        let decline_percent = 0;
        let goal = 0;
        t(".crm_row").each(function() {
            let crm_id = t(this).prop("id").substring(4);
            if (!isNaN(t("#crm1_" + crm_id + "_0").html())) {
                step1 += parseInt(t("#crm1_" + crm_id + "_0").html());
                step2 += parseInt(t("#crm2_" + crm_id + "_0").html());
                tablet += parseInt(t("#crm4_" + crm_id + "_0").html());
                tablet_percent += parseFloat(t("#crm5_" + crm_id + "_0").html());
                prepaids += parseInt(t("#crm6_" + crm_id + "_0").html());
                order_percent += parseFloat(t("#crm7_" + crm_id + "_0").html());
                decline_percent += parseFloat(t("#crm8_" + crm_id + "_0").html());
                goal += parseInt(t("#crm10_" + crm_id + "_0").html().split(' / ')[1]);
                count++;
            }
        });
        let c = 0;
        let d = 0;
        goal > 0 && (c = (100 * step1 / goal).toFixed(2)),
        step1 > 0 && (d = (100 * step2 / step1).toFixed(2)),
        count > 0 && (t("#all1").html(step1),
            t("#all2").html(step2),
            t("#all3").html(d),
            t("#all4").html(tablet),
            t("#all5").html((tablet_percent / count).toFixed(2)),
            t("#all6").html(prepaids),
            t("#all7").html((order_percent / count).toFixed(2)),
            t("#all8").html((decline_percent / count).toFixed(2)),
            t("#all9").html(c),
            t("#all10").html(step1 + " / " + goal));
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
            let r = cur_date.getDate() + 1;
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
            let r = cur_date.getDate() + 1 - 7;
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
        show_waiting("sales", "", true);
        t.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/setting_crm_goal.php",
            data: {
                crm_ids: ids,
                crm_goals: goals
            },
            success: function(e) {
                show_waiting("sales", "", false);
                if ("error" == e) {
                    show_alert("sales", "Cannot update CRM Sales Goal.");
                }
                else if ("no_cookie" == e) {
                    window.location.href = "../../admin/login.php";
                }
                else {
                    show_result();
                }
            },
            failure: function(t) {
                show_waiting("sales", "", !1);
                show_alert("sales", "Cannot update CRM Sales Goal.");
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
                    show_result();
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

    // get dashboard_columns
    show_waiting("sales", "", true);
    t.ajax({
        type: "GET",
        url: "../daemon/ajax_admin/dashboard_columns_get.php",
        data: {},
        success: function(data) {
            show_waiting("sales", "", false);
            let result = jQuery.parseJSON(data);
            if ("success" == result[0]) {
                dashboard_columns = result[1];
                show_result();
            }
            else if ("no_cookie" == result[0]) {
                window.location.href = "../../admin/login.php";
            }
        },
        failure: function() {
            show_waiting("sales", "", false);
            show_alert("sales", "Cannot load columns for dashboard.");
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
        show_result();
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
            show_waiting("sales", "", !0),
            t.ajax({
                type: "GET",
                url: "../daemon/ajax_admin/crm_position_set.php",
                data: {
                    crm_positions: e
                },
                success: function(a) {
                    if (show_waiting("sales", "", !1),
                        "success" == a)
                        crm_positions = e,
                            t("#crm_positions").html(crm_positions),
                            show_result();
                    else {
                        if ("no_cookie" == a)
                            return void (window.location.href = "../../admin/login.php");
                        show_alert("sales", "Cannot save CRM positions.")
                    }
                },
                failure: function(t) {
                    show_waiting("sales", "", !1),
                        show_alert("sales", "Cannot save CRM positions.")
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
        show_result();
    });
    t(".table_dashboard_sales_body").on("click", ".btn_refresh", function(e) {
        i = t(this).prop("id").substring(8);
        for (var a = 0; a < crm_list.length; a++)
            if (crm_list[a][0] == i)
                return void get_dahsboard_sales(i, crm_list[a][7])
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
                        show_alert("setting", "Cannot load alert level information.")
                },
                failure: function(t) {
                    show_alert("setting", "Cannot load alert level information.")
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
            return show_alert("setting", "Please input CRM Name."),
                void t(".edit_crm_name").focus();
        if ("" == t(".edit_crm_url").val())
            return show_alert("setting", "Please input CRM Site URL."),
                void t(".edit_crm_url").focus();
        if ("" == t(".edit_crm_username").val())
            return show_alert("setting", "Please input CRM User Name."),
                void t(".edit_crm_username").focus();
        if ("" == t(".edit_api_username").val())
            return show_alert("setting", "Please input API User Name."),
                void t(".edit_api_username").focus();
        if ("" == t(".edit_sales_goal").val())
            return show_alert("setting", "Please input Sales Goal."),
                void t(".edit_sales_goal").focus();
        for (var a = 0; a < e.length; a++)
            if ("1" == e[a][8] && "" == t(".edit_level_" + e[a][2]).val())
                return show_alert("setting", "Please input Alert level."),
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
                            show_result();
                        else {
                            if ("no_cookie" == t)
                                return void (window.location.href = "../../admin/login.php");
                            show_alert("sales", "CRM information cannot be changed.")
                        }
                    },
                    failure: function(t) {
                        show_waiting(!1),
                            show_alert("sales", "CRM information cannot be changed.")
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
                            show_alert("sales", "Alert level cannot be changed.");
                        else if ("no_cookie" == t)
                            return void (window.location.href = "../../admin/login.php")
                    },
                    failure: function(t) {
                        show_alert("sales", "Alert level cannot be changed.")
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
        show_result();
    }, 600000);
});
