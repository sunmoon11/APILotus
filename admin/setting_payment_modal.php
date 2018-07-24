<div class="modal fade" id="waiting_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body" style="text-align:center">
            	<img src="../images/loading.gif" style="width:22px;height:22;">
                <span style="padding-left:5px">Please wait a moment.</span>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="new_card_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">New Card</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row" style="margin-top:20px;">
			            <div class="col-xs-4 modal_input_label">Credit Card Number <span class="red_char">*</span></div>
			            <div class="col-xs-8"><div id="card_number" class="card_credentials"></div></div>
			        </div>
			        <div class="row">
			            <div class="col-xs-8 col-xs-offset-4" style="margin-top:5px;height:20px"><span id="warning_card_number" class="red_char"></span></div>
			        </div>
			        <div class="row" style="margin-top:20px;">
			            <div class="col-xs-4 modal_input_label">Expiry Date <span class="red_char">*</span></div>
			            <div class="col-xs-8"><div id="card_expiry" class="card_credentials"></div></div>
			        </div>
			        <div class="row">
			            <div class="col-xs-8 col-xs-offset-4" style="margin-top:5px;height:20px"><span id="warning_expiry_date" class="red_char"></span></div>
			        </div>
			        <div class="row" style="margin-top:20px;">
			            <div class="col-xs-4 modal_input_label">CVC Number <span class="red_char">*</span></div>
			            <div class="col-xs-8"><div id="card_cvc" class="card_credentials"></div></div>
			        </div>
			        <div class="row">
			            <div class="col-xs-8 col-xs-offset-4" style="margin-top:5px;height:20px"><span id="warning_cvc_number" class="red_char"></span></div>
			        </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button id="modal_btn_new_card" type="button" class="btn btn-success">New Card</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="edit_card_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Edit Card</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Expiry Month</div>
                        <div class="col-xs-8">
                        	<select id="expiry_month" class="form-control input-sm">
                        		<option value="1">01</option>
                        		<option value="2">02</option>
                        		<option value="3">03</option>
                        		<option value="4">04</option>
                        		<option value="5">05</option>
                        		<option value="6">06</option>
                        		<option value="7">07</option>
                        		<option value="8">08</option>
                        		<option value="9">09</option>
                        		<option value="10">10</option>
                        		<option value="11">11</option>
                        		<option value="12">12</option>
                        	</select>
                        </div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Expiry Year</div>
                        <div class="col-xs-8">
                        	<select id="expiry_year" class="form-control input-sm">
                        		<option value="2017">2017</option>
                        		<option value="2018">2018</option>
                        		<option value="2019">2019</option>
                        		<option value="2020">2020</option>
                        		<option value="2021">2021</option>
                        		<option value="2022">2022</option>
                        		<option value="2023">2023</option>
                        		<option value="2024">2024</option>
                        		<option value="2025">2025</option>
                        		<option value="2026">2026</option>
                        		<option value="2027">2027</option>
                        		<option value="2028">2028</option>
                        		<option value="2029">2029</option>
                        		<option value="2030">2030</option>
                        		<option value="2031">2031</option>
                        		<option value="2032">2032</option>
                        		<option value="2033">2033</option>
                        		<option value="2034">2034</option>
                        		<option value="2035">2035</option>
                        		<option value="2036">2036</option>
                        	</select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button id="modal_btn_edit_card" type="button" class="btn btn-success">Update Card</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="delete_card_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    Do you want to delete this credit card?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button id="modal_btn_delete_card" type="button" class="btn btn-success">Delete</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="new_subscription_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">New Subscription</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning label_add_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Plan Name</div>
                        <div class="col-xs-8"><input id="plan_name" type="text" class="form-control input-sm" readonly="readonly"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Price</div>
                        <div class="col-xs-8"><input id="plan_price" type="text" class="form-control input-sm" readonly="readonly"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-4 modal_input_label">Interval</div>
                        <div class="col-xs-8"><input id="plan_interval" type="text" class="form-control input-sm" readonly="readonly"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button id="modal_btn_new_subscription" type="button" class="btn btn-success">New Subscription</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="cancel_subscription_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    Do you want to cancel this subscription?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                <button id="modal_btn_cancel_subscription" type="button" class="btn btn-success">Cancel Subscription</button>
            </div>
        </div>
    </div>
</div>