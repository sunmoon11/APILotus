<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/2/2018
 * Time: 3:21 AM
 */

include '../daemon/api/LLCrmApi.php';
require_once '../daemon/api/DBApi.php';
require_once '../daemon/api/StripeApi.php';


session_start();
$user = $_SESSION['user'];
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];


if (!isset($user) || $user == '' || !isset($userRole) || $userRole == '' || $userRole == 0)
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

$tab_name = "Affiliate Settings";


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
                <div class="col-xs-10" style="padding-left: 0">Affiliate Source Settings</div>
                <div class="col-xs-2 affiliation_waiting" style="text-align:right"></div>
            </div>
            <div class="alert alert-warning affiliation_alert" role="alert" style="display:none"></div>
            <div class="row crm_board_row">
                <div class="col-xs-1">
                </div>
                <div class="col-xs-5">
                    <div class="input-daterange input-group" id="datepicker">
                        <span class="input-group-addon calendar_label">Date</span>
                        <input id="affiliation_date" type="text" class="input-sm form-control"/>
                        <span class="input-group-addon calendar_label">From</span>
                        <input id="from_date" type="text" class="input-sm form-control" name="start"/>
                        <span class="input-group-addon calendar_label">To</span>
                        <input id="to_date" type="text" class="input-sm form-control" name="end"/>

                        <span class="input-group-btn">
                            <button class="btn btn-default btn-sm affiliation_search_button" type="button" style="width:100px"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>&nbsp;Search</button>
                        </span>
                    </div>
                </div>
                <div class="col-xs-6" style="text-align:right; padding-right: 30px">
                    <button type="button" class="btn btn-default btn-sm btn_affiliation_add" data-toggle="modal" data-target="#affiliation_add_modal">
                        <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;Add Affiliate
                    </button>
                </div>
            </div>
            <table class="table table-hover" style="margin-top:10px;">
                <thead>
                <tr>
                    <th>Affiliate</th>
                    <th>AFIDs</th>
                    <th>Offer Name</th>
                    <th>CRM/Client</th>
                    <th>Sales Goal(CAP)</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody class="table_affiliation_body">
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include('./common/body_down.php'); ?>
</body>
</html>
