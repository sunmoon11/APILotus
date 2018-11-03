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
$labelIDs = $_GET['label_ids'];
$offer_type = $_GET['offer_type'];
$s1_payout = $_GET['s1_payout'];
$s2_payout = $_GET['s2_payout'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->addOffer($crmID, $name, $campaignIDs, $labelIDs, $offer_type, $s1_payout, $s2_payout);
if ($ret)
    echo 'success';
else
    echo 'error';

?>