<?php

require_once '../daemon/api/DBApi.php';

//session_start();
$userId = $_SESSION['user_id'];
$subDomain = $_SESSION['sub_domain'];
$userRole = $_SESSION['role'];
$userEmail = $_SESSION['user_email'];
//session_write_close();

$dbApi = DBApi::getInstance();
$alert_count = $dbApi->getRecentAlertCount($userId);
$features = $dbApi->getFeatureEnableList($subDomain);
$features = explode(',', $features);
$enableKKCRM = false;
$enableMonitorLotus = false;
$enableAffiliate = false;
$enableTablet = false;

if (in_array(1, $features))
    $enableKKCRM = true;
if (in_array(2, $features))
    $enableMonitorLotus = true;
if (in_array(3, $features))
    $enableAffiliate = true;
if (in_array(4, $features))
    $enableTablet = true;

?>

<div class="modal fade" id="alert_delete_all_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    Do you want to delete all of the alerts?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_alert_delete_all">Delete All</button>
            </div>
        </div>
    </div>
</div>
<div id="wrap_body">
	<div id="content_wrap">
		<div id="container">
			<section id="Top" class="group">
				<nav class="navbar navbar-default navbar-fixed-top main_navbar">
        			<div class="container-fluid" style="margin:0; padding: 0">
        				<div class="navbar-header">
			                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".bs-navbar-collapse" style="margin-top: 20px">
			                    <span class="sr-only">Toggle navigation</span>
			                    <span class="icon-bar"></span>
			                    <span class="icon-bar"></span>
			                    <span class="icon-bar"></span>
			                </button>
			                <img class="logo_navbar visible-xs hidden-sm visible-md visible-lg" src="../images/banner_left.png" />
			                <img class="logo_navbar hidden-xs visible-sm hidden-md hidden-lg" src="../images/banner_left_small.png" />
			            </div>
			            <div class="collapse navbar-collapse bs-navbar-collapse" style="padding-top:22px; padding-right: 20px">
			            	<ul class="nav navbar-nav navbar-right">
			            		<?php if ($tab_name == "Dashboard" || $tab_name == "Affiliate" || $tab_name == "Retention" || $tab_name == "Sales" || $tab_name == "Alerts" || $tab_name == "Accounts" || $tab_name == "Affiliate Management" || $tab_name == "Alert Percentage Levels" || $tab_name == "Campaign Management" || $tab_name == "CRM Management") { ?>
									<li class="dropdown hidden-xs visible-sm visible-md hidden-lg">
										<a href="#" class="dropdown-toggle crm_tab_label active" data-toggle="dropdown" role="button" aria-expanded="false">LIMELIGHT CRM <span class="caret"></span></a>
								<?php } else { ?>
									<li class="dropdown hidden-xs visible-sm visible-md hidden-lg">
										<a href="#" class="dropdown-toggle crm_tab_label" data-toggle="dropdown" role="button" aria-expanded="false">LIMELIGHT CRM <span class="caret"></span></a>
								<?php } ?>
										<ul class="dropdown-menu crm_setting_dropdown" role="menu">
											<?php if ($tab_name == "Dashboard") { ?>
												<li><a class="crm_tab_label active small" href="./dashboard.php">Dashboard</a></li>
											<?php } else { ?>
												<li><a class="crm_tab_label small" href="./dashboard.php">Dashboard</a></li>
											<?php } ?>
											<li role="presentation" class="divider"></li>
                                            <?php if ($tab_name == "CAP Update") { ?>
                                                <li><a class="crm_tab_label active small" href="./cap_update.php">CAP Update</a></li>
                                            <?php } else { ?>
                                                <li><a class="crm_tab_label small" href="./cap_update.php">CAP Update</a></li>
                                            <?php } ?>
											<?php if ($tab_name == "Affiliate" && $enableAffiliate) { ?>
												<li><a class="crm_tab_label active small" href="./affiliate.php">Affiliate Report</a></li>
											<?php } else if ($enableAffiliate){ ?>
												<li><a class="crm_tab_label small" href="./affiliate.php">Affiliate Report</a></li>
											<?php } ?>
											<?php if ($tab_name == "Retention" || $tab_name == "Export Retention") { ?>
												<li><a class="crm_tab_label active small" href="./retention.php">Retention Report</a></li>
											<?php } else { ?>
												<li><a class="crm_tab_label small" href="./retention.php">Retention Report</a></li>
											<?php } ?>
                                            <?php if ($tab_name == "Rebill") { ?>
                                                <li><a class="crm_tab_label active small" href="./rebill.php">Rebill Report</a></li>
                                            <?php } else { ?>
                                                <li><a class="crm_tab_label small" href="./rebill.php">Rebill Report</a></li>
                                            <?php } ?>
											<?php if ($tab_name == "Sales") { ?>
												<li><a class="crm_tab_label active small" href="./sales.php">Sales Report</a></li>
											<?php } else { ?>
												<li><a class="crm_tab_label small" href="./sales.php">Sales Report</a></li>
											<?php } ?>
											<?php if ($tab_name == "Alerts") { ?>
												<li><a class="crm_tab_label active small" href="./alerts.php">Alerts Report</a></li>
											<?php } else { ?>
												<li><a class="crm_tab_label small" href="./alerts.php">Alerts Report</a></li>
											<?php } ?>
											<li role="presentation" class="divider"></li>
											<?php if ($userRole != '0') { ?>
												<?php if ($tab_name == "CRM Management") { ?>
													<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./setting_crm.php">CRM Management Setting</a></li>
												<?php } else { ?>
													<li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_crm.php">CRM Management Setting</a></li>
												<?php } ?>
												<?php if ($tab_name == "Campaign Management") { ?>
													<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./setting_campaign.php">Campaign Management Setting</a></li>
												<?php } else { ?>
													<li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_campaign.php">Campaign Management Setting</a></li>
												<?php } ?>
												<?php if ($tab_name == "Affiliate Management" && $enableAffiliate) { ?>
													<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./setting_affiliate.php">Affiliate Management Setting</a></li>
												<?php } else if ($enableAffiliate){ ?>
													<li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_affiliate.php">Affiliate Management Setting</a></li>
												<?php } ?>
												<?php if ($tab_name == "Alert Percentage Levels") { ?>
													<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./setting_alert.php">Alert Percentage Levels Setting</a></li>
												<?php } else { ?>
													<li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_alert.php">Alert Percentage Levels Setting</a></li>
												<?php } ?>
											<?php } ?>
											<?php if ($userRole == '9') { ?>
												<?php if ($tab_name == "Payment Management") { ?>
													<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./setting_payment.php">Payment Management Setting</a></li>
												<?php } else { ?>
													<li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_payment.php">Payment Management Setting</a></li>
												<?php } ?>
											<?php } ?>
											<?php if ($tab_name == "Accounts") { ?>
												<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./setting_accounts.php">User Accounts Setting</a></li>
											<?php } else { ?>
												<li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_accounts.php">User Accounts Setting</a></li>
											<?php } ?>
										</ul>
									</li>
			            		<?php if ($tab_name == "Dashboard") { ?>
									<li class="visible-xs hidden-sm hidden-md visible-lg"><a class="crm_tab_label active" href="./dashboard.php">DASHBOARD</a></li>
								<?php } else { ?>
									<li class="visible-xs hidden-sm hidden-md visible-lg"><a class="crm_tab_label" href="./dashboard.php">DASHBOARD</a></li>
								<?php } ?>
								<?php if ($tab_name == "Affiliate" || $tab_name == "Retention" || $tab_name == "Alerts" || $tab_name == "Sales") { ?>
									<li class="dropdown visible-xs hidden-sm hidden-md visible-lg">
										<a href="#" class="dropdown-toggle crm_tab_label active" data-toggle="dropdown" role="button" aria-expanded="false">REPORTS <span class="caret"></span></a>
								<?php } else { ?>
									<li class="dropdown visible-xs hidden-sm hidden-md visible-lg">
										<a href="#" class="dropdown-toggle crm_tab_label" data-toggle="dropdown" role="button" aria-expanded="false">REPORTS <span class="caret"></span></a>
								<?php } ?>										
										<ul class="dropdown-menu crm_setting_dropdown" role="menu">
										<?php if ($tab_name == "Affiliate" && $enableAffiliate) { ?>
											<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./affiliate.php">Affiliate Report</a></li>
										<?php } else if ($enableAffiliate){ ?>
											<li class="crm_tab_left"><a class="crm_tab_label small" href="./affiliate.php">Affiliate Report</a></li>
										<?php } ?>
										<?php if ($tab_name == "Retention" || $tab_name == "Export Retention") { ?>
											<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./retention.php">Retention Report</a></li>
										<?php } else { ?>
											<li class="crm_tab_left"><a class="crm_tab_label small" href="./retention.php">Retention Report</a></li>
										<?php } ?>
                                        <?php if ($tab_name == "Rebill") { ?>
                                            <li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./rebill.php">Rebill Report</a></li>
                                        <?php } else { ?>
                                            <li class="crm_tab_left"><a class="crm_tab_label small" href="./rebill.php">Rebill Report</a></li>
                                        <?php } ?>
										<?php if ($tab_name == "Sales") { ?>
											<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./sales.php">Sales Report</a></li>
										<?php } else { ?>
											<li class="crm_tab_left"><a class="crm_tab_label small" href="./sales.php">Sales Report</a></li>
										<?php } ?>
										<?php if ($tab_name == "Alerts") { ?>
											<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./alerts.php">Alerts Report</a></li>
										<?php } else { ?>
											<li class="crm_tab_left"><a class="crm_tab_label small" href="./alerts.php">Alerts Report</a></li>
										<?php } ?>
										</ul>
									</li>
								<?php if ($tab_name == "Accounts" || $tab_name == "Affiliate Management" || $tab_name == "Alert Percentage Levels" || $tab_name == "Campaign Management" || $tab_name == "CRM Management") { ?>
									<li class="dropdown visible-xs hidden-sm hidden-md visible-lg">
										<a href="#" class="dropdown-toggle crm_tab_label active" data-toggle="dropdown" role="button" aria-expanded="false">SETTINGS <span class="caret"></span></a>
								<?php } else { ?>
									<li class="dropdown visible-xs hidden-sm hidden-md visible-lg">
										<a href="#" class="dropdown-toggle crm_tab_label" data-toggle="dropdown" role="button" aria-expanded="false">SETTINGS <span class="caret"></span></a>
								<?php } ?>										
										<ul class="dropdown-menu crm_setting_dropdown" role="menu">
											<?php if ($userRole != '0') { ?>
												<?php if ($tab_name == "CRM Management") { ?>
													<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./setting_crm.php">CRM Management</a></li>
												<?php } else { ?>
													<li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_crm.php">CRM Management</a></li>
												<?php } ?>
												<?php if ($tab_name == "Campaign Management") { ?>
													<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./setting_campaign.php">Campaign Management</a></li>
												<?php } else { ?>
													<li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_campaign.php">Campaign Management</a></li>
												<?php } ?>
												<?php if ($tab_name == "Affiliate Management" && $enableAffiliate) { ?>
													<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./setting_affiliate.php">Affiliate Management</a></li>
												<?php } else if ($enableAffiliate){ ?>
													<li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_affiliate.php">Affiliate Management</a></li>
												<?php } ?>
												<?php if ($tab_name == "Alert Percentage Levels") { ?>
													<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./setting_alert.php">Alert Percentage Levels</a></li>
												<?php } else { ?>
													<li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_alert.php">Alert Percentage Levels</a></li>
												<?php } ?>
											<?php } ?>
											<?php if ($userRole == '9') { ?>
												<?php if ($tab_name == "Payment Management") { ?>
													<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./setting_payment.php">Payment Management</a></li>
												<?php } else { ?>
													<li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_payment.php">Payment Management</a></li>
												<?php } ?>
											<?php } ?>
											<?php if ($tab_name == "Accounts") { ?>
												<li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./setting_accounts.php">User Accounts</a></li>
											<?php } else { ?>
												<li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_accounts.php">User Accounts</a></li>
											<?php } ?>
										</ul>
									</li>

                                <?php if ($tab_name == "CAP Update" || $tab_name == "Offers" || $tab_name == "Affiliate Settings") { ?>
                                    <li class="dropdown">
                                        <a href="#" class="dropdown-toggle crm_tab_label active" data-toggle="dropdown" role="button" aria-expanded="false">CAP UPDATE <span class="caret"></span></a>
                                <?php } else { ?>
                                    <li class="dropdown">
                                        <a href="#" class="dropdown-toggle crm_tab_label" data-toggle="dropdown" role="button" aria-expanded="false">CAP UPDATE <span class="caret"></span></a>
                                <?php } ?>
                                        <ul class="dropdown-menu crm_setting_dropdown" role="menu">
                                            <?php if ($tab_name == "CAP Update") { ?>
                                                <li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./cap_update.php">CAP Update</a></li>
                                            <?php } else { ?>
                                                <li class="crm_tab_left"><a class="crm_tab_label small" href="./cap_update.php">CAP Update</a></li>
                                            <?php } ?>
                                            <?php if ($tab_name == "Offers") { ?>
                                                <li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./setting_offer.php">Offers</a></li>
                                            <?php } else { ?>
                                                <li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_offer.php">Offers</a></li>
                                            <?php } ?>
                                            <?php if ($tab_name == "Affiliate Settings") { ?>
                                                <li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./cap_update.php">Affiliate Settings</a></li>
                                            <?php } else { ?>
                                                <li class="crm_tab_left"><a class="crm_tab_label small" href="./cap_update.php">Affiliate Settings</a></li>
                                            <?php } ?>
                                        </ul>
                                    </li>


	                            <?php if ($enableKKCRM) { ?>
									<?php if ($tab_name == "Konnektive Order Summary" || $tab_name == "Konnektive Retention" || $tab_name == "Konnektive Account Management" || $tab_name == "Konnektive Campaign Management") { ?>
	                                    <li class="dropdown">
	                                    	<a href="#" class="dropdown-toggle crm_tab_label active" data-toggle="dropdown" role="button" aria-expanded="false">KONNEKTIVE CRM <span class="caret"></span></a>
	                                <?php } else { ?>
	                                    <li class="dropdown">
	                                    	<a href="#" class="dropdown-toggle crm_tab_label" data-toggle="dropdown" role="button" aria-expanded="false">KONNEKTIVE CRM <span class="caret"></span></a>
	                                <?php } ?>											
											<ul class="dropdown-menu crm_setting_dropdown" role="menu">
												<?php if ($tab_name == "Konnektive Account Management") { ?>
			                                        <li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./konnektive_accounts.php">Account Management</a></li>
			                                    <?php } else { ?>
			                                        <li class="crm_tab_left"><a class="crm_tab_label small" href="./konnektive_accounts.php">Account Management</a></li>
			                                    <?php } ?>
			                                    <?php if ($tab_name == "Konnektive Campaign Management") { ?>
			                                        <li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./konnektive_campaign.php">Campaign Management</a></li>
			                                    <?php } else { ?>
			                                        <li class="crm_tab_left"><a class="crm_tab_label small" href="./konnektive_campaign.php">Campaign Management</a></li>
			                                    <?php } ?>
			                                    <?php if ($tab_name == "Konnektive Order Summary") { ?>
			                                        <li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./konnektive_order_summary.php">Order Summary Report</a></li>
			                                    <?php } else { ?>
			                                        <li class="crm_tab_left"><a class="crm_tab_label small" href="./konnektive_order_summary.php">Order Summary Report</a></li>
			                                    <?php } ?>
			                                    <?php if ($tab_name == "Konnektive Retention") { ?>
			                                        <li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./konnektive_retention.php">Retention Report</a></li>
			                                    <?php } else { ?>
			                                        <li class="crm_tab_left"><a class="crm_tab_label small" href="./konnektive_retention.php">Retention Report</a></li>
			                                    <?php } ?>
											</ul>
										</li>
	                            <?php } ?>
	                            <?php if ($enableMonitorLotus) { ?>
	                                <?php if ($tab_name == "Monitor URL Management" || $tab_name == "Monitor URL Status") { ?>
	                                    <li class="dropdown">
	                                        <a href="#" class="dropdown-toggle crm_tab_label active" data-toggle="dropdown" role="button" aria-expanded="false">MONITOR LOTUS <span class="caret"></span></a>
	                                <?php } else { ?>
	                                    <li class="dropdown">
	                                        <a href="#" class="dropdown-toggle crm_tab_label" data-toggle="dropdown" role="button" aria-expanded="false">MONITOR LOTUS <span class="caret"></span></a>
	                                <?php } ?>
	                                        <ul class="dropdown-menu crm_setting_dropdown" role="menu">
	                                            <?php if ($tab_name == "Monitor URL Management") { ?>
	                                                <li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./monitor_url_management.php">Monitor URL Management</a></li>
	                                            <?php } else { ?>
	                                                <li class="crm_tab_left"><a class="crm_tab_label small" href="./monitor_url_management.php">Monitor URL Management</a></li>
	                                            <?php } ?>
	                                            <?php if ($tab_name == "Monitor URL Status") { ?>
	                                                <li class="crm_tab_left_active"><a class="crm_tab_label active small" href="./monitor_url_status.php">Monitor URL Status</a></li>
	                                            <?php } else { ?>
	                                                <li class="crm_tab_left"><a class="crm_tab_label small" href="./monitor_url_status.php">Monitor URL Status</a></li>
	                                            <?php } ?>
	                                        </ul>
	                                    </li>
	                            <?php } ?>
	                            <li id="alert_tab_item" class="hidden-xs visible-sm visible-md visible-lg">
									<a id="alert_link" class="crm_tab_label1" href="#" style="padding-top: 8px; padding-right: 0;">
										<img src="../images/bell.png" style="width:25px; height: 25px">
										<p class="span_alert_count" style="vertical-align: top !important; height: 100%; float: right;">
										<?php
											if (isset($alert_count) && $alert_count > 0)
												echo '<span class="alert_count">' . $alert_count . '</span>';
										?>
										</p>
									</a>
									<div class="dropdown_content">
										<div class="row tab_row_alert" style="background: #f9f9f9; margin-bottom: 0">
											<div class="col-xs-6">
											</div>
											<div class="col-xs-6" style="text-align:right;padding-right:0">
												<button type="button" class="btn btn-link btn-sm btn_alert_delete_all" style="text-decoration: none !important;"><span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color: #00b9ab"></span><span style="color:#00b9ab">&nbsp;Remove All</span></button>
											</div>
										</div>
										<div class="alert_body" style="background:#fff;overflow-y:auto;border-top: 1px solid #e5e5e5">
											<div style="text-align:center"><img src="../images/loading.gif" style="width:40px"></div>
										</div>
									</div>
								</li>
								<li class="dropdown hidden-xs visible-sm visible-md visible-lg">
									<a href="#" class="dropdown-toggle crm_tab_label1" data-toggle="dropdown" role="button" aria-expanded="false" style="padding-top: 8px;"><img src="../images/user.png" style="width:25px; height: 25px"></a>
									<ul class="dropdown-menu dropdown-menu-right crm_setting_dropdown" role="menu">
										<li class="crm_tab_left"><a class="crm_tab_label small" href="#" style="background: #f9f9f9 !important; color: #00b9ab !important"><b><?php echo $userEmail; ?></b><br/><?php echo $user_name; ?></a></li>
										<li class="crm_tab_left"><a class="crm_tab_label small" href="./setting_accounts.php">My Profile</a></li>
										<li class="crm_tab_left"><a class="crm_tab_label small" href="./logout.php">Logout</a></li>
									</ul>
								</li>
								<li class="crm_tab_left visible-xs hidden-sm hidden-md hidden-lg"><a class="crm_tab_label" href="./logout.php">LOGOUT</a></li>
			            	</ul>
			            </div>
        			</div>
        		</nav>				
			</section>
			<div class="crm_content">
