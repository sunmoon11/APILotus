<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 2018-11-15
 * Time: 5:52 PM
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

$tab_name = "Billing";

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
                <div class="col-xs-10" style="padding-left: 0">Billing</div>
                <div class="col-xs-2 billing_waiting" style="text-align:right;"></div>
            </div>
            <div class="alert alert-warning billing_alert" role="alert" style="display:none"></div>
            <div class="row crm_board_row">
                <div class="col-xs-1">
                </div>
                <div class="col-xs-5">
                    <div class="input-daterange input-group" id="datepicker">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle date_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:160px; border-radius: 0">
                                Week To Date <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu date_dropdown_menu" role="menu" style="overflow: auto; max-height: 300px;">
                                <li><a href="#" id="date_thisweek">Week To Date</a></li>
                                <li><a href="#" id="date_lastweek">Last Week</a></li>
                                <?php
                                    $firstDayOfYear = mktime(0, 0, 0, 1, 1, date('Y'));
                                    $nextMonday     = strtotime('monday', $firstDayOfYear);
                                    $nextSunday     = strtotime('sunday', $nextMonday);

                                    $weeks = array();

                                    while ($nextMonday < strtotime("previous monday")) {
                                        $weeks[] = date('m.d.y', $nextMonday). '-'. date('m.d.y', $nextSunday);
                                        $nextMonday = strtotime('+1 week', $nextMonday);
                                        $nextSunday = strtotime('+1 week', $nextSunday);
                                    }
                                    foreach (array_reverse($weeks) as $week) {
                                        echo '<li><a href="#" id="date_'. $week . '">'.$week.'</a></li>';
                                    }
                                ?>
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
                        <button class="btn btn-default btn-sm btn_billing_export" type="button" style="width:120px;margin-right: 10px"><span class="glyphicon glyphicon-export" aria-hidden="true"></span>&nbsp;&nbsp;Export</button>
                    </a>
                </div>
            </div>
            <div class="c_top">
                <div class="row div_billing_body">
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('./common/body_down.php'); ?>
</body>
</html>
