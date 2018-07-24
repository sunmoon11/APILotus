<?php

require_once '../api/SignupApi.php';

$email = $_GET['email'];
$verifyCode = $_GET['verify_code'];

$signUpApi = SignupApi::getInstance();
$signUpApi->sendVerifyMailTo($email, $verifyCode);

echo 'success';
?>