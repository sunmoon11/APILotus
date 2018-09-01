<?php

require_once '../api/LLCrmHook.php';
require_once '../api/DBApi.php';


$crmID = $_GET['crm_id'];
$crmGoal = $_GET['crm_goal'];

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
        $year_start = date('01/01/Y');

        if (!$dbApi->checkCrmResult($crmID, $week_start, $today))
            $dbApi->deleteCrmResult($crmID, $week_start, $yesterday);
//        if (!$dbApi->checkCrmResult($crmID, $month_start, $today))
//            $dbApi->deleteCrmResult($crmID, $month_start, $yesterday);
//        if (!$dbApi->checkCrmResult($crmID, $year_start, $today))
//            $dbApi->deleteCrmResult($crmID, $year_start, $yesterday);

        $date_today = $llcrmHook->getCrmSalesBreakDown($token, $today, $today, $crmID);
        $dbApi->addCrmResults($crmID, $crmGoal, $date_today, $today, $today);

        $date_thisweek = $llcrmHook->getCrmSalesBreakDown($token, $week_start, $today, $crmID);
        $dbApi->addCrmResults($crmID, $crmGoal, $date_thisweek, $week_start, $today);

//        $date_thismonth = $llcrmHook->getCrmSalesBreakDown($token, $month_start, $today, $crmID);
//        $dbApi->addCrmResults($crmID, $crmGoal, $date_thismonth, $month_start, $today);

//        $date_thisyear = $llcrmHook->getCrmSalesBreakDown($token, $year_start, $today, $crmID);
//        $dbApi->addCrmResults($crmID, $crmGoal, $date_thisyear, $year_start, $today);

        if (!$dbApi->checkCrmResult($crmID, $yesterday, $yesterday)) {
            $date_yesterday = $llcrmHook->getCrmSalesBreakDown($token, $yesterday, $yesterday, $crmID);
            $dbApi->addCrmResults($crmID, $crmGoal, $date_yesterday, $yesterday, $yesterday);
        }

        if (!$dbApi->checkCrmResult($crmID, $last_week_start, $last_week_end)) {
            $date_lastweek = $llcrmHook->getCrmSalesBreakDown($token, $last_week_start, $last_week_end, $crmID);
            $dbApi->addCrmResults($crmID, $crmGoal, $date_lastweek, $last_week_start, $last_week_end);
        }
        return true;
    }
}

?>