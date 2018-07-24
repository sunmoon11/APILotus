<?php

session_start();
$userID = $_SESSION['ap_user_id'];
session_write_close();

if (!isset($userID) || $userID == '')
{
    header("Location: ./login.php");
    return;
}

$tab_name = "Customer Feedbacks";

?>


<!DOCTYPE html>
<html>
<head>
	<?php include('./common/header.php'); ?>
</head>

<body class="ap_body">
	<?php include('./customer_feedbacks_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
	<div class="ap_view">
		<div class="row ap_row">
			<div class="col-xs-10"><span class="ap_title">Feedback Information</span></div>
			<div class="col-xs-2 ap_waiting ap_feedback_waiting" style="text-align:right"></div>
		</div>
		<div class="alert alert-warning ap_feedback_alert" role="alert" style="display:none"></div>
		<table class="table table-hover ap_table">
			<thead>
				<tr>
					<th>Submitted Date</th>
					<th>User Name</th>
					<th>User Email</th>
					<th>Comment</th>					
					<th>Process Status</th>
				</tr>
			</thead>
			<tbody class="table_feedback_body">
			</tbody>
		</table>
		<div class="row ap_row">
			<span class="ap_result feedback_result"></span>
		</div>
	</div>
	<?php include('./common/body_down.php'); ?>
</body>

</html>
