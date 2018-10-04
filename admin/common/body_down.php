			</div> <!-- crm_content -->
		</div>	<!-- container -->
	</div>	<!-- content_wrap -->
	<div id="footer">
	    © <?php echo date("Y");?> API Lotus Inc. All Rights Reserved.
	</div>
</div>	<!-- wrap_body -->

<script type="text/javascript" src="../js/jquery.min.js"></script>
<?php if ($tab_name == "Dashboard") { ?>
<script type="text/javascript" src="../js/jquery-ui.min.js"></script>
<?php } ?>
<script type="text/javascript" src="../bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="../bootstrap/datepicker/js/bootstrap-datepicker.min.js"></script>

<script type="text/javascript" src="../js/admin/admin.min.js"></script>
<?php if ($tab_name == "Dashboard") echo "<script type=\"text/javascript\" src=\"../js/admin/dashboard.js\"></script>"; ?>
<?php if ($tab_name == "Affiliate") echo "<script type=\"text/javascript\" src=\"../js/admin/affiliate.min.js\"></script>"; ?>
<?php if ($tab_name == "Retention") echo "<script type=\"text/javascript\" src=\"../js/admin/retention.js\"></script>"; ?>
<?php if ($tab_name == "Rebill") echo "<script type=\"text/javascript\" src=\"../js/admin/rebill.js\"></script>"; ?>
<?php if ($tab_name == "Sales") echo "<script type=\"text/javascript\" src=\"../js/admin/sales.min.js\"></script>"; ?>
<?php if ($tab_name == "Alerts") echo "<script type=\"text/javascript\" src=\"../js/admin/alert.min.js\"></script>"; ?>
<?php if ($tab_name == "Export Retention") echo "<script type=\"text/javascript\" src=\"../js/admin/retention_export.min.js\"></script>"; ?>
<?php if ($tab_name == "Campaign Management") echo "<script type=\"text/javascript\" src=\"../js/admin/setting_campaign.js\"></script>"; ?>
<?php if ($tab_name == "CRM Management") echo "<script type=\"text/javascript\" src=\"../js/admin/setting_crm.min.js\"></script>"; ?>
<?php if ($tab_name == "Accounts") echo "<script type=\"text/javascript\" src=\"../js/admin/setting_accounts.min.js\"></script>"; ?>
<?php if ($tab_name == "Affiliate Management") echo "<script type=\"text/javascript\" src=\"../js/admin/setting_affiliate.min.js\"></script>"; ?>
<?php if ($tab_name == "Alert Percentage Levels") echo "<script type=\"text/javascript\" src=\"../js/admin/setting_alert.min.js\"></script>"; ?>
<?php if ($tab_name == "Payment Management") echo "<script type=\"text/javascript\" src=\"../js/admin/setting_payment.min.js\"></script>"; ?>
<?php if ($tab_name == "CAP Update") echo "<script type=\"text/javascript\" src=\"../js/admin/cap_update.js\"></script>"; ?>
<?php if ($tab_name == "CAP Update(Beta)") echo "<script type=\"text/javascript\" src=\"../js/admin/cap_update_.js\"></script>"; ?>
<?php if ($tab_name == "Offers") echo "<script type=\"text/javascript\" src=\"../js/admin/setting_offer.js\"></script>"; ?>
<?php if ($tab_name == "Affiliate Settings") echo "<script type=\"text/javascript\" src=\"../js/admin/setting_affiliation.js\"></script>"; ?>
<?php if ($tab_name == "Konnektive Order Summary") echo "<script type=\"text/javascript\" src=\"../js/admin/konnektive_order_summary.min.js\"></script>"; ?>
<?php if ($tab_name == "Konnektive Retention") echo "<script type=\"text/javascript\" src=\"../js/admin/konnektive_retention.min.js\"></script>"; ?>
<?php if ($tab_name == "Konnektive Account Management") echo "<script type=\"text/javascript\" src=\"../js/admin/konnektive_accounts.min.js\"></script>"; ?>
<?php if ($tab_name == "Konnektive Campaign Management") echo "<script type=\"text/javascript\" src=\"../js/admin/konnektive_campaign.min.js\"></script>"; ?>
<?php if ($tab_name == "Monitor URL Management") {
    echo "<script type=\"text/javascript\" src=\"../js/admin/monitor_url_management.min.js\"></script>";
    echo "<script type=\"text/javascript\" src=\"../js/papaparse/papaparse.min.js\"></script>";
} ?>
<?php if ($tab_name == "Monitor URL Status") {
    echo "<script type=\"text/javascript\" src=\"../js/chart/moment.min.js\"></script>";
    echo "<script type=\"text/javascript\" src=\"../js/chart/Chart.min.js\"></script>";
    echo "<script type=\"text/javascript\" src=\"../js/admin/monitor_url_status.min.js\"></script>";
} ?>
<?php if ($tab_name == "Profile") echo "<script type=\"text/javascript\" src=\"../js/admin/profile.min.js\"></script>"; ?>