<?php

require_once '../api/DBApi.php';
require_once '../api/StripeApi.php';


$expiryMonth = $_GET['expiry_month'];
$expiryYear = $_GET['expiry_year'];


session_start();
if (isset($_SESSION['sub_domain']))
	$subDomain = $_SESSION['sub_domain'];
else
	$subDomain = '';
session_write_close();

if ($subDomain != '')
{
	$dbApi = DBApi::getInstance();
    $cardID = $dbApi->getPaymentCardID($subDomain);
    $customerID = $dbApi->getPaymentCustomerID($subDomain);

    if ($cardID != '' && $customerID != '')
    {
    	$stripeApi = StripeApi::getInstance();
		$stripeApi->updateCard($customerID, $cardID, $expiryMonth, $expiryYear);

		echo 'success';
		return;
    }
} 
else
{
    echo 'no_cookie';
    return;
}

echo 'error';

?>