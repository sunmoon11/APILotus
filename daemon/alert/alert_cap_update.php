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

    $alertOfCapped = array();
    $dataOfCapped = array();
    $alertOfAway = array();
    $dataOfAway = array();
    $alertOfOver = array();
    $dataOfOver = array();

    $from = date('Y-m-d', strtotime($fromDate));
    $to = date('Y-m-d', strtotime($timeUtil->getDateOfCurrentSunday()));
    $timestamp = date('Y-m-d H:i:s');

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


                $type = 11; // Step 1 CRM Capped
                $status = 0;
                $CappedTriggered = false;
                if ($count == $goal) {
                    $status = 1;
                    $alertStatus = $dbApi->getLatestAlertReportByType($affiliate_goal[0], $type + 100);
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
                        $dataOfCapped[] = array($affiliate_name, $offer_name, $count, $goal);
                    }
                }
                $ret = $dbApi->updateAlertStatus($affiliate_goal[0], $type + 100, $count, $goal, $status, $from, $to, $timestamp);

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
                        $alertStatus = $dbApi->getAlertReportByType($affiliate_goal[0], date('Y-m-d'), $type + 100);
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
                            $dataOfAway[] = array($affiliate_name, $offer_name, $count, $goal, $level);
                        }
                    }
                    $ret = $dbApi->updateAlertStatus($affiliate_goal[0], $type + 100, $count, $level, $status, $from, $to, $timestamp);
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
                        $alertStatus = $dbApi->getAlertReportByType($affiliate_goal[0], date('Y-m-d'), $type + 100);
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
                            $dataOfOver[] = array($affiliate_name, $offer_name, $count, $goal, $level);
                        }
                    }
                    $ret = $dbApi->updateAlertStatus($affiliate_goal[0], $type + 100, $count, $level, $status, $from, $to, $timestamp);
                }
            }
        }
    }

    // send alert
    if ($dataOfAway != array() || $dataOfOver != array() || $dataOfCapped != array()) {
        $alertMgr = AlertManager::getInstance();
        if ($dataOfCapped != array()) {
            $alertOfCapped['fromDate'] = $from;
            $alertOfCapped['toDate'] = $to;
            $alertOfCapped['status'] = $dataOfCapped;
            $alertMgr->sendCapAlerts($alertOfCapped, $name, 111);
        }
        if ($dataOfAway != array()) {
            $alertOfAway['fromDate'] = $from;
            $alertOfAway['toDate'] = $to;
            $alertOfAway['status'] = $dataOfAway;
            $alertMgr->sendCapAlerts($alertOfAway, $name, 107);
        }
        if ($dataOfOver != array()) {
            $alertOfOver['fromDate'] = $from;
            $alertOfOver['toDate'] = $to;
            $alertOfOver['status'] = $dataOfOver;
            $alertMgr->sendCapAlerts($alertOfOver, $name, 108);
        }
    }
    else {
        $timestamp = date('Y-m-d H:i:s');
        echo 'alert_cap_update no alert - '.$timestamp."\r\n";
    }
}
