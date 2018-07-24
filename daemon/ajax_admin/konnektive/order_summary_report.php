<?php

require_once '../../api/DBApi.php';
require_once '../../api/KKCrmHook.php';


$crmID = $_POST['crm_id'];
$fromDate = $_POST['from_date'].' 12:00 AM';
$toDate = $_POST['to_date'].' 11:59 PM';
$campaignIDs = $_POST['campaign_ids'];
$campaignNames = $_POST['campaign_names'];
$productID = $_POST['product_id'];
$affiliateID = $_POST['affiliate_id'];


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
		$response = $kkcrmHook->getOrderSummaryReport($token, $fromDate, $toDate, $productID, $affiliateID);
		$response = $kkcrmHook->parseOrderSummaryReport($response, $campaignIDs, $campaignNames);
	    echo json_encode(array('success', $crmID, $response));
	    
		return;
	}
}

echo json_encode(array('error', $crmID));

?>