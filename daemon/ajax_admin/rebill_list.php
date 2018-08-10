<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/7/2018
 * Time: 3:31 AM
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

$result = $dbApi->getTrialCampaignResultById($crmID, $fromDate, $toDate);
if (false != $result && null != $result) {
    echo json_encode(array('success', $crmID, json_decode(str_replace("'", '"', $result))));
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

        $trials = $dbApi->getTrialCampaignById($crmID);
        $trials = explode(',', $trials);

        foreach ($result['report'] as $r) {
            if (in_array((string)$r[0], $trials)) {
                if ((int)$r[2] <= 10)
                    continue;
                $aid_response = $llcrmHook->getRetentionReportByCampaign($token, $fromDate, $toDate , $cycle, $r[0]);
                $aid_result = $llcrmHook->parseRetentionByAffiliate($aid_response);

                $aids = array();
                foreach ($aid_result['report'] as $aid_r) {
                    if ((int)$aid_r[2] <= 10)
                        continue;
                    $sub_response = $llcrmHook->getRetentionReportByAffiliate($token, $fromDate, $toDate , $cycle, $r[0], $aid_r[0]);
                    $sub_result = $llcrmHook->parseRetentionBySubAffiliate($sub_response);
                    $sub_aids = array();
                    foreach ($sub_result['report'] as $sub_r) {
                        if ((int)$sub_r[2] <= 10)
                            continue;
                        $sub_aids[] = array($sub_r[0], $sub_r[1], $sub_r[3], $sub_r[7], $sub_r[13]);
                    }
                    $aids[] = array(array($aid_r[0], $aid_r[1], $aid_r[3], $aid_r[7], $aid_r[13]), $sub_aids);
                }
                $trial_results[] = array(array($r[0], $r[1], $r[3], $r[7], $r[13]), $aids);
            }
        }

        $dbApi->addTrialCampaign($crmID, $fromDate, $toDate, json_encode($trial_results));
        echo json_encode(array('success', $crmID, $trial_results));
        return;
    }
}

echo json_encode(array('error', $crmID));

?>