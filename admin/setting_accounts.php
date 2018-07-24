<?php

require_once '../daemon/api/DBApi.php';
require_once '../daemon/api/StripeApi.php';


session_start();
$user = $_SESSION['user'];
$userRole = $_SESSION['role'];


if (!isset($user) || $user == '' || !isset($userRole) || $userRole == '')
{
    header("Location: ./login.php");
    return;
}
// session timeout
$now = time();
if ($now - $_SESSION['last_activity'] > 9660)
{
    session_unset();
    session_destroy();
    header("Location: ./login.php");
    return;
}
$_SESSION['last_activity'] = time();
if (isset($_COOKIE[session_name()]))
    setcookie(session_name(), $_COOKIE[session_name()], time() + 9660);
if ($_SESSION['last_activity'] - $_SESSION['created'] > 9660)
{
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
session_write_close();
// check client ip
$dbApi = DBApi::getInstance();
if(!$dbApi->checkClientIp())
{
    header("Location: ./blockip_alert.php");
    return;
}

// check subscription for payment
include ('./common/check_payment.php');

$user_name = $user;
$tab_name = "Accounts";


?>


<!DOCTYPE html>
<html>
	<?php include('./common/header.php'); ?>
<body>
	<?php include('./setting_accounts_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
 	<div class="row">
		<div class="col-xs-9">
			<div class="crm_board">
				<div class="row crm_board_title">
					<div class="col-xs-10" style="padding-left: 0">Account Report</div>
					<div class="col-xs-2 setting_account_waiting" style="text-align:right"></div>
				</div>
				<div class="alert alert-warning setting_account_alert" role="alert" style="display:none"></div>
				<?php if ($userRole == '9') { ?>
				<div class="row crm_board_row" style="padding-bottom: 0">
					<div class="col-xs-12" style="text-align:right; padding-right: 30px">
						<button type="button" class="btn btn-default btn-sm btn_account_add" data-toggle="modal" data-target="#account_add_modal"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;Add Account</button>
					</div>
				</div>
				<?php } ?>
				<table class="table table-hover" style="margin-top: 15px;">
					<thead>
						<tr>
							<th>#</th>
							<th>User ID</th>
							<!--<th>Password</th>-->
							<th>User Name</th>
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
			</div>
 		</div>
 		<div class="col-xs-3">
 			<div class="crm_board">
				<div class="row crm_board_title">
					<div class="col-xs-10" style="padding-left: 0">Block IP Address</div>
					<div class="col-xs-2 setting_ip_waiting" style="text-align:right"></div>
				</div>
				<div class="alert alert-warning setting_ip_alert" role="alert" style="display:none"></div>
				<?php if ($userRole == '9') { ?>
				<div class="row crm_board_row" style="padding-bottom: 0">
					<div class="col-xs-12" style="text-align:right; padding-right: 30px">
						<button type="button" class="btn btn-default btn-sm btn_blockip_add" data-toggle="modal" data-target="#block_ip_modal"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;Add IP Address</button>
					</div>
				</div>
				<?php } ?>
				<table class="table table-hover" style="margin-top: 15px;">
					<thead>
						<tr>
							<th>#</th>
							<th>IP Address</th>
							<th>Description</th>
							<?php if ($userRole == '9') { ?>
							<th>Action</th>
							<?php } ?>
						</tr>
					</thead>
					<tbody class="table_blockip_body">
					</tbody>
				</table>
			</div>
		</div>
 	</div>
	<?php include('./common/body_down.php'); ?>
</body>
</html>
