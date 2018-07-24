<?php

session_start();
$userID = $_SESSION['ap_user_id'];
session_write_close();

if (!isset($userID) || $userID == '')
{
    header("Location: ./login.php");
    return;
}

$tab_name = "Payments";

?>


<!DOCTYPE html>
<html>
<head>
	<?php include('./common/header.php'); ?>
</head>

<body class="ap_body">
	<?php include('./payments_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
	<div class="ap_view">
		<div class="row ap_row">
			<div class="col-xs-5"><span class="ap_title">All Credit Cards</span></div>
			<div class="col-xs-4 col-xs-offset-1"><span class="ap_title">All Subscriptions</span></div>
			<div class="col-xs-1 col-xs-offset-1 ap_waiting ap_chart_waiting" style="text-align:right"></div>
		</div>
		<div class="alert alert-warning ap_chart_alert" role="alert" style="display:none"></div>
		<div class="row ap_row" style="padding-top:10px">
			<div class="col-xs-5"><canvas id="ap_cards_canvas" class="ap_canvas"></canvas></div>
			<div class="col-xs-5 col-xs-offset-1"><canvas id="ap_subscriptions_canvas" class="ap_canvas"></canvas></div>
		</div>
	</div>
	<div style="width:100%;height:30px;background-color:transparent"></div>
	<div class="ap_view">
		<div class="row ap_row">
			<div class="col-xs-10"><span class="ap_title">Subscription Information</span></div>
			<div class="col-xs-2 ap_waiting ap_detail_waiting" style="text-align:right"></div>
		</div>
		<div class="alert alert-warning ap_detail_alert" role="alert" style="display:none"></div>
		<table class="table table-hover ap_table">
			<thead>
				<tr>
					<th>Sub-Domain</th>
					<th>User Name</th>
					<th>Email Address</th>
					<th>Card ID</th>
					<th>Subscription ID</th>
					<th>Created Date</th>
					<th>Updated Date</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody class="table_detail_body">
			</tbody>
		</table>
		<div class="row ap_row">
			<span class="ap_result detail_result"></span>
		</div>
	</div>
	<?php include('./common/body_down.php'); ?>
</body>

</html>
