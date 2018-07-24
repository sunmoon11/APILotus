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

$userID = $_GET['user_id'];
$displayName = $_GET['display_name'];
$email = $_GET['email_address'];


$dbApi = DBApi::getInstance();
$dbApi->setSubDomain($subDomain);

$ret = $dbApi->updateProfile($userID, $email, $displayName);
if ($ret != null)
{
	echo 'success';
	return;
}

echo 'error';

?>