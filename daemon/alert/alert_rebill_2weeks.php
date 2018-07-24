<?php
require_once '../api/AlertDataApi.php';
require_once '../api/AlertMethodApi.php';
require_once '../../lib/utils/TimeUtils.php';

$dbApi = DBApi::getInstance();
$subDomains = $dbApi->getAllSubDomain();
foreach ($subDomains as $item) {
    $name = $item[1];
    getRetentionReportForRebillAlertByCrm($name);
}
return;

function getRetentionReportForRebillAlertByCrm($subDomain){
    global $dbApi;
    $alertDataApi = AlertDataApi::getInstance();
    $timeUtil = TimeUtils::getInstance();
    $fromDate = $timeUtil->getDateOf2WeeksPrior()[0];
    $toDate = $timeUtil->getDateOf2WeeksPrior()[1];
    $dbApi->setSubDomain($subDomain);
    $crmList = $dbApi->getAllActiveCrm();
    foreach ($crmList as $crm)
    {
        if ($crm != null)
        {
            $crmID = $crm[0];
            $crmUrl = $crm[2];
            $userName = $crm[3];
            $password = $crm[4];

            $cycle = 2;
            $data = $alertDataApi->getRebillAlertDataByCrm($crmID, $crmUrl, $userName, $password, $fromDate, $toDate, $cycle, $subDomain);

            $settings = $dbApi->getAlertLevelListByCrm($crmID);
            $from = date('Y-m-d', strtotime($fromDate));
            $to = date('Y-m-d', strtotime($toDate));
            $timestamp = date('Y-m-d H:i:s');
            foreach ($settings as $setting)
            {
                $type = 1;
                if($setting[2] == $type)
                {
                    // Step1 Rebill Report Alert
                    $status = 0;
                    if($data[0] <= $setting[4])
                    {
                        $status = 1;
                    }

                    $dbApi->updateAlertStatus($crmID, $type, $data[0], $setting[4], $status, $from, $to, $timestamp);
                }
                $type = 2;
                if($setting[2] == $type)
                {
                    // Step2 Rebill Report Alert
                    $status = 0;
                    if($data[1] <= $setting[4])
                    {
                        $status = 1;
                    }

                    $dbApi->updateAlertStatus($crmID, $type, $data[1], $setting[4], $status, $from, $to, $timestamp);
                }
            }
        }
    }
    echo 'alert_retention_2weeks OK';
}

?>