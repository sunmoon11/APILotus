<?php

session_start();
$userID = $_SESSION['ap_user_id'];
session_write_close();

if (!isset($userID) || $userID == '')
{
    header("Location: ./login.php");
    return;
}

$tab_name = "Profile";

?>


<!DOCTYPE html>
<html>
<head>
	<?php include('./common/header.php'); ?>
</head>

<body class="ap_body">
	<?php include('./profile_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
	<div class="ap_view">
		<div class="row ap_row">
			<div class="col-xs-10"><span class="ap_title">Profile</span></div>
			<div class="col-xs-2 ap_waiting ap_profile_waiting" style="text-align:right"></div>
		</div>
		<div class="alert alert-warning ap_profile_alert" role="alert" style="display:none"></div>
		<div class="row ap_row" style="padding-top:20px">
			<div class="row" style="margin-bottom:20px;">
                <div class="col-xs-2 modal_input_label">User ID</div>
                <div class="col-xs-3"><input type="text" class="form-control input-sm profile_user_id" readonly="readonly" style="background-color:#fff"></div>
            </div>
            <div class="row" style="margin-bottom:20px;">
                <div class="col-xs-2 modal_input_label">User Name</div>
                <div class="col-xs-3"><input type="text" class="form-control input-sm profile_display_name" readonly="readonly" style="background-color:#fff"></div>
            </div>
            <div class="row" style="margin-bottom:20px;">
                <div class="col-xs-2 modal_input_label">Email Address</div>
                <div class="col-xs-3"><input type="text" class="form-control input-sm profile_email_address" readonly="readonly" style="background-color:#fff"></div>
            </div>
            <div class="row" style="margin-bottom:20px;">
                <div class="col-xs-5" style="text-align:right;padding-top:10px;">
                	<button type="button" class="btn btn-default btn_change_password" style="margin-right:10px">Change Password</button>
                	<button type="button" class="btn btn-default btn_edit_profile">Edit Profile</button>
                </div>
            </div>
		</div>
	</div>
	<div style="width:100%;height:30px;background-color:transparent"></div>
	<div class="ap_view">
		<div class="row ap_row">
			<div class="col-xs-10"><span class="ap_title">Login History</span></div>
			<div class="col-xs-2 ap_waiting ap_login_history_waiting" style="text-align:right"></div>
		</div>
		<div>
			<span style="padding-left:20px;padding-top:10px;">Places where the subdomain users are logged into API Lotus.</span>
		</div>
		<div class="alert alert-warning ap_login_history_alert" role="alert" style="display:none"></div>
		<table class="table table-hover ap_table">
			<thead>
				<tr>
					<th>Sub-Domain</th>
					<th>User Name</th>
					<th>Location</th>
					<th>User-Agent</th>
					<th>IP Address</th>
					<th>Login Date</th>
				</tr>
			</thead>
			<tbody class="table_login_history_body">
			</tbody>
		</table>
		<div class="row ap_row">
			<span class="ap_result login_history_result"></span>
		</div>
	</div>
	<?php include('./common/body_down.php'); ?>
</body>

</html>
