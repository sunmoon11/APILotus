<?php

session_start();
$userID = $_SESSION['ap_user_id'];
session_write_close();

if (!isset($userID) || $userID == '')
{
    header("Location: ./login.php");
    return;
}

$tab_name = "Accounts";

?>


<!DOCTYPE html>
<html>
<head>
	<?php include('./common/header.php'); ?>
</head>

<body class="ap_body">
	<?php include('./accounts_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
	<div class="ap_view">
		<div class="row ap_row">
			<div class="col-xs-10"><span class="ap_title">Registered Accounts</span></div>
			<div class="col-xs-2 ap_waiting ap_accounts_waiting" style="text-align:right"></div>
		</div>
		<div class="alert alert-warning ap_accounts_alert" role="alert" style="display:none"></div>
		<div class="row ap_row" style="padding-top:10px">
			<canvas id="ap_accounts_canvas" class="ap_canvas"></canvas>
		</div>
	</div>
	<div style="width:100%;height:30px;background-color:transparent"></div>
	<div class="ap_view">
		<div class="row ap_row">
			<div class="col-xs-10">
				<select name="sub_domain" class="input-sm select_sub_domain" style="width:200px;margin-left:5px;">
                </select>
			</div>
			<div class="col-xs-2 ap_waiting ap_account_detail_waiting" style="text-align:right"></div>
		</div>
		<div class="alert alert-warning ap_account_detail_alert" role="alert" style="display:none"></div>
		<table class="table table-hover ap_table">
			<thead>
				<tr>
					<th>User Name</th>
					<!--<th>Password</th>-->
					<th>Display Name</th>
					<th>SMS Number</th>
					<th>Email Address</th>
					<th>Telegram Bot</th>
					<th>User Role</th>
					<th>Status</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody class="table_account_body">
			</tbody>
		</table>
		<div class="row ap_row">
			<span class="ap_result account_detail_result"></span>
		</div>
	</div>
	<?php include('./common/body_down.php'); ?>
</body>

</html>
