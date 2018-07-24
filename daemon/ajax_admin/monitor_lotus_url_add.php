<?php

require_once '../api/DBApi.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo 'no_cookie';
    return;
}

$user_id = $_SESSION['user_id'];
session_write_close();

$siteName = $_GET['site_name'];
$siteUrl = $_GET['site_url'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$ret = $dbApi->checkUrl($user_id, $siteName, $siteUrl);
if ($ret === false)
{
    $ret = $dbApi->addMonitorSite($user_id, $siteName, $siteUrl);
    if ($ret)
        echo 'success';
    else
        echo 'error';
    return;
} else if ($ret === true){
    echo 'exist';
    return;
}

echo 'error';