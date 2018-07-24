<?php

require_once '../daemon/api/DBApi.php';
require_once '../daemon/api/StripeApi.php';


session_start();
$user_name = $_SESSION['user'];
$user_id = $_SESSION['user_id'];
$subDomain = $_SESSION['sub_domain'];


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
$features =  $dbApi->getFeatureEnableList($subDomain);
$features = explode(',', $features);
if (!in_array(1, $features))
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

$crmList = $dbApi->getKKCrmActiveList($user_id);
$tab_name = "Konnektive Campaign Management";


?>


<!DOCTYPE html>
<html>
	<?php include('./common/header.php'); ?>
<body>
	<?php include('./konnektive_campaign_modal.php'); ?>
	<?php include('./common/body_up.php'); ?>
 	<div class="row">        
	 	<div class="col-xs-12">
	 		<div class="crm_board">
				<div class="row crm_board_title">
					<div class="col-xs-10" style="padding-left: 0">Campaign Management</div>
					<div class="col-xs-2 kk_campaign_waiting" style="text-align:right"></div>
				</div>
				<div class="alert alert-warning kk_campaign_alert" role="alert" style="display:none"></div>
	  			<div class="row crm_board_row">
	  				<div class="col-xs-3">
	  					<div class="input-group">
		  					<span class="input-group-btn">
								<button type="button" class="btn btn-default btn-sm dropdown-toggle crm_toggle_button" data-toggle="dropdown" aria-expanded="false" style="min-width:160px">
									<?php
										if ($crmList != null && count($crmList) > 0)
											echo $crmList[0][1].' ';
										else
											echo 'None CRM ';
									?>
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu crm_dropdown_menu" role="menu">
								<?php
									if ($crmList != null) {
										for ($i = 0; $i < count($crmList); $i++)
											echo '<li><a href="#" id="'.$crmList[$i][0].'" class="crm_dropdown_list">'.$crmList[$i][1].'</a></li>';
									}
								?>
								</ul>
							</span>
							<span class="input-group-addon calendar_label">Campaign Category</span>
						    <span class="input-group-btn">
								<button type="button" class="btn btn-default btn-sm dropdown-toggle category_toggle_button" data-toggle="dropdown" aria-expanded="false" style="min-width:200px">
									All Campaigns <span class="caret"></span>
								</button>
								<ul class="dropdown-menu category_dropdown_menu" role="menu" style="max-height: 400px; overflow-y:auto">
									<li><a href="#" id="0">All Campaigns</a></li>
								</ul>
							</span>
						</div>
					</div>
					<div class="col-xs-9" style="text-align:right; padding-right: 30px">
						<button type="button" class="btn btn-default btn-sm btn_category_add" data-toggle="modal" data-target="#category_add_modal" disabled="disabled" style="margin-right: 10px"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;Add Category</button>
						<button type="button" class="btn btn-default btn-sm btn_category_edit" data-toggle="modal" data-target="#category_edit_modal" disabled="disabled" style="margin-right: 10px"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span>&nbsp;Edit Category</button>
						<button type="button" class="btn btn-default btn-sm btn_category_delete" data-toggle="modal" data-target="#category_delete_modal" disabled="disabled"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true" style="color: #ffa5a5"></span>&nbsp;Delete Category</button>
					</div>
				</div>
				<table class="table table-hover" style="margin-top: 10px">
					<thead>
						<tr>
							<th>ID</th>
							<th>Campaign Name</th>
							<th>Type</th>
							<th>Currency</th>
	                        <th>Q A</th>
						</tr>
					</thead>
					<tbody class="table_campaign_body">
					</tbody>
				</table>
			</div>
 		</div> 		 
	 </div>
	<?php include('./common/body_down.php'); ?>
</body>
</html>
