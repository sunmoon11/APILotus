<?php
require_once '../api/DBApi.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo 'no_cookie';
    return;
}
$userID = $_SESSION['user_id'];
session_write_close();

$interval = $_GET['interval'];
$sms = $_GET['enable_sms'];
$email = $_GET['enable_email'];
$tbot = $_GET['enable_bot'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$result = $dbApi->updateMonitorSchedule($interval, $sms, $email, $tbot, $userID);

if($result)
    echo 'success';
else
    echo 'error';

