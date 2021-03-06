<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 10/3/2018
 * Time: 7:11 AM
 */

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

$tab_name = "CAP Update";

?>


<!DOCTYPE html>
<html>
<?php include('./common/header.php'); ?>
<body>
<?php include('./setting_affiliation_modal.php'); ?>
<?php include('./common/body_up.php'); ?>
<div class="row">
    <div class="col-xs-12">
        <div class="crm_board">
            <div class="row crm_board_title">
                <div class="col-xs-10" style="padding-left: 0">CAP Update</div>
                <div class="col-xs-2 cap_update_waiting" style="text-align:right;"></div>
            </div>
            <div class="alert alert-warning cap_update_alert" role="alert" style="display:none"></div>
            <div class="row crm_board_row">
                <div class="col-xs-1">
                </div>
                <div class="col-xs-5">
                    <div class="input-daterange input-group" id="datepicker">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle date_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:160px; border-radius: 0">
                                Week To Date <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu date_dropdown_menu" role="menu">
                                <li><a href="#" id="date_today">Today</a></li>
                                <li><a href="#" id="date_yesterday">Yesterday</a></li>
                                <li><a href="#" id="date_thisweek">Week To Date</a></li>
                                <li><a href="#" id="date_lastweek">Last Week</a></li>
                            </ul>
                        </span>
                        <span class="input-group-addon calendar_label">From</span>
                        <input id="from_date" type="text" class="input-sm form-control" name="start"/>
                        <span class="input-group-addon calendar_label">To</span>
                        <input id="to_date" type="text" class="input-sm form-control" name="end"/>
                        <span class="input-group-btn">
                            <button class="btn btn-default btn-sm cap_search_button" type="button" style="width:100px"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>&nbsp;Search</button>
                        </span>
                    </div>
                </div>
                <div class="col-xs-6" style="text-align:right; height:30px; padding-right: 30px;">
                    <a download="cap_result.txt" id="downloadlink">
                        <button class="btn btn-default btn-sm btn_cap_export" type="button" style="width:120px;margin-right: 10px"><span class="glyphicon glyphicon-export" aria-hidden="true"></span>&nbsp;&nbsp;Export</button>
                    </a>
                </div>
            </div>
            <div class="c_top">
                <div class="row div_cap_update_body">
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('./common/body_down.php'); ?>
</body>
</html>
