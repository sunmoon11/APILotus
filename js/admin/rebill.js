jQuery(document).ready(function(t) {
    function get_selected_crm() {
        if (t(".crm_dropdown_list").length > 0) {
            crm_id = t(".crm_dropdown_list").prop("id");
            crm_name = t(".crm_dropdown_list").html();
        }
    }
    function show_alert(e) {
        t(".rebill_alert").html(e);
        t(".rebill_alert").fadeIn(1e3, function() {
            t(".rebill_alert").fadeOut(3e3);
        });
    }
    function show_status(e, r, a) {
        "list" == e && (a ? t(".rebill_waiting").html(loading_gif) : t(".rebill_waiting").html(""));
    }
    function set_dates() {
        t("#from_date").prop("disabled", true);
        t("#to_date").prop("disabled", true);
        var cur_date = new Date;
        var formatted_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
        if ("date_fullweek" == date_type) {
            var r = cur_date.getDate() - 21;
            0 == cur_date.getDay() ? r -= 7 : r -= cur_date.getDay();
            cur_date.setDate(r);
            from_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
            r = cur_date.getDate() + 6;
            cur_date.setDate(r);
            to_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());

            t("#rebill_date").val(formatted_date);
            t("#rebill_date_label").show();
            t("#rebill_date").show();
        } else if ("date_custom" == date_type) {
            from_date = "";
            to_date = "";
            t("#rebill_date_label").hide();
            t("#rebill_date").hide();
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
    function get_rebill() {
        show_status("list", "", true);
        t(".table_rebill_body").html("");
        t("#id_head_sign").html(loading_gif);
        t("#id_campaign_title").html(crm_name);
        if ("" == t("#from_date").val()) {
            show_alert("Please select FROM DATE.");
        }
        else if ("" == t("#to_date").val()) {
            show_alert("Please select TO DATE.");
        }
        else {
            t.ajax({
                type: "GET",
                url: "../daemon/ajax_admin/rebill_list.php",
                data: {
                    crm_id: crm_id,
                    from_date: t("#from_date").val(),
                    to_date: t("#to_date").val()
                },
                success: function(e) {
                    var data = jQuery.parseJSON(e);
                    if ("error" === data[0])
                        show_alert("Cannot load rebill information.");
                    else if ("no_cookie" === data[0])
                        return void (window.location.href = "../../admin/login.php");
                    else {
                        show_status("list", "", false);
                        t("#id_head_sign").html("");
                        var total_length = data[2].length;

                        if (0 === total_length)
                            return void show_alert("There is no rebill information.");

                        var result_html = "";
                        for (var campaign_id = 0; campaign_id < total_length; campaign_id++) {
                            var campaign = data[2][campaign_id][0];

                            result_html += '<tr id="camprow_' + campaign[0] + '" class="camp_tr">';
                            if (-1 === campaign[0]) {
                                result_html += "<td></td>";
                                result_html += "<td></td>";
                                result_html += "<td><b>" + "Total" + "</b></td>";
                            }
                            else {
                                result_html += '<td>' + minus_sign + '</td>';
                                result_html += "<td></td>";
                                result_html += "<td>(" + campaign[0] + ") " + campaign[1] + "</td>";
                            }
                            result_html += '<td style="border-left: 1px solid #dadada">' + campaign[2] + "</td>";
                            result_html += "<td>" + campaign[3] + "%" + "</td>";
                            var color = campaign[4] < 65.0 ? 'red': campaign[4] < 80.0 ? 'yellow': 'green';
                            result_html += '<td style="background-color: ' + color + '"><b>' + campaign[4] + '%' + '</b></td>';
                            result_html += "</tr>";

                            var affiliates = data[2][campaign_id][1];
                            for (var affiliate_id = 0; affiliate_id < affiliates.length; affiliate_id++) {
                                var affiliate = affiliates[affiliate_id][0];

                                var a = '<tr id="affrow_' + campaign[0] + "_" + affiliate[0] + '" class="aff_item_by_' + campaign[0] + '">';
                                a += '<td style="border-top:none"></td>';
                                a += '<td id="affmark_' + campaign[0] + "_" + affiliate[0] + '">' + triangle_sign + "</td>";
                                a += "<td>(" + affiliate[0] + ") " + affiliate[1] + "</td>";
                                a += '<td style="border-left: 1px solid #dadada">' + affiliate[2] + "</td>";
                                a += "<td>" + affiliate[3] + "%</td>";
                                color = affiliate[4] < 65.0 ? 'red': affiliate[4] < 80.0 ? 'yellow': 'green';
                                a += '<td style="background-color: ' + color + '">' + affiliate[4] + '%</td>';
                                a += "</tr>";
                                result_html += a;

                                var sub_affiliates = affiliates[affiliate_id][1];
                                sub_affiliates.length > 0 && 0 === affiliate_id && t("#affrow_" + campaign[0] + "_" + affiliate[0]).css("border-bottom", "none");
                                for (var sub_affiliate_id = 0; sub_affiliate_id < sub_affiliates.length; sub_affiliate_id++) {
                                    var sub_affiliate = sub_affiliates[sub_affiliate_id];

                                    var o = '<tr id="subaff_' + sub_affiliate[0] + '" class="subaff_item_by_' + campaign[0] + " esubaff_" + campaign[0] + "_" + affiliate[0] + '">';
                                    o += '<td style="border-top:none"></td>';
                                    o += '<td style="border-top:none"></td>';
                                    o += "<td>(" + sub_affiliate[0] + ") " + sub_affiliate[1] + "</td>";
                                    o += '<td style="border-left: 1px solid #dadada">' + sub_affiliate[2] + "</td>";
                                    o += "<td>" + sub_affiliate[3] + "%</td>";
                                    color = sub_affiliate[4] < 65.0 ? 'red': sub_affiliate[4] < 80.0 ? 'yellow': 'green';
                                    o += '<td style="background-color: ' + color + '">' + sub_affiliate[4] + '%</td>';
                                    o += "</tr>";
                                    result_html += o;
                                }
                            }
                        }
                        t(".table_rebill_body").html(result_html);
                    }
                },
                failure: function(t) {
                    show_alert("Cannot load rebill information.");
                }
            })
        }
    }
    t(".crm_dropdown_menu li").on("click", function(e) {
        crm_name = t(this).text();
        crm_id = t(this).find("a").attr("id");
        t(".crm_toggle_button").html(crm_name + ' <span class="caret"></span>');
    });
    t("#from_date").datepicker({});
    t("#to_date").datepicker({});
    t("#rebill_date").datepicker({});
    t("#rebill_date").change(function () {
        var cur_date = new Date($(this).val());
        var r = cur_date.getDate() - 21;
        0 == cur_date.getDay() ? r -= 7 : r -= cur_date.getDay();
        cur_date.setDate(r);
        from_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
        r = cur_date.getDate() + 6;
        cur_date.setDate(r);
        to_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
        t("#from_date").val(from_date);
        t("#to_date").val(to_date);
    });
    t(".date_dropdown_menu li").on("click", function(e) {
        var r = t(this).text();
        date_type = t(this).find("a").attr("id");
        t(".date_toggle_button").html(r + ' <span class="caret"></span>');
        set_dates();
    });
    t(".rebill_search_button").click(function() {
        get_rebill("1");
    });
    t(".btn_export_quick").click(function() {
        var e = "./export_quick_rebill.php?from_date=" + t("#from_date").val() + "&to_date=" + t("#to_date").val() + "&crm_id=" + crm_id + "&crm_name=" + crm_name;
        window.location.href = e
    });
    t(".btn_export_full").click(function() {
        var e = "./export_full_rebill.php?from_date=" + t("#from_date").val() + "&to_date=" + t("#to_date").val();
        window.location.href = e
    });

    var loading_gif = '<img src="../images/loading.gif" style="width:22px;height:22px;">';
    var minus_sign = '<span class="glyphicon glyphicon-minus-sign" aria-hidden="true" style="color: #ffa5a5"></span>';
    var triangle_sign = '<span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true" style="color: #ffa5a5"></span>';
    var date_type = "date_fullweek";
    var from_date = "";
    var to_date = "";
    var crm_id = -1;
    var crm_name = "";

    get_selected_crm();
    set_dates();
    get_rebill();
});
