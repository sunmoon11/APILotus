<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/23/2018
 * Time: 6:09 AM
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

$crmList = $dbApi->getAllActiveCrmsByAccountId($userId);

$tab_name = "Offers";


?>


<!DOCTYPE html>
<html>
<?php include('./common/header.php'); ?>
<body>
<?php include('./setting_offer_modal.php'); ?>
<?php include('./common/body_up.php'); ?>
<div class="row">
    <div class="col-xs-2"></div>
    <div class="col-xs-8">
        <div class="crm_board">
            <div class="row crm_board_title">
                <div class="col-xs-10" style="padding-left: 0">Offers</div>
                <div class="col-xs-1 setting_offer_waiting"></div>
                <div class="col-xs-2" style="text-align:right;">
                    <button type="button" class="btn btn-default btn-sm btn_offer_add" style="font-family: 'Varela Round', sans-serif;">
                        <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;Add Offer
                    </button>
                </div>
            </div>
            <div class="alert alert-warning setting_offer_alert" role="alert" style="display:none"></div>
            <table class="table table-hover" style="margin-top:10px;">
                <thead>
                <tr>
                    <th>Offer ID</th>
                    <th>Client</th>
                    <th>Offer Name</th>
<!--                    <th>Campaigns of Offer</th>-->
                    <th>Vertical Labels of Offer</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody class="table_offer_body">
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-xs-2"></div>
</div>
<?php include('./common/body_down.php'); ?>
</body>
</html>
