<?php
require_once '../api/AlertDataApi.php';
require_once '../api/AlertMethodApi.php';
require_once '../../lib/utils/TimeUtils.php';

$dbApi = DBApi::getInstance();
$subDomains = $dbApi->getAllSubDomain();
foreach ($subDomains as $item) {
    $name = $item[1];
    getRetentionReportForDeclineDayAlertByCrm($name);
}
return;

function getRetentionReportForDeclineDayAlertByCrm($subDomain){
    global $dbApi;
    $alertDataApi = AlertDataApi::getInstance();
    $timeUtil = TimeUtils::getInstance();
    $fromDate = $timeUtil->getDateOfCurrentDay();
    $toDate = $timeUtil->getDateOfCurrentDay();

    $dbApi->setSubDomain($subDomain);
    $crmList = $dbApi->getAllActiveCrm();
    if ($crmList == array())
        return;
    $type = 5;
    $settings = $dbApi->getAlertLevelList($type);
    foreach ($crmList as $crm)
    {
        if ($crm != null)
        {
            $crmID = $crm[0];
            $crmUrl = $crm[2];
            $userName = $crm[3];
            $password = $crm[4];

            $cycle = 1;
            $data = $alertDataApi->getDeclineAlertDataByCrm($crmID, $crmUrl, $userName, $password, $fromDate, $toDate, $cycle, $subDomain);
            $from = date('Y-m-d', strtotime($fromDate));
            $to = date('Y-m-d', strtotime($toDate));
            $timestamp = date('Y-m-d H:i:s');

            foreach ($settings as $setting)
            {
                // $type = 3;
                // if($setting[2] == $type)
                // {
                // 	// Initial Approval Day Alert
                // 	$status = 0;
                // 	if($data[0] <= $setting[4])
                // 	{
                // 		$status = 1;
                // 	}
                // 	$dbApi->updateAlertStatus($crmID, $type, $data[0], $setting[4], $status, $from, $to, $timestamp);
                // }

                if($setting[1] == $crmID)
                {
                    // Decline Percentage Day Alert
                    $status = 0;
                    if($data[1] >= $setting[4])
                    {
                        $status = 1;
                    }
                    $dbApi->updateAlertStatus($crmID, $type, $data[1], $setting[4], $status, $from, $to, $timestamp);
                }
            }
        }
    }
    echo 'alert_retention_day OK';
}


?>