<?php

include '../daemon/api/LLCrmApi.php';
require_once '../daemon/api/DBApi.php';
require_once '../daemon/api/StripeApi.php';


session_start();
$user = $_SESSION['user'];
$userRole = $_SESSION['role'];
$subDomain = $_SESSION['sub_domain'];


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
$features =  $dbApi->getFeatureEnableList($subDomain);
$features = explode(',', $features);
if (!in_array(2, $features))
{
    header("Location: ./dashboard.php");
    return;
}
if(!$dbApi->checkClientIp())
{
    header("Location: ./blockip_alert.php");
    return;
}

// check subscription for payment
include ('./common/check_payment.php');

$user_name = $user;
$tab_name = "Monitor URL Status";

?>


<!DOCTYPE html>
<html>
	<?php include('./common/header.php'); ?>
<body>
    <?php include('./setting_crm_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
	<div class="row">        
	 	<div class="col-xs-9">
	 		<div class="crm_board">
				<div class="row crm_board_title">
					<div class="col-xs-10" style="padding-left: 0">Site Status</div>
					<div class="col-xs-2 site_status_waiting" style="text-align:right"></div>
				</div>
				<div class="alert alert-warning site_status_alert" role="alert" style="display:none"></div>
	  			<div class="row crm_board_row">
					<div class="col-xs-12" style="text-align:right; padding-right: 30px">						
						<div class="btn-group monitor_pagination" role="group">
						</div>
						<div class="btn-group">
							<button type="button" class="btn btn-default btn-sm dropdown-toggle count_toggle_button" data-toggle="dropdown" aria-expanded="false" style="width:60px">
								10 <span class="caret"></span>
							</button>
							<ul class="dropdown-menu dropdown-menu-right count_dropdown_menu" role="menu" style="width: 80px !important; min-width: 80px !important">
								<li><a href="#">10</a></li>
								<li><a href="#">20</a></li>
								<li><a href="#">50</a></li>
								<li><a href="#">100</a></li>
								<li><a href="#">500</a></li>
								<li><a href="#">1000</a></li>
							</ul>
						</div>
					</div>
				</div>
				<table class="table table-hover" style="margin-top: 10px">
					<thead>
						<tr>
							<th>#</th>
							<th>Site Name</th>
							<th>Site URL</th>
							<th>Status</th>
							<th>Response Code</th>
							<th>Update Time</th>
						</tr>
					</thead>
					<tbody class="table_url_body">
					</tbody>
				</table>
			</div>
			<section id="metrics">
				<div class="crm_board">
					<div class="row crm_board_title">
						<div class="col-xs-10" style="padding-left: 0">Site Metrics</div>
						<div class="col-xs-2 site_metrics_waiting" style="text-align:right"></div>
					</div>
					<div class="alert alert-warning site_metrics_alert" role="alert" style="display:none"></div>
		  			<div class="row crm_board_row">
						<div class="col-xs-4">
							<div class="btn-group">
								<button type="button" class="btn btn-default btn-sm dropdown-toggle site_name_toggle_button" data-toggle="dropdown" aria-expanded="false" style="min-width:160px">
									Site Name <span class="caret"></span>
								</button>
								<ul class="dropdown-menu site_name_dropdown_menu" role="menu" style="max-height: 400px; overflow-y:auto">
								</ul>
							</div>
						</div>
						<div class="col-xs-8" style="text-align:right; padding-right: 30px">
							<span class="uptime_sum" style="font-weight: bold; margin-right: 50px"></span>
							<button type="button" id="date_DAY" class="btn btn-default btn-sm btn_metrics_date active" style="width: 70px; margin-right: 10px">Day</button>
							<button type="button" id="date_WEEK" class="btn btn-default btn-sm btn_metrics_date" style="width: 70px; margin-right: 10px">Week</button>
							<button type="button" id="date_MONTH" class="btn btn-default btn-sm btn_metrics_date" style="width: 70px;">Month</button>
						</div>
					</div>
					<div class="row crm_board_row" style="max-height: 250px; padding-top: 10px; padding-bottom: 10px">
                        <div class="col-xs-12">
                            <div style="width: 100%; max-height: 210px; margin-right: 20px;">
                                <canvas id="url_status_chart"></canvas>
                            </div>
                        </div>

					</div>
				</div>
			</section>
 		</div>
 		<div class="col-xs-3">
	 		<div class="crm_board">
	 			<div class="row crm_board_title" style="margin-bottom: 15px">
					<div class="col-xs-10" style="padding-left: 0">Monitor Issue History</div>
					<div class="col-xs-2 site_issue_waiting" style="text-align:right"></div>
				</div>
				<div id="issue_history_content" style="max-height: 690px; overflow-y:auto">		  			
				</div>
	 		</div>
	 	</div>
	 </div>
	<?php include('./common/body_down.php'); ?>
</body>
</html>
