<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/19/2018
 * Time: 3:57 PM
 */

require_once '../api/DBApi.php';


$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$affiliates_goal = $dbApi->getCapUpdate($fromDate, $toDate);

echo json_encode($affiliates_goal);

?>