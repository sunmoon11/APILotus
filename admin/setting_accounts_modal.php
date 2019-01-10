<div class="modal fade" id="account_add_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Account Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning account_add_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">User ID</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm add_user_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Password</div>
                        <div class="col-xs-8"><input type="password" class="form-control input-sm add_password"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Re Password</div>
                        <div class="col-xs-8"><input type="password" class="form-control input-sm add_repassword"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">User Name</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm add_display_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">SMS Number</div>
                        <div class="col-xs-4"><input type="text" class="form-control input-sm add_sms_number"></div>
                        <div class="col-xs-4"><input type="checkbox" class="input-sm add_disable_sms" style="vertical-align:middle;margin:0;padding:0;">&nbsp;&nbsp;Disable SMS Alert</input></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Email Address</div>
                        <div class="col-xs-4"><input type="text" class="form-control input-sm add_email_address"></div>
                        <div class="col-xs-4"><input type="checkbox" class="input-sm add_disable_email" style="vertical-align:middle;margin:0;padding:0;">&nbsp;&nbsp;Disable Email Alert</input></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Telegram Bot (Chat ID)</div>
                        <div class="col-xs-4">
                            <input type="text" class="form-control input-sm add_telegram_bot">
                        </div>
                        <div class="col-xs-4"><input type="checkbox" class="input-sm add_disable_bot" style="vertical-align:middle;margin:0;padding:0;">&nbsp;&nbsp;Disable Bot Alert</input></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-4 modal_input_label">User Role</div>
                        <div class="col-xs-8">
                            <select name="authority" class="input-sm form-control add_role">
                                <option value="0">Regular User</option>
                                <option value="1">Super User</option>
                                <option value="9">Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" style="margin-top:10px">
                        <div class="col-xs-8 col-xs-offset-4" style="height:30px;"><input type="checkbox" class="input-sm add_disable_account" style="vertical-align:middle;margin:0;padding:0;">&nbsp;&nbsp;Disable Account</input></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_account_add">Add Account</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="account_edit_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Account Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning account_edit_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">User Name</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm edit_display_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">User ID</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm edit_user_name" disabled="disabled"></div>
                    </div>
