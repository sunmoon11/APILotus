jQuery(document).ready(function (t) {
    function get_affiliate_offer_id() {
        if ($(".affiliate_dropdown_list").length > 0) {
            affiliate_offer_id = $(".affiliate_dropdown_list").prop("id");
        }
    }

    function show_alert(type, content) {
        if ("main" === type) {
            t(".affiliation_alert").html(content);
            t(".affiliation_alert").fadeIn(1e3, function () {
                t(".affiliation_alert").fadeOut(3e3);
            });
        }
        else if ("add" === type) {
            t(".affiliation_add_alert").html(content);
            t(".affiliation_add_alert").fadeIn(1e3, function () {
                t(".affiliation_add_alert").fadeOut(3e3);
            });
        }
        else if ("edit" === type) {
            t(".affiliation_edit_alert").html(content);
            t(".affiliation_edit_alert").fadeIn(1e3, function () {
                t(".affiliation_edit_alert").fadeOut(3e3);
            });
        }
        else if ("ao_waiting" === type) {
            t(".affiliation_offer_add_alert").html(content);
            t(".affiliation_offer_add_alert").fadeIn(1e3, function () {
                t(".affiliation_offer_add_alert").fadeOut(3e3);
            });
        }
    }

    function show_waiting(type, status) {
        if ("main" === type) {
            status ? t(".affiliation_waiting").html(loading_gif) : t(".affiliation_waiting").html("");
        }
        else if ("ao_waiting" === type) {
            status ? t(".affiliate_offer_waiting").html(loading_gif) : t(".affiliate_offer_waiting").html("");
        }
        else if ("oc_waiting" === type) {
            status ? t(".offer_cap_waiting").html(loading_gif) : t(".offer_cap_waiting").html("");
        }
    }

    function set_dates() {
        t("#from_date").prop("disabled", true);
        t("#to_date").prop("disabled", true);
        var cur_date = new Date;
        var formatted_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());

        var r = cur_date.getDate() + 1;
        0 == cur_date.getDay() ? r -= 7 : r -= cur_date.getDay();
        cur_date.setDate(r);
        from_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
        r = cur_date.getDate() + 6;
        cur_date.setDate(r);
        to_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());

        t("#affiliation_date").val(formatted_date);
        t("#from_date").val(from_date);
        t("#to_date").val(to_date);
    }

    function format_date(year, month, date) {
        if (month < 10) month = "0" + month;
        if (date < 10) date = "0" + date;
        return month + "/" + date + "/" + year;
    }

    function get_offer_list() {
        show_waiting("main", true);
        t.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/offer_list.php",
            data: {},
            success: function (e) {
                show_waiting("main", false);
                if ("no_cookie" === e)
                    window.location.href = "../../admin/login.php";
                else if ("error" === e) {
                    show_alert("main", "Offers cannot be loaded.");
                }
                else {
                    all_offers = jQuery.parseJSON(e);
                    get_affiliate_offers();
                }
            },
            failure: function () {
                show_waiting("main", false);
                show_alert("main", "Offers cannot be loaded.");
            }
        })
    }

    function get_affiliation_list() {
        show_waiting("main", true);
        t(".table_affiliation_body").html("");
        t.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/setting_affiliation_list.php",
            data: {},
            success: function (e) {
                show_waiting("main", false);
                if ("no_cookie" === e)
                    return void (window.location.href = "../../admin/login.php");

                results = jQuery.parseJSON(e);
                var html = "";
                for (var i = 0; i < results.length; i++) {
                    var affiliate = results[i];
                    html += '<tr>';
                    html += '<td><button type="button" class="btn btn-link btn-sm btn_affiliation_edit payment_badge_blue" id="aedit_' + i + '" data-toggle="modal" data-target="#affiliation_edit_modal" style="font-size: inherit">' + affiliate[0][1] + '</div></button>';
                    if (null == affiliate[0][2])
                        html += '<td></td>';
                    else
                        html += '<td style="vertical-align: middle">' + affiliate[0][2] + '</td>';
                    html += '<td></td>';
                    html += '<td></td>';
                    html += '<td><button type="button" class="btn btn-link btn-sm btn_affiliation_goal_edit" id="gedit_' + i + '" data-toggle="modal" data-target="#affiliation_goal_edit_modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span>&nbsp;Edit</button></td>';
                    html += '</tr>';
                    for (var j = 0; j < affiliate[1].length; j++) {
                        var offer = affiliate[1][j];
                        html += '<tr>';
                        html += "<td></td>";
                        html += "<td></td>";
                        html += "<td>" + offer[3] + "</td>";
                        html += "<td>" + offer[4] + "</td>";
                        html += "<td>" + offer[1] + "</td>";
                        html += '</tr>';
                    }
                }
                t(".table_affiliation_body").html(html);
            },
            failure: function (t) {
                show_waiting("main", false);
                show_alert("main", "Cannot load affiliate goal information.");
            }
        })
    }

    function add_affiliate() {
        show_waiting("main", true);
        $.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/setting_affiliation_add.php",
            data: {
                name: $(".add_affiliation_name").val(),
                afid: $(".add_affiliation_afid").val()
            },
            success: function (status) {
                show_waiting("main", false);
                if ("error" == status)
                    show_alert("main", "Affiliate cannot be added.");
                else if ("no_cookie" == status)
                    window.location.href = "../../admin/login.php";
                else if ("success" == status)
                    get_affiliation_list();
            },
            failure: function (e) {
                show_waiting("main", false);
                show_alert("main", "Affiliate cannot be added.");
            }
        });
    }

    function edit_affiliate() {
        show_waiting("main", true);
        $.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/setting_affiliation_edit.php",
            data: {
                affiliate_id: affiliate_id,
                name: $(".edit_affiliation_name").val(),
                afid: $(".edit_affiliation_afid").val()
            },
            success: function (status) {
                show_waiting("main", false);
                if ("error" == status)
                    show_alert("main", "Affiliate cannot be changed.");
                else if ("no_cookie" == status)
                    window.location.href = "../../admin/login.php";
                else if ("success" == status)
                    get_affiliation_list();
            },
            failure: function (e) {
                show_waiting("main", false);
                show_alert("main", "Affiliate cannot be changed.");
            }
        });
    }

    function delete_affiliate() {
        show_waiting("main", true);
        $.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/setting_affiliation_delete.php",
            data: {
                affiliate_id: affiliate_id
            },
            success: function (status) {
                show_waiting("main", false);
                if ("error" == status)
                    show_alert("main", "Affiliate cannot be deleted.");
                else if ("no_cookie" == status)
                    window.location.href = "../../admin/login.php";
                else if ("success" == status)
                    get_affiliation_list();
            },
            failure: function (e) {
                show_waiting("main", false);
                show_alert("main", "Affiliate cannot be deleted.");
            }
        });
    }

    function edit_affiliate_goal(affiliation_goal_id, offer_ids, offer_goals) {
        show_waiting("main", true);
        $.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/setting_affiliation_edit_goal.php",
            data: {
                affiliate_id: affiliation_goal_id,
                offer_ids: offer_ids,
                offer_goals: offer_goals
            },
            success: function (status) {
                show_waiting("main", false);
                if ("error" == status)
                    show_alert("main", "Affiliate goals cannot be changed.");
                else if ("no_cookie" == status)
                    window.location.href = "../../admin/login.php";
                else if ("success" == status)
                    get_affiliation_list();
            },
            failure: function (e) {
                show_waiting("main", false);
                show_alert("main", "Affiliate goals cannot be changed.");
            }
        });
    }

    function check_afids(afids) {
        afids = afids.split(',');
        if ((new Set(afids)).size !== afids.length)
            return false;
        for (var i = 0; i < afids.length; i++) {
            if (isNaN(afids[i]))
                return false;
        }
        return true;
    }

    function get_affiliate_offers() {
        show_waiting("ao_waiting", true);
        $.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/setting_affiliation_offer_list.php",
            data: {
                affiliate_id: affiliate_offer_id
            },
            success: function (data) {
                show_waiting("ao_waiting", false);
                if ("error" == data)
                    show_alert("main", "Affiliate offers cannot be loaded.");
                else if ("no_cookie" == data)
                    window.location.href = "../../admin/login.php";
                else {
                    var sub_offers = jQuery.parseJSON(data);
                    var all_options = '';
                    var chosen_options = '';
                    for (var i = 0; i < all_offers.length; i++) {
                        var contains = false;
                        for (var j = 0; j < sub_offers.length; j++) {
                            if (all_offers[i][0] == sub_offers[j][2]) {
                                contains = true;
                                break;
                            }
                        }
                        if (contains)
                            chosen_options += '<option value="' + all_offers[i][0] + '">' + all_offers[i][1] + '</option>';
                        else
                            all_options += '<option value="' + all_offers[i][0] + '">' + all_offers[i][1] + '</option>';
                        $(".all_options").html(all_options);
                        $(".chosen_options").html(chosen_options);
                    }
                }
            },
            failure: function () {
                show_waiting("ao_waiting", false);
                show_alert("ao_waiting", "Affiliate offers cannot be loaded.");
            }
        });
    }

    function set_affiliate_offers(offer_ids) {
        show_waiting("ao_waiting", true);
        $.ajax({
            type: "GET",
            url: "../daemon/ajax_admin/setting_affiliation_offer_set.php",
            data: {
                affiliate_id: affiliate_offer_id,
                offer_ids: offer_ids
            },
            success: function (status) {
                show_waiting("ao_waiting", false);
                if ("error" == status)
                    show_alert("ao_waiting", "Affiliate offers cannot be saved.");
                else if ("no_cookie" == status)
                    window.location.href = "../../admin/login.php";
                else if ("success" == status) {
                    get_affiliation_list();
                }
            },
            failure: function (e) {
                show_waiting("ao_waiting", false);
                show_alert("ao_waiting", "Affiliate offers cannot be saved.");
            }
        });
    }


    // t("#from_date").datepicker({});
    // t("#to_date").datepicker({});
    // t("#affiliation_date").datepicker({});
    // t("#affiliation_date").change(function () {
    //     var cur_date = new Date($(this).val());
    //     var r = cur_date.getDate() + 1;
    //     0 == cur_date.getDay() ? r -= 7 : r -= cur_date.getDay();
    //     cur_date.setDate(r);
    //     from_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
    //     r = cur_date.getDate() + 6;
    //     cur_date.setDate(r);
    //     to_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
    //     t("#from_date").val(from_date);
    //     t("#to_date").val(to_date);
    // });
    t(".affiliation_search_button").click(function () {
        get_affiliation_list();
    });

    $(".btn_affiliation_add").click(function () {
        $(".add_affiliation_name").val("");
        $(".add_affiliation_afid").val("");
    });
    $(".modal_btn_affiliation_add").click(function () {
        if ("" == $(".add_affiliation_name").val()) {
            show_alert("add", "Please input Affiliate Name.");
            $(".add_affiliation_name").focus();
            return;
        }
        if ("" == $(".add_affiliation_afid").val()) {
            show_alert("add", "Please input AFIDs of Affiliate.");
            $(".add_affiliation_afid").focus();
            return;
        }
        if (false === check_afids($(".add_affiliation_afid").val())) {
            show_alert("add", "There is duplicates or incorrect ids in AFIDs. Please check again.");
            $(".add_affiliation_afid").focus();
            return;
        }

        $("#affiliation_add_modal").modal("toggle");
        add_affiliate();
    });

    $(document).on("click", ".btn_affiliation_edit", function () {
        var id = $(this).prop("id").substring(6);
        affiliate_id = results[id][0][0];
        $(".edit_affiliation_name").val(results[id][0][1]);
        $(".edit_affiliation_afid").val(results[id][0][2]);
    });
    $(".modal_btn_affiliation_edit").click(function () {
        if ("" == $(".edit_affiliation_name").val()) {
            show_alert("edit", "Please input Affiliate Name.");
            $(".edit_affiliation_name").focus();
            return;
        }
        if ("" == $(".edit_affiliation_afid").val()) {
            show_alert("edit", "Please input AFIDs of Affiliate.");
            $(".edit_affiliation_afid").focus();
            return;
        }
        if (false === check_afids($(".edit_affiliation_afid").val())) {
            show_alert("edit", "There is duplicates or incorrect ids in AFIDs. Please check again.");
            $(".edit_affiliation_afid").focus();
            return;
        }

        $("#affiliation_edit_modal").modal("toggle");
        edit_affiliate();
    });

    $(".modal_btn_affiliation_delete").click(function () {
        $("#affiliation_delete_modal").modal("toggle");
        $("#affiliation_edit_modal").modal("toggle");
        delete_affiliate();
    });

    $(document).on("click", ".btn_affiliation_goal_edit", function () {
        var html = "";
        affiliate_goal_id = $(this).prop("id").substring(6);
        var affiliation = results[affiliate_goal_id];
        $(".affiliation_goal_edit_body_label").html(affiliation[0][1] + '&nbsp;&nbsp;<span class="offer_cap_waiting" style="text-align:right"></span>');
        for (var i = 0; i < affiliation[1].length; i++) {
            var offer = affiliation[1][i];
            html += '<div class="row" style="margin-bottom: 5px;">';
            html += '<div class="col-xs-4 modal_input_label">' + offer[3] + "</div>";
            html += '<div class="col-xs-7"><input type="text" id="editgoal_' + offer[2] + '" class="form-control input-sm edit_goals" value="' + offer[1] + '"></div>';
            html += '<div class="col-xs-1"><button type="button" id="doffer_' + offer[2] + '" class="close btn_remove_offer" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            html += "</div>";
        }
        t(".affiliation_goal_edit_body").html(html);
    });
    $(".modal_btn_affiliation_goal_edit").click(function () {
        var ids = "";
        var goals = "";
        t(".edit_goals").each(function () {
            "" != ids && (ids += ",");
            "" != goals && (goals += ",");
            ids += t(this).prop("id").substring(9);
            "" == t(this).val() ? goals += "0" : goals += t(this).val();
        });
        $("#affiliation_goal_edit_modal").modal("toggle");
        edit_affiliate_goal(results[affiliate_goal_id][0][0], ids, goals);
    });

    $(".affiliate_dropdown_menu li").on("click", function () {
        affiliate_offer_id = $(this).find("a").attr("id");
        $(".affiliate_toggle_button").html($(this).text() + ' <span class="caret"></span>');
        get_affiliate_offers();
    });
    $(".modal_btn_affiliation_offer_add").click(function () {
        var ids = "";
        t(".chosen_options option").each(function () {
            "" != ids && (ids += ",");
            ids += $(this).val();
        });
        $("#affiliation_offer_add_modal").modal("toggle");
        set_affiliate_offers(ids);
    });

    $(document).on("click", ".btn_remove_offer", function () {
        selected_offer_id = $(this).prop("id").substring(7);
        $("#remove_offer_modal").modal("toggle");
    });
    $(".modal_btn_remove_offer").click(function () {
        var html = "";
        var affiliation = results[affiliate_goal_id];
        for (var i = 0; i < affiliation[1].length; i++) {
            var offer = affiliation[1][i];
            if (offer[2] == selected_offer_id) {
                affiliation[1].splice(i, 1);
                i--;
                continue;
            }
            html += '<div class="row" style="margin-bottom: 5px;">';
            html += '<div class="col-xs-4 modal_input_label">' + offer[3] + "</div>";
            html += '<div class="col-xs-7"><input type="text" id="editgoal_' + offer[2] + '" class="form-control input-sm edit_goals" value="' + offer[1] + '"></div>';
            html += '<div class="col-xs-1"><button type="button" id="doffer_' + offer[2] + '" class="close btn_remove_offer" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
            html += "</div>";
        }
        t(".affiliation_goal_edit_body").html(html);
        $("#remove_offer_modal").modal("toggle");
    });

    $('.go_in').click(function() {
        return !$('.all_options option:selected').remove().appendTo('.chosen_options');
    });
    $('.go_out').click(function() {
        return !$('.chosen_options option:selected').remove().appendTo('.all_options');
    });

    var loading_gif = '<img src="../images/loading.gif" style="width:22px;height:22px;">';
    var from_date = "";
    var to_date = "";
    var results = null;
    var all_offers = null;
    var affiliate_id = -1;
    var affiliate_goal_id = -1;
    var affiliate_offer_id = -1;
    var selected_offer_id = -1;

    // set_dates();
    get_affiliate_offer_id();
    get_offer_list();
    get_affiliation_list();
});
