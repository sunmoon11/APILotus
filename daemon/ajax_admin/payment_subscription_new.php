<?php

require_once '../api/DBApi.php';
require_once '../api/StripeApi.php';


session_start();
if (isset($_SESSION['sub_domain']))
	$subDomain = $_SESSION['sub_domain'];
else
	$subDomain = '';
session_write_close();

if ($subDomain != '')
{
    $dbApi = DBApi::getInstance();
    $customerID = $dbApi->getPaymentCustomerID($subDomain);

    if ($customerID != '')
    {
		$stripeApi = StripeApi::getInstance();
		$subscriptionID = $stripeApi->createSubscription($customerID);

		if ($subscriptionID)
		{
			$ret = $dbApi->updatePaymentSubscriptionID($subDomain, $subscriptionID);

			echo 'success';
			return;
		}
    }
} 
else 
{
    echo 'no_cookie';
    return;
}

echo 'error';

?>