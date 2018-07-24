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
		$customer = $stripeApi->retrieveCustomer($customerID);

		echo json_encode(array('success', $customer));
		return;
    }
} 
else 
{
    echo json_encode(array('no_cookie'));
    return;
}

echo json_encode(array('error'));


?>