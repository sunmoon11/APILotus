<?php

require_once '../api/DBApi.php';
require_once '../api/LLCrmHook.php';


$userToken = $_GET['user_token'];
$crmID = $_GET['crm_id'];
$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];
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
		$response = $llcrmHook->getAffiliateReport($token, $fromDate, $toDate, $userToken, $delete, $crmID);

		echo json_encode(array('success', $crmID, $response));
		return;
	}
}

echo json_encode(array('error', $crmID));

?>