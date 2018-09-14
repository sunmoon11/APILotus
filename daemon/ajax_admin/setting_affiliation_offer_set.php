<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/15/2018
 * Time: 1:00 AM
 */

require_once '../api/DBApi.php';


$affiliate_id = $_GET['affiliate_id'];
$offer_ids = $_GET['offer_ids'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->setOffersOfAffiliateID($affiliate_id, $offer_ids);

if ($ret)
    echo 'success';
else
    echo 'error';

?>