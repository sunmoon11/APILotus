<?php

require_once '../api/LLCrmHook.php';
require_once '../api/DBApi.php';


$crmID = $_GET['crm_id'];
$crmGoal = $_GET['crm_goal'];
$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];

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

		$response = $llcrmHook->getCrmSalesBreakDown($token, $fromDate, $toDate, $crmID);

		if ($response != 'error')
			echo json_encode(array('success', $crmID, $crmGoal, $response));
		else
			echo json_encode(array('error', $crmID));

		return;
	}
}

echo json_encode(array('error', $crmID));

?>