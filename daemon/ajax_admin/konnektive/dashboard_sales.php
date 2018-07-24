<?php

require_once '../../api/KKCrmHook.php';
require_once '../../api/DBApi.php';


$crmID = $_GET['crm_id'];
$crmGoal = $_GET['crm_goal'];
$fromDate = $_GET['from_date'].' 12:00 AM';
$toDate = $_GET['to_date'].' 11:59 PM';

$dbApi = DBApi::getInstance();

if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$crmList = $dbApi->getKKActiveCrmById($crmID);

if ($crmList != null)
{
	$crmUrl = $crmList[2];
	$userName = $crmList[3];
	$password = $crmList[4];

	$kkcrmHook = new KKCrmHook();
	if (($token = $kkcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
	{
		$response = $kkcrmHook->getCrmSalesBreakDown($token, $fromDate, $toDate, $crmID);

		if ($response != 'error')
			echo json_encode(array('success', $crmID, $crmGoal, $response));
		else
			echo json_encode(array('error', $crmID));

		return;
	}
}

echo json_encode(array('error', $crmID));

?>