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
 				<table class="for_pc table table_overall" style="margin-top:15px;">
					<thead>
						<tr>
							<th>STEP 1</th>
							<th>STEP 2</th>
							<th>TAKE RATE</th>
							<th>TABLET</th>
							<th>TABLET %</th>
							<th>PREPAIDS</th>
							<th>ORDER%</th>
							<th>DECLINE%</th>
	            	        <th>GOAL%</th>
	                	    <th>GOAL</th>
						</tr>
					</thead>
					<tbody class="table_dashboard_overall_body">
						<tr>
							<td class="all1">0</td>
							<td class="all2">0</td>
							<td class="all3">0</td>
							<td class="all4">0</td>
							<td class="all5">0</td>
							<td class="all6">0</td>
							<td class="all7">0</td>
							<td class="all8">0</td>
							<td class="all9">0</td>
							<td class="all10">0</td>
						</tr>
					</tbody>
				</table>
                <div class="for_mobile row overall_dv">
                    <div class="col-xs-6 overall_tlt">STEP 1</div><div class="col-xs-6 all1">0</div>
                    <div class="col-xs-6 overall_tlt">STEP 2</div><div class="col-xs-6 all2">0</div>
                    <div class="col-xs-6 overall_tlt">TAKE RATE</div><div class="col-xs-6 all3">0</div>
                    <div class="col-xs-6 overall_tlt">TABLET</div><div class="col-xs-6 all4">0</div>
                    <div class="col-xs-6 overall_tlt">TABLET %</div><div class="col-xs-6 all5">0</div>
                    <div class="col-xs-6 overall_tlt">PREPAIDS</div><div class="col-xs-6 all6">0</div>
                    <div class="col-xs-6 overall_tlt">ORDER%</div><div class="col-xs-6 all7">0</div>
                    <div class="col-xs-6 overall_tlt">DECLINE%</div><div class="col-xs-6 all8">0</div>
                    <div class="col-xs-6 overall_tlt">GOAL%</div><div class="col-xs-6 all9">0</div>
                    <div class="col-xs-6 overall_tlt">GOAL</div><div class="col-xs-6 all10">0</div>
                </div>
 			</div>
 			<div class="crm_board">
 				<div class="row crm_board_title">
 					<div class="col-xs-10" style="padding-left: 0">Sales Report</div>
					<div class="col-xs-2 dashboard_sales_waiting" style="text-align:right;"></div>
 				</div>
 				<div class="alert alert-warning dashboard_sales_alert" role="alert" style="display:none"></div>
 				<div class="row crm_board_row">
					<div class="col-sm-7">
						<div class="input-daterange input-group a_for_search_top" id="datepicker">
                            <div class="a_for_search">
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
                            </div>
                            <div class="a_for_search">
                                <span class="input-group-addon calendar_label">From</span>
                                <input id="from_date" type="text" class="input-sm form-control" name="start"/>
                            </div>
                            <div class="a_for_search">
                                <span class="input-group-addon calendar_label">To &nbsp;&nbsp;&nbsp;&nbsp;</span>
                                <input id="to_date" type="text" class="input-sm form-control" name="end"/>
                            </div>
                            <div class="a_for_search">
                                <span class="input-group-btn">
                                    <button class="btn btn-default btn-sm sales_search_button" type="button" style="width:100px"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>&nbsp;Search</button>
                                </span>
                            </div>
						</div>
					</div>
					<div class="col-sm-5 a_mobile_crm_div" style="text-align: right; padding-right: 30px">
						<button type="button" class="btn btn-default btn-sm btn_crm_position"><span class="glyphicon glyphicon-sort" aria-hidden="true"></span> CRM Position</button>
						<button type="button" class="btn btn-default btn-sm btn_quick_edit" data-toggle="modal" data-target="#quick_edit_modal"><span class="glyphicon glyphicon-list" aria-hidden="true"></span> Quick Edit</button>
					</div>
				</div>
<!--                <div class="box-body table-responsive">--> <!--  a_dt table_dashboard -->
                <div class="row a_no_lr_margin">
                    <div class="table-responsive">
                        <table id="a_crm_list_tb" class="table nowrap a_dt table_dashboard" style="margin-top:10px; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>CRM</th>
                                        <th>VERTICAL</th>
                                        <th>STEP 1</th>
                                        <th class="g_none_dis">STEP 2</th>
                                        <th>TAKE RATE</th>
                                        <th>TABLET</th>
                                        <th>TABLET %</th>
                                        <th>S1 PREPAIDS</th>
                                        <th class="g_none_dis">ORDER%</th>
                                        <th class="g_none_dis">DECLINE%</th>
                                        <th>S1 PP%</th>
                                        <th>GOAL%</th>
                                        <th>GOAL</th>
        <!--                                <th>UPDATED</th>-->
                                        <th>SETTING</th>
<!--                                        <th><button type="button" class="btn btn-link btn-sm btn_refresh_all" id=""><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button></th>-->
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                    </div>
                </div>
<!--                </div>-->
 			</div>
 		</div>
 		<span id="crm_positions" style="display: none;"><?php echo $crm_positions; ?></span>
 	</div>
	<?php include('./common/body_down.php'); ?>
</body>
</html>
