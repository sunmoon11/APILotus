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

$tab_name = "Profile";


?>


<!DOCTYPE html>
<html>
	<?php include('./common/header.php'); ?>
<body>
	<?php include('./profile_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
 	<div class="row">
		<div class="col-xs-4">
			<div class="crm_board">
				<div class="row crm_board_title">
					<div class="col-xs-10" style="padding-left: 0">Profile</div>
					<div class="col-xs-2 setting_account_waiting" style="text-align:right"></div>
				</div>
				<div id="card_buttons" class="row crm_board_row" style="text-align:right;padding-right: 30px">
					<button id="btn_edit_profile" type="button" class="btn btn-default btn-sm" style="margin-right: 10px"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span>&nbsp;Edit Profile</button>
					<button id="btn_change_password" type="button" class="btn btn-default btn-sm" style="margin-right: 10px"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span>&nbsp;Change Password</button>
					<button id="btn_crm_permission" type="button" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>&nbsp;CRM Permission</button>
				</div>
				<table class="table table-hover" style="margin-top: 10px">
					<tbody id="table_contact_body">
					</tbody>
				</table>
			</div>
 		</div>
 		<div class="col-xs-8">
 			<div class="crm_board">
				<div class="row crm_board_title">
					<div class="col-xs-10" style="padding-left: 0">Login History</div>
					<div class="col-xs-2 setting_payment_waiting" style="text-align:right"></div>
				</div>
				<table class="table table-hover" style="margin-top: 15px">
					<thead>
						<tr>
							<th>Login Date</th>
							<th>User Name</th>
							<th>Location</th>
							<th>User-Agent</th>
							<th>IP Address</th>							
						</tr>
					</thead>
					<tbody id="table_payment_body">
					</tbody>
				</table>
			</div>
 		</div> 	
 	</div>
	<?php include('./common/body_down.php'); ?>
</body>
</html>
