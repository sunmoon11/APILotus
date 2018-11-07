<?php

include '../daemon/api/LLCrmApi.php';
require_once '../daemon/api/DBApi.php';
require_once '../daemon/api/StripeApi.php';


session_start();
$user = $_SESSION['user'];
$userRole = $_SESSION['role'];


if (!isset($user) || $user == '' || !isset($userRole) || $userRole == '' || $userRole == 0)
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
$tab_name = "Client Setup";

?>


<!DOCTYPE html>
<html>
	<?php include('./common/header.php'); ?>
<body>
    <?php include('./setting_crm_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
	<div class="row">        
	 	<div class="col-xs-12">
	 		<div class="crm_board">
				<div class="row crm_board_title">
					<div class="col-xs-10" style="padding-left: 0">Client Setup</div>
					<div class="col-xs-2 setting_crm_waiting" style="text-align:right"></div>
				</div>
				<div class="alert alert-warning setting_crm_alert" role="alert" style="display:none"></div>
	  			<div class="row crm_board_row">
					<div class="col-xs-12" style="text-align:right; padding-right: 30px">
						<button type="button" class="btn btn-default btn-sm btn_crm_add" data-toggle="modal" data-target="#crm_add_modal"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;Add CRM</button>
					</div>
				</div>
				<table class="table table-hover" style="margin-top: 10px">
					<thead>
						<tr>
							<th>#</th>
							<th>Client Name</th>
							<th>Client URL</th>
<!--							<th>CRM User Name</th>-->
<!--							<th>CRM Password</th>-->
<!--	                        <th>API User Name</th>-->
<!--	                        <th>API Password</th>-->
	                        <th>Sales Goal</th>
	                        <th>Status*</th>
	                        <th>Password Valid Days</th>
	                        <th>Rebill Length</th>
<!--	                        <th>Test CC</th>-->
							<th>CRM Action</th>
						</tr>
					</thead>
					<tbody class="table_crm_body">
					</tbody>
				</table>
			</div>
 		</div> 		 
	 </div>
	<?php include('./common/body_down.php'); ?>
</body>
</html>
