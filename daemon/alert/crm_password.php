<?php
require_once '../api/AlertMethodApi.php';
require_once '../api/AlertManager.php';
require_once '../../lib/utils/TimeUtils.php';
require_once '../telegram/TelegramBot.php';

$dbApi = DBApi::getInstance();
$subDomains = $dbApi->getAllSubDomain();
foreach ($subDomains as $item) {
    $name = $item[1];
    checkPasswordOfCrmBySubDomain($name);
}
return;

function checkPasswordOfCrmBySubDomain($name) {
    global $dbApi;
    $timeUtil = TimeUtils::getInstance();
    $alertMgr = AlertManager::getInstance();

    $dbApi->setSubDomain($name);
    // get all CRM including paused.
    $crmList = $dbApi->getAllCrm();

    $data = array();
    // CRM account should be updated every 30 days.
    $level = 29;
    foreach ($crmList as $crm)
    {
        $updatedDay = $crm[9];
        if($updatedDay != null)
        {
            $diffDays = $timeUtil->checkPasswordUpdate($updatedDay);
            if($diffDays >= $level) {
//                $status = 1;
                $data[] = $crm;
            } else {
//                $status = 0;
            }
//            $dbApi->updateAlertStatus($crmId, 12, $diffDays, $level, $status, $from, $to, $currentTime);
        }
    }
    if($data != array())
    {
        $telegramBot = false;
        $email = false;
        $sms = false;
        $setting = $dbApi->getAlertTypeByType(12);
        if($setting[9] == 1)
            $telegramBot = true;
        if($setting[8] == 1)
            $email = true;
        if($setting[7] == 1)
            $sms = true;
        $alertMgr->sendAlertsByCrmPermissionOfAccount($sms, $email, $telegramBot, $data, 12, $name);
    }
}

?>