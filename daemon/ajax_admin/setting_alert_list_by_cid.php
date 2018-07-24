<?php

require_once '../api/DBApi.php';

$crmID = $_GET['crm_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$response = $dbApi->getAlertLevelListByCrm($crmID);
if (true)
{
    echo json_encode(array('success', $crmID, $response));
    return;
}

echo json_encode(array('error', $crmID));

?>