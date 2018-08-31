<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/31/2018
 * Time: 6:01 PM
 */

require_once '../api/DBApi.php';

$offerID = $_GET['offer_id'];
$name = $_GET['name'];
$campaignIDs = $_GET['campaign_ids'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->editOffer($offerID, $name, $campaignIDs);
if ($ret)
    echo 'success';
else
    echo 'error';

?>