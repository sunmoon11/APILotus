<?php

require_once '../daemon/api/DBApi.php';
require_once '../daemon/api/StripeApi.php';


session_start();
$user = $_SESSION['user'];
$userRole = $_SESSION['role'];
$subDomain = $_SESSION['sub_domain'];


if (!isset($user) || $user == '' || $userRole == 0)
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
if (!in_array(3, $features))
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

//$user_name = 'admin';
//$userRole = 9;
$tab_name = "Affiliate Management";

?>


<!DOCTYPE html>
<html>
	<?php include('./common/header.php'); ?>
<body>
	<?php include('./setting_affiliate_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
	<div class="row">        
	 	<div class="col-xs-12">
	 		<div class="crm_board">
				<div class="row crm_board_title">
					<div class="col-xs-10" style="padding-left: 0">Affiliate Report</div>
					<div class="col-xs-2 setting_affiliate_waiting" style="text-align:right"></div>
				</div>
				<div class="alert alert-warning setting_affiliate_alert" role="alert" style="display:none"></div>
	  			<div class="row crm_board_row">
	                <div class="col-xs-12" style="text-align:right; padding-right: 30px">
	                	<button type="button" class="btn btn-default btn-sm btn_affiliate_add" data-toggle="modal" data-target="#affiliate_add_modal" style="margin-right: 10px"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;Add Affiliate</button>
	                	<button class="btn btn-default btn-sm btn_export" type="button" style="margin-right: 100px"><span class="glyphicon glyphicon-export" aria-hidden="true"></span>&nbsp;&nbsp;Export To Excel</button>
						<div class="btn-group affiliate_pagination" role="group">
						</div>
						<div class="btn-group">
							<button type="button" class="btn btn-default btn-sm dropdown-toggle count_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:60px">
								10 <span class="caret"></span>
							</button>
							<ul class="dropdown-menu dropdown-menu-right count_dropdown_menu" role="menu" style="width: 80px !important; min-width: 80px !important">
								<li><a href="#">10</a></li>
								<li><a href="#">20</a></li>
								<li><a href="#">50</a></li>
								<li><a href="#">100</a></li>
								<li><a href="#">500</a></li>
								<li><a href="#">1000</a></li>
							</ul>
						</div>							
					</div>
				</div>
				<table class="table table-hover" style="margin-top:10px;">
					<thead class="table_affiliate_head">
					</thead>
					<tbody class="table_affiliate_body">
					</tbody>
				</table>
			</div>
 		</div> 		 
	 </div>
	<?php include('./common/body_down.php'); ?>
</body>
</html>
