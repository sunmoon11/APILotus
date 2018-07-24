<?php

require_once '../api/DBApi.php';


$crmID = $_GET['crm_id'];
$crmName = $_GET['crm_name'];
$crmUrl = $_GET['crm_url'];
$crmUsername = $_GET['crm_username'];
//$crmPassword = $_GET['crm_password'];
$apiUsername = $_GET['api_username'];
//$apiPassword = $_GET['api_password'];
$salesGoal = $_GET['sales_goal'];
$paused = $_GET['crm_paused'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->updateCrm($crmID, $crmName, $crmUrl, $crmUsername, $apiUsername, $salesGoal, $paused);
if ($ret)
	echo 'success';
else
	echo 'error';

?>