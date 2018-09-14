<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/14/2018
 * Time: 9:55 PM
 */

require_once '../api/DBApi.php';


$affiliate_id = $_GET['affiliate_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$offers = $dbApi->getOffersOfAffiliateID($affiliate_id);

echo json_encode($offers);

?>