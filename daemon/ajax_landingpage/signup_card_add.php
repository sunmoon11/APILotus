<?php

require_once '../api/DBApi.php';
require_once '../api/StripeApi.php';

$userID = $_GET['user_id'];
$subDomain = $_GET['sub_domain'];
$email = $_GET['email'];
$tokenID = $_GET['token_id'];
$cardID = $_GET['card_id'];


$stripeApi = StripeApi::getInstance();
$customerID = $stripeApi->createCustomer($email, $tokenID);

if ($customerID)
{
	$subscriptionID = $stripeApi->createSubscription($customerID);
	
	if ($subscriptionID)
	{
		$dbApi = DBApi::getInstance();
		$ret = $dbApi->addCardInfo($userID, $subDomain, $email, $customerID, $subscriptionID, $cardID);

		if ($ret) {
			echo 'success';
			return;
		}
	}
}

echo 'error';

?>