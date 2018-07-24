<?php

require_once '../api/DBApi.php';
require_once '../api/StripeApi.php';


$tokenID = $_GET['token_id'];
$cardID = $_GET['card_id'];


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

    $stripeApi = StripeApi::getInstance();
		
    if ($customerID != '')
    {
    	$cardID = $stripeApi->createCard($customerID, $tokenID);

		$ret = $dbApi->updatePaymentCardID($subDomain, $cardID);

		echo 'success';
		return;
    }
    else
    {
    	$userID = $dbApi->getUserIDBySubDomain($subDomain);
    	$email = $dbApi->getEmailBySubDomain($subDomain);;

		$customerID = $stripeApi->createCustomer($email, $tokenID);

		if ($customerID)
		{
			$ret = $dbApi->addCardInfo($userID, $subDomain, $email, $customerID, '', $cardID);
			
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