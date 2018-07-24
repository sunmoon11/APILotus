<?php

require_once '../../api/DBApi.php';
require_once '../../api/KKCrmApi.php';


$crmID = $_GET['crm_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$crmList = $dbApi->getActiveKKCrmById($crmID);
if ($crmList != null)
{
    $kkcrmApi = KKCrmApi::getInstance();
    $kkcrmApi->credentials($crmList[3], $crmList[4]);
    $campaignList = $kkcrmApi->getAllCampaigns();
    if ($campaignList != array())
    {
        echo json_encode($campaignList);
        return;
    }
}

echo 'error';
