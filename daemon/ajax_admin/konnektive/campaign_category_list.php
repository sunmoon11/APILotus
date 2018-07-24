<?php

require_once '../../api/DBApi.php';


$crmID = $_GET['crm_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$categoryList = $dbApi->getKKCrmCampaignCategoryList($crmID);
if ($categoryList != null)
{
	echo json_encode($categoryList);
	return;
}

echo 'error';

?>