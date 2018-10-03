<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 10/2/2018
 * Time: 1:44 PM
 */

require_once '../api/DBApi.php';
require_once '../api/LLCrmHook.php';
require_once '../api/LLCrmApi.php';


$crmID = $_GET['crm_id'];
$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];
$cycle = 1;


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

    $apiUrl = $crmList[0].'/admin/';
    $apiUserName = $crmList[3];
    $apiPassword = $crmList[4];

    $initial_results = array();
    $llcrmHook = new LLCrmHook();
    if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
    {
        $response = $llcrmHook->getRetentionReport($token, $fromDate, $toDate, $cycle);
        $result = $llcrmHook->parseRetentionByCampaign($response);

        $crmApi = LLCrmApi::getInstanceWithCredentials($apiUrl, $apiUserName, $apiPassword);
        $campaigns = $crmApi->getSelectedCampaigns($crmID);

        foreach ($result['report'] as $r) {
            if (in_array((string)$r[0], $campaigns)) {
                $aids = array();
                if ('yes' == $r[8]) {
                    $aid_response = $llcrmHook->getRetentionReportByCampaign($token, $fromDate, $toDate , $cycle, $r[0]);
                    $aid_result = $llcrmHook->parseRetentionByAffiliate($aid_response);

                    foreach ($aid_result['report'] as $aid_r) {
                        $sub_response = $llcrmHook->getRetentionReportByAffiliate($token, $fromDate, $toDate , $cycle, $r[0], $aid_r[0]);
                        $sub_result = $llcrmHook->parseRetentionBySubAffiliate($sub_response);
                        $sub_aids = array();
                        foreach ($sub_result['report'] as $sub_r) {
                            $sub_aids[] = array($sub_r[0], $sub_r[1], $sub_r[3], $sub_r[9], number_format($sub_r[3] * 100 / $sub_r[2], 2));
                        }
                        $aids[] = array(array($aid_r[0], $aid_r[1], $aid_r[3], $aid_r[9], number_format($aid_r[3] * 100 / $aid_r[2], 2)), $sub_aids);
                    }
                }
                $initial_results[] = array(array($r[0], $r[1], $r[3], $r[9], number_format($r[3] * 100 / $r[2], 2)), $aids);
            }
        }

        $db_result = $dbApi->getInitialReportById($crmID, $fromDate, $toDate);
        if (false != $db_result && null != $db_result) {
            if (str_replace("'", '"', $db_result) == json_encode($initial_results)) {
                echo json_encode(array('success', $crmID, 'same result'));
                return;
            }
        }
        $dbApi->addInitialReport($crmID, $fromDate, $toDate, json_encode($initial_results));
        echo json_encode(array('success', $crmID, $initial_results));
        return;
    }
}

echo json_encode(array('error', $crmID));
