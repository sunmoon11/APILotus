<?php

require_once '../daemon/api/DBApi.php';
require_once '../daemon/api/StripeApi.php';


session_start();
$user_name = $_SESSION['user'];
$user_id = $_SESSION['user_id'];
session_write_close();

if (!isset($user_name) || $user_name == '' || !isset($user_id) || $user_id == '')
{
    header("Location: ./login.php");
    return;
}


// check client ip
$dbApi = DBApi::getInstance();
if(!$dbApi->checkClientIp())
{
    header("Location: ./blockip_alert.php");
    return;
}

// check subscription for payment
include ('./common/check_payment.php');

$tab_name = "Monitor Lotus";

?>
<!DOCTYPE html>
<html>
<?php include('./common/header.php'); ?>
<head>
</head>
<body>
    <?php include('./setting_monitor_modal.php'); ?>
    <?php include('./common/body_up.php'); ?>
        <div class="row">
            <div class="col-xs-9">
                <div class="row tab_row_default">
                    <div class="col-xs-10"><span class="glyphicon glyphicon-tasks" aria-hidden="true" style="width:25px;color:#fff"></span> Monitor Urls</div>
                    <div class="col-xs-2 monitor_url_waiting" style="text-align:right"></div>
                </div>
                <div class="alert alert-warning monitor_url_alert" role="alert" style="display:none"></div>
                <div class="row" style="padding-bottom: 6px">
                    <div class="col-xs-12" style="text-align:right">
                        <button type="button" class="btn btn-link btn-sm btn_url_add" data-toggle="modal" data-target="#monitor_add_modal"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;Add Url</button>
                        <button type="button" class="btn btn-link btn-sm btn_schedule" data-toggle="modal" data-target="#monitor_schedule_modal"><span class="glyphicon glyphicon-time" aria-hidden="true" style="color: #ffa5a5"></span>&nbsp;Schedule</button>
                        <button type="button" class="btn btn-link btn-sm btn_site_refresh"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>&nbsp;Refresh</button>
                        &nbsp;&nbsp;
                        <div class="btn-group monitor_site_pagination" role="group">
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle count_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:60px">
                                10 <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu count_dropdown_menu" role="menu">
                                <li><a href="#">10</a></li>
                                <li><a href="#">25</a></li>
                                <li><a href="#">50</a></li>
                                <li><a href="#">100</a></li>
                                <li><a href="#">500</a></li>
                                <li><a href="#">1000</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Url</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="table_monitor_url_body">
                    </tbody>
                </table>
            </div>
            <div class="col-xs-3">
                <div class="row tab_row_alert">
                    <div class="col-xs-10"><span class="glyphicon glyphicon-info-sign" aria-hidden="true" style="width:25px;color:#fff"></span> Monitor Status</div>
                    <div class="col-xs-2 monitor_status_waiting" style="text-align:right"></div>
                </div>
                <div class="row">
                    <div class="col-xs-12" style="text-align:right">
                        <button type="button" class="btn btn-link btn-sm btn_status_refresh"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>&nbsp;Refresh</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 monitor_status">
                        <div class="notice notice-danger"><strong>404</strong> EG CRM 2017 11/30 03:19</div>
                        <div class="notice notice-danger"><strong>404</strong> EG CRM 2017 11/30 03:19</div>
                        <div class="notice notice-danger"><strong>404</strong> EG CRM 2017 11/30 03:19</div>
                        <div class="notice notice-danger"><strong>404</strong> EG CRM 2017 11/30 03:19</div>
                    </div>
                </div>
            </div>

        </div>
    <?php include('./common/body_down.php'); ?>
</body>
</html>

