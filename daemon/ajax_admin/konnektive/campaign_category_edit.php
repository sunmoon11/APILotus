<?php

require_once '../../api/DBApi.php';


$categoryID = $_GET['category_id'];
$categoryName = $_GET['category_name'];
$campaignIDs = $_GET['campaign_ids'];
$campaignNames = $_GET['campaign_names'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->updateKKCrmCampaignCategory($categoryID, $categoryName, $campaignIDs, $campaignNames);
if ($ret)
	echo 'success';
else
	echo 'error';

?>