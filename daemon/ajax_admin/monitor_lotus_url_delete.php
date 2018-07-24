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

$urlID = $_GET['site_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$result = $dbApi->deleteMonitorSite($userID, $urlID);

if ($result)
    echo 'success';
else
    echo 'error';