<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/23/2018
 * Time: 7:08 AM
 */

require_once '../api/DBApi.php';


$crmID = $_GET['crm_id'];					// crm id
$offerIDs = $_GET['offer_ids'];		        // array of offer id or id list

$offerIDs = preg_replace('/\s+/', '', $offerIDs);
$arrayOfferID = array();
if ($offerIDs != '')
    $arrayOfferID = explode(',', $offerIDs);

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->getOffers($crmID);
if ($ret != null) {
    echo json_encode($ret);
    return;
}
else if (0 == sizeof($ret)) {
    echo json_encode($ret);
    return;
}

echo 'error';

?>