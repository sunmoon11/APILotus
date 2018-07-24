<?php

require_once '../api/DBApi.php';
require_once '../api/LLCrmHook.php';


$userToken = $_GET['user_token'];
$crmID = $_GET['crm_id'];
$campaignID = $_GET['campaign_id'];
$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];
$cycle = $_GET['cycle'];
$delete = $_GET['delete'];


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}
$crmList = $dbApi->getActiveCrmById($crmID);

if ($crmList != null)
{
	$crmUrl = $crmList[0];
	$userName = $crmList[1];
	$password = $crmList[2];

	$llcrmHook = new LLCrmHook();
	if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
	{
		$response = $llcrmHook->getRetentionReportByCampaign($token, $fromDate, $toDate , $cycle, $campaignID, $userToken, $delete);		
		$result = $llcrmHook->parseRetentionByAffiliate($response);
		$llcrmHook->writeRetentionQuickByCampaign($crmID, $campaignID, $result, $cycle, $userToken, $delete);
		echo json_encode(array('success', $userToken, $campaignID, $result));
		return;
	}
}

echo json_encode(array('error', $userToken, $campaignID));

?>