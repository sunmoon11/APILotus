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

$tab_name = "Alerts";

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
						<div class="col-xs-10" style="padding-left: 0">Alert Report</div>
						<div class="col-xs-2 alert_waiting" style="text-align:right"></div>
					</div>
					<div class="alert alert-warning level_alert" role="alert" style="display:none"></div>
					<table class="table table-hover" style="margin-top:15px;">
						<thead class="table_alert_head">
						</thead>
						<tbody class="table_alert_body">
						</tbody>
					</table>
				</div>
			</div>
	 	</div>
	 	<section id="history">
		 	<div class="row">
		 		<div class="col-xs-12">
		 			<div class="crm_board">
		 				<div class="row crm_board_title">
							<div class="col-xs-10" style="padding-left: 0">Alert History</div>
							<div class="col-xs-2 history_waiting" style="text-align:right"></div>
						</div>
						<div class="alert alert-warning history_alert" role="alert" style="display:none"></div>
						<div class="row crm_board_row">
							<div class="col-xs-6">
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
														echo '<li><a href="#history" id="'.$crmList[$i][0].'" class="crm_dropdown_list">'.$crmList[$i][1].'</a></li>';
													echo '<li><a href="#history" id="0" class="crm_dropdown_list">'.'All CRM'.'</a></li>';
												}
											?>
										</ul>
									</span>
									<span class="input-group-btn">
										<button type="button" class="btn btn-default btn-sm dropdown-toggle date_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:160px; border-radius: 0">
											Week To Date <span class="caret"></span>
										</button>
										<ul class="dropdown-menu date_dropdown_menu" role="menu">
											<li><a href="#history" id="date_today">Today</a></li>
											<li><a href="#history" id="date_yesterday">Yesterday</a></li>
											<li><a href="#history" id="date_thisweek">Week To Date</a></li>
											<li><a href="#history" id="date_thismonth">Month To Date</a></li>
											<li><a href="#history" id="date_thisyear">Year To Date</a></li>
											<li><a href="#history" id="date_lastweek">Last Week</a></li>
											<li><a href="#history" id="date_custom">Custom</a></li>
										</ul>
									</span>
									<span class="input-group-addon calendar_label">From</span>
								    <input id="from_date" type="text" class="input-sm form-control" name="start"/>
								    <span class="input-group-addon calendar_label">To</span>
								    <input id="to_date" type="text" class="input-sm form-control" name="end"/>
								    <span class="input-group-btn">
										<button class="btn btn-default btn-sm history_search_button" type="button" style="width:100px"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>&nbsp;Search</button>
									</span>    
								</div>
							</div>
							<div class="col-xs-6" style="text-align:right; padding-right: 30px">
								<div class="btn-group campaign_pagination" role="group">
								</div>
								<div class="btn-group">
									<button type="button" class="btn btn-default btn-sm dropdown-toggle count_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:60px">
										10 <span class="caret"></span>
									</button>
									<ul class="dropdown-menu dropdown-menu-right count_dropdown_menu" role="menu" style="width: 80px !important; min-width: 80px !important">
										<li><a href="#history">10</a></li>
										<li><a href="#history">20</a></li>
										<li><a href="#history">50</a></li>
										<li><a href="#history">100</a></li>
										<li><a href="#history">500</a></li>
										<li><a href="#history">1000</a></li>
									</ul>
								</div>
							</div>
						</div>
						<table class="table table-hover" style="margin-top:10px;">
	  						<thead>
	  							<tr>
		  							<th>#</th>
		  							<th>CRM Name</th>
		  							<th>Alert Name</th>
		  							<th>Register Date</th>
		  							<th>From Date</th>
		  							<th>To Date</th>
		  							<th>Read</th>
		  							<th>Delete</th>
		  						</tr>
	  						</thead>
	  						<tbody class="table_history_body">	  							
	  						</tbody>
						</table>
					</div>
				</div>
		 	</div>
	 	</section>
	<?php include('./common/body_down.php'); ?>
</body>
</html>
