<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 2018-11-28
 * Time: 6:51 AM
 */

require_once '../api/DBApi.php';
require_once '../../lib/utils/TimeUtils.php';

$dbApi = DBApi::getInstance();
$affiliates = $dbApi->getAffiliationGoal();

$timeUtil = TimeUtils::getInstance();
$fromDate = $timeUtil->getDateOfCurrentWeek()[0];
$toDate = $timeUtil->getDateOfCurrentWeek()[1];

foreach ($affiliates as $affiliate) {
    $dbApi->addAffiliateForBilling($affiliate[1], $affiliate[2], $affiliate[3],
        $affiliate[4], $affiliate[5], $fromDate, $toDate);
}

$timestamp = date('Y-m-d H:i:s');
echo 'affiliate for billing backed up - '.$timestamp."\r\n";
