<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/29/2018
 * Time: 10:08 AM
 */

require_once '../api/DBApi.php';


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode('no_cookie');
    return;
}

$offers = $dbApi->getAllOffers();
if ($offers) {
    echo json_encode($offers);
    return;
}

echo json_encode('error');
