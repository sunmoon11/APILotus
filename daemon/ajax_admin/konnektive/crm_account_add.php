<?php


require_once '../../api/DBApi.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo 'no_cookie';
    return;
}
$user_id = $_SESSION['user_id'];
session_write_close();

$crmName = $_GET['crm_name'];
$crmUrl = $_GET['crm_url'];
$crmUsername = $_GET['crm_username'];
$crmPassword = $_GET['crm_password'];
$apiUsername = $_GET['api_username'];
$apiPassword = $_GET['api_password'];
$salesGoal = $_GET['sales_goal'];
$paused = $_GET['crm_paused'];

// add insert table here
$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->addKKCrmAccount($crmName, $crmUrl, $crmUsername, $crmPassword, $apiUsername, $apiPassword, $salesGoal, $paused, $user_id);
if ($ret)
	echo 'success';
else
	echo 'error';

?>