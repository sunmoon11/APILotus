<?php

require_once '../api/DBApi.php';

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$response = $dbApi->getAlertType();
if ($response != array())
{
	echo json_encode($response);
	return;
}

echo 'error';

?>