<?php

require_once '../api/DBApi.php';


$accountID = $_GET['account_id'];
$userName = $_GET['user_name'];
//$Password = $_GET['password'];
$displayName = $_GET['display_name'];
$role = $_GET['role'];						// 9 : 'admin user', 1: 'super user', 0: 'regular user'
$sms = $_GET['sms'];
$email = $_GET['email'];
$bot = $_GET['bot'];
$state = $_GET['state'];					// Enable Account : 1, Disable : 0
$enableSMS = $_GET['enable_sms'];			// Enable SMS Alert Receiver : 1, Disable : 0
$enableEmail = $_GET['enable_email'];		// Enable Email Alert Receiver : 1, Disable : 0
$enableBot = $_GET['enable_bot'];			// Enable Telegram Bot Alert Receiver : 1, Disable : 0

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->updateAccount($accountID, $userName, $displayName, $state, $role, $sms, $email, $bot, $enableSMS, $enableEmail, $enableBot);
if ($ret)
	echo 'success';
else
	echo 'error';

?>