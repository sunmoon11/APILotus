<?php

require_once '../api/DBApi.php';


$blockIP = $_GET['block_ip'];
$description = $_GET['description'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->addBlockedIp($blockIP, $description);
if ($ret)
	echo 'success';
else
	echo 'error';

?>