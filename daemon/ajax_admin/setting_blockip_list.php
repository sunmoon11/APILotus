<?php

require_once '../api/DBApi.php';


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$blockIPList = $dbApi->getBlockedIpList();
if (true)
{
	echo json_encode($blockIPList);
	return;
}

echo 'error';

?>