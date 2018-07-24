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
$tab_name = "Affiliate";

?>


<!DOCTYPE html>
<html>
	<?php include('./common/header.php'); ?>
<body>
	<?php include('./affiliate_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
 	<div class="row">
 		<div class="col-xs-12">
 			<div class="crm_board">
				<div class="row crm_board_title">
					<div class="col-xs-10" style="padding-left: 0">Affiliate Report</div>
					<div class="col-xs-2 dashboard_affiliate_waiting" style="text-align:right"></div>
				</div>
				<div class="alert alert-warning dashboard_affiliate_alert" role="alert" style="display:none"></div>
				<div class="row crm_board_row">
					<div class="col-xs-5">
						<div class="input-daterange input-group" id="datepicker">
							<span class="input-group-btn">
								<button type="button" class="btn btn-default btn-sm dropdown-toggle date_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:160px">
									Week To Date <span class="caret"></span>
								</button>
								<ul class="dropdown-menu date_dropdown_menu" role="menu">
									<li><a href="#" id="date_today" class="crm_dropdown_list">Today</a></li>
									<li><a href="#" id="date_yesterday" class="crm_dropdown_list">Yesterday</a></li>
									<li><a href="#" id="date_thisweek" class="crm_dropdown_list">Week To Date</a></li>
									<li><a href="#" id="date_thismonth" class="crm_dropdown_list">Month To Date</a></li>
									<li><a href="#" id="date_thisyear" class="crm_dropdown_list">Year To Date</a></li>
									<li><a href="#" id="date_lastweek" class="crm_dropdown_list">Last Week</a></li>
									<li><a href="#" id="date_custom" class="crm_dropdown_list">Custom</a></li>
								</ul>
							</span>
							<span class="input-group-addon calendar_label">From</span>
						    <input id="from_date" type="text" class="input-sm form-control" name="start"/>
						    <span class="input-group-addon calendar_label">To</span>
						    <input id="to_date" type="text" class="input-sm form-control" name="end"/>
							<span class="input-group-btn">
								<button class="btn btn-default btn-sm affiliate_search_button" type="button" style="width:100px"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>&nbsp;Search</button>
							</span>    
						</div>
					</div>
				</div>
				<div class="row" style="margin-top:10px;">
					<div class="col-xs-8">
						<table class="table table-hover" style="border-right: 1px solid #dadada">
	  						<thead>
	  							<tr>
	  								<th><button type="button" class="btn btn-link btn-sm btn_expand_all" id=""><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></button></th>
		  							<th>Affiliate (ID) Label</th>
		  							<th>STEP 1</th>
		  							<th>STEP 2</th>
		  							<th>TAKE RATE</th>
		  							<th>TABLET</th>
		  							<th>TABLET %</th>
	                                <th>GOAL %</th>
	                                <th>GOAL</th>
	                                <th>SETTING</th>
		  						</tr>
	  						</thead>
	  						<tbody class="table_affiliate_data_body">
	  						</tbody>
						</table>
					</div>
					<div class="col-xs-4">
						<table class="table table-hover" style="border-left: 1px solid #dadada">
	  						<thead>
	  							<tr>
	  								<th>#</th>
	  								<th>CRM</th>
	  								<th>Campaign Count</th>
	  								<th>Status</th>
	  								<th><button type="button" class="btn btn-link btn-sm btn_refresh_all" id=""><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button></th>
	  							</tr>
	  						</thead>
	  						<tbody class="table_affiliate_state_body">
	  						</tbody>
						</table>
					</div>
				</div>
			</div>
 		</div>
 	</div>
	<?php include('./common/body_down.php'); ?>
</body>
</html>
