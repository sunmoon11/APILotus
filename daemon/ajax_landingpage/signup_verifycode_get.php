<?php

require_once '../api/DBApi.php';


$userID = $_GET['user_id'];

$dbApi = DBApi::getInstance();
$verify_code = $dbApi->getVerifyCodeOfUser($userID);

if ($verify_code != '')
	echo json_encode(array('success', $verify_code));
else
	echo json_encode(array('error'));
?>