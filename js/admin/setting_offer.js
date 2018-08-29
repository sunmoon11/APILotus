jQuery(document).ready(function($) {
    function get_crm_id() {
        if ($(".crm_dropdown_list").length > 0) {
            crm_id = $(".crm_dropdown_list").prop("id");
        }
    }

    function show_alert(alert_type, alert_content) {
        if ("campaign" == alert_type) {
            $(".setting_campaign_alert").html(alert_content);
            $(".setting_campaign_alert").fadeIn(1e3, function() {
                $(".setting_campaign_alert").fadeOut(3e3);
            });
        }
        else if ("offer" == alert_type) {
            $(".setting_offer_alert").html(alert_content);
            $(".setting_offer_alert").fadeIn(1e3, function() {
                $(".setting_offer_alert").fadeOut(3e3);
            });
        }
        else if ("action_edit" == alert_type) {
            $(".action_edit_alert").html(alert_content);
            $(".action_edit_alert").fadeIn(1e3, function () {
                $(".action_edit_alert").fadeOut(3e3);
            });
        }
    }

    function show_waiting(waiting_type, show) {
        if ("campaign" == waiting_type) {
            if (campaign_waiting = show) {
                $(".setting_campaign_waiting").html(loading_icon);
            } else {
                $(".setting_campaign_waiting").html("");
            }
        }
        else if ("offer" == waiting_type) {
            if (offer_waiting = show) {
                $(".setting_offer_waiting").html(loading_icon);
            } else {
                $(".setting_offer_waiting").html("");
            }
        }
    }

    function get_campaign_list() {
        if (!(campaign_waiting || -1 == crm_id)) {
            show_waiting("campaign", true);
            $.ajax({
                type : "GET",
                url : "../daemon/ajax_admin/setting_campaign_list.php",
                data : {
                    crm_id : crm_id,
                    campaign_ids : $(".search_campaign_ids").val(),
                    page_number : cur_page_num,
                    items_page : show_count
                },
                success : function(data) {
                    show_waiting("campaign", false);
                    if ("error" == data) {
                        show_alert("campaign", "Cannot load campaign list.");
                    }
                    else if ("no_cookie" == data) {
                        window.location.href = "../../admin/login.php";
                    }
                    else {
                        var campaign_list = jQuery.parseJSON(data);
                        var campaign_table_html = "";
                        total_campaigns_count = campaign_list.length;
                        if (campaign_list.ids.length > 0) {
                            for (var j = 0; j < campaign_list.ids.length; j++) {
                                campaign_table_html = campaign_table_html + ('<tr><td><input type="checkbox" id="campaign_' + campaign_list.ids[j] + '" class="campaign_item"></td>');
                                campaign_table_html = campaign_table_html + ("<td>" + campaign_list.ids[j] + "</td>");
                                campaign_table_html = campaign_table_html + ("<td>" + campaign_list.names[j] + "</td>");
                                campaign_table_html = campaign_table_html + ("<td>" + campaign_list.labels[j] + "</td></tr>");
                            }
                        } else {
                            show_alert("campaign", "There is no any campaign data.");
                        }
                        $(".table_campaign_body").html(campaign_table_html);
                        $(".campaign_select_all").prop("checked", false);
                        reset_pagination();
                    }
                },
                failure : function(e) {
                    show_waiting("campaign", false);
                    show_alert("campaign", "Cannot load campaign list.");
                }
            });
        }
    }

    function get_label_list() {
        $.ajax({
            type : "GET",
            url : "../daemon/ajax_admin/setting_campaign_label_list.php",
            data : {
                crm_id : crm_id
            },
            success : function(data) {
                if ("error" == data) {
                    show_alert("campaign", "Cannot load label list.");
                }
                else if ("no_cookie" == data) {
                    window.location.href = "../../admin/login.php";
                }
                else {
                    label_list = jQuery.parseJSON(data);
                }
            },
            failure : function(e) {
                show_waiting("campaign", false);
                show_alert("campaign", "Cannot load label list.");
            }
        });
    }

    function reset_pagination() {
        var meg = "";
        var pagination_html = "";
        if (total_campaigns_count > 0) {
            var n = Math.floor((cur_page_num - 1) / p);
            var m = n * p + 1;
            var i = (n + 1) * p;
            if (i * show_count > total_campaigns_count) {
                i = Math.floor(total_campaigns_count / show_count);
                if (total_campaigns_count % show_count > 0) {
                    i++;
                }
            }
            for (var c = m; c <= i; c++) {
                meg = meg + (c == cur_page_num ? '<button type="button" class="btn btn-success btn-sm campaign_page" id="page_' + c + '">' + c + "</button>" : '<button type="button" class="btn btn-default btn-sm campaign_page" id="page_' + c + '">' + c + "</button>");
            }
            if (n > 0) {
                pagination_html = pagination_html + '<button type="button" class="btn btn-default btn-sm campaign_page" id="page_first">&lt;&lt;</button><button type="button" class="btn btn-default btn-sm campaign_page" id="page_prev">&lt;</button>';
            }
            pagination_html = pagination_html + meg;
            if (i * show_count < total_campaigns_count) {
                pagination_html = pagination_html + '<button type="button" class="btn btn-default btn-sm campaign_page" id="page_next">&gt;</button><button type="button" class="btn btn-default btn-sm campaign_page" id="page_last">&gt;&gt;</button>';
            }
        }
        $(".campaign_pagination").html(pagination_html);
    }

    function get_offer_list() {
        if (!(offer_waiting || -1 == crm_id)) {
            show_waiting("offer", true);
            $.ajax({
                type : "GET",
                url : "../daemon/ajax_admin/setting_offer_list.php",
                data : {
                    crm_id : crm_id,
                    offer_ids : $(".search_offer_ids").val()
                },
                success : function(data) {
                    show_waiting("offer", false);
                    if ("error" == data) {
                        show_alert("offer", "Cannot load offer list.");
                    }
                    else if ("no_cookie" == data) {
                        window.location.href = "../../admin/login.php";
                    }
                    else {
                        offer_list = jQuery.parseJSON(data);
                        var offer_table_html = "";
                        total_campaigns_count = offer_list.length;
                        if (offer_list.length > 0) {
                            for (var i = 0; i < offer_list.length; i++) {
                                var offer = offer_list[i];
                                offer_table_html += '<tr>';
                                offer_table_html += "<td>" + offer[0] + "</td>";
                                offer_table_html += "<td>" + offer[1] + "</td>";
                                offer_table_html += '<td id="clabel_' + offer[0] + '">' + offer[2] + "</td>";
                                offer_table_html += '<td><button type="button" class="btn btn-link btn-sm setting_offer_edit" id="ogoal_' + offer[0] + '" data-toggle="modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span>&nbsp;Edit Offer</button></td>';
                                // offer_table_html += '<button type="button" class="btn btn-link btn-sm setting_offer_label_edit" id="oedit_' + offer[0] + '" data-toggle="modal" data-target="#campaign_action_edit_modal"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span>&nbsp;Edit Label</button>';
                                // offer_table_html += '<button type="button" class="btn btn-link btn-sm setting_offer_label_delete" id="odelete_' + offer[0] + '" data-toggle="modal" data-target="#campaign_action_delete_modal"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true" style="color: #ffa5a5"></span>&nbsp;Delete Label</button></td>';
                                offer_table_html += '</tr>';
                            }
                        } else {
                            show_alert("offer", "There is no any offer data.");
                        }
                        $(".table_offer_body").html(offer_table_html);
                    }
                },
                failure : function(e) {
                    show_waiting("offer", false);
                    show_alert("offer", "Cannot load offer list.");
                }
            });
        }
    }

    function get_checked_campaigns() {
        var authors = "";
        $(".campaign_item").each(function(e) {
            if ($(this).prop("checked")) {
                if ("" != authors)
                    authors = authors + ",";
                authors = authors + $(this).prop("id").substring(9);
            }
        });
        return authors;
    }

    function update_campaign(url) {
        show_waiting("campaign", true);
        $.ajax({
            type : "GET",
            url : "../daemon/ajax_admin/setting_campaign_action_edit.php",
            data : {
                crm_id : crm_id,
                campaign_ids : selected_campaigns,
                label_ids : url
            },
            success : function(status) {
                show_waiting("campaign", false);
                if ("error" == status)
                    show_alert("campaign", "Campaign label cannot be changed.");
                else if ("no_cookie" == status)
                    window.location.href = "../../admin/login.php";
                else if ("success" == status)
                    get_campaign_list();
            },
            failure : function(e) {
                show_waiting("campaign", false);
                show_alert("campaign", "Campaign label cannot be changed.");
            }
        });
    }

    function delete_campaign() {
        show_waiting("campaign", true);
        $.ajax({
            type : "GET",
            url : "../daemon/ajax_admin/setting_campaign_action_delete.php",
            data : {
                crm_id : crm_id,
                campaign_ids : selected_campaigns
            },
            success : function(status) {
                show_waiting("campaign", false);
                if ("error" == status)
                    show_alert("campaign", "Campaign label cannot be deleted.");
                else if ("no_cookie" == status)
                    window.location.href = "../../admin/login.php";
                else if ("success" == status)
                    get_campaign_list();
            },
            failure : function(e) {
                show_waiting("campaign", false);
                show_alert("campaign", "Campaign label cannot be deleted.");
            }
        });
    }
    var offer_list;
    var label_list;
    var crm_id = -1;
    var title = "";
    var cur_page_num = 1;
    var show_count = 10;
    var p = 7;
    var total_campaigns_count = 0;
    var campaign_waiting = false;
    var offer_waiting = false;
    var selected_campaigns = "";
    var loading_icon = '<img src="../images/loading.gif" style="width:22px; height:22px;">';
    get_crm_id();
    get_offer_list();
    get_label_list();
    get_campaign_list();

    $(".campaign_select_all").click(function() {
        $(".campaign_item").each(function(a) {
            if ($(".campaign_select_all").prop("checked")) {
                $(this).prop("checked", true);
            } else {
                $(this).prop("checked", false);
            }
        });
    });
    $(".crm_dropdown_menu li").on("click", function(a) {
        title = $(this).text();
        crm_id = $(this).find("a").attr("id");
        $(".crm_toggle_button").html(title + ' <span class="caret"></span>');
    });
    $(".campaign_action_dropdown_menu li").on("click", function(a) {
        var selected_action = $(this).find("a").attr("id");
        selected_campaigns = get_checked_campaigns();
        if ("" != selected_campaigns) {
            if ("action_edit" == selected_action) {
                $(".modal_tlabel_item").each(function(a) {
                    $(this).prop("checked", false);
                });
                $("#tlabel_11").prop("checked", false);
                $("#tlabel_12").prop("checked", false);
                $("#tlabel_21").prop("checked", false);
                $("#tlabel_22").prop("checked", false);
                $("#tlabel_41").prop("checked", false);
                $("#tlabel_42").prop("checked", false);
                var modal_table_html = "";
                for (var i = 0; i < label_list.length; i++) {
                    if ("3" == label_list[i][2]) {
                        modal_table_html += '<tr><td><input type="radio" id="vlabel_' + label_list[i][0] + '" class="modal_vlabel_item" name="vertical"></td>';
                        modal_table_html += "<td>" + label_list[i][1] + "</td></tr>";
                    }
                }
                $(".modal_vlabel_body").html(modal_table_html);
                $("#campaign_action_edit_modal").modal("toggle");
            } else if ("action_delete" == selected_action) {
                $("#campaign_action_delete_modal").modal("toggle");
            }
        } else {
            show_alert("campaign", "Please select campaign items.");
        }
    });
    $(".modal_tlabel_item").click(function() {
        $("#tlabel_11").prop("checked", false);
        $("#tlabel_12").prop("checked", false);
        $("#tlabel_21").prop("checked", false);
        $("#tlabel_22").prop("checked", false);
        $("#tlabel_41").prop("checked", false);
        $("#tlabel_42").prop("checked", false);
    });
    $("#tlabel_11").click(function() {
        $("#tlabel_1").prop("checked", true);
        $("#tlabel_12").prop("checked", false);
        $("#tlabel_21").prop("checked", false);
        $("#tlabel_22").prop("checked", false);
        $("#tlabel_41").prop("checked", false);
        $("#tlabel_42").prop("checked", false);
    });
    $("#tlabel_12").click(function() {
        $("#tlabel_1").prop("checked", true);
        $("#tlabel_11").prop("checked", false);
        $("#tlabel_21").prop("checked", false);
        $("#tlabel_22").prop("checked", false);
        $("#tlabel_41").prop("checked", false);
        $("#tlabel_42").prop("checked", false);
    });
    $("#tlabel_21").click(function() {
        $("#tlabel_2").prop("checked", true);
        $("#tlabel_11").prop("checked", false);
        $("#tlabel_12").prop("checked", false);
        $("#tlabel_22").prop("checked", false);
        $("#tlabel_41").prop("checked", false);
        $("#tlabel_42").prop("checked", false);
    });
    $("#tlabel_22").click(function() {
        $("#tlabel_2").prop("checked", true);
        $("#tlabel_11").prop("checked", false);
        $("#tlabel_12").prop("checked", false);
        $("#tlabel_21").prop("checked", false);
        $("#tlabel_41").prop("checked", false);
        $("#tlabel_42").prop("checked", false);
    });
    $("#tlabel_41").click(function() {
        $("#tlabel_4").prop("checked", true);
        $("#tlabel_11").prop("checked", false);
        $("#tlabel_12").prop("checked", false);
        $("#tlabel_21").prop("checked", false);
        $("#tlabel_22").prop("checked", false);
        $("#tlabel_42").prop("checked", false);
    });
    $("#tlabel_42").click(function() {
        $("#tlabel_4").prop("checked", true);
        $("#tlabel_11").prop("checked", false);
        $("#tlabel_12").prop("checked", false);
        $("#tlabel_21").prop("checked", false);
        $("#tlabel_22").prop("checked", false);
        $("#tlabel_41").prop("checked", false);
    });
    $(".modal_btn_action_edit").click(function() {
        var campaign_type = "";
        var vertical_label = "";
        $(".modal_tlabel_item").each(function(e) {
            if ($(this).prop("checked")) {
                campaign_type = $(this).prop("id").substring(7);
                if ("1" == campaign_type) {
                    if ($("#tlabel_11").prop("checked")) {
                        campaign_type = "1,5";
                    } else if ($("#tlabel_12").prop("checked")) {
                        campaign_type = "1,6";
                    }
                } else if ("2" == campaign_type) {
                    if ($("#tlabel_21").prop("checked")) {
                        campaign_type = "2,5";
                    } else if ($("#tlabel_22").prop("checked")) {
                        campaign_type = "2,6";
                    }
                } else if ("4" == campaign_type) {
                    if ($("#tlabel_41").prop("checked")) {
                        campaign_type = "4,1";
                    } else if ($("#tlabel_42").prop("checked")) {
                        campaign_type = "4,2";
                    }
                }
            }
        });
        if ("" != campaign_type) {
            $(".modal_vlabel_item").each(function(a) {
                if ($(this).prop("checked")) {
                    vertical_label = $(this).prop("id").substring(7);
                }
            });
            if ("" != vertical_label) {
                $("#campaign_action_edit_modal").modal("toggle");
                update_campaign(campaign_type + "," + vertical_label);
            } else {
                show_alert("action_edit", "Please select vertical label.");
            }
        } else {
            show_alert("action_edit", "Please select campaign type.");
        }
    });
    $(".modal_btn_action_delete").click(function() {
        $("#campaign_action_delete_modal").modal("toggle");
        delete_campaign();
    });
    $(".count_dropdown_menu li").on("click", function(a) {
        show_count = $(this).text();
        $(".count_toggle_button").html(show_count + ' <span class="caret"></span>');
        cur_page_num = 1;
        get_campaign_list();
    });
    $(".campaign_search_button").click(function() {
        cur_page_num = 1;
        get_campaign_list();
    });
    $(".campaign_pagination").on("click", ".campaign_page", function(a) {
        var i = $(this).prop("id").substring(5);
        var n = Math.floor((cur_page_num - 1) / p);
        if ("first" == i) {
            cur_page_num = 1;
        } else {
            if ("prev" == i) {
                cur_page_num = (n - 1) * p + 1;
            } else {
                if ("next" == i) {
                    cur_page_num = (n + 1) * p + 1;
                } else {
                    if ("last" == i) {
                        cur_page_num = Math.floor(total_campaigns_count / show_count);
                        if (total_campaigns_count % show_count > 0) {
                            cur_page_num++;
                        }
                    } else {
                        cur_page_num = i;
                    }
                }
            }
        }
        get_campaign_list();
    });
});
