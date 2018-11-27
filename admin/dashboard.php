<?php
require_once '../daemon/api/DBApi.php';
require_once '../daemon/api/StripeApi.php';


session_start();
$user_name = $_SESSION['user'];
$user_id = $_SESSION['user_id'];


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
if(!$dbApi->checkClientIp())
{
    header("Location: ./blockip_alert.php");
    return;
}

// check subscription for payment
include ('./common/check_payment.php');

// check crm position
$crm_positions = $dbApi->getCrmPositions($user_id);

$tab_name = "Dashboard";

?>


<!DOCTYPE html>
<html>
	<?php include('./common/header.php'); ?>
<body>
	<?php include('./dashboard_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
 	<div class="row">
 		<div class="col-xs-12">
 			<div class="crm_board">
 				<div class="row crm_board_title">Overall Sales</div>
 				<table class="table table_overall" style="margin-top:15px;">
					<thead>
						<tr>
							<th>STEP1</th>
							<th>STEP2</th>
							<th>TAKE RATE</th>
							<th>TABLET</th>
							<th>TABLET%</th>
							<th>PREPAIDS</th>
							<th>ORDER%</th>
							<th>DECLINE%</th>
	            	        <th>GOAL%</th>
	                	    <th>GOAL</th>
						</tr>
					</thead>
					<tbody class="table_dashboard_overall_body">
						<tr>
							<td id="all1">0</td>
							<td id="all2">0</td>
							<td id="all3">0</td>
							<td id="all4">0</td>
							<td id="all5">0</td>
							<td id="all6">0</td>
							<td id="all7">0</td>
							<td id="all8">0</td>
							<td id="all9">0</td>
							<td id="all10">0</td>
						</tr>
					</tbody>
				</table>
 			</div>
 			<div class="crm_board">
 				<div class="row crm_board_title">
 					<div class="col-xs-10" style="padding-left: 0">Sales Report</div>
					<div class="col-xs-2 dashboard_sales_waiting" style="text-align:right;"></div>
 				</div>
 				<div class="alert alert-warning dashboard_sales_alert" role="alert" style="display:none"></div>
 				<div class="row crm_board_row">
					<div class="col-xs-5">
						<div class="input-daterange input-group" id="datepicker">
							<span class="input-group-btn">
								<button type="button" class="btn btn-default btn-sm dropdown-toggle date_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:160px">
									Week To Date <span class="caret"></span>
								</button>
								<ul class="dropdown-menu date_dropdown_menu" role="menu">
									<li><a class="calendar_item" href="#" id="date_today">Today</a></li>
									<li><a class="calendar_item" href="#" id="date_yesterday">Yesterday</a></li>
									<li><a class="calendar_item" href="#" id="date_thisweek">Week To Date</a></li>
									<li><a class="calendar_item" href="#" id="date_thismonth">Month To Date</a></li>
									<li><a class="calendar_item" href="#" id="date_thisyear">Year To Date</a></li>
									<li><a class="calendar_item" href="#" id="date_lastweek">Last Week</a></li>
									<li><a class="calendar_item" href="#" id="date_custom">Custom</a></li>
								</ul>
							</span>
							<span class="input-group-addon calendar_label">From</span>
						    <input id="from_date" type="text" class="input-sm form-control" name="start"/>
						    <span class="input-group-addon calendar_label">To</span>
						    <input id="to_date" type="text" class="input-sm form-control" name="end"/>
							<span class="input-group-btn">
								<button class="btn btn-default btn-sm sales_search_button" type="button" style="width:100px"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>&nbsp;Search</button>
							</span>    
						</div>
					</div>
					<div class="col-xs-7" style="text-align: right; padding-right: 30px">
						<button type="button" class="btn btn-default btn-sm btn_crm_position"><span class="glyphicon glyphicon-sort" aria-hidden="true"></span> CRM Position</button>
						<button type="button" class="btn btn-default btn-sm btn_quick_edit" data-toggle="modal" data-target="#quick_edit_modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span> Quick Edit</button>
					</div>
					<!--
					<div class="col-xs-6" style="text-align:right">
						<button type="button" class="btn btn-default btn-sm btn_show_columns" data-toggle="modal" data-target="#show_columns_modal"><span class="glyphicon glyphicon-check" aria-hidden="true"></span>&nbsp;Show Columns</button>
					</div>
					-->
				</div>
				<table class="table table-hover table_dashboard" style="margin-top:10px;">
					<thead>
						<tr>
							<th>#</th>
							<th>CRM</th>
							<th>VERTICAL</th>
							<th>STEP1</th>
							<th>STEP2</th>
							<th>TAKE RATE</th>
							<th>TABLET</th>
							<th>TABLET%</th>
							<th>S1 PREPAIDS</th>
							<th>ORDER%</th>
							<th>DECLINE%</th>
                            <th>S1 PP%</th>
<!--                            <th>STEP2 PP%</th>-->
	            	        <th>GOAL%</th>
	                	    <th>GOAL</th>
                            <th>UPDATED</th>
	                	    <th>SETTING</th>
	                    	<th><button type="button" class="btn btn-link btn-sm btn_refresh_all" id=""><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button></th>
						</tr>
					</thead>
					<tbody class="table_dashboard_sales_body">
					</tbody>
				</table>
 			</div>
 		</div>
 		<span id="crm_positions" style="display: none;"><?php echo $crm_positions; ?></span>
 	</div>
	<?php include('./common/body_down.php'); ?>
</body>
</html>
