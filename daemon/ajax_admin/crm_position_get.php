<?php

require_once '../api/DBApi.php';


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo json_encode(array('no_cookie'));
    return;
}
$userID = $_SESSION['user_id'];
session_write_close();


$crmPositions = $dbApi->getCrmPositions($userID);
if ($crmPositions)
	echo json_encode(array('success', $crmPositions));
else
	echo json_encode(array('error'));

?>