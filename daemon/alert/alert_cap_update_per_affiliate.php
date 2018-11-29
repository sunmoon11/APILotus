<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 2018-11-29
 * Time: 1:04 PM
 */

require_once '../api/AlertDataApi.php';
require_once '../api/AlertMethodApi.php';
require_once '../api/AlertManager.php';
require_once '../../lib/utils/TimeUtils.php';
require_once '../telegram/TelegramBot.php';

$dbApi = DBApi::getInstance();

$timeUtil = TimeUtils::getInstance();
$day = $timeUtil->getDayOfWeek();
$hour = $timeUtil->getHour();

$setting = $dbApi->getAlertTypeByType(24);
$days = $setting[5];
if ("" == $days)
    $days = "Sun,Mon,Tue,Wed,Thu,Fri,Sat";
$hours = $setting[6];

$days = explode(',', $days);
$hours = explode(',', $hours);

if(in_array($day, $days)) {
    if (in_array($hour, $hours)) {
        $subDomains = $dbApi->getAllSubDomain();
        foreach ($subDomains as $item) {
            $name = $item[1];
            getCapUpdateReportAndSendAlerts($name);
        }
    }
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

    $from = date('Y-m-d', strtotime($fromDate));
    $to = date('Y-m-d', strtotime($timeUtil->getDateOfCurrentSunday()));
    $timestamp = date('Y-m-d H:i:s');

    $available_affiliates = $dbApi->getAvailableAffiliateIDs();
    foreach ($available_affiliates as $affiliate) {
        $alertOfCapped = array();
        $dataOfCapped = array();
        $alertOfAway = array();
        $dataOfAway = array();
        $alertOfOver = array();
        $dataOfOver = array();

        $offers = $dbApi->getCapUpdateByAffiliateID($affiliate['id']);
        foreach ($offers as $idx=>$offer) {
            $result_by_crm = $dbApi->getCapUpdateResult($offer['crm_id'], $fromDate, $toDate);
            if (false == $result_by_crm || null == $result_by_crm)  continue;
            $result = json_decode(str_replace("'", '"', $result_by_crm[0]));
            $updated_time = $result_by_crm[1];

            $count = 0;
            $afids = explode(',', $offer['afid']);
            $campaign_ids = explode(',', $offer['campaign_ids']);
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

            $goal = $offer['goal'];
            $affiliate_name = $offer['affiliate_name'];
            $offer_name = $offer['offer_name'];

            $type = 11; // Step 1 CRM Capped
            $status = 0;
            $CappedTriggered = false;
            if ($count == $goal) {
                $status = 1;
                $alertStatus = $dbApi->getLatestAlertReportByType($offer['id'], $type + 200);
                if ($alertStatus != array()) {
                    $data = $alertStatus[0];
                    $date = $data[6];
                    $date = date('Y-m-d', strtotime($date));
                    if ($timeUtil->checkInCurrentWeek($date)) {
                        if ($data[5] == 0) {
                            $CappedTriggered = true;
                        }
                    }
                    else {
                        $CappedTriggered = true;
                    }
                }
                else {
                    $CappedTriggered = true;
                }
                if ($CappedTriggered) {
                    $dataOfCapped[] = array($affiliate_name, $offer_name, $count, $goal, $updated_time);
                }
            }
            $ret = $dbApi->updateAlertStatus($offer['id'], $type + 200, $count, $goal, $status, $from, $to, $timestamp);

            // 100, 50, 10 Step1 Sales Away From Cap
            $types = [15, 14, 7];
            $awayAlreadyTriggered = false;
            foreach ($types as $type) {
                $status = 0;
                $AwayTriggered = false;
                $setting = $dbApi->getAlertTypeByType($type);
                $level = explode(' ', $setting[2])[0];

                if ($count >= ($goal - $level) && ($goal > $count) && $awayAlreadyTriggered == false) {
                    $status = 1;
                    $awayAlreadyTriggered = true;
                    $alertStatus = $dbApi->getAlertReportByType($offer['id'], date('Y-m-d'), $type + 200);
                    if ($alertStatus != array()) {
                        $data = $alertStatus[0];
                        if($data[5] == 0) {
                            $AwayTriggered = true;
                        }
                    }
                    else {
                        $AwayTriggered = true;
                    }

                    if ($AwayTriggered) {
                        $dataOfAway[] = array($affiliate_name, $offer_name, $count, $goal, $updated_time);
                    }
                }
                $ret = $dbApi->updateAlertStatus($offer['id'], $type + 200, $count, $level, $status, $from, $to, $timestamp);
            }

            // 10, 25, 50, 75, 100, 125, 150, 200, 250 Step1 Sales Over Cap
            $types = [23, 22, 21, 20, 19, 18, 17, 16, 8];
            $over30Sales = false;
            foreach ($types as $type) {
                $status = 0;
                $Step130Triggered = false;
                $setting = $dbApi->getAlertTypeByType($type);
                $level = explode(' ', $setting[2])[0];

                if ($count >= ($goal + $level) && ($goal < $count) && $over30Sales == false) {
                    $status = 1;
                    $over30Sales = true;
                    $alertStatus = $dbApi->getAlertReportByType($offer['id'], date('Y-m-d'), $type + 200);
                    if ($alertStatus != array()) {
                        $data = $alertStatus[0];
                        if ($data[5] == 0) {
                            $Step130Triggered = true;
                        }
                    }
                    else {
                        $Step130Triggered = true;
                    }

                    if ($Step130Triggered) {
                        $dataOfOver[] = array($affiliate_name, $offer_name, $count, $goal, $updated_time);
                    }
                }
                $ret = $dbApi->updateAlertStatus($offer['id'], $type + 200, $count, $level, $status, $from, $to, $timestamp);
            }
        }

        // send alert
        if ($dataOfAway != array() || $dataOfOver != array() || $dataOfCapped != array()) {
            $alertMgr = AlertManager::getInstance();
            if ($dataOfCapped != array()) {
                $alertOfCapped['fromDate'] = $from;
                $alertOfCapped['toDate'] = $to;
                $alertOfCapped['status'] = $dataOfCapped;
                $alertMgr->sendCapAlertsToAffiliates($alertOfCapped, 211, $affiliate['bot']);
            }
            if ($dataOfAway != array()) {
                $alertOfAway['fromDate'] = $from;
                $alertOfAway['toDate'] = $to;
                $alertOfAway['status'] = $dataOfAway;
                $alertMgr->sendCapAlertsToAffiliates($alertOfAway, 207, $affiliate['bot']);
            }
            if ($dataOfOver != array()) {
                $alertOfOver['fromDate'] = $from;
                $alertOfOver['toDate'] = $to;
                $alertOfOver['status'] = $dataOfOver;
                $alertMgr->sendCapAlertsToAffiliates($alertOfOver, 208, $affiliate['bot']);
            }
        }
    }

    $timestamp = date('Y-m-d H:i:s');
    echo 'alert_cap_update_per_affiliate no alert - '.$timestamp."\r\n";
}
