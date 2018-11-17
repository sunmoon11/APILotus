<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 2018-11-15
 * Time: 6:13 PM
 */

require_once '../api/DBApi.php';


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$billing = $dbApi->getBilling();

echo json_encode($billing);
