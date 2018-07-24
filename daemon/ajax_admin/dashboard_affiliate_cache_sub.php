<?php

require_once '../api/DBApi.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo json_encode(array('no_cookie'));
    return;
}
$userID = $_SESSION['user_id'];
session_write_close();

$date_type = $_GET['date_type'];
$affiliateID = $_GET['affiliate_id'];

$dbApi = DBApi::getInstance();

if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$ret = $dbApi->getAffiliateCacheSumPerCRM($userID, $date_type, $affiliateID);

if ($ret)
{
    echo json_encode(array('success', $affiliateID, $ret));
    return;
}

echo json_encode(array('error', $affiliateID));
