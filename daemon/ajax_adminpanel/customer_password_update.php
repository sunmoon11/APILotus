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

$userId = $_GET['user_id'];
$password = $_GET['password'];

$dbApi = DBApi::getInstance();
$ret = $dbApi->updateAdminAccountPassword($userId, $password);

if ($ret)
{
	echo 'success';
	return;
}

echo 'error';

?>