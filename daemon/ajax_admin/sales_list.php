<?php

require_once '../api/DBApi.php';
require_once '../api/LLCrmHook.php';
require_once '../api/LLCrmApi.php';


$crmID = $_GET['crm_id'];
$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];
$campaignID = $_GET['campaign_id'];
$aff = $_GET['aff'];
$f = $_GET['f'];
$sf = $_GET['sf'];

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
    $apiName = $crmList[3];
    $apiPassword = $crmList[4];

    $llcrmHook = new LLCrmHook();
    if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
    {
        $response = $llcrmHook->getSalesReport($token, $fromDate, $toDate, $campaignID, $aff, $f, $sf);
        $result = $llcrmHook->parseSalesReport($response);

        $campaign = false;
        foreach ($result as $item)
        {
            if ($item[8] == 'Sub-Affiliate')
            {
                $campaign = false;
                break;
            }
            if ($item[8] == 'Affiliate')
            {
                $campaign = true;
                break;
            }
        }

        // get campaign name
        if ($campaign)
        {
            $llcrmApi = LLCrmApi::getInstanceWithCredentials($crmUrl.'/admin/', $apiName, $apiPassword);
            $data = $llcrmApi->getAllCampaign1();
            $ret = array();
            if ($data != null)
            {
                foreach ($result as $item)
                {
                    $campaignIDs = $data['ids'];
                    $campaignNames = $data['names'];

                    $index = array_search($item[0], $campaignIDs);
                    $campaignName = $campaignNames[$index];
                    $item[9] = $campaignName;
                    $ret[] = $item;
                }
            }
            echo json_encode(array('success', $crmID, $ret));
            return;
        }

        echo json_encode(array('success', $crmID, $result));
        return;
    }
}

echo json_encode(array('error', $crmID));
