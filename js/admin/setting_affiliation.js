jQuery(document).ready(function(t) {
    function show_alert(e) {
        t(".affiliation_alert").html(e);
        t(".affiliation_alert").fadeIn(1e3, function() {
            t(".affiliation_alert").fadeOut(3e3);
        });
    }
    function show_status(e, r, a) {
        "list" == e && (a ? t(".affiliation_waiting").html(loading_gif) : t(".affiliation_waiting").html(""));
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
    function get_affiliation() {
        show_status("list", "", true);
        t(".table_affiliation_body").html("");
        if ("" == t("#from_date").val()) {
            show_alert("Please select FROM DATE.");
        }
        else if ("" == t("#to_date").val()) {
            show_alert("Please select TO DATE.");
        }
        else {
            t.ajax({
                type: "GET",
                url: "../daemon/ajax_admin/setting_affiliation_list.php",
                data: {
                    from_date: t("#from_date").val(),
                    to_date: t("#to_date").val()
                },
                success: function(e) {
                    show_status("list", "", false);
                    t(".table_affiliation_body").html('');
                    if ("no_cookie" === e)
                        return void (window.location.href = "../../admin/login.php");

                    var results = jQuery.parseJSON(e);
                    var html = "";
                    for (var i = 0; i < results.length; i++) {
                        var affiliate = results[i];
                        for (var j = 0; j < affiliate[1].length; j++) {
                            var offer = affiliate[1][j];
                            html += '<tr>';
                            html += "<td>" + affiliate[0] + "</td>";
                            html += "<td>" + offer[1] + "</td>";
                            html += "<td>" + offer[2] + "</td>";
                            html += "<td>" + offer[3] + "</td>";
                            html += '<td><button type="button" class="btn btn-link btn-sm setting_offer_edit" id="oedit_' + offer[0] + '" data-toggle="modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span>&nbsp;Edit</button>';
                            html += '<button type="button" class="btn btn-link btn-sm setting_offer_delete" id="odelete_' + offer[0] + '" data-toggle="modal" data-target="#offer_delete_modal"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true" style="color: #ffa5a5"></span>&nbsp;Delete</button></td>';
                            html += '</tr>';
                        }
                    }
                    t(".table_affiliation_body").html(html);
                },
                failure: function(t) {
                    show_alert("Cannot load affiliate goal information.");
                }
            })
        }
    }
    t("#from_date").datepicker({});
    t("#to_date").datepicker({});
    t("#affiliation_date").datepicker({});
    t("#affiliation_date").change(function () {
        var cur_date = new Date($(this).val());
        var r = cur_date.getDate() + 1;
        0 == cur_date.getDay() ? r -= 7 : r -= cur_date.getDay();
        cur_date.setDate(r);
        from_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
        r = cur_date.getDate() + 6;
        cur_date.setDate(r);
        to_date = format_date(cur_date.getFullYear(), cur_date.getMonth() + 1, cur_date.getDate());
        t("#from_date").val(from_date);
        t("#to_date").val(to_date);
    });
    t(".affiliation_search_button").click(function() {
        get_affiliation("1");
    });


    var loading_gif = '<img src="../images/loading.gif" style="width:22px;height:22px;">';
    var minus_sign = '<span class="glyphicon glyphicon-minus-sign" aria-hidden="true" style="color: #ffa5a5"></span>';
    var triangle_sign = '<span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true" style="color: #ffa5a5"></span>';
    var from_date = "";
    var to_date = "";

    set_dates();
    get_affiliation();
});
