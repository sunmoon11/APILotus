<?php
require_once '../api/AlertDataApi.php';
require_once '../api/AlertMethodApi.php';
require_once '../api/AlertManager.php';
require_once '../../lib/utils/TimeUtils.php';
require_once '../telegram/TelegramBot.php';

$dbApi = DBApi::getInstance();
$subDomains = $dbApi->getAllSubDomain();
foreach ($subDomains as $item) {
    $name = $item[1];
    $features = $dbApi->getFeatureEnableList($name);
    $features = explode(',', $features);
    if (!in_array(1, $features)) // if kkcrm disabled in admin panel
        continue;
    getProspectReportAndSendAlerts($name);
}
return;
/*
 *@description
 * Get the data for Prospect report and then send the alerts of some alerts by condition.
 * -    100 Step1 sales away from Cap
 * -    30  Step1 sales over Cap
 * -    Step1 CRM Capped
 */
function getProspectReportAndSendAlerts($name){
    global $dbApi;
    $alertDataApi = AlertDataApi::getInstance();
    $timeUtil = TimeUtils::getInstance();
    $fromDate = $timeUtil->getDateOfCurrentWeek()[0];
    $toDate = $timeUtil->getDateOfCurrentWeek()[1];

    $dbApi->setSubDomain($name);
    $crmList = $dbApi->getKKCrmActiveList(null);
    if ($crmList == array())
        return;
    // variables for 100, 30 step1 alert
    $over30Sales = false;
    $alertOfOverGoal = array();
    $dataOfOverGoal = array();
    $alertOf30AwaySales = array();
    $dataOf30AwaySales = array();
    $alertOf100AwaySales = array();
    $dataOf100AwaySales = array();

    foreach ($crmList as $crm)
    {
        if ($crm != null)
        {
            $crmID = $crm[0];
            $crmName = $crm[1];
            $crmUrl = $crm[2];
            $userName = $crm[3];
            $password = $crm[4];
            $goal = $crm[7];
            $startTime = $fromDate.' 12:00 AM';
            $endTime = $toDate.' 11:59 PM';
            $salesData = $alertDataApi->getKKCrmSalesProgress($crmID, $crmUrl, $userName, $password, $startTime, $endTime);

            if($salesData == array())
                continue;
            // Step1
            $salesStep1 = $salesData[0];
            // Step1 NNPsales
//            $salesStep1NNP = $salesData[4];
//            // tablet sales
//            $salesTablet = $salesData[2];
//            // Step2 NNP
//            $salesStep2NNP = $salesData[3];
//
//            if(($salesStep1NNP) == 0)
//                $takeRate = 0;
//            else
//            {
//                $takeRate = (($salesTablet + $salesStep2NNP) / $salesStep1NNP) * 100;
//                // $takeRate = number_format($takeRate, 2);
//            }
//            if(($salesStep2NNP + $salesTablet) == 0)
//                $tabletTakeRate = 0;
//            else
//            {
//                $tabletTakeRate = ($salesTablet / ($salesTablet + $salesStep2NNP)) * 100;
//                // $takeRate = number_format($takeRate, 2);
//            }
            // store data to DB
            $dbApi->storeKKCrmDashboardData($crmID, $crmName, $salesStep1, $salesData[1], $salesData[2], $salesData[3], $salesData[4], $goal);

            $from = date('Y-m-d', strtotime($fromDate));
            $toDate = $timeUtil->getDateOfCurrentSunday();
            $to = date('Y-m-d', strtotime($toDate));
            $timestamp = date('Y-m-d H:i:s');
            $minSales = 100;
            $type = 11; // over crm Goal
            $status = 0;
            $Step1GoalTriggered = false;
            if($salesStep1 >= $minSales)
            {
                if($salesStep1 >= ($goal))
                {
                    $status = 1;
                    $alertStatus = $dbApi->getLatestKKCrmAlertReportByType($crmID, $type);
                    if($alertStatus != array())
                    {
                        $data = $alertStatus[0];
                        $date = $data[6];
                        $date = date('Y-m-d', strtotime($date));
                        if($timeUtil->checkInCurrentWeek($date))
                        {
                            if($data[5] == 0 || ($salesStep1 - $data[3]) >= 15)
                            {
                                $Step1GoalTriggered = true;
                            }
                        }
                        else
                        {
                            $Step1GoalTriggered = true;
                        }
                    }
                    else
                    {
                        $Step1GoalTriggered = true;
                    }
                    if($Step1GoalTriggered)
                    {
                        $alertOfOverGoal['fromDate'] = $from;
                        $alertOfOverGoal['toDate'] = $to;
                        $dataOfOverGoal[] = array($crmName, $salesStep1, $goal, $type, 1, $crmID);
                    }
                }
                $ret = $dbApi->updateKKCrmAlertStatus($crmID, $type, $salesStep1, $goal, $status, $from, $to, $timestamp);
            }

//            $settings = $dbApi->getAlertLevelListByCrm($crmID);
//            foreach ($settings as $setting)
//            {
                if($salesStep1 >= $minSales)
                {
                    $type = 8;
                    if(true)//$setting[2] == $type)
                    {
                        // 30 Step1 Sales Away From Cap Alert
                        $status = 0;
                        $over30Sales = false;
                        $Step130Triggered = false;
                        if($salesStep1 >= ($goal - 30) && ($goal > $salesStep1))//$salesStep1 >= ($goal - $setting[4]) && ($goal > $salesStep1))
                        {
                            $status = 1;
                            $over30Sales = true;
                            $alertStatus = $dbApi->getKKCrmAlertReportByType($crmID, date('Y-m-d'), $type);
                            if($alertStatus != array())
                            {
                                $data = $alertStatus[0];
                                if($data[5] == 0)
                                {
                                    $Step130Triggered = true;
                                }
                            }
                            else
                            {
                                $Step130Triggered = true;
                            }

                            if($Step130Triggered)
                            {
                                $alertOf30AwaySales['fromDate'] = $from;
                                $alertOf30AwaySales['toDate'] = $to;
//                                $dataOf30AwaySales[] = array($crmName, $salesStep1, $setting[4], $type, 1, $crmID);
                                $dataOf30AwaySales[] = array($crmName, $salesStep1, 30, $type, 1, $crmID);
                            }
                        }
//                        $ret = $dbApi->updateKKCrmAlertStatus($crmID, $type, $salesStep1, $setting[4], $status, $from, $to, $timestamp);
                        $ret = $dbApi->updateKKCrmAlertStatus($crmID, $type, $salesStep1, 30, $status, $from, $to, $timestamp);
                    }
                    $type = 7;
                    if(true)//$setting[2] == $type)
                    {
                        // 100 Step1 Sales Away From Cap Alert
                        $status = 0;
                        $Step1100Triggered = false;
                        if($salesStep1 >= ($goal - 100) && ($goal > $salesStep1))//$salesStep1 >= ($goal - $setting[4]) && ($goal > $salesStep1))
                        {
                            $status = 1;
                            $alertStatus = $dbApi->getKKCrmAlertReportByType($crmID, date('Y-m-d'), $type);
                            if($over30Sales == false)
                            {
                                if($alertStatus != array())
                                {
                                    $data = $alertStatus[0];
                                    if($data[5] == 0)
                                    {
                                        $Step1100Triggered = true;
                                    }
                                }
                                else
                                {
                                    $Step1100Triggered = true;
                                }

                                if($Step1100Triggered)
                                {
                                    $alertOf100AwaySales['fromDate'] = $from;
                                    $alertOf100AwaySales['toDate'] = $to;
//                                    $dataOf100AwaySales[] = array($crmName, $salesStep1, $setting[4], $type, 1, $crmID);
                                    $dataOf100AwaySales[] = array($crmName, $salesStep1, 100, $type, 1, $crmID);
                                }
                            }
                        }
                        //$ret = $dbApi->updateKKCrmAlertStatus($crmID, $type, $salesStep1, $setting[4], $status, $from, $to, $timestamp);
                        $ret = $dbApi->updateKKCrmAlertStatus($crmID, $type, $salesStep1, 100, $status, $from, $to, $timestamp);
                    }
                }

//                $type = 9;
//                if($setting[2] == $type)
//                {
//                    // Take Rate Alert
//                    $status = 0;
//                    if($takeRate < $setting[4])
//                    {
//                        $status = 1;
//                    }
//                    $ret = $dbApi->updateAlertStatus($crmID, $type, $takeRate, $setting[4], $status, $from, $to, $timestamp);
//                }
//
//                $type = 10;
//                if($setting[2] == $type)
//                {
//                    // Tablet Take Rate Alert
//                    $status = 0;
//                    if($tabletTakeRate <= $setting[4])
//                    {
//                        $status = 1;
//                    }
//                    $ret = $dbApi->updateAlertStatus($crmID, $type, $tabletTakeRate, $setting[4], $status, $from, $to, $timestamp);
//                }
//            }

        }
    }
//    print_r(array($dataOf30AwaySales, $name));
//    print_r(array($dataOf100AwaySales, $name));
//    print_r(array($dataOfOverGoal, $name));
//    return;

    // send alert
    if($dataOf100AwaySales != array() || $dataOf30AwaySales != array() || $dataOfOverGoal != array())
    {
        $alertMgr = AlertManager::getInstance();
        if($dataOfOverGoal != array())
        {
            $alertOfOverGoal['status'] = $dataOfOverGoal;
            $setting = $dbApi->getAlertTypeByType(11);
            $telegramBot = false;
            $email = false;
            $sms = false;
            if($setting[9] == 1)
                $telegramBot = true;
            if($setting[8] == 1)
                $email = true;
            if($setting[7] == 1)
                $sms = true;

            $alertMgr->checkStep1SalesGoalOverWithData($alertOfOverGoal, $sms, $email, $telegramBot, $name, 'konnektive');
        }
        if($dataOf30AwaySales != array())
        {
            $alertOf30AwaySales['status'] = $dataOf30AwaySales;
            $setting = $dbApi->getAlertTypeByType(8);
            $telegramBot = false;
            $email = false;
            $sms = false;
            if($setting[9] == 1)
                $telegramBot = true;
            if($setting[8] == 1)
                $email = true;
            if($setting[7] == 1)
                $sms = true;
            $alertMgr->check30Step1SalesAwayWithData($alertOf30AwaySales, $sms, $email, $telegramBot, $name, 'konnektive');
        }
        if($dataOf100AwaySales != array())
        {
            $alertOf100AwaySales['status'] = $dataOf100AwaySales;
            $setting = $dbApi->getAlertTypeByType(7);
            $telegramBot = false;
            $email = false;
            $sms = false;
            if($setting[9] == 1)
                $telegramBot = true;
            if($setting[8] == 1)
                $email = true;
            if($setting[7] == 1)
                $sms = true;
            $alertMgr->check100Step1SalesAwayWithData($alertOf100AwaySales, $sms, $email, $telegramBot, $name, 'konnektive');
        }
    }
    else
    {
        echo 'alert_prospect no alert';
    }
}



?>