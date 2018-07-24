<?php

// session_start();

// if (!isset($_SESSION['user']) || $_SESSION['user'] == '')
// 	header("Location: ./login.php");

// $user_name = $_SESSION['user'];
// $tab_name = "Alerts";	

?>


<!DOCTYPE html>
<html>
	<head>
		<title>API Lotus</title>
		<meta charset="utf-8" />
	    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	    <meta name="viewport" content="width=device-width, initial-scale=1" />

	    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	    <link href="../css/limelightcrm.css" rel="stylesheet" type="text/css" />
		<link rel="icon" href="../images/favicon.png" type="image/x-icon">
	</head>
	<body>
		<div class="row" style="margin-top:50px;">
			<div class="col-xs-4 col-xs-offset-4">
				<div class="panel panel-danger">
					<div class="panel-heading">
						Subscription Alert
					</div>
					<div class="panel-body">
						<label>
							Sorry!<br>
							Unfortunately, this subdomain doesn't exist any subscription or expired.<br>
							Please add subscription or pay on this <a href="./setting_payment.php">PAYMENT PAGE</a>.<br>
							If you have any comments on this, please contact us.<br><br>
							API Lotus Team
						</label>
					</div>
				</div>
			</div>	
		</div>
	</body>
</html>
