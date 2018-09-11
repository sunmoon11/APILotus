<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/8/2018
 * Time: 7:03 PM
 */

require_once '../api/DBApi.php';


$date_type = $_GET['date_type'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$refresh = $dbApi->updateDashboardRefresh($date_type);

echo "success";

?>