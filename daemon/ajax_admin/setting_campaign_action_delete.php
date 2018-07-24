<?php

require_once '../api/DBApi.php';


$crmID = $_GET['crm_id'];
$campaignIDs = $_GET['campaign_ids'];

// add update table here
$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->deleteCampaignsInLabeling($crmID, $campaignIDs);
if ($ret)
	echo 'success';
else 
	echo 'error';

?>