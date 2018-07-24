<?php

require_once '../../api/DBApi.php';


$categoryID = $_GET['category_id'];


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->deleteKKCrmCampaignCategory($categoryID);
if ($ret)
	echo 'success';
else 
	echo 'error';

?>