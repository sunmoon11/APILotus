<?php

require_once '../api/DBApi.php';


$email = $_GET['email'];

$dbApi = DBApi::getInstance();
$ret = $dbApi->checkIfEmailRegistered($email);

echo json_encode(array('success', $ret));
?>