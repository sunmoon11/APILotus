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

$crmList = $dbApi->getAllActiveCrmsByAccountId($userId);
$verticalList = $dbApi->getOfferLabels();

$tab_name = "Affiliate Settings";


?>


<!DOCTYPE html>
<html>
<?php include('./common/header.php'); ?>
<body>
<?php include('./setting_affiliation_modal.php'); ?>
<?php include('./common/body_up.php'); ?>
<div class="row">
    <div class="col-xs-2"></div>
    <div class="col-xs-8">
        <div class="crm_board">
            <div class="row crm_board_title">
                <div class="col-xs-3" style="padding-left: 0">Affiliates</div>
                <div class="col-xs-6">
                    <input type="text" class="form-control input-sm search_affiliates" placeholder="Search by Affiliate name, AFID(s), Offer name, Cap" style="margin-left: 20px;">
                </div>
                <div class="col-xs-3" style="text-align:right;">
                    <div class="affiliation_waiting" style="display: inline;"></div>
                    <button type="button" class="btn btn-default btn-sm btn_affiliation_add" style="font-family: 'Varela Round', sans-serif;" data-toggle="modal" data-target="#affiliation_add_modal">
                        <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;Add Affiliate
                    </button>
                </div>
            </div>
            <div class="alert alert-warning affiliation_alert" role="alert" style="display:none"></div>

            <div class="row crm_board_row">
                <div class="col-xs-4"></div>
                <div class="col-xs-4">
                    <div class="input-group">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle crm_main_toggle_button" data-toggle="dropdown" aria-expanded="false" style="min-width:200px">
                                <?php
                                if ($crmList != null && count($crmList) > 0)
                                    echo 'All Clients ';
                                else
                                    echo 'None Client ';
                                ?>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu crm_main_dropdown_menu" role="menu">
                                <?php
                                if ($crmList != null) {
                                    echo '<li><a href="#" id="0" class="crm_main_dropdown_list">All Clients</a></li>';
                                    for ($i = 0; $i < count($crmList); $i++)
                                        echo '<li><a href="#" id="'.$crmList[$i][0].'" class="crm_main_dropdown_list">'.$crmList[$i][1].'</a></li>';
                                }
                                ?>
                            </ul>
                        </span>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle vertical_main_toggle_button" data-toggle="dropdown" aria-expanded="false" style="min-width:200px; margin-left: 10px;">
                                <?php
                                if ($verticalList != null && count($verticalList) > 0)
                                    echo 'All Vertical Labels ';
                                else
                                    echo 'None Vertical Label ';
                                ?>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu vertical_main_dropdown_menu" role="menu">
                                <?php
                                if ($verticalList != null) {
                                    echo '<li><a href="#" id="0" class="vertical_main_dropdown_list">All Vertical Labels</a></li>';
                                    for ($i = 0; $i < count($verticalList); $i++)
                                        echo '<li><a href="#" id="'.$verticalList[$i][0].'" class="vertical_main_dropdown_list">'.$verticalList[$i][1].'</a></li>';
                                }
                                ?>
                            </ul>
                        </span>
                    </div>
                </div>
            </div>

            <table class="table table-hover" style="margin-top:10px;">
                <thead>
                <tr>
                    <th style="width: 400px">Affiliate</th>
                    <th>AFIDs</th>
                    <th>Offer Name</th>
                    <th>Client</th>
                    <th>Offer CAP</th>
                </tr>
                </thead>
                <tbody class="table_affiliation_body">
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-xs-2">
    </div>
</div>
<?php include('./common/body_down.php'); ?>
</body>
</html>
