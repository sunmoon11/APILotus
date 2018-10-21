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
    $crmList = $dbApi->getAllActiveCrm();
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

            $salesData = $alertDataApi->getPrespectAlertDataByCrm($crmID, $crmUrl, $userName, $password, $fromDate, $toDate);
            if($salesData == array())
                continue;
            // Step1
            $salesStep1 = $salesData[0];
            // Step1 NNPsales
            $salesStep1NNP = $salesData[4];
            // tablet sales
            $salesTablet = $salesData[2];
            // Step2 NNP
            $salesStep2NNP = $salesData[3];

            if(($salesStep1NNP) == 0)
                $takeRate = 0;
            else
            {
                $takeRate = (($salesTablet + $salesStep2NNP) / $salesStep1NNP) * 100;
                // $takeRate = number_format($takeRate, 2);
            }
            if(($salesStep2NNP + $salesTablet) == 0)
                $tabletTakeRate = 0;
            else
            {
                $tabletTakeRate = ($salesTablet / ($salesTablet + $salesStep2NNP)) * 100;
                // $takeRate = number_format($takeRate, 2);
            }

            // store data to DB
            $dbApi->storeDashboardData($crmID, $crmName, $salesStep1, $salesData[1], $takeRate, $salesTablet, $tabletTakeRate, $goal);

            $from = date('Y-m-d', strtotime($fromDate));
            $toDate = $timeUtil->getDateOfCurrentSunday();
            $to = date('Y-m-d', strtotime($toDate));
            $timestamp = date('Y-m-d H:i:s');
            $minSales = 100;


            if($salesStep1 >= $minSales)
            {
                $type = 11; // Step 1 CRM Capped
                $status = 0;
                $Step1GoalTriggered = false;
                if($salesStep1 >= ($goal))
                {
                    $status = 1;
                    $alertStatus = $dbApi->getLatestAlertReportByType($crmID, $type);
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
                $ret = $dbApi->updateAlertStatus($crmID, $type, $salesStep1, $goal, $status, $from, $to, $timestamp);

                // 100, 50, 10 Step1 Sales Away From Cap
                $types = [7, 14, 15];
                foreach ($types as $type) {
                    $status = 0;
                    $Step1100Triggered = false;
                    $setting = $dbApi->getAlertTypeByType($type);
                    $level = explode(' ', $setting[2])[0];

                    if($salesStep1 >= ($goal - $level) && ($goal > $salesStep1))
                    {
                        $status = 1;
                        $alertStatus = $dbApi->getAlertReportByType($crmID, date('Y-m-d'), $type);
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
                                $dataOf100AwaySales[] = array($crmName, $salesStep1, $level, $type, 1, $crmID);
                            }
                        }
                    }
                    $ret = $dbApi->updateAlertStatus($crmID, $type, $salesStep1, $level, $status, $from, $to, $timestamp);
                }

                // 10, 25, 50, 75, 100, 125, 150, 200, 250 Step1 Sales Over Cap
                $types = [8, 16, 17, 18, 19, 20, 21, 22, 23];
                foreach ($types as $type) {
                    $status = 0;
                    $over30Sales = false;
                    $Step130Triggered = false;
                    $setting = $dbApi->getAlertTypeByType($type);
                    $level = explode(' ', $setting[2])[0];

                    if($salesStep1 >= ($goal + $level) && ($goal < $salesStep1))
                    {
                        $status = 1;
                        $over30Sales = true;
                        $alertStatus = $dbApi->getAlertReportByType($crmID, date('Y-m-d'), $type);
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
                            $dataOf30AwaySales[] = array($crmName, $salesStep1, $level, $type, 1, $crmID);
                        }
                    }
                    $ret = $dbApi->updateAlertStatus($crmID, $type, $salesStep1, $level, $status, $from, $to, $timestamp);
                }
            }

            $settings = $dbApi->getAlertLevelListByCrm($crmID);
            foreach ($settings as $setting)
            {
                $type = 9;
                if($setting[2] == $type)
                {
                    // Take Rate Alert
                    $status = $takeRate < $setting[4] ? 1 : 0;
                    $ret = $dbApi->updateAlertStatus($crmID, $type, $takeRate, $setting[4], $status, $from, $to, $timestamp);
                }

                $type = 10;
                if($setting[2] == $type)
                {
                    // Tablet Take Rate Alert
                    $status = $tabletTakeRate <= $setting[4] ? 1 : 0;
                    $ret = $dbApi->updateAlertStatus($crmID, $type, $tabletTakeRate, $setting[4], $status, $from, $to, $timestamp);
                }
            }

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

            $alertMgr->checkStep1SalesGoalOverWithData($alertOfOverGoal, $sms, $email, $telegramBot, $name);
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
            $alertMgr->check30Step1SalesAwayWithData($alertOf30AwaySales, $sms, $email, $telegramBot, $name);
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
            $alertMgr->check100Step1SalesAwayWithData($alertOf100AwaySales, $sms, $email, $telegramBot, $name);
        }
    }
    else
    {
        echo 'alert_prospect no alert';
    }
}



?>