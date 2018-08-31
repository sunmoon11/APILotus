<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/31/2018
 * Time: 4:24 PM
 */

require_once '../api/DBApi.php';

$name = $_GET['name'];
$crmID = $_GET['crm_id'];
$campaignIDs = $_GET['campaign_ids'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->addOffer($crmID, $name, $campaignIDs);
if ($ret)
    echo 'success';
else
    echo 'error';

?>