<?php

require_once '../api/DBApi.php';


$userID = $_GET['user_id'];

$dbApi = DBApi::getInstance();
$ret = $dbApi->checkIfUserIdRegistered($userID);

echo json_encode(array('success', $ret));
?>