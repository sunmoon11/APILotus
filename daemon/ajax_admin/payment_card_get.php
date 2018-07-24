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
    $cardID = $dbApi->getPaymentCardID($subDomain);
    $customerID = $dbApi->getPaymentCustomerID($subDomain);
    
    if ($cardID != '' && $customerID != '')
    {
		$stripeApi = StripeApi::getInstance();
		$card = $stripeApi->retrieveCard($customerID, $cardID);

		echo json_encode(array('success', $card));
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