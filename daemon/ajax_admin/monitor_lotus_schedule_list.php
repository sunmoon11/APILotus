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

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$schedule = $dbApi->getMonitorSchedule($userID);
if ($schedule == null)
    echo 'error';
else
    echo json_encode($schedule);