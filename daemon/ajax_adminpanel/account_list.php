<?php

require_once '../api/DBApi.php';

session_start();
if (!isset($_SESSION['ap_user_id']) || $_SESSION['ap_user_id'] == '')
{
    session_write_close();
    echo 'no_cookie';
    return;
}
$subDomain = $_GET['sub_domain'];
session_write_close();

$dbApi = DBApi::getInstance();
$dbApi->setSubDomain($subDomain);

$accountList = $dbApi->getAllUsers();
if ($accountList != null)
{
	echo json_encode(array('success', $accountList));
	return;
}

echo 'error';

?>