<?php
require_once '../api/AlertDataApi.php';
require_once '../../lib/utils/TimeUtils.php';
require_once '../api/AlertManager.php';

$dbApi = DBApi::getInstance();
$subDomains = $dbApi->getAllSubDomain();
foreach ($subDomains as $item) {
    $name = $item[1];
    getRetentionReportForInitialApprovalWeekAlertByCrm($name);
}
return;

function getRetentionReportForInitialApprovalWeekAlertByCrm($subDomain){
    global $dbApi;
    $type = 4;
    $day = 0;

    $alertDataApi = AlertDataApi::getInstance();
    $alertDataApi->setSubDomain($subDomain);
    $timeUtil = TimeUtils::getInstance();
    $fromDate = $timeUtil->getDateOfCurrentWeek()[0];
    $toDate = $timeUtil->getDateOfCurrentWeek()[1];
    $dbApi->setSubDomain($subDomain);
    $allCrmList = $dbApi->getAllActiveCrm();
    if ($allCrmList == array())
        return;
    $alertSettings = $dbApi->getAlertLevelList($type);
    $crmList = array();
    foreach ($alertSettings as $alertInfo){
        foreach ($allCrmList as $crm){
            $crmId = $crm[0];
            if ($alertInfo[1] == $crmId){
                $crmList[] = $crm;
            }
        }
    }

    // fetch retention page by crm
    $time = date('Y-m-d H:i:s');
    echo "Start time: ".$time."\n";

    $alertDataApi->getInitialAlertDataByCrm($fromDate, $toDate, $crmList, $day);
    // get alerts info by crm level
    $alertOverStatusData = array();
    $alertBelowCrmIdList = array();

    foreach ($crmList as $crmInfo) {
        $crmId = $crmInfo[0];
        $crmName = $crmInfo[1];

        $data = $dbApi->getSTEP1ApprovalRateForInitialAlertByCrm($crmId, $day);
        // print_r($data);
        $approvalRateOfSTEP1 = $data[0];
        $approvalRateOfSTEP1 = round($approvalRateOfSTEP1, 2);
        foreach ($alertSettings as $alertInfo) {
            if($alertInfo[1] == $crmId) { // if type == 4 (initia alert for week)
                $level = $alertInfo[4];
                if($approvalRateOfSTEP1 <= $level && $approvalRateOfSTEP1 > 0) {
                    $alertBelowCrmIdList[] = array('crm_info' => $crmInfo, 'value' => $approvalRateOfSTEP1,'alert_level' =>$level);
                }elseif ($approvalRateOfSTEP1 > $level) {
                    $alertOverStatusData[] = array('crm_name' =>$crmName, 'value' =>$approvalRateOfSTEP1, 'alert_level'=>$level);
                }
                // update alert status
                $status = ($approvalRateOfSTEP1 > $level) ? 1: 0;
                $from = date('Y-m-d', strtotime($fromDate));
                $toDate1 = $timeUtil->getDateOfCurrentSunday();
                $to = date('Y-m-d', strtotime($toDate1));
                $timestamp = date('Y-m-d H:i:s');
                $dbApi->updateAlertStatus($crmId, $type, $approvalRateOfSTEP1, $level, $status, $from, $to, $timestamp);
            }
        }
    }
    if($alertBelowCrmIdList != array()) {
        // fetch retention page by STEP1 campaigns for detailed

        // fetch retention page by STEP1 campaigns
        $alertDataApi->getInitialAlertDataBySTEP1Campaign($fromDate, $toDate, $alertBelowCrmIdList, $day);
        // sleep(60);
        $alertDataApi->getInitialAlertDataByAffiliate($fromDate, $toDate, $alertBelowCrmIdList, $day);
    }
    $time = date('Y-m-d H:i:s');
    echo "End time: ".$time."\n";
}

?>