<?php

include '../daemon/api/DBApi.php';
include '../daemon/api/LLCrmApi.php';
require_once '../daemon/api/StripeApi.php';


session_start();
$user = $_SESSION['user'];


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
$tab_name = "Export Retention";

?>


<!DOCTYPE html>
<html>
    <?php include('./common/header.php'); ?>
<body>
    <?php include('./retention_export_modal.php'); ?>
    <?php include('./common/body_up.php'); ?>
    <div class="row">
        <div class="col-xs-12">
            <div class="crm_board">
                <div class="row crm_board_title">
                    <div class="col-xs-10" style="padding-left: 0">Retention Report</div>
                    <div class="col-xs-2 retention_waiting" style="text-align:right"></div>
                </div>
                <div class="alert alert-warning retention_alert" role="alert" style="display:none"></div>
                <div class="row crm_board_row" style="margin-bottom: 10px">
                    <div class="col-xs-6">
                        <div class="input-daterange input-group" id="datepicker">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle date_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:160px">
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
                            <span class="input-group-addon calendar_label">Subscription Cycles</span>
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle cycle_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:50px; border-radius: 0">
                                    1 <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu cycle_dropdown_menu" role="menu" style="width: 50px !important; min-width: 50px !important">
                                    <li><a href="#" id="cycle_1">1</a></li>
                                    <li><a href="#" id="cycle_2">2</a></li>
                                </ul>
                            </span>
                            <span class="input-group-btn">
                                <button class="btn btn-default btn-sm retention_search_button" type="button" style="width:100px">Search</button>
                            </span>    
                        </div>
                    </div>
                    <div class="col-xs-3" style="text-align:right; padding-right: 20px">
                        <button class="btn btn-default btn-sm btn_export" type="button"><span class="glyphicon glyphicon-export" aria-hidden="true"></span>&nbsp;&nbsp;Export to Excel</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-9 export_content">
                    </div>
                    <nav class="col-xs-2 col-xs-offset-1 bs-docs-sidebar">
                        <ul id="sidebar" class="nav nav-stacked crm_sidebar" style="display:none">
                            <li><a href="#Top"><b>Back to top</b></a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>              
    </div>
    <?php include('./common/body_down.php'); ?>
</body>
</html>