<!--                    <div class="row" style="margin-bottom:5px;">-->
<!--                        <div class="col-xs-4 modal_input_label">Password</div>-->
<!--                        <div class="col-xs-8"><input type="password" class="form-control input-sm add_password"></div>-->
<!--                    </div>-->
<!--                    <div class="row" style="margin-bottom:5px;">-->
<!--                        <div class="col-xs-4 modal_input_label">Re Password</div>-->
<!--                        <div class="col-xs-8"><input type="password" class="form-control input-sm add_repassword"></div>-->
<!--                    </div>-->
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">SMS Number</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm edit_sms_number"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Email Address</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm edit_email_address"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Telegram Bot (Chat ID)</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm edit_telegram_bot"></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-4 modal_input_label">User Role</div>
                        <div class="col-xs-8">
                            <select name="authority" class="input-sm form-control edit_role" <?php if (isset($_SESSION['role']) && $_SESSION['role'] != '9') echo 'disabled="disabled"'; ?>>
                                <option value="0">Regular User</option>
                                <option value="1">Super User</option>
                                <option value="9">Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" style="margin-top:10px">
                        <div class="col-xs-8 col-xs-offset-4" style="height:30px;"><input type="checkbox" class="input-sm edit_disable_account" style="vertical-align:middle;margin:0;padding:0;">&nbsp;&nbsp;Disable Account</input></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-8 col-xs-offset-4" style="height:30px;"><input type="checkbox" class="input-sm edit_disable_sms" style="vertical-align:middle;margin:0;padding:0;">&nbsp;&nbsp;Disable SMS Alert</input></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-8 col-xs-offset-4" style="height:30px;"><input type="checkbox" class="input-sm edit_disable_email" style="vertical-align:middle;margin:0;padding:0;">&nbsp;&nbsp;Disable Email Alert</input></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-8 col-xs-offset-4" style="height:30px;"><input type="checkbox" class="input-sm edit_disable_bot" style="vertical-align:middle;margin:0;padding:0;">&nbsp;&nbsp;Disable Bot Alert</input></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_account_edit">Apply Account</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="account_password_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Account Password Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning account_password_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">New Password</div>
                        <div class="col-xs-9"><input type="password" class="form-control input-sm edit_password"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">Re Password</div>
                        <div class="col-xs-9"><input type="password" class="form-control input-sm edit_repassword"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_account_password">Apply Password</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="account_delete_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    Do you want to delete this account?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_account_delete">Delete</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="permission_edit_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Client Permissions</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row" style="padding: 15px; border: 1px solid #ccc;">
                        <div class="permission_table">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr class="permission_category">
                                        <th>Client Name</th>
                                        <th>Enable <span id="penable" class="permission_all_btn">All</span></th>
                                        <th>Disable <span id="pdisable" class="permission_all_btn">All</span></th>
                                    </tr>
                                </thead>
                                <tbody class="table_permission_body">
                                </tbody>
                            </table>
                        </div>
                        <div id="selected_perms_count" style="text-align: right"></div>
                    </div>
                </div>
                <div class="container-fluid" style="margin-top: 10px;">
                    <div class="row" style="padding: 15px; border: 1px solid #ccc;">
                        <div style="overflow: auto; max-height: 300px;">
                            <table class="table table-hover">
                                <tbody>
                                    <tr class="permission_category">
                                        <td>User Profile</td>
                                        <td>Enable <span id="penable_1" class="permission_all_btn">All</span></td>
                                        <td>Disable <span id="pdisable_1" class="permission_all_btn">All</span></td>
                                    </tr>
                                    <tr>
                                        <td>My Profile</td>
                                        <td><input id="penable_11" type="radio" class="penable_item" name="pradio_11"/></td>
                                        <td><input id="pdisable_11" type="radio" class="pdisable_item" name="pradio_11"/></td>
                                    </tr>
                                    <tr>
                                        <td>Payment Management</td>
                                        <td><input id="penable_12" type="radio" class="penable_item" name="pradio_12"/></td>
                                        <td><input id="pdisable_12" type="radio" class="pdisable_item" name="pradio_12"/></td>
                                    </tr>

                                    <tr class="permission_category">
                                        <td>Reports</td>
                                        <td>Enable <span id="penable_2" class="permission_all_btn">All</span></td>
                                        <td>Disable <span id="pdisable_2" class="permission_all_btn">All</span></td>
                                    </tr>
                                    <tr>
                                        <td>Initial Report</td>
                                        <td><input id="penable_21" type="radio" class="penable_item" name="pradio_21"/></td>
                                        <td><input id="pdisable_21" type="radio" class="pdisable_item" name="pradio_21"/></td>
                                    </tr>
                                    <tr>
                                        <td>Rebill Report</td>
                                        <td><input id="penable_22" type="radio" class="penable_item" name="pradio_22"/></td>
                                        <td><input id="pdisable_22" type="radio" class="pdisable_item" name="pradio_22"/></td>
                                    </tr>

                                    <tr class="permission_category">
                                        <td>Settings</td>
                                        <td>Enable <span id="penable_4" class="permission_all_btn">All</span></td>
                                        <td>Disable <span id="pdisable_4" class="permission_all_btn">All</span></td>
                                    </tr>
                                    <tr>
                                        <td>Client Setup</td>
                                        <td><input id="penable_41" type="radio" class="penable_item" name="pradio_41"/></td>
                                        <td><input id="pdisable_41" type="radio" class="pdisable_item" name="pradio_41"/></td>
                                    </tr>
                                    <tr>
                                        <td>Campaign Management</td>
                                        <td><input id="penable_42" type="radio" class="penable_item" name="pradio_42"/></td>
                                        <td><input id="pdisable_42" type="radio" class="pdisable_item" name="pradio_42"/></td>
                                    </tr>
                                    <tr>
                                        <td>Alert Percentage Levels</td>
                                        <td><input id="penable_43" type="radio" class="penable_item" name="pradio_43"/></td>
                                        <td><input id="pdisable_43" type="radio" class="pdisable_item" name="pradio_43"/></td>
                                    </tr>
                                    <tr>
                                        <td>User Accounts</td>
                                        <td><input id="penable_44" type="radio" class="penable_item" name="pradio_44"/></td>
                                        <td><input id="pdisable_44" type="radio" class="pdisable_item" name="pradio_44"/></td>
                                    </tr>

                                    <tr class="permission_category">
                                        <td>CAP Update</td>
                                        <td>Enable <span id="penable_3" class="permission_all_btn">All</span></td>
                                        <td>Disable <span id="pdisable_3" class="permission_all_btn">All</span></td>
                                    </tr>
                                    <tr>
                                        <td>Cap Update</td>
                                        <td><input id="penable_31" type="radio" class="penable_item" name="pradio_31"/></td>
                                        <td><input id="pdisable_31" type="radio" class="pdisable_item" name="pradio_31"/></td>
                                    </tr>
                                    <tr>
                                        <td>Offers</td>
                                        <td><input id="penable_32" type="radio" class="penable_item" name="pradio_32"/></td>
                                        <td><input id="pdisable_32" type="radio" class="pdisable_item" name="pradio_32"/></td>
                                    </tr>
                                    <tr>
                                        <td>Affiliate Settings</td>
                                        <td><input id="penable_33" type="radio" class="penable_item" name="pradio_33"/></td>
                                        <td><input id="pdisable_33" type="radio" class="pdisable_item" name="pradio_33"/></td>
                                    </tr>

                                    <tr class="permission_category">
                                        <td>Billing</td>
                                        <td><input id="penable_51" type="radio" class="penable_item" name="pradio_51"/></td>
                                        <td><input id="pdisable_51" type="radio" class="pdisable_item" name="pradio_51"/></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_permission_edit">Apply Permission</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="block_ip_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Block IP Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning block_ip_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">IP Address</div>
                        <div class="col-xs-9"><input type="text" class="form-control input-sm ip_address"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">Description</div>
                        <div class="col-xs-9"><input type="text" class="form-control input-sm ip_description"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_blockip_apply">Apply Block IP</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="blockip_delete_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    Do you want to delete this IP address in Block List?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_blockip_delete">Delete</button>
            </div>
        </div>
    </div>
</div>