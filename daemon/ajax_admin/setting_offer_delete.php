<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/31/2018
 * Time: 5:43 PM
 */

require_once '../api/DBApi.php';


$offerID = $_GET['offer_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->deleteOffer($offerID);
if ($ret)
    echo 'success';
else
    echo 'error';

?>