<?php
require_once '../../lib/utils/TimeUtils.php';
require_once '../api/AlertManager.php';

$timeUtil = TimeUtils::getInstance();
$alertMgr = AlertManager::getInstance();

$day = $timeUtil->getDayOfWeek();
$hour = $timeUtil->getHour();

//echo $day.' '.$hour."\n";

//$alertMgr->checkAlerts($day, $hour);
$alertMgr->checkAlertsForAllSubDomain($day, $hour);

return;
?>