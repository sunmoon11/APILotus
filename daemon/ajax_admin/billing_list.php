<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 2018-11-15
 * Time: 6:13 PM
 */

require_once '../api/DBApi.php';

$date_type = $_GET['date_type'];
$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$billing = $dbApi->getBilling($date_type, $fromDate, $toDate);

echo json_encode($billing);
