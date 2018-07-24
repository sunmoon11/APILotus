<?php

require_once '../api/DBApi.php';

$type = $_GET['type'];		// alert_type
$days = $_GET['days'];		// comma separated string for DAY
$hours = $_GET['hours'];	// comma separated string for HOUR
$sms = $_GET['sms'];
$email = $_GET['email'];
$bot = $_GET['bot'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->changeAlertSchedule($type, $days, $hours, $sms, $email, $bot);
if ($ret)
	echo 'success';
else
	echo 'error';

?>