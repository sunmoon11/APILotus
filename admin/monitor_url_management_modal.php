<div class="modal fade" id="url_add_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Monitor URL Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning url_add_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Site Name</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm add_site_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Site URL</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm add_site_url"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_url_add">Add URL</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="url_edit_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Monitor URL Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning url_edit_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Site Name</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm edit_site_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Site URL</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm edit_site_url"></div>
                    </div>                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_url_edit">Edit URL</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="url_delete_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    Do you want to delete this URL?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_url_delete">Delete</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="url_setting_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Monitor URL Setting</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning url_setting_alert" role="alert" style="display:none"></div>
                    <div class="row">
                        <div class="col-xs-4 modal_input_label">Interval (minute)</div>
                        <div class="col-xs-8" style="height:30px;">
                            <input id="min_5" type="radio" class="input-sm edit_interval" name="interval" style="vertical-align:middle;margin:0;padding:0;" checked="checked">&nbsp;5</input>
                            <input id="min_10" type="radio" class="input-sm edit_interval" name="interval" style="vertical-align:middle;margin:0;padding:0;margin-left:28px;">&nbsp;10</input>
                            <input id="min_15" type="radio" class="input-sm edit_interval" name="interval" style="vertical-align:middle;margin:0;padding:0;margin-left:28px;">&nbsp;15</input>
                            <input id="min_20" type="radio" class="input-sm edit_interval" name="interval" style="vertical-align:middle;margin:0;padding:0;margin-left:28px;">&nbsp;20</input>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-4 modal_input_label">Receiver</div>
                        <div class="col-xs-8" style="height:30px;">
                            <input id="receiver_0" type="checkbox" class="input-sm edit_receiver" style="vertical-align:middle;margin:0;padding:0;">&nbsp;SMS</input>
                            <input id="receiver_1" type="checkbox" class="input-sm edit_receiver" style="vertical-align:middle;margin:0;padding:0;margin-left:40px;">&nbsp;Email</input>
                            <input id="receiver_2" type="checkbox" class="input-sm edit_receiver" style="vertical-align:middle;margin:0;padding:0;margin-left:41px;">&nbsp;Telegram Bot</input>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_url_setting">Apply Setting</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="import_confirm_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid confirm_message">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success modal_btn_import_yes">Yes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>
