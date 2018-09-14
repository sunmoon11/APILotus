<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/15/2018
 * Time: 2:54 AM
 */

require_once '../api/DBApi.php';

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$labelList = $dbApi->getOfferLabels();
if ($labelList != array())
{
    echo json_encode($labelList);
    return;
}

echo 'error';

?>