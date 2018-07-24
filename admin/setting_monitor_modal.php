<div class="modal fade" id="monitor_add_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Monitor Url Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid modal_input_url_body">
                    <div class="alert alert-warning monitor_add_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Name</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm add_site_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Url</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm add_site_url"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_site_add">Add Url</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="monitor_schedule_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Monitor Schedule Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning alert_edit_time" role="alert" style="display:none"></div>
<!--                    <div class="row" style="margin-bottom:5px;">-->
<!--                        <div class="col-xs-2 modal_input_label">Day</div>-->
<!--                        <div class="col-xs-10" style="height:30px;">-->
<!--                            <input id="day_Sun" type="checkbox" class="input-sm edit_day" style="vertical-align:middle;margin:0;padding:0;">&nbsp;Sun</input>-->
<!--                            <input id="day_Mon" type="checkbox" class="input-sm edit_day" style="vertical-align:middle;margin:0;padding:0;margin-left:9px;">&nbsp;Mon</input>-->
<!--                            <input id="day_Tue" type="checkbox" class="input-sm edit_day" style="vertical-align:middle;margin:0;padding:0;margin-left:9px;">&nbsp;Tue</input>-->
<!--                            <input id="day_Wed" type="checkbox" class="input-sm edit_day" style="vertical-align:middle;margin:0;padding:0;margin-left:10px;">&nbsp;Wed</input>-->
<!--                            <input id="day_Thu" type="checkbox" class="input-sm edit_day" style="vertical-align:middle;margin:0;padding:0;margin-left:10px;">&nbsp;Thu</input>-->
<!--                            <input id="day_Fri" type="checkbox" class="input-sm edit_day" style="vertical-align:middle;margin:0;padding:0;margin-left:10px;">&nbsp;Fri</input>-->
<!--                            <input id="day_Sat" type="checkbox" class="input-sm edit_day" style="vertical-align:middle;margin:0;padding:0;margin-left:9px;">&nbsp;Sat</input>-->
<!--                        </div>-->
<!--                    </div>-->
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-2 modal_input_label">Interval</div>
                        <div class="col-xs-10" style="height:30px;align-items:center;display: flex">
                            <label class="radio-inline"><input id="interval_1" type="radio" name="interval">1</label>
                            <label class="radio-inline" style="margin-left: 15px;"><input id="interval_5" type="radio" name="interval">5</label>
                            <label class="radio-inline" style="margin-left: 15px;"><input id="interval_10" type="radio" name="interval">10</label>
                            <label class="radio-inline" style="margin-left: 15px;"><input id="interval_15" type="radio" name="interval">15</label>
                            <label class="radio-inline" style="margin-left: 15px;"><input id="interval_20" type="radio" name="interval">20 &nbsp;&nbsp;<small>(minute)</small></label>

                        </div>
                    </div>
                    <div class="row" style="margin-bottom: 5px;">
                        <div class="col-xs-2 modal_input_label">Receiver</div>
                        <div class="col-xs-10" style="height:30px;">
                            <input id="receiver_sms" type="checkbox" class="input-sm edit_receiver" style="vertical-align:middle;margin:0;padding:0;">&nbsp;SMS</input>
                            <input id="receiver_email" type="checkbox" class="input-sm edit_receiver" style="vertical-align:middle;margin:0;padding:0;margin-left:43px;">&nbsp;Email</input>
                            <input id="receiver_tbot" type="checkbox" class="input-sm edit_receiver" style="vertical-align:middle;margin:0;padding:0;margin-left:38px;">&nbsp;Telegram Bot</input>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-offset-8" style="height: 30px;">
                            <input id="disable_monitor" type="checkbox" class="input-sm" style="vertical-align:middle;margin:0;padding:0;">&nbsp Disable Monitoring</input>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_apply_schedule">Apply Schedule</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="monitor_url_edit_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Monitor Url Edit Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning monitor_url_edit_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">Name</div>
                        <div class="col-xs-9"><input type="text" class="form-control input-sm edit_monitor_url_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">Url</div>
                        <div class="col-xs-9"><input type="text" class="form-control input-sm edit_monitor_url"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_monitor_url_edit">Apply Url</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="monitor_url_delete_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    Do you want to delete url?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_monitor_url_delete">Delete</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="monitor_url_history">
    <div class="modal-dialog" style="width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="col-xs-10">
                    <h4 class="modal-title"><span class="history_site_name"></span></h4>
                    <div style="background-color: #0088cc; width: 100px;height:100%">
                    </div>
                </div>
                <div class="col-xs-2">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <canvas id="url_history_chart" style="width: 1500px;height: 300px"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>