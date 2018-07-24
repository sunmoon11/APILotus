<?php

require_once '../daemon/api/DBApi.php';
require_once '../daemon/api/StripeApi.php';


session_start();
$user_name = $_SESSION['user'];
$user_id = $_SESSION['user_id'];
$subDomain = $_SESSION['sub_domain'];


if (!isset($user_name) || $user_name == '' || !isset($user_id) || $user_id == '')
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


$tab_name = "Konnektive Retention";
$crmList = $dbApi->getKKCrmActiveList($user_id);

?>


<!DOCTYPE html>
<html>
	<?php include('./common/header.php'); ?>
<body>
	<?php include('./common/body_up.php'); ?>
 	<div class="row">
 		<div class="col-xs-12">
 			<div class="crm_board">
				<div class="row crm_board_title">
					<div class="col-xs-10" style="padding-left: 0">Retention Report</div>
					<div class="col-xs-2 retention_report_waiting" style="text-align:right"></div>
				</div>
				<div class="alert alert-warning retention_report_alert" role="alert" style="display:none"></div>
				<div class="row crm_board_row">
					<div class="col-xs-11">
						<div class="input-daterange input-group" id="datepicker">
							<span class="input-group-btn">
								<button type="button" class="btn btn-default btn-sm dropdown-toggle crm_toggle_button" data-toggle="dropdown" aria-expanded="false" style="min-width:160px">
									<?php
										if ($crmList != null && count($crmList) > 0)
											echo $crmList[0][1].' ';
										else
											echo 'None CRM ';
									?>
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu crm_dropdown_menu" role="menu">
								<?php
									if ($crmList != null) {
										for ($i = 0; $i < count($crmList); $i++)
											echo '<li><a href="#" id="'.$crmList[$i][0].'" class="crm_dropdown_list">'.$crmList[$i][1].'</a></li>';
									}
								?>
								</ul>
							</span>
							<span class="input-group-btn">
								<button type="button" class="btn btn-default btn-sm dropdown-toggle date_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:160px; border-radius: 0">
									Week To Date <span class="caret"></span>
								</button>
								<ul class="dropdown-menu date_dropdown_menu" role="menu">
									<li><a href="#" id="date_today">Today</a></li>
									<li><a href="#" id="date_yesterday">Yesterday</a></li>
									<li><a href="#" id="date_thisweek">Week To Date</a></li>
									<li><a href="#" id="date_thismonth">Month To Date</a></li>
									<li><a href="#" id="date_thisyear">Year To Date</a></li>
									<li><a href="#" id="date_lastweek">Last Week</a></li>
									<li><a href="#" id="date_custom">Custom</a></li>
								</ul>
							</span>
							<span class="input-group-addon calendar_label">From</span>
						    <input id="from_date" type="text" class="input-sm form-control" name="start"/>
						    <span class="input-group-addon calendar_label">To</span>
						    <input id="to_date" type="text" class="input-sm form-control" name="end"/>
						    <span class="input-group-addon calendar_label">Cycles</span>
						    <span class="input-group-btn">
								<button type="button" class="btn btn-default btn-sm dropdown-toggle cycle_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:50px;border-radius: 0">
									1 <span class="caret"></span>
								</button>
								<ul class="dropdown-menu cycle_dropdown_menu" role="menu" style="width: 50px; min-width: 50px">
									<li><a href="#" id="cycle_1">1</a></li>
									<li><a href="#" id="cycle_2">2</a></li>
								</ul>
							</span>
							<span class="input-group-addon calendar_label">Campaign Category</span>
							<span class="input-group-btn">
								<button type="button" class="btn btn-default btn-sm dropdown-toggle category_toggle_button" data-toggle="dropdown" aria-expanded="false" style="min-width:200px;border-radius: 0">
									All Categories <span class="caret"></span>
								</button>
								<ul class="dropdown-menu category_dropdown_menu" role="menu" style="max-height: 400px; overflow-y:auto">
									<li><a href="#" id="date_today">All Categories</a></li>
								</ul>
							</span>
							<span class="input-group-addon calendar_label">Product</span>
							<span class="input-group-btn">
								<button type="button" class="btn btn-default btn-sm dropdown-toggle product_toggle_button" data-toggle="dropdown" aria-expanded="false" style="min-width:200px;border-radius: 0">
									All Products <span class="caret"></span>
								</button>
								<ul class="dropdown-menu product_dropdown_menu" role="menu" style="max-height: 400px; overflow-y:auto">
									<li><a href="#" id="date_today">All Products</a></li>
								</ul>
							</span>
							<span class="input-group-addon calendar_label">Affiliate</span>
							<span class="input-group-btn">
								<button type="button" class="btn btn-default btn-sm dropdown-toggle affiliate_toggle_button" data-toggle="dropdown" aria-expanded="false" style="min-width:200px;border-radius: 0">
									All Affiliates <span class="caret"></span>
								</button>
								<ul class="dropdown-menu affiliate_dropdown_menu" role="menu" style="max-height: 400px; overflow-y:auto">
									<li><a href="#" id="date_today">All Affiliates</a></li>
								</ul>
							</span>
						</div>
					</div>
					<div class="col-xs-1" style="text-align:right;padding-right: 30px">
						<button class="btn btn-default btn-sm retention_search_button" type="button" style="width:100px"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>&nbsp;Search</button>
					</div>
				</div>
				<div style="overflow-x: auto;">
					<table class="table table-hover" style="margin-top:10px;">
						<thead class="table_retention_report_head">
							<tr>
								<th rowspan="2"></th>
								<th rowspan="2" style="vertical-align:middle">Campaign Name</th>
								<th colspan="10" style="border: 1px solid #dadada">Initial Cycle</th>
								<th colspan="3" style="border: 1px solid #dadada">Totals</th>
							</tr>
							<tr>
								<th style="border-left: 1px solid #dadada">Orders</th>
								<th>CPA</th>
								<th>Cancels</th>
								<th>Chargebacks</th>
								<th>Gross</th>
								<th>Partial Refund</th>
								<th>Full Refund</th>
								<th>Expenses</th>
								<th>Commission</th>
								<th>Net</th>
								<th style="border-left: 1px solid #dadada">AVG LTV</th>
								<th>Gross</th>
								<th>Net</th>
							</tr>
						</thead>
						<tbody class="table_retention_report_body">
						</tbody>
					</table>
				</div>
			</div>
 		</div>
 	</div>
	<?php include('./common/body_down.php'); ?>
</body>
</html>
