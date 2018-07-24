<?php

require_once '../api/DBApi.php';


$userID = $_GET['user_id'];
$firstName = $_GET['first_name'];
$lastName = $_GET['last_name'];
$displayName = $_GET['display_name'];
$subDomain = $_GET['sub_domain'];
$smsNumber = $_GET['sms_number'];
$botID = $_GET['bot_id'];

$dbApi = DBApi::getInstance();
$ret = $dbApi->checkIfUserIdRegistered($userID);
if($ret)
    $ret = $dbApi->updateUserInfo($userID, $firstName, $lastName, $displayName, $subDomain, $smsNumber, $botID);
else
    $ret = false;

if ($ret)
	echo 'success';
else
	echo 'error';
?>