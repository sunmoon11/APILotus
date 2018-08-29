<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/29/2018
 * Time: 7:15 AM
 */
require_once '../api/LLCrmApi.php';
require_once '../api/DBApi.php';


$crmID = $_GET['crm_id'];
$offerID = $_GET['offer_id'];

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

    $crmApi = LLCrmApi::getInstanceWithCredentials($apiUrl, $apiUserName, $apiPassword);
    $ret = $crmApi->getOfferCampaigns($crmID);

    echo json_encode($ret);
    return;
}

echo 'error';

?>