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
                <h4 class="modal-title affiliation_goal_edit_body_label"></h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid affiliation_goal_edit_body">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_affiliation_goal_edit">Edit Offer Cap</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="affiliation_add_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Affiliate Settings</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning affiliation_add_alert" role="alert" style="display:none; margin-bottom:10px;"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">Name:</div>
                        <div class="col-xs-9"><input type="text" class="form-control input-sm add_affiliation_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">AFID(s):</div>
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
                <h4 class="modal-title">Affiliate Settings</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning affiliation_edit_alert" role="alert" style="display:none; margin-bottom:10px;"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">Name:</div>
                        <div class="col-xs-9"><input type="text" class="form-control input-sm edit_affiliation_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">AFID(s):</div>
                        <div class="col-xs-9"><input type="text" class="form-control input-sm edit_affiliation_afid"></div>
                    </div>
                    <div class="row affiliation_goal_edit_body"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_affiliation_edit">Edit Affiliate</button>
                <button type="button" class="btn btn-danger btn_affiliation_delete" data-toggle="modal" data-target="#affiliation_delete_modal" style="color: white">Delete Affiliate</button>
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
<div class="modal fade" id="affiliation_offer_add_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add/Remove Offers to/from Affiliate  <span class="affiliate_offer_waiting" style="text-align:right"></span></h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning affiliation_offer_add_alert" role="alert" style="display:none; margin-bottom:10px;"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-3 modal_input_label">Select Affiliate</div>
                        <div class="col-xs-9">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle affiliate_toggle_button" data-toggle="dropdown" aria-expanded="false" style="min-width:160px">
                                    <?php
                                    if ($affiliationList != null && count($affiliationList) > 0)
                                        echo $affiliationList[0][1].' ';
                                    else
                                        echo 'None Affiliate ';
                                    ?>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu affiliate_dropdown_menu" role="menu">
                                    <?php
                                    if ($affiliationList != null) {
                                        for ($i = 0; $i < count($affiliationList); $i++)
                                            echo '<li><a href="#" id="'.$affiliationList[$i][0].'" class="affiliate_dropdown_list">'.$affiliationList[$i][1].'</a></li>';
                                    }
                                    ?>
                                </ul>
                            </span>
                        </div>
                    </div>
                    <div class="row" style="margin-top:20px;">
                        <div class="col-xs-5">
                            <select multiple="multiple" name="all_options" class="all_options" style="width: 200px; height: 300px;">
                            </select>
                        </div>
                        <div class="col-xs-2" style="margin-top: 119px;">
                            <button class="go_in" style="display: block; margin: 0 auto 10px auto;">&gt;</button>
                            <button class="go_out" style="display: block; margin: auto;">&lt;</button>
                        </div>
                        <div class="col-xs-5">
                            <select multiple="multiple" name="chosen_options" class="chosen_options" style="width: 200px; height: 300px;">
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_affiliation_offer_add">Add/Remove Offers</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="remove_offer_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    Do you want to remove this offer?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_remove_offer">Remove</button>
            </div>
        </div>
    </div>
</div>