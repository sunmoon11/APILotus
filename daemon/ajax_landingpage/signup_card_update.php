<?php

require_once '../api/DBApi.php';

$userID = $_GET['user_id'];
$cardNumber = $_GET['card_number'];
$expiryMonth = $_GET['expiry_month'];
$expiryYear = $_GET['expiry_year'];
$cvc_number = $_GET['cvc_number'];

$dbApi = DBApi::getInstance();
$ret = $dbApi->updateCardInfo($userID, $email, $customerID, $subscriptionID);
if ($ret)
	echo 'success';
else
	echo 'error';
?>