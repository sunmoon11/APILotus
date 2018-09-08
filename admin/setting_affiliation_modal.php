/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/2/2018
 * Time: 3:37 AM
 */

<div class="modal fade" id="affiliation_goal_edit_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title affiliation_goal_edit_body_label">Sales Goal</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid affiliation_goal_edit_body">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_affiliation_goal_edit">Edit Affiliate Goal</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="affiliation_add_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Affiliate</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning affiliation_add_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">Affiliate Name</div>
                        <div class="col-xs-9"><input type="text" class="form-control input-sm add_affiliation_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">AFIDs</div>
                        <div class="col-xs-9"><input type="text" class="form-control input-sm add_affiliation_afid"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_affiliation_add">Add Affiliate</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="affiliation_edit_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Affiliate</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning affiliation_edit_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">Affiliate Name</div>
                        <div class="col-xs-9"><input type="text" class="form-control input-sm edit_affiliation_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">AFIDs</div>
                        <div class="col-xs-9"><input type="text" class="form-control input-sm edit_affiliation_afid"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_affiliation_edit">Edit Affiliate</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="affiliation_delete_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    Do you want to delete this affiliation?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_affiliation_delete">Delete</button>
            </div>
        </div>
    </div>
</div>