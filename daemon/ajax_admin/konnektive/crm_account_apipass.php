<?php

require_once '../../api/DBApi.php';


$crmID = $_GET['crm_id'];
$apiPassword = $_GET['api_password'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->updateKKCrmApiPassword($crmID, $apiPassword);
if ($ret)
	echo 'success';
else
	echo 'error';

?>