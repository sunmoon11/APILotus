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

    $llcrmHook = new LLCrmHook();
    if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
    {
        $crmApi = LLCrmApi::getInstanceWithCredentials($apiUrl, $apiUserName, $apiPassword);
        $campaigns = $crmApi->getSelectedCampaigns($crmID);

        $today = date('m/d/Y');
        $yesterday = date('m/d/Y', strtotime('-1 day'));
        $weekday = date('N');
        if (7 == $weekday) {
            $week_start = date('m/d/Y', strtotime('-6 day'));
            $last_week_start = date('m/d/Y', strtotime('-13 day'));
            $last_week_end = date('m/d/Y', strtotime('-7 day'));
        }
        else {
            $minus_day = '-' . ($weekday - 1) . ' day';
            $week_start = date('m/d/Y', strtotime($minus_day));
            $minus_day = '-' . ($weekday - 1 + 7) . ' day';
            $last_week_start = date('m/d/Y', strtotime($minus_day));
            $minus_day = '-' . ($weekday) . ' day';
            $last_week_end = date('m/d/Y', strtotime($minus_day));
        }
        $month_start = date('m/01/Y');
//        $year_start = date('01/01/Y');

        $wtd_result = getInitialReport($dbApi, $llcrmHook, $campaigns, $token, $crmID, $week_start, $today);
        $today_result = getInitialReport($dbApi, $llcrmHook, $campaigns, $token, $crmID, $today, $today);
        $mtd_result = getInitialReport($dbApi, $llcrmHook, $campaigns, $token, $crmID, $month_start, $today);
//        $ytd_result = getInitialReport($dbApi, $llcrmHook, $campaigns, $token, $crmID, $year_start, $today);

        if (!$dbApi->checkInitialReport($crmID, $yesterday, $yesterday))
            $yesterday_result = getInitialReport($dbApi, $llcrmHook, $campaigns, $token, $crmID, $yesterday, $yesterday);

        if (!$dbApi->checkInitialReport($crmID, $last_week_start, $last_week_end))
            $last_week_result = getInitialReport($dbApi, $llcrmHook, $campaigns, $token, $crmID, $last_week_start, $last_week_end);

        echo json_encode(array('success', $crmID));
        return;
    }
}

echo json_encode(array('error', $crmID));

function getInitialReport($dbApi, $llcrmHook, $campaigns, $token, $crmID, $fromDate, $toDate)
{
    $initial_results = array();

    $response = $llcrmHook->getRetentionReport($token, $fromDate, $toDate, 1);
    $result = $llcrmHook->parseRetentionByCampaign($response);

    foreach ($result['report'] as $r) {
        if (in_array((string)$r[0], $campaigns)) {
            $aids = array();
            if ('yes' == $r[8]) {
                $aid_response = $llcrmHook->getRetentionReportByCampaign($token, $fromDate, $toDate , 1, $r[0]);
                $aid_result = $llcrmHook->parseRetentionByAffiliate($aid_response);

                foreach ($aid_result['report'] as $aid_r) {
                    $sub_response = $llcrmHook->getRetentionReportByAffiliate($token, $fromDate, $toDate , 1, $r[0], $aid_r[0]);
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
            return json_encode(array('success', $crmID, 'same result'));
        }
    }
    $dbApi->addInitialReport($crmID, $fromDate, $toDate, json_encode($initial_results));
    return json_encode(array('success', $crmID, $initial_results));
}