<?php

require_once '../api/DBApi.php';


$userID = $_GET['user_id'];

$dbApi = DBApi::getInstance();
$ret = $dbApi->checkIfUserIdRegistered($userID);
if($ret)
    $ret = $dbApi->setUserVerified($userID);
else
    $ret = false;

if ($ret)
	echo 'success';
else
	echo 'error';
?>