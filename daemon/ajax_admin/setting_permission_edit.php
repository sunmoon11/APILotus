<?php

require_once '../api/DBApi.php';


$account_id = $_GET['account_id'];
$permissions = $_GET['permissions'];


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$result = $dbApi->setCrmPermissionOfAccount($account_id, $permissions);
if ($result == true)
{
	echo json_encode(array('success', $account_id));
	return;
}

echo json_encode(array('error', $account_id));

?>