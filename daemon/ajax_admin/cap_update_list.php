<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/19/2018
 * Time: 3:57 PM
 */

require_once '../api/DBApi.php';
require_once '../api/LLCrmHook.php';


$crm_list = $_GET['crm_list'];
$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$cap_results = array();

foreach ($crm_list as $crmList) {
    $crmID = $crmList[0];
    $crmUrl = $crmList[2];
    $userName = $crmList[3];
    $password = $crmList[4];

    $campaign_result = array();
    $llcrmHook = new LLCrmHook();
    if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null) {
        $response = $llcrmHook->getRetentionReport($token, $fromDate, $toDate, 1);
        $result = $llcrmHook->parseRetentionByCampaign($response);

        $trials = $dbApi->getTrialCampaignById($crmID);
        $trials = explode(',', $trials);

        foreach ($result['report'] as $r) {
            if (in_array((string)$r[0], $trials)) {
                $aid_response = $llcrmHook->getRetentionReportByCampaign($token, $fromDate, $toDate, 1, $r[0]);
                $aid_result = $llcrmHook->parseRetentionByAffiliate($aid_response);
                $campaign_result[] = array($r, $aid_result['report']);
            }
        }
    }
    $cap_results[] = array($crmID, $campaign_result);
    break;
}

echo json_encode($cap_results);

?>