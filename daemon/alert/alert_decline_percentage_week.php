<?php
require_once '../api/AlertDataApi.php';
require_once '../api/AlertMethodApi.php';
require_once '../../lib/utils/TimeUtils.php';

$dbApi = DBApi::getInstance();
$subDomains = $dbApi->getAllSubDomain();
foreach ($subDomains as $item) {
    $name = $item[1];
    getRetentionReportForDeclineWeekAlertByCrm($name);
}
return;

function getRetentionReportForDeclineWeekAlertByCrm($subDomain) {
    global $dbApi;
    $alertDataApi = AlertDataApi::getInstance();
    $timeUtil = TimeUtils::getInstance();
    $fromDate = $timeUtil->getDateOfCurrentWeek()[0];
    $toDate = $timeUtil->getDateOfCurrentWeek()[1];
    $dbApi->setSubDomain($subDomain);
    $crmList = $dbApi->getAllActiveCrm();
    $type = 6;
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

            $settings = $dbApi->getAlertLevelList($type);
            $from = date('Y-m-d', strtotime($fromDate));
            $to = date('Y-m-d', strtotime($toDate));
            $timestamp = date('Y-m-d H:i:s');
            foreach ($settings as $setting)
            {
                // $type = 4;
                // if($setting[2] == $type)
                // {
                // 	// Initial Approval Week Alert
                // 	$status = 0;
                // 	if($data[0] <= $setting[4])
                // 	{
                // 		$status = 1;
                // 	}
                // 	$toDate = $timeUtil->getDateOfCurrentSunday();
                // 	$to = date('Y-m-d', strtotime($toDate));
                // 	$dbApi->updateAlertStatus($crmID, $type, $data[0], $setting[4], $status, $from, $to, $timestamp);
                // }

                if($setting[1] == $crmID)
                {
                    // Decline Percentage Week Alert
                    $status = 0;
                    if($data[1] >= $setting[4])
                    {
                        $status = 1;
                    }
                    $toDate = $timeUtil->getDateOfCurrentSunday();
                    $to = date('Y-m-d', strtotime($toDate));
                    $dbApi->updateAlertStatus($crmID, $type, $data[1], $setting[4], $status, $from, $to, $timestamp);
                }
            }
        }
    }
    echo 'alert_retention_week OK ';
}

?>