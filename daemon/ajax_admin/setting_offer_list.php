<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/23/2018
 * Time: 7:08 AM
 */

require_once '../api/LLCrmApi.php';
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

$crmList = $dbApi->getActiveCrmById($crmID);
if ($crmList != null)
{
    $apiUrl = $crmList[0].'/admin/';
    $apiUserName = $crmList[3];
    $apiPassword = $crmList[4];

    $ret = $dbApi->getOffers($crmID);

    echo json_encode($ret);
    return;
}

echo 'error';

?>