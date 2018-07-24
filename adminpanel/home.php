<?php

session_start();
$userID = $_SESSION['ap_user_id'];
session_write_close();

if (!isset($userID) || $userID == '')
{
    header("Location: ./login.php");
    return;
}

$tab_name = "Home";

?>


<!DOCTYPE html>
<html>
<head>
	<?php include('./common/header.php'); ?>
</head>

<body class="ap_body">
	<?php include('./home_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
	<div class="ap_view">
		<div class="row ap_row">
			<div class="col-xs-4"><span class="ap_title">All Sub-Domains</span></div>
			<div class="col-xs-4"><span class="ap_title">All Accounts</span></div>
			<div class="col-xs-3"><span class="ap_title">All CRMs</span></div>
			<div class="col-xs-1 ap_waiting ap_home_waiting" style="text-align:right"></div>
		</div>
		<div class="alert alert-warning ap_home_alert" role="alert" style="display:none"></div>
		<div class="row ap_row" style="padding-top:10px">
			<div class="col-xs-4"><canvas id="ap_subdomains_canvas" class="ap_canvas"></canvas></div>
			<div class="col-xs-4"><canvas id="ap_accounts_canvas" class="ap_canvas"></canvas></div>
			<div class="col-xs-4"><canvas id="ap_crms_canvas" class="ap_canvas"></canvas></div>
		</div>
	</div>
	<div style="width:100%;height:30px;background-color:transparent"></div>
	<div class="ap_view">
		<div class="row ap_row">
			<div class="col-xs-10"><span class="ap_title">Customers</span></div>
			<div class="col-xs-2 ap_waiting ap_customers_waiting" style="text-align:right"></div>
		</div>
		<div class="alert alert-warning ap_customers_alert" role="alert" style="display:none"></div>
		<table class="table table-hover ap_table">
			<thead>
				<tr>
					<th>Sub-Domain</th>
					<th>User ID</th>
					<th>User Name</th>
					<th>Email Address</th>
					<th>SMS Number</th>
					<th>Telegram Bot</th>
					<th>Created Date</th>
					<th>Status</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody class="table_customers_body">
			</tbody>
		</table>
		<div class="row ap_row">
			<span class="ap_result customers_result"></span>
		</div>
	</div>
	<?php include('./common/body_down.php'); ?>
</body>

</html>
