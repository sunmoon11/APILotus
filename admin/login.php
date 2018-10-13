<?php

session_start();

include_once '../daemon/api/DBApi.php';
//$subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];

$domain = $_SERVER['HTTP_HOST'];
if ('apilotus.com' == $domain or 'www.apilotus.com' == $domain or 'primary.apilotus.com' == $domain) {
    header("Location: https://www.google.com");
    return;
}

$subdomain = 'primary';


$_SESSION['sub_domain'] = $subdomain;
if (isset($_SESSION['user_id']))
    $user = $_SESSION['user'];
else
    $user = '';
session_write_close();

$dbApi = DBApi::getInstance();
if(!$dbApi->checkClientIp())
{
	header("Location: ./blockip_alert.php");
	return;
}	

if ($user != '')
{
	header("Location: ./dashboard.php");
	return;
}

if (isset($_POST['signin']))
{
    $domainBlocked = $dbApi->checkSubDomainBlock($subdomain);
    if (!$domainBlocked)
    {
        $user = $dbApi->validateAccount($_POST['user_name'], $_POST['password']);
        if ($user != null && $user[2] == 1)
        {
            session_start();
            $_SESSION['user_id'] = $user[3];
            $_SESSION['user'] = $user[0];           // user display name
            $_SESSION['role'] = $user[1];
            $_SESSION['user_name'] = $user[4];      // user name
            $_SESSION['user_email'] = $user[5];      // user name
            $_SESSION['last_activity'] = time();
            $_SESSION['created'] = $_SESSION['last_activity'];
            setcookie(session_name(), $_COOKIE[session_name()], time() + 9660);
            session_write_close();

            // store login user info.
            $ipaddress = '';
            if (!empty($_SERVER['HTTP_CLIENT_IP']) && getenv('HTTP_CLIENT_IP')) {
                $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && getenv('HTTP_X_FORWARDED_FOR')) {
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($_SERVER['REMOTE_HOST']) && getenv('REMOTE_HOST')) {
                $ipaddress = $_SERVER['REMOTE_HOST'];
            } elseif (!empty($_SERVER['REMOTE_ADDR']) && getenv('REMOTE_ADDR')) {
                $ipaddress = $_SERVER['REMOTE_ADDR'];
            } else {
                $ipaddress = 'UNKNOWN';
            }
            $region = '';
            $country = '';
            if ($ipaddress != 'UNKNOWN') {
                $details = json_decode(file_get_contents("http://ipinfo.io/{$ipaddress}/json"));
                $region = $details->region;
                $country = $details->country;
            }
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $location = $region.' '.$country;

            $dbApi->insertLoginUserInfo($ipaddress, $location, $userAgent, $user[0], $subdomain);

            header("Location: ./dashboard.php");
        }
    }
}

?>


<!DOCTYPE html>
<html>
<head>
	<title>API Lotus</title>
	<meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Varela+Round" />
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../css/limelightcrm.css" rel="stylesheet" type="text/css" />
	<link rel="icon" href="../images/favicon.png" type="image/x-icon">
</head>

<body>
    <div class="crm_login_container">
		<div class="crm_login_logo">
            <img src="../images/logo_login.png" style="width: 200px; height: 158px">
        </div>
        <div class="crm_login_seperator"></div>
        <div style="height:5px;"></div>
		<form class="form-signin" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
			<h5 class="form-signin-heading">User Name</h5>
			<label for="username" class="sr-only">User Name</label>
			<input type="text" id="username" name="user_name" class="form-control" required="" autofocus="">
			<div style="height:15px;"></div>
            <h5 class="form-signin-heading">Password</h5>
			<label for="inputPassword" class="sr-only">Password</label>
			<input type="password" id="inputPassword" name="password" class="form-control" required="">			
			<div style="height:30px;"></div>
        	<button name="signin" class="btn btn-lg btn_signin btn-block" type="submit">Sign In</button>
		</form>
	</div>

    <script src="../js/jquery.min.js"></script>
    <script src="../bootstrap/js/bootstrap.min.js"></script>

</body>

</html>
