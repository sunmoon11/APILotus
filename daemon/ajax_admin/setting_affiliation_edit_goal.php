<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/5/2018
 * Time: 6:35 AM
 */

require_once '../api/DBApi.php';

$affiliate_id = $_GET['affiliate_id'];
$offer_ids = explode(',', $_GET['offer_ids']);
$offer_goals = explode(',', $_GET['offer_goals']);

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret1 = $dbApi->editAffiliationGoals($affiliate_id, $offer_ids, $offer_goals);
$ret2 = $dbApi->setOffersOfAffiliateID($affiliate_id, $offer_ids);

if ($ret1 && $ret2)
    echo 'success';
else
    echo 'error';
