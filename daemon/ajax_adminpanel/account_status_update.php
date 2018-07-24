<?php

require_once '../api/DBApi.php';


session_start();
if (!isset($_SESSION['ap_user_id']) || $_SESSION['ap_user_id'] == "")
{
    session_write_close();
    echo 'no_cookie';
    return;
}
session_write_close();


$subDomain = $_GET['sub_domain'];
$accountID = $_GET['account_id'];
$status = $_GET['status'];		// 1 or 0

$dbApi = DBApi::getInstance();
$dbApi->setSubDomain($subDomain);

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