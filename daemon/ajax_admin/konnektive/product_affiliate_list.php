<?php

require_once '../../api/DBApi.php';
require_once '../../api/KKCrmHook.php';


$crmID = $_GET['crm_id'];


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$crmInfo = $dbApi->getActiveKKCrmById($crmID);

if ($crmInfo != null)
{
	$crmUrl = $crmInfo[0];
	$userName = $crmInfo[1];
	$password = $crmInfo[2];

	$kkcrmHook = new KKCrmHook();
	if (($token = $kkcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
	{
		$response = $kkcrmHook->getProductAffiliateList($token);
	    echo json_encode(array('success', $crmID, $response[0], $response[1]));
	    
		return;
	}
}

echo json_encode(array('error', $crmID));

?>