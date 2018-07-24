<?php

require_once '../api/DBApi.php';
require_once '../api/StripeApi.php';


session_start();
if (!isset($_SESSION['ap_user_id']) || $_SESSION['ap_user_id'] == "")
{
    session_write_close();
    echo 'no_cookie';
    return;
}
session_write_close();


$subDomain = $_GET['sub_domain'];

$dbApi = DBApi::getInstance();
$dbApi->setSubDomain($subDomain);

$cardID = $dbApi->getPaymentCardID($subDomain);
$customerID = $dbApi->getPaymentCustomerID($subDomain);

if ($cardID != '' && $customerID != '')
{
	$stripeApi = StripeApi::getInstance();
	$card = $stripeApi->retrieveCard($customerID, $cardID);

	echo json_encode(array('success', $card));
	return;
}

echo 'error';

?>