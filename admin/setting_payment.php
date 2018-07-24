<?php

include '../daemon/api/LLCrmApi.php';
require_once '../daemon/api/DBApi.php';

session_start();
$user = $_SESSION['user'];
$userRole = $_SESSION['role'];


if (!isset($user) || $user == '' || !isset($userRole) || $userRole == '' || $userRole != 9)
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

$user_name = $user;
$tab_name = "Payment Management";


?>


<!DOCTYPE html>
<html>
	<?php include('./common/header.php'); ?>
<body>
    <?php include('./setting_payment_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
		<div class="row">        
		 	<div class="col-xs-4">
		 		<div class="crm_board">
					<div class="row crm_board_title">
						<div class="col-xs-10" style="padding-left: 0">Credit Card Crednetials</div>
						<div class="col-xs-2 setting_card_waiting" style="text-align:right"></div>
					</div>
					<div id="card_buttons" class="row crm_board_row" style="text-align:right;padding-right: 30px">
					</div>
					<table class="table table-hover" style="margin-top: 10px">
						<thead id="table_card_head">
						</thead>
						<tbody id="table_card_body">
						</tbody>
					</table>
				</div>
				<div class="crm_board">
					<div class="row crm_board_title">
						<div class="col-xs-10" style="padding-left: 0">Subscription Information</div>
						<div class="col-xs-2 setting_subscription_waiting" style="text-align:right"></div>
					</div>
					<div id="subscription_buttons" class="row crm_board_row" style="text-align:right;padding-right: 30px">
					</div>
					<table class="table table-hover" style="margin-top: 10px">
						<thead id="table_subscription_head">
						</thead>
						<tbody id="table_subscription_body">
						</tbody>
					</table>					
				</div>
	 		</div>
	 		<div class="col-xs-8">
	 			<div class="crm_board">
					<div class="row crm_board_title">
						<div class="col-xs-10" style="padding-left: 0">Payment History</div>
						<div class="col-xs-2 setting_payment_waiting" style="text-align:right"></div>
					</div>
					<table class="table table-hover" style="margin-top: 15px">
						<thead>
							<tr>
								<th>#</th>
								<th>AMOUNT</th>
								<th>STATUS</th>
								<th>DATE</th>
								<th>PERIOD</th>
								<th>INVOICE ID</th>
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
