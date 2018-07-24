<?php

require_once '../api/DBApi.php';


$crmID = $_GET['crm_id'];
$crmPassword = $_GET['crm_password'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$ret = $dbApi->updateCrmPassword($crmID, addslashes($crmPassword));
if ($ret)
	echo 'success';
else
	echo 'error';

?>