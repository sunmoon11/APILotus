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
    $timeUtil = TimeUtils::getInstance();
    $fromDate = $timeUtil->getDateOfCurrentWeek()[0];
    $toDate = $timeUtil->getDateOfCurrentWeek()[1];
    $day = $timeUtil->getDayOfWeek();
    $hour = $timeUtil->getHour();

    $dbApi->setSubDomain($name);
    $crmList = $dbApi->getAllActiveCrm();
    if ($crmList == array())
        return;

    $arrayCrm = array();
    foreach ($crmList as $crm) {
        $crmID = $crm[0];
        $crmGoal = $crm[7];
        $crm_result = $dbApi->getCrmResult($crmID, $fromDate, $toDate);
        $arrayCrm[] = array($crmID, $crm[1], $crmGoal, $crm_result);
    }

    // variables for 100, 30 step1 alert
    $away100Sales = false;
    $over30Sales = false;
    $alertOfCapped = array();
    $dataOfCapped = array();
    $alertOfOverSales = array();
    $dataOfOverSales = array();
    $alertOfAwaySales = array();
    $dataOfAwaySales = array();

    foreach ($arrayCrm as $crm) {
        $crmID = $crm[0];
        $crmName = $crm[1];
        $goal = $crm[2];
        $salesData = $crm[3];

        if($salesData == array())
            continue;

        $salesData = $salesData[0];
        $salesStep1 = $salesData[3];
        $salesTablet = $salesData[5];
        $salesTabletS2 = $salesData['tablet_step2'];
        $salesStep1NNP = $salesData[7];
        $salesStep2NNP = $salesData[8];

        if(($salesStep1NNP) == 0)
            $takeRate = 0;
        else
            $takeRate = ($salesTabletS2 + $salesStep2NNP) / ($salesTablet + $salesStep1NNP) * 100;

        if(($salesStep2NNP + $salesTablet) == 0)
            $tabletTakeRate = 0;
        else
            $tabletTakeRate = ($salesTablet / ($salesTablet + $salesStep2NNP)) * 100;

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
            if($salesStep1 == ($goal))
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
                    $alertOfCapped['fromDate'] = $from;
                    $alertOfCapped['toDate'] = $to;
                    $dataOfCapped[] = array($crmName, $salesStep1, $goal, $type, 1, $crmID);
                }
            }
            $dbApi->updateAlertStatus($crmID, $type, $salesStep1, $goal, $status, $from, $to, $timestamp);

            $setting = $dbApi->getAlertTypeByType(7);
            $days = $setting[5];
            if ("" == $days)
                $days = "Sun,Mon,Tue,Wed,Thu,Fri,Sat";
            $hours = $setting[6];
            $days = explode(',', $days);
            $hours = explode(',', $hours);
            if (in_array($day, $days) and in_array($hour, $hours)) {
                // 100, 50, 10 Step1 Sales Away From Cap
                $types = [15, 14, 7];
                foreach ($types as $type) {
                    $status = 0;
                    $Step1100Triggered = false;
                    $setting = $dbApi->getAlertTypeByType($type);
                    $level = explode(' ', $setting[2])[0];

                    if($salesStep1 >= ($goal - $level) && ($goal > $salesStep1) && $away100Sales == false)
                    {
                        $status = 1;
                        $away100Sales = true;
                        $alertStatus = $dbApi->getAlertReportByType($crmID, date('Y-m-d'), $type);
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
                            $alertOfAwaySales['fromDate'] = $from;
                            $alertOfAwaySales['toDate'] = $to;
                            $dataOfAwaySales[] = array($crmName, $salesStep1, $level, $type, 1, $crmID);
                        }
                    }
                    $dbApi->updateAlertStatus($crmID, $type, $salesStep1, $level, $status, $from, $to, $timestamp);
                }
            }

            $setting = $dbApi->getAlertTypeByType(8);
            $days = $setting[5];
            if ("" == $days)
                $days = "Sun,Mon,Tue,Wed,Thu,Fri,Sat";
            $hours = $setting[6];
            $days = explode(',', $days);
            $hours = explode(',', $hours);
            if (in_array($day, $days) and in_array($hour, $hours)) {
                // 10, 25, 50, 75, 100, 125, 150, 200, 250 Step1 Sales Over Cap
                $types = [23, 22, 21, 20, 19, 18, 17, 16, 8];
                foreach ($types as $type) {
                    $status = 0;
                    $Step130Triggered = false;
                    $setting = $dbApi->getAlertTypeByType($type);
                    $level = explode(' ', $setting[2])[0];

                    if($salesStep1 >= ($goal + $level) && ($goal < $salesStep1) && $over30Sales == false)
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
                            $alertOfOverSales['fromDate'] = $from;
                            $alertOfOverSales['toDate'] = $to;
                            $dataOfOverSales[] = array($crmName, $salesStep1, $level, $type, 1, $crmID);
                        }
                    }
                    $dbApi->updateAlertStatus($crmID, $type, $salesStep1, $level, $status, $from, $to, $timestamp);
                }
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
                $dbApi->updateAlertStatus($crmID, $type, $takeRate, $setting[4], $status, $from, $to, $timestamp);
            }

            $type = 10;
            if($setting[2] == $type)
            {
                // Tablet Take Rate Alert
                $status = $tabletTakeRate <= $setting[4] ? 1 : 0;
                $dbApi->updateAlertStatus($crmID, $type, $tabletTakeRate, $setting[4], $status, $from, $to, $timestamp);
            }
        }
    }

    // send alert
    if($dataOfAwaySales != array() || $dataOfOverSales != array() || $dataOfCapped != array())
    {
        $alertMgr = AlertManager::getInstance();
        if($dataOfCapped != array())
        {
            $alertOfCapped['status'] = $dataOfCapped;
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

            $alertMgr->checkStep1SalesGoalOverWithData($alertOfCapped, $sms, $email, $telegramBot, $name);
        }
        if($dataOfOverSales != array())
        {
            $alertOfOverSales['status'] = $dataOfOverSales;
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
            $alertMgr->check30Step1SalesAwayWithData($alertOfOverSales, $sms, $email, $telegramBot, $name);
        }
        if($dataOfAwaySales != array())
        {
            $alertOfAwaySales['status'] = $dataOfAwaySales;
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
            $alertMgr->check100Step1SalesAwayWithData($alertOfAwaySales, $sms, $email, $telegramBot, $name);
        }
    }
    else
    {
        $timestamp = date('Y-m-d H:i:s');
        echo 'alert_prospect no alert - '.$timestamp."\r\n";
    }
}
