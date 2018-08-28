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
        else if ("label" == alert_type) {
            $(".setting_label_alert").html(alert_content);
            $(".setting_label_alert").fadeIn(1e3, function () {
                $(".setting_label_alert").fadeOut(3e3);
            });
        }
        else if ("label_add" == alert_type) {
            $(".label_add_alert").html(alert_content);
            $(".label_add_alert").fadeIn(1e3, function () {
                $(".label_add_alert").fadeOut(3e3);
            });
        }
        else if ("label_edit" == alert_type) {
            $(".label_edit_alert").html(alert_content);
            $(".label_edit_alert").fadeIn(1e3, function () {
                $(".label_edit_alert").fadeOut(3e3);
            });
        }
        else if ("action_edit" == alert_type) {
            $(".action_edit_alert").html(alert_content);
            $(".action_edit_alert").fadeIn(1e3, function () {
                $(".action_edit_alert").fadeOut(3e3);
            });
        }
        else if ("action_goal" == alert_type) {
            $(".goal_edit_alert").html(alert_content);
            $(".goal_edit_alert").fadeIn(1e3, function () {
                $(".goal_edit_alert").fadeOut(3e3);
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
        else if ("label" == waiting_type) {
            if (label_waiting = show) {
                $(".setting_label_waiting").html(loading_icon);
            } else {
                $(".setting_label_waiting").html("");
            }
        }
    }

    function get_offer_list() {
        if (!(campaign_waiting || -1 == crm_id)) {
            show_waiting("campaign", true);
            $.ajax({
                type : "GET",
                url : "../daemon/ajax_admin/setting_offer_list.php",
                data : {
                    crm_id : crm_id,
                    offer_ids : $(".search_campaign_ids").val(),
                    page_number : cur_page_num,
                    items_page : show_count
                },
                success : function(data) {
                    show_waiting("campaign", false);
                    if ("error" == data) {
                        show_alert("campaign", "Cannot load offer list.");
                    }
                    else if ("no_cookie" == data) {
                        window.location.href = "../../admin/login.php";
                    }
                    else {
                        offer_list = jQuery.parseJSON(data);
                        var offer_table_html = "";
                        total_offers_count = offer_list.length;
                        if (offer_list.ids.length > 0) {
                            for (var i = 0; i < offer_list.ids.length; i++) {
                                offer_table_html += '<tr><td><input type="checkbox" id="campaign_' + offer_list.ids[i] + '" class="campaign_item"></td>';
                                offer_table_html += "<td>" + offer_list.ids[i] + "</td>";
                                offer_table_html += "<td>" + offer_list.names[i] + "</td>";
                                offer_table_html += '<td id="clabel_' + offer_list.ids[i] + '">' + offer_list.labels[i] + "</td>";
                                offer_table_html += '<td><button type="button" class="btn btn-link btn-sm setting_offer_goal" id="ogoal_' + offer_list.ids[i] + '" data-toggle="modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span>&nbsp;Set Goal</button>';
                                offer_table_html += '<button type="button" class="btn btn-link btn-sm setting_offer_edit" id="oedit_' + offer_list.ids[i] + '" data-toggle="modal" data-target="#campaign_action_edit_modal"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span>&nbsp;Edit Label</button>';
                                offer_table_html += '<button type="button" class="btn btn-link btn-sm setting_offer_delete" id="odelete_' + offer_list.ids[i] + '" data-toggle="modal" data-target="#campaign_action_delete_modal"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true" style="color: #ffa5a5"></span>&nbsp;Clear Label</button></td></tr>';
                            }
                        } else {
                            show_alert("campaign", "There is no any offer data.");
                        }
                        $(".table_campaign_body").html(offer_table_html);
                        $(".campaign_select_all").prop("checked", false);
                        reset_pagination();
                    }
                },
                failure : function(e) {
                    show_waiting("campaign", false);
                    show_alert("campaign", "Cannot load offer list.");
                }
            });
            get_label_list();
        }
    }

    function reset_pagination() {
        var meg = "";
        var pagination_html = "";
        if (total_offers_count > 0) {
            var n = Math.floor((cur_page_num - 1) / p);
            var m = n * p + 1;
            var i = (n + 1) * p;
            if (i * show_count > total_offers_count) {
                i = Math.floor(total_offers_count / show_count);
                if (total_offers_count % show_count > 0) {
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
            if (i * show_count < total_offers_count) {
                pagination_html = pagination_html + '<button type="button" class="btn btn-default btn-sm campaign_page" id="page_next">&gt;</button><button type="button" class="btn btn-default btn-sm campaign_page" id="page_last">&gt;&gt;</button>';
            }
        }
        $(".campaign_pagination").html(pagination_html);
    }

    function get_label_list() {
        if (!label_waiting) {
            show_waiting("label", true);
            if ("" != title) {
                $("#crm_name_4vertical").html(title);
            }
            $.ajax({
                type : "GET",
                url : "../daemon/ajax_admin/setting_campaign_label_list.php",
                data : {
                    crm_id : crm_id
                },
                success : function(data) {
                    show_waiting("label", false);
                    if ("error" == data) {
                        show_alert("label", "Cannot load label list.");
                    }
                    else if ("no_cookie" == data) {
                        window.location.href = "../../admin/login.php";
                    }
                    else {
                        label_list = jQuery.parseJSON(data);
                        var label_table_html = "";
                        var c = 1;
                        if (label_list.length > 0) {
                            for (var i = 0; i < label_list.length; i++) {
                                if ("3" == label_list[i][2]) {
                                    label_table_html += "<tr><td>" + c + "</td>";
                                    label_table_html += '<td id="lname_' + label_list[i][0] + '">' + label_list[i][1] + "</td>";
                                    label_table_html += '<td id="lgoal_' + label_list[i][0] + '">' + label_list[i][3] + "</td>";
                                    label_table_html += '<td id="lshow_' + label_list[i][0] + '">' + ("1" == label_list[i][4] ? j : "") + "</td>";
                                    label_table_html += '<td><button type="button" class="btn btn-link btn-sm setting_label_edit" id="ledit_' + label_list[i][0] + '" data-toggle="modal" data-target="#campaign_label_edit_modal"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span>&nbsp;Edit</button>';
                                    label_table_html += '<button type="button" class="btn btn-link btn-sm setting_label_delete" id="ldelete_' + label_list[i][0] + '" data-toggle="modal" data-target="#campaign_label_delete_modal"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true" style="color: #ffa5a5"></span>&nbsp;Delete</button></td></tr>';
                                    c++;
                                }
                            }
                        } else {
                            show_alert("label", "There is no any label list.");
                        }
                        $(".table_label_body").html(label_table_html);
                    }
                },
                failure : function(e) {
                    show_waiting("label", false);
                    show_alert("label", "Cannot load label list.");
                }
            });
        }
    }

    function add_campaign_label() {
        show_waiting("label", true);
        $.ajax({
            type : "GET",
            url : "../daemon/ajax_admin/setting_campaign_label_add.php",
            data : {
                label_name : $(".add_label_name").val()
            },
            success : function(status) {
                show_waiting("label", false);
                if ("error" == status)
                    show_alert("label", "Campaign label cannot be added.");
                else if ("no_cookie" == status)
                    window.location.href = "../../admin/login.php";
                else if ("success" == status)
                    get_label_list();
            },
            failure : function(e) {
                show_waiting("label", false);
                show_alert("label", "Campaign label cannot be added.");
            }
        });
    }

    function edit_campaign_label() {
        show_waiting("label", true);
        $.ajax({
            type : "GET",
            url : "../daemon/ajax_admin/setting_campaign_label_edit.php",
            data : {
                crm_id : crm_id,
                label_id : label_id,
                label_name : $(".edit_label_name").val(),
                label_show : 1 == $(".edit_label_show").prop("checked") ? "1" : "0",
                label_goal : $(".edit_label_goal").val()
            },
            success : function(status) {
                show_waiting("label", false);
                if ("error" == status)
                    show_alert("label", "Campaign label cannot be changed.");
                else if ("no_cookie" == status)
                    window.location.href = "../../admin/login.php";
                else if ("success" == status)
                    get_label_list();
            },
            failure : function(e) {
                show_waiting("label", false);
                show_alert("label", "Campaign label cannot be changed.");
            }
        });
    }

    function delete_campaign_label() {
        show_waiting("label", true);
        $.ajax({
            type : "GET",
            url : "../daemon/ajax_admin/setting_campaign_label_delete.php",
            data : {
                label_id : label_id
            },
            success : function(status) {
                show_waiting("label", false);
                if ("error" == status)
                    show_alert("label", "Campaign label cannot be deleted.");
                else if ("no_cookie" == status)
                    window.location.href = "../../admin/login.php";
                else if ("success" == status)
                    get_label_list();
            },
            failure : function(e) {
                show_waiting("label", false);
                show_alert("label", "Campaign label cannot be deleted.");
            }
        });
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
                    get_offer_list();
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
                    get_offer_list();
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
    var show_count = 500;
    var p = 7;
    var total_offers_count = 0;
    var campaign_waiting = false;
    var label_id = -1;
    var label_waiting = false;
    var selected_campaigns = "";
    var j = "&#10003;";
    var loading_icon = '<img src="../images/loading.gif" style="width:22px;height:22px;">';
    get_crm_id();
    get_offer_list();

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
        selected_campaigns = get_checked_campaigns();
        if ("" != selected_campaigns) {
            if ("action_edit" == $(this).find("a").attr("id")) {
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
            } else if ("action_delete" == $(this).find("a").attr("id")) {
                $("#campaign_action_delete_modal").modal("toggle");
            } else if ("action_goal" == $(this).find("a").attr("id")) {
                $("#campaign_action_goal_modal").modal("toggle");
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
        /** @type {number} */
        cur_page_num = 1;
        get_offer_list();
    });
    $(".campaign_search_button").click(function() {
        /** @type {number} */
        cur_page_num = 1;
        get_offer_list();
    });
    $(".campaign_pagination").on("click", ".campaign_page", function(a) {
        var i = $(this).prop("id").substring(5);
        /** @type {number} */
        var n = Math.floor((cur_page_num - 1) / p);
        if ("first" == i) {
            /** @type {number} */
            cur_page_num = 1;
        } else {
            if ("prev" == i) {
                /** @type {number} */
                cur_page_num = (n - 1) * p + 1;
            } else {
                if ("next" == i) {
                    /** @type {number} */
                    cur_page_num = (n + 1) * p + 1;
                } else {
                    if ("last" == i) {
                        /** @type {number} */
                        cur_page_num = Math.floor(total_offers_count / show_count);
                        if (total_offers_count % show_count > 0) {
                            cur_page_num++;
                        }
                    } else {
                        cur_page_num = i;
                    }
                }
            }
        }
        get_offer_list();
    });
    $(".btn_label_add").click(function() {
        $(".add_label_name").val("");
    });
    $(".modal_btn_label_add").click(function() {
        if ("" == $(".add_label_name").val()) {
            return show_alert("label_add", "Please input Label Name."), void $(".add_label_name").focus();
        }
        $("#campaign_label_add_modal").modal("toggle");
        add_campaign_label();
    });
    $(".table_label_body").on("click", ".setting_label_edit", function(a) {
        label_id = $(this).prop("id").substring(6);
        $(".edit_label_name").val($("#lname_" + label_id).text());
        $(".edit_label_goal").val($("#lgoal_" + label_id).text());
        $(".edit_label_show").prop("checked", "" != $("#lshow_" + label_id).text());
    });
    $(".modal_btn_label_edit").click(function() {
        return "" == $(".edit_label_name").val() ? (show_alert("label_edit", "Please input Label Name."), void $(".edit_label_name").focus()) : "" == $(".edit_label_goal").val() ? (show_alert("label_edit", "Please input Label Goal."), void $(".edit_label_goal").focus()) : ($("#campaign_label_edit_modal").modal("toggle"), void edit_campaign_label());
    });
    $(".table_label_body").on("click", ".setting_label_delete", function(a) {
        label_id = $(this).prop("id").substring(8);
    });
    $(".modal_btn_label_delete").click(function() {
        $("#campaign_label_delete_modal").modal("toggle");
        delete_campaign_label();
    });

    $(".table_campaign_body").on("click", ".setting_offer_edit", function(a) {
        selected_campaigns = $(this).prop("id").substring(6);

        var label = $("#clabel_" + selected_campaigns).text();
        var labels = label.split(', ');
        var vlabel = '';
        var type1 = '';
        var type2 = '';
        if (3 === labels.length) {
            type1 = labels[0];
            type2 = labels[1];
            vlabel = labels[2];
        }
        else if (2 == labels.length) {
            type1 = labels[0];
            vlabel = labels[1];
        }

        $(".modal_tlabel_item").each(function(a) {
            $(this).prop("checked", false);
        });
        $("#tlabel_11").prop("checked", false);
        $("#tlabel_12").prop("checked", false);
        $("#tlabel_21").prop("checked", false);
        $("#tlabel_22").prop("checked", false);
        $("#tlabel_41").prop("checked", false);
        $("#tlabel_42").prop("checked", false);

        if ('Step1' == type1) {
            $("#tlabel_1").prop('checked', true);
            if ('Desktop' == type2)
                $("#tlabel_11").prop("checked", true);
            else if ('Mobile' == type2)
                $("#tlabel_12").prop("checked", true);
        }
        else if ('Step2' == type1) {
            $("#tlabel_2").prop('checked', true);
            if ('Desktop' == type2)
                $("#tlabel_21").prop("checked", true);
            else if ('Mobile' == type2)
                $("#tlabel_22").prop("checked", true);
        }
        else if ('Prepaids' == type1)
            $("#tlabel_3").prop('checked', true);
        else if ('Tablet' == type1) {
            $("#tlabel_4").prop('checked', true);
            if ('Step1' == type2)
                $("#tlabel_41").prop("checked", true);
            else if ('Step2' == type2)
                $("#tlabel_42").prop("checked", true);
        }

        var modal_table_html = "";
        for (var i = 0; i < label_list.length; i++) {
            if ("3" == label_list[i][2]) {
                if (label_list[i][1] == vlabel)
                    modal_table_html += '<tr><td><input type="radio" id="vlabel_' + label_list[i][0] + '" class="modal_vlabel_item" name="vertical" checked></td>';
                else
                    modal_table_html += '<tr><td><input type="radio" id="vlabel_' + label_list[i][0] + '" class="modal_vlabel_item" name="vertical"></td>';
                modal_table_html += "<td>" + label_list[i][1] + "</td></tr>";
            }
        }
        $(".modal_vlabel_body").html(modal_table_html);
    });
    $(".table_campaign_body").on("click", ".setting_offer_delete", function(a) {
        selected_campaigns = $(this).prop("id").substring(8);
    });
    $(".table_campaign_body").on("click", ".setting_offer_goal", function(a) {
        selected_campaigns = $(this).prop("id").substring(6);

        show_waiting("campaign", true);
        $.ajax({
            type : "GET",
            url : "../daemon/ajax_admin/setting_offer_goal_list.php",
            data : {
                crm_id : crm_id,
                campaign_id : selected_campaigns
            },
            success : function(data) {
                show_waiting("campaign", false);
                if ("error" == data) {
                    show_alert("campaign", "Cannot load offer goals.");
                }
                else if ("no_cookie" == data) {
                    window.location.href = "../../admin/login.php";
                }
                else {
                    var goal_list = jQuery.parseJSON(data);
                    var t = "";
                    t += '<div class="alert alert-warning goal_edit_alert" role="alert" style="display:none"></div>';
                    t += '<div class="row" style="margin-bottom:5px;">';
                    t += '<div class="col-xs-5 modal_input_label">Sales Goal</div>';
                    t += '<div class="col-xs-7"><input type="text" class="form-control input-sm edit_total_goal"></div>';
                    t += '</div>';
                    for (var i = 0; i < goal_list.length; i++) {
                        t += '<div class="row" style="margin-bottom:5px;">';
                        t += '<div class="col-xs-5 modal_input_label">' + goal_list[i][1] + '</div>';
                        t += '<div class="col-xs-7"><input type="text" class="form-control input-sm edit_offer_goal" value="' + (null == goal_list[i][2] ? 0 : goal_list[i][2]) + '"></div>';
                        t += '</div>';
                    }
                    $("#affiliate_set_goal").html(t);
                    $("#campaign_action_goal_modal").modal("toggle");
                }
            },
            failure : function(e) {
                show_waiting("campaign", false);
                show_alert("campaign", "Cannot load offer goals.");
            }
        });
    });
    $(".modal_btn_set_goal").click(function() {
        if ("" == $(".edit_offer_goal").val()) {
            return show_alert("action_goal", "Please input sales goal."), void $(".edit_offer_goal").focus();
        }
        $("#campaign_action_goal_modal").modal("toggle");
        // set_campaign_goal();
    });
});
