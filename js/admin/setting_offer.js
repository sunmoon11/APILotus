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

    function get_campaign_list(edit) {
        edit = edit || false;
        if (!(campaign_waiting || -1 == crm_id)) {
            show_waiting("campaign", true);
            $.ajax({
                type : "GET",
                url : "../daemon/ajax_admin/setting_campaign_list.php",
                data : {
                    crm_id : crm_id,
                    campaign_ids : $(".search_campaign_ids").val(),
                    page_number : 1,
                    items_page : 1000
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
                        var labels = [
                            ['step1', 'Step1'],
                            ['step2', 'Step2'],
                            ['step1pp', 'Step1 Prepaid'],
                            ['step2pp', 'Step2 Prepaid'],
                            ['step1tab', 'Step1 Tablet'],
                            ['step2tab', 'Step2 Tablet']
                        ];
                        if (campaign_list.ids.length > 0) {
                            for (var i = 0; i < labels.length; i++) {
                                campaign_table_html += '<div class="row crm_board_title"><div class="col-xs-12" style="padding-left: 0"><button type="button" class="btn btn-link btn-sm btn_offer_expand" id="id_' + labels[i][0] + '"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></button>' + labels[i][1] + '</div></div>';
                                campaign_table_html += '<table class="table table-hover offer_table" style="margin-top:10px; max-height: 300px; overflow-y: scroll; display: none;" id="table_' + labels[i][0] + '">';
                                campaign_table_html += '<thead><tr>';
                                campaign_table_html += '<th><input type="checkbox" class="campaign_select_all" id="select_all_' + labels[i][0] + '"></th>';
                                campaign_table_html += '<th>Campaign ID</th>';
                                campaign_table_html += '<th>Campaign Name</th>';
                                campaign_table_html += '<th>Campaign Labels</th>';
                                campaign_table_html += '</tr></thead>';
                                campaign_table_html += '<tbody class="table_campaign_body">';
                                for (var j = 0; j < campaign_list.ids.length; j++) {
                                    campaign_table_html += '<tr><td><input type="checkbox" id="campaign_' + labels[i][0] + '_' + campaign_list.ids[j] + '" class="campaign_item"></td>';
                                    campaign_table_html += "<td>" + campaign_list.ids[j] + "</td>";
                                    campaign_table_html += "<td>" + campaign_list.names[j] + "</td>";
                                    campaign_table_html += "<td>" + campaign_list.labels[j] + "</td></tr>";
                                }
                                campaign_table_html += "</tbody></table>";
                            }
                        } else {
                            show_alert("campaign", "There is no any campaign data.");
                        }
                        $("#div_select_campaign").html(campaign_table_html);
                        $(".campaign_select_all").prop("checked", false);

                        if (true === edit) {
                            for (i = 0; i < offer_list.length; i++) {
                                var offer = offer_list[i];
                                if (offer[0] == offer_id) {
                                    if (offer[4] != null) {
                                        var campaign_ids = offer[4].split(',');
                                        for (j = 0; j < campaign_ids.length; j++) {
                                            var campaign_id = campaign_ids[j];
                                            $("#campaign_" + campaign_id).prop("checked", true);
                                        }
                                    }

                                    if (offer[5] != null) {
                                        var label_ids = offer[5].split(',');
                                        for (j = 0; j < label_ids.length; j++) {
                                            $("#vlabel_" + label_ids[j]).prop("checked", true);
                                        }
                                    }
                                }
                            }
                        }
                    }
                },
                failure : function(e) {
                    show_waiting("campaign", false);
                    show_alert("campaign", "Cannot load campaign list.");
                }
            });
        }
    }

    function get_offer_list() {
        if (!(offer_waiting || -1 == crm_id)) {
            show_waiting("offer", true);
            $.ajax({
                type : "GET",
                url : "../daemon/ajax_admin/setting_offer_list.php",
                data : {},
                success : function(data) {
                    show_waiting("offer", false);
                    $(".table_offer_body").html('');
                    if ("error" == data) {
                        show_alert("offer", "Cannot load offer list.");
                    }
                    else if ("no_cookie" == data) {
                        window.location.href = "../../admin/login.php";
                    }
                    else {
                        offer_list = jQuery.parseJSON(data);
                        var offer_table_html = "";
                        if (offer_list.length > 0) {
                            for (var i = 0; i < offer_list.length; i++) {
                                var offer = offer_list[i];
                                offer_table_html += '<tr>';
                                offer_table_html += "<td>" + offer[0] + "</td>";
                                offer_table_html += "<td>" + offer[2] + "</td>";
                                offer_table_html += "<td>" + offer[1] + "</td>";
                                var campaigns = offer[4] == null ? '' : offer[4];
                                if (campaigns.length > 80) {
                                    campaigns = campaigns.substr(0, 80);
                                    campaigns = campaigns.substr(0, campaigns.lastIndexOf(',') + 1);
                                    campaigns = campaigns + '...'
                                }
                                offer_table_html += "<td>" + campaigns + "</td>";
                                offer_table_html += "<td>" + offer[6] + "</td>";
                                offer_table_html += '<td><button type="button" class="btn btn-link btn-sm setting_offer_edit" id="oedit_' + offer[0] + '" data-toggle="modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span>&nbsp;Edit</button>';
                                offer_table_html += '<button type="button" class="btn btn-link btn-sm setting_offer_delete" id="odelete_' + offer[0] + '" data-toggle="modal" data-target="#offer_delete_modal"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true" style="color: #ffa5a5"></span>&nbsp;Delete</button></td>';
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

    function get_label_list() {
        show_waiting("offer", true);
        $.ajax({
            type : "GET",
            url : "../daemon/ajax_admin/setting_offer_label_list.php",
            data : {},
            success : function(data) {
                show_waiting("offer", false);
                if ("error" == data) {
                    show_alert("label", "Cannot load label list.");
                }
                else if ("no_cookie" == data) {
                    window.location.href = "../../admin/login.php";
                }
                else {
                    label_list = jQuery.parseJSON(data);
                    var modal_table_html = "";
                    for (var i = 0; i < label_list.length; i++) {
                        modal_table_html += '<tr><td><input type="checkbox" id="vlabel_' + label_list[i][0] + '" class="modal_vlabel_item" name="vertical"></td>';
                        modal_table_html += "<td>" + label_list[i][1] + "</td></tr>";
                    }
                    $(".modal_offer_vlabel_body").html(modal_table_html);
                }
            },
            failure : function(e) {
                show_waiting("label", false);
                show_alert("label", "Cannot load label list.");
            }
        });
    }

    function add_offer() {
        show_waiting("offer", true);
        $.ajax({
            type : "GET",
            url : "../daemon/ajax_admin/setting_offer_add.php",
            data : {
                name: $(".add_offer_name").val(),
                crm_id : crm_id,
                campaign_ids : selected_campaigns,
                label_ids : selected_labels
            },
            success : function(status) {
                show_waiting("offer", false);
                if ("error" == status)
                    show_alert("offer", "Offer cannot be added.");
                else if ("no_cookie" == status)
                    window.location.href = "../../admin/login.php";
                else if ("success" == status)
                    get_offer_list();
            },
            failure : function(e) {
                show_waiting("offer", false);
                show_alert("offer", "Offer cannot be added.");
            }
        });
    }

    function edit_offer() {
        show_waiting("offer", true);
        $.ajax({
            type : "GET",
            url : "../daemon/ajax_admin/setting_offer_edit.php",
            data : {
                offer_id: offer_id,
                name: $(".add_offer_name").val(),
                campaign_ids : selected_campaigns,
                label_ids : selected_labels
            },
            success : function(status) {
                show_waiting("offer", false);
                if ("error" == status)
                    show_alert("offer", "Offer cannot be changed.");
                else if ("no_cookie" == status)
                    window.location.href = "../../admin/login.php";
                else if ("success" == status)
                    get_offer_list();
            },
            failure : function(e) {
                show_waiting("offer", false);
                show_alert("offer", "Offer cannot be changed.");
            }
        });
    }

    function delete_offer() {
        show_waiting("offer", true);
        $.ajax({
            type : "GET",
            url : "../daemon/ajax_admin/setting_offer_delete.php",
            data : {
                offer_id : offer_id
            },
            success : function(status) {
                show_waiting("offer", false);
                if ("error" == status)
                    show_alert("offer", "Offer cannot be deleted.");
                else if ("no_cookie" == status)
                    window.location.href = "../../admin/login.php";
                else if ("success" == status)
                    get_offer_list();
            },
            failure : function(e) {
                show_waiting("offer", false);
                show_alert("offer", "Offer cannot be deleted.");
            }
        });
    }

    function get_checked_campaigns() {
        var campaigns = "";
        $(".campaign_item").each(function(e) {
            if ($(this).prop("checked")) {
                "" !== campaigns && (campaigns += ",");
                campaigns += $(this).prop("id").substring(9);
            }
        });
        return campaigns;
    }

    function get_checked_labels() {
        var labels = "";
        $(".modal_vlabel_item").each(function() {
            if ($(this).prop('checked')) {
                "" !== labels && (labels += ",");
                labels += $(this).prop("id").substring(7);
            }
        });
        return labels;
    }

    function reset_offer_modal() {
        $(".add_offer_name").val('');
        $(".campaign_item").each(function() {
            $(this).prop("checked", false);
        });
        $(".btn_offer_expand").each(function () {
            var id = $(this).prop("id").substring(3);
            if ("none" !== $("#table_" + id).css("display")) {
                $("#table_" + id).css("display", "none");
                $(this).html('<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>');
            }
        });
        $(".modal_vlabel_item").each(function () {
            $(this).removeAttr('checked');
        });
    }

    var offer_list;
    var crm_id = -1;
    var offer_id = -1;
    var campaign_waiting = false;
    var offer_waiting = false;
    var selected_campaigns = "";
    var selected_labels = "";
    var loading_icon = '<img src="../images/loading.gif" style="width:22px; height:22px;">';
    var label_list = null;
    get_crm_id();
    get_offer_list();
    get_label_list();
    get_campaign_list();

    $(".crm_dropdown_menu li").on("click", function() {
        crm_id = $(this).find("a").attr("id");
        $(".crm_toggle_button").html($(this).text() + ' <span class="caret"></span>');
    });
    $(document).on("click", ".campaign_select_all", function () {
        var check = $(this);
        var id = $(this).prop("id").substring(11);
        $(".campaign_item").each(function() {
            if (check.prop("checked")) {
                $("[id^=campaign_" + id + "_]").prop("checked", true);
            } else {
                $("[id^=campaign_" + id + "_]").prop("checked", false);
            }
        });
    });
    $(".campaign_search_button").click(function() {
        get_campaign_list();
    });

    $(".btn_offer_add").click(function() {
        $("#label_add_offer").html('Add Offer&nbsp;<span class="setting_campaign_waiting" style="text-align:right"></span>');
        $(".modal_btn_offer_add").html('Add Offer');
        reset_offer_modal();
        $("#offer_add_modal").modal("toggle");
    });
    $(document).on("click", ".modal_btn_offer_add", function () {
        if ("" === $(".add_offer_name").val()) {
            show_alert("campaign", "Please input Offer Name.");
            $(".add_offer_name").focus();
            return;
        }
        selected_campaigns = get_checked_campaigns();
        selected_labels = get_checked_labels();

        $("#offer_add_modal").modal("toggle");
        if ('Add Offer' === $(".modal_btn_offer_add").html())
            add_offer();
        else
            edit_offer();
    });
    $(document).on("click", ".btn_offer_expand", function () {
        var id = $(this).prop("id").substring(3);
        if ("none" === $("#table_" + id).css("display")) {
            $("#table_" + id).css("display", "block");
            $(this).html('<span class="glyphicon glyphicon-minus-sign" aria-hidden="true" style="color: #ffa5a5"></span>');
        }
        else {
            $("#table_" + id).css("display", "none");
            $(this).html('<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>');
        }
    });

    $(".table_offer_body").on("click", ".setting_offer_edit", function(a) {
        offer_id = $(this).prop("id").substring(6);
        $("#label_add_offer").html('Edit Offer&nbsp;<span class="setting_campaign_waiting" style="text-align:right"></span>');
        $(".modal_btn_offer_add").html('Edit Offer');
        reset_offer_modal();

        for (var i = 0; i < offer_list.length; i++) {
            var offer = offer_list[i];
            if (offer[0] == offer_id) {
                $(".add_offer_name").val(offer[1]);
                crm_id = offer[7];
                $(".crm_toggle_button").html(offer[2] + ' <span class="caret"></span>');
                get_campaign_list(true);
            }
        }
        $("#offer_add_modal").modal("toggle");
    });

    $(".table_offer_body").on("click", ".setting_offer_delete", function(a) {
        offer_id = $(this).prop("id").substring(8);
    });
    $(document).on("click", ".modal_btn_offer_delete", function () {
        $("#offer_delete_modal").modal("toggle");
        delete_offer();
    });
});
