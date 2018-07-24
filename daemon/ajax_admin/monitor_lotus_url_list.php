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
$pageIndex = $_GET['page_number'];
$items4Page = $_GET['items_page'];
$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$sites = $dbApi->getMonitorSiteListPaging($userID, $pageIndex, $items4Page);

if ($sites != array())
    echo json_encode($sites);
else
    echo 'error';