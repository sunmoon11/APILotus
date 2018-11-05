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
                    campaign_ids : '',
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

    function show_offer_list() {
        let data = offer_list;
        if (0 != crm_main_id)
            data = data.filter(item => item[7] === crm_main_id);
        if (0 != vertical_main_id)
            data = data.filter(item => item[5] === vertical_main_id);
        if (search_keyword)
            data = data.filter(item => item[0].includes(search_keyword) ||
                item[2].toLowerCase().includes(search_keyword.toLowerCase()) ||
                item[1].toLowerCase().includes(search_keyword.toLowerCase()) ||
                item[6].toLowerCase().includes(search_keyword.toLowerCase())
            );

        let offer_table_html = "";
        for (let i = 0; i < data.length; i++) {
            let offer = data[i];
            offer_table_html += '<tr>';
            offer_table_html += "<td>" + offer[0] + "</td>";
            offer_table_html += "<td>" + offer[2] + "</td>";
            offer_table_html += "<td>" + offer[1] + "</td>";
            // var campaigns = offer[4] == null ? '' : offer[4];
            // if (campaigns.length > 80) {
            //     campaigns = campaigns.substr(0, 80);
            //     campaigns = campaigns.substr(0, campaigns.lastIndexOf(',') + 1);
            //     campaigns = campaigns + '...'
            // }
            // offer_table_html += "<td>" + campaigns + "</td>";
            offer_table_html += "<td>" + offer[6] + "</td>";
            offer_table_html += '<td><button type="button" class="btn btn-link btn-sm setting_offer_edit" id="oedit_' + offer[0] + '" data-toggle="modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span>&nbsp;Edit</button>';
            offer_table_html += '<button type="button" class="btn btn-link btn-sm setting_offer_delete" id="odelete_' + offer[0] + '" data-toggle="modal" data-target="#offer_delete_modal"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true" style="color: #ffa5a5"></span>&nbsp;Delete</button></td>';
            offer_table_html += '</tr>';
        }
        $(".table_offer_body").html(offer_table_html);
    }

    function get_offer_list() {
        if (!offer_waiting) {
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
                        show_offer_list();
                    }
                },
                failure : function() {
                    show_waiting("offer", false);
                    show_alert("offer", "Cannot load offer list.");
                }
            });
        }
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
                label_ids : vertical_id,
                offer_type: offer_type,
                s1_payout: $("#input_default_s1_payout").val(),
                s2_payout: $("#input_default_s2_payout").val()
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
                label_ids : vertical_id,
                offer_type: offer_type,
                s1_payout: $("#input_default_s1_payout").val(),
                s2_payout: $("#input_default_s2_payout").val()
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
        $(".campaign_item").each(function() {
            if ($(this).prop("checked")) {
                "" !== campaigns && (campaigns += ",");
                campaigns += $(this).prop("id").substring(9);
            }
        });
        return campaigns;
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
    }

    var offer_list;
    var crm_main_id = 0;
    var vertical_main_id = 0;
    var search_keyword = "";

    var crm_id = -1;
    var offer_type = -1;
    var vertical_id = -1;
    var offer_id = -1;
    var s1_payout = 0;
    var s2_payout = 0;

    var campaign_waiting = false;
    var offer_waiting = false;
    var selected_campaigns = "";
    var loading_icon = '<img src="../images/loading.gif" style="width:22px; height:22px;">';

    get_offer_list();
    get_crm_id();
    get_campaign_list();

    $(".crm_main_dropdown_menu li").on("click", function() {
        crm_main_id = $(this).find("a").attr("id");
        $(".crm_main_toggle_button").html($(this).text() + ' <span class="caret"></span>');
        show_offer_list();
    });
    $(".vertical_main_dropdown_menu li").on("click", function() {
        vertical_main_id = $(this).find("a").attr("id");
        $(".vertical_main_toggle_button").html($(this).text() + ' <span class="caret"></span>');
        show_offer_list();
    });
    $(".search_offers").on("input", function () {
        search_keyword = $(this).val();
        show_offer_list();
    });

    $(".crm_dropdown_menu li").on("click", function() {
        crm_id = $(this).find("a").attr("id");
        $(".crm_toggle_button").html($(this).text() + ' <span class="caret"></span>');
        get_campaign_list();
    });
    $(".offer_type_dropdown_menu li").on("click", function() {
        offer_type = $(this).find("a").attr("id");
        $(".offer_type_toggle_button").html($(this).text() + ' <span class="caret"></span>');
        1 == offer_type ? $("#div_default_s2_payout").css("display", "none") : $("#div_default_s2_payout").css("display", "block");
    });
    $(".vertical_dropdown_menu li").on("click", function() {
        vertical_id = $(this).find("a").attr("id");
        $(".vertical_toggle_button").html($(this).text() + ' <span class="caret"></span>');
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

    $(".btn_offer_add").click(function() {
        $("#label_add_offer").html('Add Offer&nbsp;<span class="setting_campaign_waiting" style="text-align:right"></span>');
        $(".modal_btn_offer_add").html('Add Offer');
        reset_offer_modal();
        $(".crm_toggle_button").removeAttr('disabled');
        offer_type = 1;
        s1_payout = 0;
        s2_payout = 0;
        $(".offer_type_toggle_button").html('Single Step <span class="caret"></span>');
        $("#div_default_s2_payout").css("display", "none");
        $("#input_default_s1_payout").val('');
        $("#input_default_s2_payout").val('');

        $("#offer_add_modal").modal("toggle");
    });
    $(document).on("click", ".modal_btn_offer_add", function () {
        if ("" === $(".add_offer_name").val()) {
            show_alert("campaign", "Please input Offer Name.");
            $(".add_offer_name").focus();
            return;
        }
        selected_campaigns = get_checked_campaigns();

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
                $(".crm_toggle_button").attr('disabled', true);
                vertical_id = offer[5];
                $(".vertical_toggle_button").html(offer[6] + ' <span class="caret"></span>');
                offer_type = offer[8];
                s1_payout = offer[9];
                s2_payout = offer[10];
                if (1 == offer_type) {
                    $(".offer_type_toggle_button").html('Single Step <span class="caret"></span>');
                    $("#div_default_s2_payout").css("display", "none");
                }
                else {
                    $(".offer_type_toggle_button").html('2 Step <span class="caret"></span>');
                    $("#div_default_s2_payout").css("display", "block");
                }
                $("#input_default_s1_payout").val(s1_payout);
                $("#input_default_s2_payout").val(s2_payout);

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
