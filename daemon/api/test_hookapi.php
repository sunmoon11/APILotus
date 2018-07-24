<?php
require_once 'DBApi.php';
require_once 'LLCrmHook.php';
require_once 'LLCrmApi.php';

$dbApi = DBApi::getInstance();
$dbApi->setSubDomain("primary");

$crmID = 75;
//$campaignID = "129";
$affiliateID = 10;
$subAffiliateID = '003nitri-RO-ad';
$subAffiliateID1 = 'sxaln5ac3ed5b81d7c638942650';
$fromDate = '06/04/2018';
$toDate = '06/09/2018';
$userToken = '1';

$delete = 1;

$crmList = $dbApi->getActiveCrmById($crmID);

if ($crmList != null)
{
    $crmUrl = $crmList[0];
    $userName = $crmList[1];
    $password = $crmList[2];
    $apiName = $crmList[3];
    $apiPassword = $crmList[4];

    $llcrmHook = new LLCrmHook();
    if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
    {
        echo $response = $llcrmHook->getProspectReport($token, $fromDate, $toDate);
        return;
        $response = $llcrmHook->getCrmSalesBreakDown($token, $fromDate, $toDate, $crmID);
        print_r($response);
        return;
        //$response = $llcrmHook->getSalesByProspectReport($token, $fromDate, $toDate, $campaignID);
        $report = $llcrmHook->parseSalesByProspectReport($response, 'campaign');
        print_r($report);
        return;
        $llcrmApi = LLCrmApi::getInstanceWithCredentials($crmUrl.'/admin/', $apiName, $apiPassword);
        $data = $llcrmApi->getAllCampaign1();
        $result = array();
        if ($data != null)
        {
            $campaignIDs = $data['ids'];
            $campaignNames = $data['names'];
            foreach ($report as $item)
            {
                if ($item['campaign_id'] != 'Total')
                {
                    $index = array_search($item['campaign_id'], $campaignIDs);
                    $campaignName = $campaignNames[$index];
                    $item += ['campaign_name' => $campaignName];
                    $result[] = $item;
                }
            }
        }


        //$response = $llcrmHook->getSalesByProspectReportByCampaign($token, $fromDate, $toDate, $campaignID);
        //$response = $llcrmHook->getSalesByProspectReportByCampaign($token, $fromDate, $toDate, 'Total');
        //$result = $llcrmHook->parseSalesByProspectReport($response, 'affiliate');
        //$response = $llcrmHook->getSalesByProspectReportByAffiliate($token, $fromDate, $toDate, $campaignID, $affiliateID);
        //$response = $llcrmHook->getSalesByProspectReportByAffiliate($token, $fromDate, $toDate, $campaignID, 'Total');
        //$result = $llcrmHook->parseSalesByProspectReport($response, 'sub_affiliate');
        //$response = $llcrmHook->getSalesByProspectReportBySubAffiliate($token, $fromDate, $toDate, $campaignID, 1, $subAffiliateID);
        //$response = $llcrmHook->getSalesByProspectReportBySubAffiliate($token, $fromDate, $toDate, $campaignID, 1,'Total');
        //$response = $llcrmHook->getSalesByProspectReportBySubAffiliate($token, $fromDate, $toDate, $campaignID, 2, $subAffiliateID1);
        //$response = $llcrmHook->getSalesByProspectReportBySubAffiliate($token, $fromDate, $toDate, $campaignID, 2,'Total');
        //$result = $llcrmHook->parseSalesByProspectReport($response, 'sub_affiliate');
        //$llcrmHook->writeQuickRetentionByCrm($userToken, $crmID, $result, $delete, $cycle);


        echo json_encode(array('success', $userToken, $crmID, $result));
        return;
    }
}

echo json_encode(array('error', $userToken, $crmID));