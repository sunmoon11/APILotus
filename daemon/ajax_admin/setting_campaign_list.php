<?php

require_once '../api/LLCrmApi.php';
require_once '../api/DBApi.php';


$crmID = $_GET['crm_id'];					// crm id
$campaignIDs = $_GET['campaign_ids'];		// array of campaign id or id list
$pageNumber = $_GET['page_number'];			// current page number
$items4Page = $_GET['items_page'];			// item count per page

$campaignIDs = preg_replace('/\s+/', '', $campaignIDs);
$arrayCampaignID = array();
if ($campaignIDs != '')
	$arrayCampaignID = explode(',', $campaignIDs);

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$crmList = $dbApi->getActiveCrmById($crmID);
if ($crmList != null)
{
	$apiUrl = $crmList[0].'/admin/';
	$apiUserName = $crmList[3];
	$apiPassword = $crmList[4];

	$crmApi = LLCrmApi::getInstanceWithCredentials($apiUrl, $apiUserName, $apiPassword);	
	$ret = $crmApi->getCampaigns($crmID, $arrayCampaignID, $pageNumber, $items4Page);
	
	echo json_encode($ret);
	return;
}

echo 'error';

?>