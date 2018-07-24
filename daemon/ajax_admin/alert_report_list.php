<?php

require_once '../api/LLCrmApi.php';
require_once '../api/DBApi.php';


$alertType = $_GET['alert_type'];		// alert type
$date = $_GET['date'];					// report date

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo json_encode(array('no_cookie'));
    return;
}
$userId = $_SESSION['user_id'];

session_write_close();

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}
$response = $dbApi->getAlertReport($alertType, $date, $userId);

echo json_encode(array('success', $alertType, $response));
return;
?>