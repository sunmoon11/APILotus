/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/29/2018
 * Time: 7:03 AM
 */

<div class="modal fade" id="campaign_action_edit_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Campaign Label</h4>
            </div>
            <div class="modal-body" style="max-height:700px;overflow-y:auto;">
                <div class="container-fluid">
                    <div class="alert alert-warning action_edit_alert" role="alert" style="display:none"></div>
                    <label>Campaign Type</label>
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th style="width:100px">#</th>
                            <th>Type Name</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><input type="radio" id="tlabel_1" class="modal_tlabel_item" name="type"></td>
                            <td>Step1</td>
                            <td><input type="checkbox" id="tlabel_11" class="input-sm" style="vertical-align:middle;margin:0;padding:0;">&nbsp;Desktop</td>
                            <td><input type="checkbox" id="tlabel_12" class="input-sm" style="vertical-align:middle;margin:0;padding:0;">&nbsp;Mobile</td>
                        </tr>
                        <tr>
                            <td><input type="radio" id="tlabel_2" class="modal_tlabel_item" name="type"></td>
                            <td>Step2</td>
                            <td><input type="checkbox" id="tlabel_21" class="input-sm" style="vertical-align:middle;margin:0;padding:0;">&nbsp;Desktop</input></td>
                            <td><input type="checkbox" id="tlabel_22" class="input-sm" style="vertical-align:middle;margin:0;padding:0;">&nbsp;Mobile</input></td>
                        </tr>
                        <tr>
                            <td><input type="radio" id="tlabel_3" class="modal_tlabel_item" name="type"></td>
                            <td>Prepaids</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td><input type="radio" id="tlabel_4" class="modal_tlabel_item" name="type"></td>
                            <td>Tablet</td>
                            <td><input type="checkbox" id="tlabel_41" class="input-sm" style="vertical-align:middle;margin:0;padding:0;">&nbsp;Step1</input></td>
                            <td><input type="checkbox" id="tlabel_42" class="input-sm" style="vertical-align:middle;margin:0;padding:0;">&nbsp;Step2</input></td>
                        </tr>
                        </tbody>
                    </table>
                    <label style="margin-top: 10px;">Vertical Label</label>
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th style="width:100px">#</th>
                            <th>Label Name</th>
                        </tr>
                        </thead>
                        <tbody class="modal_vlabel_body">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_action_edit">Apply Label</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="campaign_action_delete_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    Do you want to delete this CRM label?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_action_delete">Delete</button>
            </div>
        </div>
    </div>
</div>