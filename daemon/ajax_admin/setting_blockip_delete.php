<?php

require_once '../api/DBApi.php';


$ipID = $_GET['ip_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->deleteBlockedIp($ipID);
if ($ret)
	echo 'success';
else 
	echo 'error';

?>