<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/10/2018
 * Time: 5:00 AM
 */

require_once '../api/DBApi.php';
require_once '../api/LLCrmHook.php';


$crmID = $_GET['crm_id'];
$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];
$cycle = 2;


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

    $trial_results = array();
    $llcrmHook = new LLCrmHook();
    if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
    {
        $response = $llcrmHook->getRetentionReport($token, $fromDate, $toDate, $cycle);
        $result = $llcrmHook->parseRetentionByCampaign($response);
        // $llcrmHook->writeQuickRetentionByCrm($userToken, $crmID, $result, $delete, $cycle);

        $trials = $dbApi->getTrialCampaignById($crmID);
        $trials = explode(',', $trials);

        foreach ($result['report'] as $r) {
            if (in_array((string)$r[0], $trials)) {
                $aid_response = $llcrmHook->getRetentionReportByCampaign($token, $fromDate, $toDate , $cycle, $r[0]);
                $aid_result = $llcrmHook->parseRetentionByAffiliate($aid_response);
                // $llcrmHook->writeRetentionQuickByCampaign($crmID, $campaignID, $result, $cycle, $userToken, $delete);

                $sub_aids = array();
                foreach ($aid_result['report'] as $aid_r) {
                    $sub_response = $llcrmHook->getRetentionReportByAffiliate($token, $fromDate, $toDate , $cycle, $r[0], $aid_r[0]);
                    $sub_result = $llcrmHook->parseRetentionBySubAffiliate($sub_response);
                    // $llcrmHook->writeRetentionQuickByAffiliate($crmID, $campaignID, $affiliateID, $result, $cycle, $userToken, $delete);
                    $sub_aids[] = array($aid_r, $sub_result['report']);
                }
                $trial_results[] = array($r, $sub_aids);
            }
        }
        $result = json_encode(array('success', $crmID, $trial_results));
        $dbApi->addTrialCampaign($crmID, $fromDate, $toDate, $result);
        echo $result;
        return;
    }
}

echo json_encode(array('error', $crmID));

?>