<?php


session_start();
if (isset($_SESSION['sub_domain']))
	$subDomain = $_SESSION['sub_domain'];
else
	$subDomain = '';
session_write_close();


if ($subDomain != '')
{
    $dbApi = DBApi::getInstance();
    $subscriptionID = $dbApi->getPaymentSubscriptionID($subDomain);

    $status = '';

    if ($subscriptionID != '')
    {
		$stripeApi = StripeApi::getInstance();
		$subscription = $stripeApi->retrieveSubscription($subscriptionID);

		$status = $subscription->status;
    }

	if ($status != 'active')
	{
		header("Location: ./payment_alert.php");
		return;
	}
}


?>