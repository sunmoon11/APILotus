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

$siteID = $_GET['site_id'];
$siteName = $_GET['site_name'];
$siteUrl = $_GET['site_url'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$result = $dbApi->updateMonitorSite($userID, $siteName, $siteUrl, $siteID);
if($result)
    echo 'success';
else
    echo 'error';

