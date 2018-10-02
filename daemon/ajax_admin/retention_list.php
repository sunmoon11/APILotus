<?php

require_once '../api/DBApi.php';
require_once '../api/LLCrmHook.php';
require_once '../api/simple_html_dom.php';


$userToken = $_GET['user_token'];
$crmID = $_GET['crm_id'];
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
		$response = $llcrmHook->getRetentionReport($token, $fromDate, $toDate, 1);
        $html = str_get_html($response);
        $result = $llcrmHook->parseInitialReport($response, $html);
//		$llcrmHook->writeQuickRetentionByCrm($userToken, $crmID, $result, 1, 1);
		echo json_encode(array('success', $userToken, $crmID, $result));
		return;
	}
}

echo json_encode(array('error', $userToken, $crmID));
