<?php

$user_name = '';
session_start();
if (isset($_SESSION['ap_user_name']))
 	$user_name = $_SESSION['ap_user_name'];
session_write_close();

?>


<div class="container-fluid">
	<div class="row">
		<div class="col-xs-2 ap_sidebar">
			<div>
				<a style="margin:0;padding:0" href="../index.php"><img src="../images/logo.png"></a>
			</div>
			<ul class="nav nav-sidebar" style="margin-top:20px">
				<?php if ($tab_name == "Home") { ?>
					<li><a class="ap_sidebar_list" style="font-weight:bold;color:#6772e5 !important" href="./home.php"><span class="glyphicon glyphicon-home ap_icon ap_active" aria-hidden="true"></span>Home</a></li>
				<?php } else { ?>
					<li><a class="ap_sidebar_list" style="font-weight:none" href="./home.php"><span class="glyphicon glyphicon-home ap_icon" aria-hidden="true"></span>Home</a></li>
				<?php } ?>
				<?php if ($tab_name == "Accounts") { ?>
					<li><a class="ap_sidebar_list" style="font-weight:bold;color:#6772e5 !important" href="./accounts.php"><span class="glyphicon glyphicon-user ap_icon ap_active" aria-hidden="true"></span>Accounts</a></li>
				<?php } else { ?>
					<li><a class="ap_sidebar_list" style="font-weight:none" href="./accounts.php"><span class="glyphicon glyphicon-user ap_icon" aria-hidden="true"></span>Accounts</a></li>
				<?php } ?>
				<?php if ($tab_name == "CRM Management") { ?>
					<li><a class="ap_sidebar_list" style="font-weight:bold;color:#6772e5 !important" href="./crms.php"><span class="glyphicon glyphicon-th-large ap_icon ap_active" aria-hidden="true"></span>CRM Management</a></li>
				<?php } else { ?>
					<li><a class="ap_sidebar_list" style="font-weight:none" href="./crms.php"><span class="glyphicon glyphicon-th-large ap_icon" aria-hidden="true"></span>CRM Management</a></li>
				<?php } ?>
				<?php if ($tab_name == "Payments") { ?>
					<li><a class="ap_sidebar_list" style="font-weight:bold;color:#6772e5 !important" href="./payments.php"><span class="glyphicon glyphicon-credit-card ap_icon ap_active" aria-hidden="true"></span>Payments</a></li>
				<?php } else { ?>
					<li><a class="ap_sidebar_list" style="font-weight:none" href="./payments.php"><span class="glyphicon glyphicon-credit-card ap_icon" aria-hidden="true"></span>Payments</a></li>
				<?php } ?>
				<?php if ($tab_name == "Customer Feedbacks") { ?>
					<li><a class="ap_sidebar_list" style="font-weight:bold;color:#6772e5 !important" href="./customer_feedbacks.php"><span class="glyphicon glyphicon-edit ap_icon ap_active" aria-hidden="true"></span>Customer Feedbacks</a></li>
				<?php } else { ?>
					<li><a class="ap_sidebar_list" style="font-weight:none" href="./customer_feedbacks.php"><span class="glyphicon glyphicon-edit ap_icon" aria-hidden="true"></span>Customer Feedbacks</a></li>
				<?php } ?>
				<?php if ($tab_name == "Profile") { ?>
					<li><a class="ap_sidebar_list" style="font-weight:bold;color:#6772e5 !important" href="./profile.php"><span class="glyphicon glyphicon-pencil ap_icon ap_active" aria-hidden="true"></span>Profile</a></li>
				<?php } else { ?>
					<li><a class="ap_sidebar_list" style="font-weight:none" href="./profile.php"><span class="glyphicon glyphicon-pencil ap_icon" aria-hidden="true"></span>Profile</a></li>
				<?php } ?>
			</ul>
		</div>
		<div class="col-xs-10 col-xs-offset-2 ap_content">
			<nav class="navbar navbar-default ap_navbar">
				<div class="container-fluid">
					<ul class="nav navbar-nav navbar-right">
						<li><a class="ap_sidebar_list" href="./profile.php" style="padding:40px 0 0 0">Welcome <span style="color:#6772e5"><?php echo $user_name; ?>!</span></a></li>
						<li style="width:100px"><a class="ap_sidebar_list" href="./logout.php" style="text-align:right;padding:40px 0 0 0"><span class="glyphicon glyphicon-log-out ap_icon" aria-hidden="true"></span> Logout</a></li>
					</ul>
				</div>
			</nav>