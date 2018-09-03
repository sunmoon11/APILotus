<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/23/2018
 * Time: 7:08 AM
 */

require_once '../api/DBApi.php';


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->getAllOffersWithCRMGoal();
if (null != $ret or 0 == sizeof($ret)) {
    echo json_encode($ret);
    return;
}

echo 'error';

?>