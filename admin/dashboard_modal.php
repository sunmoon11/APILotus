<div class="modal fade" id="setting_edit_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">CRM Settings Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning setting_edit_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-6 modal_input_label"><label>CRM Management</label></div>                        
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-6 modal_input_label">CRM Name</div>
                        <div class="col-xs-6"><input type="text" class="form-control input-sm edit_crm_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-6 modal_input_label">CRM Site URL</div>
                        <div class="col-xs-6"><input type="text" class="form-control input-sm edit_crm_url"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-6 modal_input_label">CRM User Name</div>
                        <div class="col-xs-6"><input type="text" class="form-control input-sm edit_crm_username"></div>
                    </div>
                    <!--
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-6 modal_input_label">CRM Password</div>
                        <div class="col-xs-6"><input type="text" class="form-control input-sm edit_crm_password"></div>
                    </div>
                    -->
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-6 modal_input_label">API User Name</div>
                        <div class="col-xs-6"><input type="text" class="form-control input-sm edit_api_username"></div>
                    </div>
                    <!--
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-6 modal_input_label">API Password</div>
                        <div class="col-xs-6"><input type="text" class="form-control input-sm edit_api_password"></div>
                    </div>
                    -->
                    <div class="row">
                        <div class="col-xs-6 modal_input_label">Sales Goal</div>
                        <div class="col-xs-6"><input type="text" class="form-control input-sm edit_sales_goal"></div>
                    </div>
                    <div class="row" style="margin-top:10px">
                        <div class="col-xs-6 col-xs-offset-6" style="height:30px;"><input type="checkbox" class="input-sm edit_crm_paused" style="vertical-align:middle;margin:0;padding:0;">&nbsp;&nbsp;Pause CRM</div>
                    </div>                    
                </div>
                <div class="row" style="width:100%;height:1px;background:#dadada;margin:10px 0 10px 0"></div>
                <div class="container-fluid modal_setting_alert_body">                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_setting_edit">Apply Settings</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="crm_position_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">CRM Position Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row" style="padding-bottom: 20px">
                        * Please drag and drop crms for repositioning.
                    </div>
                    <div class="row" style="text-align: center;">
                        <div class="col-xs-2">
                            <ul id="crm_number_ul" style="list-style-type: none; margin: 0; padding: 0"></ul>
                        </div>
                        <div class="col-xs-10">
                            <ul id="crm_position_ul" style="list-style-type: none; margin: 0; padding: 0"></ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_crm_position">Apply Position</button>
            </div>
        </div>
    </div>
</div>
<!--
<div class="modal fade" id="show_columns_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Dashboard List Items</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning show_columns_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-12 modal_input_label" style="text-align:left"><label>Please check the items to be showed in Dashboard List.</label></div>                        
                    </div>
                    <div class="row">
                        <div class="col-xs-6 modal_input_label">STEP1</div>
                        <div class="col-xs-6"><input type="checkbox" class="input-sm show_column_1" style="vertical-align:middle;margin:0;padding:0;" checked="true" disabled="disabled"></input></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 modal_input_label">STEP2</div>
                        <div class="col-xs-6"><input type="checkbox" class="input-sm show_column_2" style="vertical-align:middle;margin:0;padding:0;" checked="true" disabled="disabled"></input></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 modal_input_label">TAKE RATE</div>
                        <div class="col-xs-6"><input type="checkbox" class="input-sm show_column_3" style="vertical-align:middle;margin:0;padding:0;" checked="true" disabled="disabled"></input></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 modal_input_label">TABLET</div>
                        <div class="col-xs-6"><input type="checkbox" class="input-sm show_column_4" style="vertical-align:middle;margin:0;padding:0;" checked="true"></input></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 modal_input_label">TABLET%</div>
                        <div class="col-xs-6"><input type="checkbox" class="input-sm show_column_5" style="vertical-align:middle;margin:0;padding:0;" checked="true"></input></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 modal_input_label">PREPAIDS</div>
                        <div class="col-xs-6"><input type="checkbox" class="input-sm show_column_6" style="vertical-align:middle;margin:0;padding:0;" checked="true"></input></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 modal_input_label">ORDER%</div>
                        <div class="col-xs-6"><input type="checkbox" class="input-sm show_column_7" style="vertical-align:middle;margin:0;padding:0;" checked="true"></input></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 modal_input_label">DECLINE%</div>
                        <div class="col-xs-6"><input type="checkbox" class="input-sm show_column_8" style="vertical-align:middle;margin:0;padding:0;" checked="true"></input></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_show_columns">Apply Items</button>
            </div>
        </div>
    </div>
</div>
-->