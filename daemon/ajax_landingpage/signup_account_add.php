<?php

require_once '../api/DBApi.php';
require_once '../api/SignupApi.php';

$userID = $_GET['user_id'];
$email = $_GET['email'];
$password = $_GET['password'];
$verifyCode = mt_rand(100000, 999999);

$dbApi = DBApi::getInstance();
$ret = $dbApi->checkIfUserIdRegistered($userID);
if ($ret)
{
	echo json_encode(array('error', 'user already exists.'));
    return;
}

$ret = $dbApi->checkIfEmailRegistered($email);
if ($ret)
{
    echo json_encode(array('error', 'email already exists.'));
    return;
}

$ret = $dbApi->addNewUser($userID, $email, $password, $verifyCode);
if ($ret)
	echo json_encode(array('success', $verifyCode));
else
	echo json_encode(array('error', 'fail to add user.'));
?>