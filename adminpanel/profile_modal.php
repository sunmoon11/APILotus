<div class="modal fade" id="change_password_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Password Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning change_password_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:10px;">
                        <div class="col-xs-4 modal_input_label">Password</div>
                        <div class="col-xs-8"><input type="password" class="form-control input-sm input_password"></div>
                    </div>
                    <div class="row" style="margin-bottom:10px;">
                        <div class="col-xs-4 modal_input_label">Re Password</div>
                        <div class="col-xs-8"><input type="password" class="form-control input-sm input_repassword"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_change_password">Change Password</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="edit_profile_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Profile Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning edit_profile_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:10px;">
                        <div class="col-xs-4 modal_input_label">User ID</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm input_user_id" readonly="readonly"></div>
                    </div>
                    <div class="row" style="margin-bottom:10px;">
                        <div class="col-xs-4 modal_input_label">User Name</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm input_display_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:10px;">
                        <div class="col-xs-4 modal_input_label">Email Address</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm input_email_address"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_edit_profile">Update Profile</button>
            </div>
        </div>
    </div>
</div>