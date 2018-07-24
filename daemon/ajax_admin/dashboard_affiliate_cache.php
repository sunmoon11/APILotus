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

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}
$ret = $dbApi->getAffiliateCacheSum($userID, $date_type);

if ($ret != array())
{
    echo json_encode($ret);
    return;
}

echo json_encode(array('error'));