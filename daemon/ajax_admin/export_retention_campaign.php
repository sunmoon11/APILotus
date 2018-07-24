<?php

require_once '../api/DBApi.php';
require_once '../api/LLCrmHook.php';


$userToken = $_GET['user_token'];
$crmID = $_GET['crm_id'];
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
		// return value : array of campaign id, is affiliate id
		$response = $llcrmHook->exportRetentionCampaign($token, $crmID, $fromDate, $toDate, $cycle, $userToken, $delete);

		echo json_encode(array('success', $userToken, $crmID, $response));
		return;
	}
}

echo json_encode(array('error', $userToken, $crmID));

?>