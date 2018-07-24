<?php

require_once '../daemon/api/DBApi.php';
require_once '../daemon/api/StripeApi.php';


session_start();
$user = $_SESSION['user'];
$userId = $_SESSION['user_id'];


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
if(!$dbApi->checkClientIp())
{
    header("Location: ./blockip_alert.php");
    return;
}

// check subscription for payment
include ('./common/check_payment.php');

$user_name = $user;
$crmList = $dbApi->getAllActiveCrmsByAccountId($userId);

$tab_name = "Sales";

$salesType = "Campaign";
if (isset($_GET['f']) && $_GET['f'] != '')
{
	$salesType = "Affiliate";

	if (isset($_GET['sf']) && $_GET['sf'] != '')
		$salesType = "Sub-Affiliate";
}

$crmID = '';
if (isset($_GET['crm']))
	$crmID = $_GET['crm'];

$dateType = '2';
if (isset($_GET['dt']) && $_GET['dt'] != '')
	$dateType = $_GET['dt'];

$fromDate = '';
if (isset($_GET['fd']))
	$fromDate = $_GET['fd'];

$toDate = '';
if (isset($_GET['td']))
	$toDate = $_GET['td'];

?>


<!DOCTYPE html>
<html>
	<?php include('./common/header.php'); ?>
<body>
	<?php include('./sales_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
	 	<div class="row">
	 		<div class="col-xs-12">
	 			<div class="crm_board">
	 				<div class="row crm_board_title">
						<div class="col-xs-10" style="padding-left: 0">Sales Report</div>
						<div class="col-xs-2 sales_waiting" style="text-align:right"></div>
					</div>
					<div class="alert alert-warning sales_alert" role="alert" style="display:none"></div>
					<div class="row crm_board_row">
						<div class="col-xs-7">
							<div class="input-daterange input-group" id="datepicker">
								<span class="input-group-btn">
									<button type="button" class="btn btn-default btn-sm dropdown-toggle crm_toggle_button" data-toggle="dropdown" aria-expanded="false" style="min-width:160px">
										<?php
											$crmName = 'None CRM ';
											if ($crmList != null && count($crmList) > 0)
											{
												$crmName = $crmList[0][1].' ';
												
												for ($i = 0; $i < count($crmList); $i++)
													if ($crmList[$i][0] == $crmID)
														$crmName =  $crmList[$i][1].' ';
											}
											
											echo $crmName;
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
										<?php
											if ($dateType == '0') echo 'Today ';
											elseif ($dateType == '1') echo 'Yesterday ';
											elseif ($dateType == '2') echo 'Week To Date ';
											elseif ($dateType == '3') echo 'Month To Date ';
											elseif ($dateType == '4') echo 'Year To Date ';
											elseif ($dateType == '5') echo 'Last Week ';
											elseif ($dateType == '6') echo 'Custom ';
											else echo 'Week To Date ';
											echo '<span class="caret"></span>';
										?>
									</button>
									<ul class="dropdown-menu date_dropdown_menu" role="menu">
										<li><a href="#" id="0">Today</a></li>
										<li><a href="#" id="1">Yesterday</a></li>
										<li><a href="#" id="2">Week To Date</a></li>
										<li><a href="#" id="3">Month To Date</a></li>
										<li><a href="#" id="4">Year To Date</a></li>
										<li><a href="#" id="5">Last Week</a></li>
										<li><a href="#" id="6">Custom</a></li>
									</ul>
								</span>
								<span class="input-group-addon calendar_label">From</span>
							    <input id="from_date" type="text" class="input-sm form-control" name="start"/>
							    <span class="input-group-addon calendar_label">To</span>
							    <input id="to_date" type="text" class="input-sm form-control" name="end"/>
							    <span class="input-group-addon calendar_label">
							    	<?php
							    		if ($salesType == "Campaign")
							    			echo 'Campaign ID';
							    		else
							    			echo 'Affiliate ID';
							    	?>
							    </span>
							    <input type="text" class="input-sm form-control search_campaign_ids" placeholder="Optional CSV list" style="text-align:left" />
								<span class="input-group-btn">
									<button class="btn btn-default btn-sm sales_search_button" type="button" style="width:100px"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>&nbsp;Search</button>
								</span>    
							</div>
						</div>
						<div class="col-xs-5" style="text-align:right; height:30px; padding-right: 30px;">
							<button class="btn btn-default btn-sm btn_export" type="button" style="width:140px"><span class="glyphicon glyphicon-export" aria-hidden="true"></span>&nbsp;&nbsp;Export To Excel</button>
						</div>
					</div>
					<table class="table table-hover table_sales" style="margin-top:10px;">
						<thead>
							<tr>
								<th>
									<?php
										if ($salesType == "Campaign") {
											echo 'Campaign Name';
										} elseif ($salesType == "Affiliate") {
											echo 'Affiliate ID';
										} elseif ($salesType == "Sub-Affiliate") {
											echo 'Sub-Affiliate ID';
										}
									?>
								</th>
								<th>Prospects</th>
								<th>Initial Customers</th>
								<th>Conversion Rate</th>
								<th>Affiliate Breakdown</th>
							</tr>
						</thead>
						<tbody class="table_sales_body">
						</tbody>
					</table>
				</div>
			</div>
	 	</div>	 	
	<?php include('./common/body_down.php'); ?>
</body>
</html>
