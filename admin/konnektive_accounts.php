<?php

require_once '../daemon/api/DBApi.php';
require_once '../daemon/api/StripeApi.php';


session_start();
$user = $_SESSION['user'];
$subDomain = $_SESSION['sub_domain'];


if (!isset($user) || $user == '')
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
$features =  $dbApi->getFeatureEnableList($subDomain);
$features = explode(',', $features);
if (!in_array(1, $features))
{
    header("Location: ./dashboard.php");
    return;
}
if(!$dbApi->checkClientIp())
{
    header("Location: ./blockip_alert.php");
    return;
}

// check subscription for payment
include ('./common/check_payment.php');


$user_name = $user;
$tab_name = "Konnektive Account Management";

?>


<!DOCTYPE html>
<html>
	<?php include('./common/header.php'); ?>
<body>
	<?php include('./konnektive_accounts_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
 	<div class="row">        
	 	<div class="col-xs-12">
	 		<div class="crm_board">
				<div class="row crm_board_title">
					<div class="col-xs-10" style="padding-left: 0">Account Information</div>
					<div class="col-xs-2 crm_account_waiting" style="text-align:right"></div>
				</div>
				<div class="alert alert-warning crm_account_alert" role="alert" style="display:none"></div>
	  			<div class="row crm_board_row">
					<div class="col-xs-12" style="text-align:right; padding-right: 30px">
						<button type="button" class="btn btn-default btn-sm btn_crm_add" data-toggle="modal" data-target="#crm_add_modal"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;Add CRM Account</button>
					</div>
				</div>
				<table class="table table-hover" style="margin-top: 10px">
					<thead>
						<tr>
							<th>CRM Name</th>
							<th>CRM Site URL</th>
							<th>CRM User Name</th>
							<th>CRM Password</th>
	                        <th>API User Name</th>
	                        <th>API Password</th>
	                        <th>Sales Goal</th>
	                        <th>Status*</th>
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
