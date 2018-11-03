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

$ret = $dbApi->editOffer($offerID, $name, $campaignIDs, $labelIDs, $offer_type, $s1_payout, $s2_payout);
if ($ret)
    echo 'success';
else
    echo 'error';

?>