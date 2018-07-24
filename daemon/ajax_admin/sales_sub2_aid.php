<?php

require_once '../api/DBApi.php';
require_once '../api/LLCrmHook.php';


$userToken = $_GET['user_token'];
$crmID = $_GET['crm_id'];
$campaignID = $_GET['campaign_id'];
$subAffiliateID = $_GET['sub_affiliate_id'];
$depth = $_GET['depth'];
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
        $response = $llcrmHook->getSalesByProspectReportBySubAffiliate($token, $fromDate, $toDate, $campaignID, $depth, $subAffiliateID);
        $result = $llcrmHook->parseSalesByProspectReport($response, 'sub_affiliate');
        //$llcrmHook->writeQuickRetentionByCrm($userToken, $crmID, $result, $delete, $cycle);
        echo json_encode(array('success', $userToken, $crmID, $result));
        return;
    }
}

echo json_encode(array('error', $userToken, $crmID));
