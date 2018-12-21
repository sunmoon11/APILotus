<?php

require_once '../api/DBApi.php';


$accountID = $_GET['account_id'];


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$permissionList = $dbApi->getCrmNamePermissionOfAccount($accountID);
$page_permissions = $dbApi->getPagePermissionOfAccount($accountID);
if ($permissionList != null)
{
	echo json_encode(array('success', $accountID, $permissionList, $page_permissions));
	return;
}

echo json_encode(array('error', $accountID));

?>