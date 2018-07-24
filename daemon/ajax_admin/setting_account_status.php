<?php

require_once '../api/DBApi.php';


$accountID = $_GET['account_id'];
$status = $_GET['status'];		// 1 or 0

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->updateAccountStatus($accountID, $status);
if ($ret)
	echo 'success';
else
	echo 'error';

?>