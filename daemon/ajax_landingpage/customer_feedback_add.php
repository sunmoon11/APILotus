<?php

require_once '../api/DBApi.php';

$clientName = $_POST['client_name'];
$clientEmail = $_POST['client_email'];
$clientComment = $_POST['client_comment'];

$dbApi = DBApi::getInstance();
$ret = $dbApi->insertCustomerFeedback($clientName, $clientEmail, $clientComment);

if ($ret)
{
	echo "success";
    return;
}

echo "fail";
?>