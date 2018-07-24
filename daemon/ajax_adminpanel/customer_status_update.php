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

$customerId = $_GET['customer_id'];
$block = $_GET['block'];			// 1 or 0

$dbApi = DBApi::getInstance();
$ret = $dbApi->updateAdminAccountBlock($customerId, $block);

if ($ret)
{
	echo 'success';
	return;
}

echo 'error';

?>