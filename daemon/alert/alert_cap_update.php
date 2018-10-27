<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 10/25/2018
 * Time: 5:52 AM
 */

require_once '../api/AlertDataApi.php';
require_once '../api/AlertMethodApi.php';
require_once '../api/AlertManager.php';
require_once '../../lib/utils/TimeUtils.php';
require_once '../telegram/TelegramBot.php';

$dbApi = DBApi::getInstance();
$subDomains = $dbApi->getAllSubDomain();
foreach ($subDomains as $item) {
    $name = $item[1];
    getCapUpdateReportAndSendAlerts($name);
}
return;

function getCapUpdateReportAndSendAlerts($name){
    global $dbApi;
    $timeUtil = TimeUtils::getInstance();
    $fromDate = $timeUtil->getDateOfCurrentWeek()[0];
    $toDate = $timeUtil->getDateOfCurrentWeek()[1];

    $dbApi->setSubDomain($name);
    $crmList = $dbApi->getAllActiveCrm();
    if ($crmList == array())
        return;

    $alertOf100AwaySales = array();
    $dataOf100AwaySales = array();

    $cap_updates = $dbApi->getCapUpdate();
    foreach ($crmList as $crm) {
        $result_by_crm = $dbApi->getCapUpdateResult($crm[0], $fromDate, $toDate);
        if (false == $result_by_crm || null == $result_by_crm)  continue;
        $result = json_decode(str_replace("'", '"', $result_by_crm[0]));
        $updated_time = $result_by_crm[1];
        foreach ($cap_updates as $affiliate_goal) {
            if ($crm[0] == $affiliate_goal[7]) {
                $count = 0;
                $afids = explode(',', $affiliate_goal[5]);
                $campaign_ids = explode(',', $affiliate_goal[10]);
                foreach ($result as $campaign_prospects) {
                    foreach ($campaign_ids as $campaign_id) {
                        if ("step1" === explode('_', $campaign_id)[0]) {
                            if (explode('_', $campaign_id)[1] == $campaign_prospects[0]) {
                                foreach ($campaign_prospects[1] as $campaign_prospect) {
                                    foreach ($afids as $afid) {
                                        if ($campaign_prospect[0] == $afid) {
                                            $count += $campaign_prospect[2];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $goal = $affiliate_goal[3];
                $affiliate_name = $affiliate_goal[4];
                $offer_name = $affiliate_goal[6];

                // 100, 50, 10 Step1 Sales Away From Cap
                $types = [15, 14, 7];
                $away100Sales = false;
                foreach ($types as $type) {
                    $status = 0;
                    $Step1100Triggered = false;
                    $setting = $dbApi->getAlertTypeByType($type);
                    $level = explode(' ', $setting[2])[0];

                    if($count >= ($goal - $level) && ($goal > $count) && $away100Sales == false)
//                    if($count >= $level && $away100Sales == false)
                    {
                        $status = 1;
                        $away100Sales = true;
//                        $alertStatus = $dbApi->getAlertReportByType($crmID, date('Y-m-d'), $type);
//                        if($alertStatus != array())
//                        {
//                            $data = $alertStatus[0];
//                            if($data[5] == 0)
//                            {
//                                $Step1100Triggered = true;
//                            }
//                        }
//                        else
//                        {
//                            $Step1100Triggered = true;
//                        }
//
//                        if($Step1100Triggered)
//                        {
//                            $alertOf100AwaySales['fromDate'] = $from;
//                            $alertOf100AwaySales['toDate'] = $to;
                            $dataOf100AwaySales[] = array($affiliate_name, $offer_name, $count, $goal, $level);
//                        }
                    }
//                    $ret = $dbApi->updateAlertStatus($crmID, $type, $salesStep1, $level, $status, $from, $to, $timestamp);
                }
            }
        }
    }

    // send alert
    if ($dataOf100AwaySales != array())
    {
        $alertMgr = AlertManager::getInstance();

        $from = date('Y-m-d', strtotime($fromDate));
        $toDate = $timeUtil->getDateOfCurrentSunday();
        $to = date('Y-m-d', strtotime($toDate));

        $alertOf100AwaySales['fromDate'] = $from;
        $alertOf100AwaySales['toDate'] = $to;
        $alertOf100AwaySales['status'] = $dataOf100AwaySales;

        $alertMgr->sendCapAlerts($alertOf100AwaySales, $name);
    }
    else
    {
        $timestamp = date('Y-m-d H:i:s');
        echo 'alert_cap_update no alert - '.$timestamp."\r\n";
    }
}
