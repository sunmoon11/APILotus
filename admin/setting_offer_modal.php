/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/29/2018
 * Time: 7:03 AM
 */

<div class="modal fade" id="offer_add_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="label_add_offer"></h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning offer_add_alert" role="alert" style="display:none"></div>
                    <div class="alert alert-warning setting_campaign_alert" role="alert" style="display:none"></div>
                    <div class="row crm_board_row">
                        <div style="text-align:right; padding-right: 15px">
                            <div class="col-xs-4 modal_input_label">Name:</div>
                            <div class="col-xs-8"><input type="text" class="form-control input-sm add_offer_name"></div>
                        </div>
                        <div style="text-align:right; padding-right: 15px;">
                            <div class="col-xs-4 modal_input_label" style="margin-top: 10px;">Client Select:</div>
                            <div class="col-xs-8" style="margin-top: 10px;">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle crm_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width: 100%;">
                                        <?php
                                        if ($crmList != null && count($crmList) > 0)
                                            echo $crmList[0][1].' ';
                                        else
                                            echo 'None CRM ';
                                        ?>
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu crm_dropdown_menu" role="menu">
                                        <?php
                                        if ($crmList != null) {
                                            for ($i = 0; $i < count($crmList); $i++)
                                                echo '<li><a href="#" id="'.$crmList[$i][0].'" class="crm_dropdown_list">'.$crmList[$i][1].'</a></li>';
                                        }
                                        ?>
                                    </ul>
                                </span>
                            </div>
                        </div>
                        <div style="text-align:right; padding-right: 15px;">
                            <div class="col-xs-4 modal_input_label" style="margin-top: 10px;">Offer Type:</div>
                            <div class="col-xs-8" style="margin-top: 10px;">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle offer_type_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width: 100%;">
                                        Single Step
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu offer_type_dropdown_menu" role="menu">
                                        <li><a href="#" id="1" class="offer_type_dropdown_list">Single Step</a></li>
                                        <li><a href="#" id="2" class="offer_type_dropdown_list">2 Step</a></li>
                                    </ul>
                                </span>
                            </div>
                        </div>
                        <div style="text-align:right; padding-right: 15px" id="div_default_s1_payout">
                            <div class="col-xs-4 modal_input_label" style="margin-top: 10px;">Default S1 Payout:</div>
                            <div class="col-xs-8" style="margin-top: 10px;"><input type="text" class="form-control input-sm" id="input_default_s1_payout"></div>
                        </div>
                        <div style="text-align:right; padding-right: 15px" id="div_default_s2_payout">
                            <div class="col-xs-4 modal_input_label" style="margin-top: 10px;">Default S2 Payout:</div>
                            <div class="col-xs-8" style="margin-top: 10px;"><input type="text" class="form-control input-sm" id="input_default_s2_payout"></div>
                        </div>
                        <div style="text-align:right; padding-right: 15px;">
                            <div class="col-xs-4 modal_input_label" style="margin-top: 10px;">Vertical:</div>
                            <div class="col-xs-8" style="margin-top: 10px;">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle vertical_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width: 100%;">
                                        <?php
                                        if ($verticalList != null && count($verticalList) > 0)
                                            echo $verticalList[0][1].' ';
                                        else
                                            echo 'None Vertical Labels ';
                                        ?>
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu vertical_dropdown_menu" role="menu">
                                        <?php
                                        if ($verticalList != null) {
                                            for ($i = 0; $i < count($verticalList); $i++)
                                                echo '<li><a href="#" id="'.$verticalList[$i][0].'" class="vertical_dropdown_list">'.$verticalList[$i][1].'</a></li>';
                                        }
                                        ?>
                                    </ul>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="div_select_campaign">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_offer_add">Add Offer</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="offer_delete_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    Do you want to delete this offer?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_offer_delete">Delete</button>
            </div>
        </div>
    </div>
</div>