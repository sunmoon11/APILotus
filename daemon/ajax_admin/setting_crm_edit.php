<?php

require_once '../api/DBApi.php';


$crmID = $_GET['crm_id'];
$crmName = $_GET['crm_name'];
$crmUrl = $_GET['crm_url'];
$crmUsername = $_GET['crm_username'];
$crmPassword = $_GET['crm_password'];
$apiUsername = $_GET['api_username'];
$apiPassword = $_GET['api_password'];
$salesGoal = $_GET['sales_goal'];
$rebill_length = $_GET['rebill_length'];
$test_cc = $_GET['test_cc'];
$paused = $_GET['crm_paused'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->updateCrm($crmID, $crmName, $crmUrl, $crmUsername, $crmPassword, $apiUsername, $apiPassword, $salesGoal, $paused, $rebill_length, $test_cc);
if ($ret)
	echo 'success';
else
	echo 'error';

?>