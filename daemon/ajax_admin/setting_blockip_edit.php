<?php

require_once '../api/DBApi.php';


$ipID = $_GET['ip_id'];
$blockIP = $_GET['block_ip'];
$description = $_GET['description'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->updateBlockedIp($ipID, $blockIP, $description);
if ($ret)
	echo 'success';
else
	echo 'error';

?>