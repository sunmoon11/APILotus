<?php

require_once '../api/DBApi.php';


$affiliateID = $_GET['affiliate_id'];
$affiliateLabel = $_GET['affiliate_label'];
$crmIDs = $_GET['crm_ids'];
$goals = $_GET['goals'];


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->updateAffiliate($affiliateID, $affiliateLabel, $crmIDs, $goals);
if ($ret)
	echo 'success';
else
	echo 'error';

?>