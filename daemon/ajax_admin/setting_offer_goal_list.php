<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/25/2018
 * Time: 2:00 AM
 */

require_once '../api/DBApi.php';


$crmID = $_GET['crm_id'];					// crm id
$offerID = $_GET['campaign_id'];		    // offer id

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->getOfferGoal($crmID, $offerID);
if ($ret)
{
    echo json_encode($ret);
    return;
}

echo 'error';

?>