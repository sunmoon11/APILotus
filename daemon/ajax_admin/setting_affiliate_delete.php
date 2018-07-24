<?php

require_once '../api/DBApi.php';


$affiliateID = $_GET['affiliate_id'];


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->deleteAffiliate($affiliateID);
if ($ret)
	echo 'success';
else 
	echo 'error';

?>