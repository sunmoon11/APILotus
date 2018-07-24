<?php

$tab_name = "Signup";

?>


<!doctype html>
<html>
	<?php include('./landingpage/header.php'); ?>
	<body style="background-color: white">
		<?php include('./landingpage/body_up.php'); ?>
		<div class="mid_content" style="width:800px;padding-top:130px;">
			<div style="text-align: center;">
				<span style="font-size: 40px; color: #00b9ab">Sign Up</span>
			</div>
			<div style="width: 100px; height: 3px; background-color: #00b9ab; margin: 0 auto"></div>
			<div style="text-align:center; margin-top: 50px">
				<span id="step1_btn" class="signup_btn_step active">1. Account</span>
				<span id="step1_arrow" class="glyphicon glyphicon-arrow-right signup_arrow" aria-hidden="true"></span>
				<span id="step2_btn" class="signup_btn_step">2. Personal</span>
				<span id="step2_arrow" class="glyphicon glyphicon-arrow-right signup_arrow" aria-hidden="true"></span>
				<span id="step3_btn" class="signup_btn_step">3. Payment</span>
				<span id="step3_arrow" class="glyphicon glyphicon-arrow-right signup_arrow" aria-hidden="true"></span>
				<span id="step4_btn" class="signup_btn_step">4. Complete</span>
			</div>
			<div id="step1_content" style="height:850px">
				<div class="row" style="margin-top:50px;">
		            <div class="col-xs-4 modal_input_label">User ID <span class="red_char">*</span></div>
		            <div class="col-xs-5"><input id="input_user_id" type="text" class="form-control"></div>
		        </div>
		        <div class="row">
		            <div class="col-xs-5 col-xs-offset-4" style="margin-top:5px;height:20px"><span id="warning_user_id" class="red_char"></span></div>
		        </div>
		        <div class="row" style="margin-top:20px;">
		            <div class="col-xs-4 modal_input_label">Email Address <span class="red_char">*</span></div>
		            <div class="col-xs-5"><input id="input_email" type="text" class="form-control"></div>
		        </div>
		        <div class="row">
		            <div class="col-xs-5 col-xs-offset-4" style="margin-top:5px;height:20px"><span id="warning_email" class="red_char"></span></div>
		        </div>
		        <div class="row" style="margin-top:20px;">
		            <div class="col-xs-4 modal_input_label">Password <span class="red_char">*</span></div>
		            <div class="col-xs-5"><input id="input_password" type="password" class="form-control"></div>
		        </div>
		        <div class="row">
		            <div class="col-xs-5 col-xs-offset-4" style="margin-top:5px;height:20px"><span id="warning_password" class="red_char"></span></div>
		        </div>
		        <div class="row" style="margin-top:20px;">
		            <div class="col-xs-4 modal_input_label">Re-type Password <span class="red_char">*</span></div>
		            <div class="col-xs-5"><input id="input_repassword" type="password" class="form-control"></div>
		        </div>
		        <div class="row">
		            <div class="col-xs-5 col-xs-offset-4" style="margin-top:5px;height:20px"><span id="warning_repassword" class="red_char"></span></div>
		        </div>
		        <div class="row" style="margin-top:20px;">
		        	<div class="col-xs-5 col-xs-offset-4"><div class="g-recaptcha" data-sitekey="6LfFeyUUAAAAAEldHSuFiAgHX4GGUSr4cOhE2vpD"></div></div>
		        </div>
		        <div class="row" style="margin-top:30px;">
		        	<div class="col-xs-3 col-xs-offset-9">
		            	<button id="verify_email_btn" type="button" class="btn btn-info" style="width:130px"><span class="glyphicon glyphicon-envelope" style="color:white" aria-hidden="true"></span>&nbsp;&nbsp;Verify Email</button>
		            </div>
		        </div>
		        <div id="verify_email_content" class="row" style="margin-top:40px;display:none">
		        	<div class="col-xs-10 col-xs-offset-1" style="height:1px;background:#d0d0d0"></div>
		        	<div class="col-xs-10 col-xs-offset-2" style="margin-top:40px;margin-bottom:30px;">
		        		<span style="font-size:18px;">Verify your email address to access all of API Lotus!</span><br><br>
		        		<span style="font-size:16px;"><b>We’ve just sent an email to your address.</b></span><br>
		        		Please check your email and type the verification code provided.
		        	</div>
		        	<div class="col-xs-5 modal_input_label">Email verification Code <span class="red_char">*</span></div>
		            <div class="col-xs-4"><input id="input_verify_code" type="text" class="form-control"></div>
		            <div class="col-xs-4 col-xs-offset-5" style="margin-top:5px;height:20px"><span id="warning_verify_code" class="red_char"></span></div>
		            <div class="col-xs-3 col-xs-offset-9" style="margin-top:50px;margin-bottom:50px">
		            	<button id="next_step1_btn" type="button" class="btn btn-info" style="width:130px"><span class="glyphicon glyphicon-arrow-right" style="color:white" aria-hidden="true"></span>&nbsp;&nbsp;Next Step</button>
		            </div>
		        </div>
		    </div>
		    <div id="step2_content" style="display:none;height:600px;">
		    	<div class="row" style="margin-top:50px;">
		            <div class="col-xs-4 modal_input_label">First Name <span class="red_char">*</span></div>
		            <div class="col-xs-5"><input id="input_first_name" type="text" class="form-control"></div>
		        </div>
		        <div class="row">
		            <div class="col-xs-5 col-xs-offset-4" style="margin-top:5px;height:20px"><span id="warning_first_name" class="red_char"></span></div>
		        </div>
		        <div class="row" style="margin-top:20px;">
		            <div class="col-xs-4 modal_input_label">Last Name <span class="red_char">*</span></div>
		            <div class="col-xs-5"><input id="input_last_name" type="text" class="form-control"></div>
		        </div>
		        <div class="row">
		            <div class="col-xs-5 col-xs-offset-4" style="margin-top:5px;height:20px"><span id="warning_last_name" class="red_char"></span></div>
		        </div>
		        <div class="row" style="margin-top:20px;">
		            <div class="col-xs-4 modal_input_label">Display Name </span></div>
		            <div class="col-xs-5"><input id="input_display_name" type="text" class="form-control"></div>
		        </div>
		        <div class="row" style="margin-top:40px;">
		            <div class="col-xs-4 modal_input_label">Sub-domain Name <span class="red_char">*</span></div>
		            <div class="col-xs-5"><input id="input_sub_domain" type="text" class="form-control"></div>
		        </div>
		        <div class="row">
		            <div class="col-xs-5 col-xs-offset-4" style="margin-top:5px;height:20px"><span id="warning_sub_domain" class="red_char"></span></div>
		        </div>
		        <div class="row" style="margin-top:20px;">
		            <div class="col-xs-4 modal_input_label">SMS Number </span></div>
		            <div class="col-xs-5"><input id="input_sms_number" type="text" class="form-control"></div>
		        </div>
		        <div class="row" style="margin-top:40px;">
		            <div class="col-xs-4 modal_input_label">Telegram Bot Chat ID</span></div>
		            <div class="col-xs-5"><input id="input_bot_id" type="text" class="form-control"></div>
		        </div>
		        <div class="row" style="margin-top:50px;">
			        <div class="col-xs-10 col-xs-offset-1" style="height:1px;background:#d0d0d0;"></div>
			        <div class="col-xs-3 col-xs-offset-9" style="margin-top:40px;margin-bottom:30px">
		            	<button id="next_step2_btn" type="button" class="btn btn-info" style="width:130px"><span class="glyphicon glyphicon-arrow-right" style="color:white" aria-hidden="true"></span>&nbsp;&nbsp;Next Step</button>
		            </div>
		        </div>
		    </div>
		    <div id="step3_content" style="display:none;">
		    	<div class="row" style="margin-top:40px;">
		    		<div class="col-xs-12 col-xs-offset-1"><span style="font-size:16px;font-weight:bold">Choose payment method below :</span></div>
		    	</div>
		    	<div class="row" style="margin-top:20px;">
		    		<div class="credit_card">
		    			<span class="glyphicon glyphicon-credit-card" style="font-size:70px;color:#31708f" aria-hidden="true"></span><br>
		    			<span style="font-size:12px">PAY WITH CREDIT CARD</span>
		    		</div>
		    	</div>
		    	<div class="row" style="margin-top:50px;">
		            <div class="col-xs-4 modal_input_label">Credit Card Number <span class="red_char">*</span></div>
		            <div class="col-xs-5"><div id="card_number" class="card_credentials"></div></div>
		        </div>
		        <div class="row">
		            <div class="col-xs-5 col-xs-offset-4" style="margin-top:5px;height:20px"><span id="warning_card_number" class="red_char"></span></div>
		        </div>
		        <div class="row" style="margin-top:20px;">
		            <div class="col-xs-4 modal_input_label">Expiry Date <span class="red_char">*</span></div>
		            <div class="col-xs-5"><div id="card_expiry" class="card_credentials"></div></div>
		        </div>
		        <div class="row">
		            <div class="col-xs-5 col-xs-offset-4" style="margin-top:5px;height:20px"><span id="warning_expiry_date" class="red_char"></span></div>
		        </div>
		        <div class="row" style="margin-top:20px;">
		            <div class="col-xs-4 modal_input_label">CVC Number <span class="red_char">*</span></div>
		            <div class="col-xs-5"><div id="card_cvc" class="card_credentials"></div></div>
		        </div>
		        <div class="row">
		            <div class="col-xs-5 col-xs-offset-4" style="margin-top:5px;height:20px"><span id="warning_cvc_number" class="red_char"></span></div>
		        </div>
		        <div class="row">
		            <div class="col-xs-5 col-xs-offset-4" style="margin-top:5px;height:30px"><label>* Card credentials will not save on the sever.</label></div>
		        </div>
		    	<div class="row" style="margin-top:50px;">
			    	<div class="col-xs-10 col-xs-offset-1" style="height:1px;background:#d0d0d0;"></div>
			        <div class="col-xs-3 col-xs-offset-9" style="margin-top:40px;margin-bottom:50px">
		            	<button id="next_step3_btn" type="button" class="btn btn-info" style="width:130px"><span class="glyphicon glyphicon-arrow-right" style="color:white" aria-hidden="true"></span>&nbsp;&nbsp;Next Step</button>
		            </div>
		        </div>
		    </div>
		    <div id="step4_content" style="display:none;height:600px;">
		    	<div class="row" style="margin-top:40px;">
		    		<div class="col-xs-12 col-xs-offset-1">
		    			<span class="glyphicon glyphicon glyphicon-ok" style="color:green" aria-hidden="true"></span>
		    			<span style="font-size:17px;font-weight:bold">&nbsp;&nbsp;Your account has been successfully registered!</span>
		    		</div>
		    	</div>
		    	<div class="row" style="margin-top:50px;">
		            <div class="col-xs-4 modal_input_label">User ID</div>
		            <div class="col-xs-5"><input id="confirm_user_id" type="text" class="form-control" readonly></div>
		        </div>
		        <div class="row" style="margin-top:40px;">
		            <div class="col-xs-4 modal_input_label">Password</div>
		            <div class="col-xs-5"><input id="confirm_password" type="text" class="form-control" readonly></div>
		        </div>
		        <div class="row" style="margin-top:40px;">
		            <div class="col-xs-4 modal_input_label">Domain URL</div>
		            <div class="col-xs-5"><input id="confirm_domain_url" type="text" class="form-control" readonly></div>
		        </div>
		        <div class="row" style="margin-top:50px;">
			    	<div class="col-xs-10 col-xs-offset-1" style="height:1px;background:#d0d0d0;"></div>
			        <div class="col-xs-3 col-xs-offset-8" style="margin-top:40px;margin-bottom:30px">
		            	<button id="next_step4_btn" type="button" class="btn btn-info" style="width:200px"><span class="glyphicon glyphicon-log-in" style="color:white" aria-hidden="true"></span>&nbsp;&nbsp;Go to my domain</button>
		            </div>
		        </div>
		    </div>
		</div>
		<?php include('./landingpage/body_down.php'); ?>
	</body>
</html>