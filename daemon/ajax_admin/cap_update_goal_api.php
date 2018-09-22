<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/21/2018
 * Time: 5:08 AM
 */

require_once '../api/DBApi.php';
require_once '../api/LLCrmHook.php';

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

    $llcrmHook = new LLCrmHook();
    if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
    {
        $today = gmdate('m/d/Y');
        $yesterday = gmdate('m/d/Y', strtotime('-1 day'));
        $weekday = gmdate('N');
        if (7 == $weekday) {
            $week_start = gmdate('m/d/Y', strtotime('-6 day'));
            $last_week_start = gmdate('m/d/Y', strtotime('-13 day'));
            $last_week_end = gmdate('m/d/Y', strtotime('-7 day'));
        }
        else {
            $minus_day = '-' . ($weekday - 1) . ' day';
            $week_start = gmdate('m/d/Y', strtotime($minus_day));
            $minus_day = '-' . ($weekday - 1 + 7) . ' day';
            $last_week_start = gmdate('m/d/Y', strtotime($minus_day));
            $minus_day = '-' . ($weekday) . ' day';
            $last_week_end = gmdate('m/d/Y', strtotime($minus_day));
        }

        $total_result = array();
        $total_result[] = $crmID;

        # Week To Date
        $response = $llcrmHook->getSalesReport($token, $week_start, $today, '', '', '', '');
        $result = $llcrmHook->parseSalesReport($response);
        $ret = array();
        foreach ($result as $item)
        {
            if ("Total" == $item[0])
                break;
            $sub_response = $llcrmHook->getSalesReport($token, $week_start, $today, '', "1", $item[0], "0");
            $sub_result = $llcrmHook->parseSalesReport($sub_response);
            $ret[] = array($item[0], array_slice($sub_result, 0, count($sub_result) - 1));
        }
        $db_result = $dbApi->getCapUpdateResult($crmID, $week_start, $today);
        if (false != $db_result && null != $db_result && str_replace("'", '"', $db_result[0]) == json_encode($ret)) {
            $total_result[] = array($week_start, $today, 'Week To Date', 'same result');
        }
        else {
            $dbApi->addCapUpdateResult($crmID, $week_start, $today, json_encode($ret));
            $total_result[] = array($week_start, $today, 'Week To Date', 'updated result');
        }

        # Today
        $response = $llcrmHook->getSalesReport($token, $today, $today, '', '', '', '');
        $result = $llcrmHook->parseSalesReport($response);
        $ret = array();
        foreach ($result as $item)
        {
            if ("Total" == $item[0])
                break;
            $sub_response = $llcrmHook->getSalesReport($token, $today, $today, '', "1", $item[0], "0");
            $sub_result = $llcrmHook->parseSalesReport($sub_response);
            $ret[] = array($item[0], array_slice($sub_result, 0, count($sub_result) - 1));
        }
        $db_result = $dbApi->getCapUpdateResult($crmID, $today, $today);
        if (false != $db_result && null != $db_result && str_replace("'", '"', $db_result[0]) == json_encode($ret)) {
            $total_result[] = array($today, $today, 'Today', 'same result');
        }
        else {
            $dbApi->addCapUpdateResult($crmID, $today, $today, json_encode($ret));
            $total_result[] = array($today, $today, 'Today', 'updated result');
        }

        # Yesterday
        $response = $llcrmHook->getSalesReport($token, $yesterday, $yesterday, '', '', '', '');
        $result = $llcrmHook->parseSalesReport($response);
        $ret = array();
        foreach ($result as $item)
        {
            if ("Total" == $item[0])
                break;
            $sub_response = $llcrmHook->getSalesReport($token, $yesterday, $yesterday, '', "1", $item[0], "0");
            $sub_result = $llcrmHook->parseSalesReport($sub_response);
            $ret[] = array($item[0], array_slice($sub_result, 0, count($sub_result) - 1));
        }
        $db_result = $dbApi->getCapUpdateResult($crmID, $yesterday, $yesterday);
        if (false != $db_result && null != $db_result && str_replace("'", '"', $db_result[0]) == json_encode($ret)) {
            $total_result[] = array($yesterday, $yesterday, 'Yesterday', 'same result');
        }
        else {
            $dbApi->addCapUpdateResult($crmID, $yesterday, $yesterday, json_encode($ret));
            $total_result[] = array($yesterday, $yesterday, 'Yesterday', 'updated result');
        }

        # Last Week
        $response = $llcrmHook->getSalesReport($token, $last_week_start, $last_week_end, '', '', '', '');
        $result = $llcrmHook->parseSalesReport($response);
        $ret = array();
        foreach ($result as $item)
        {
            if ("Total" == $item[0])
                break;
            $sub_response = $llcrmHook->getSalesReport($token, $last_week_start, $last_week_end, '', "1", $item[0], "0");
            $sub_result = $llcrmHook->parseSalesReport($sub_response);
            $ret[] = array($item[0], array_slice($sub_result, 0, count($sub_result) - 1));
        }
        $db_result = $dbApi->getCapUpdateResult($crmID, $last_week_start, $last_week_end);
        if (false != $db_result && null != $db_result && str_replace("'", '"', $db_result[0]) == json_encode($ret)) {
            $total_result[] = array($last_week_start, $last_week_end, 'Last Week', 'same result');
        }
        else {
            $dbApi->addCapUpdateResult($crmID, $last_week_start, $last_week_end, json_encode($ret));
            $total_result[] = array($last_week_start, $last_week_end, 'Last Week', 'updated result');
        }

        echo json_encode($total_result);
        return;
    }
}

echo json_encode(array('error', $crmID));
