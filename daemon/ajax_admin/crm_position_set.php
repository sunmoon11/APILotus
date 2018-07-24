<?php

require_once '../api/DBApi.php';


$crmPositions = $_GET['crm_positions'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo 'no_cookie';
    return;
}
$userID = $_SESSION['user_id'];
session_write_close();

$ret = $dbApi->setCrmPositions($userID, $crmPositions);
if ($ret)
{
	echo 'success';
	return;
}

echo 'error';

?>