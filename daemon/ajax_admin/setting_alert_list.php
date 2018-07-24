<?php

require_once '../api/DBApi.php';

$alertType = $_GET['alert_type'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$response = $dbApi->getAlertLevelList($alertType);
if (true)
{
	echo json_encode(array('success', $alertType, $response));
	return;
}

echo json_encode(array('error', $alertType));

?>