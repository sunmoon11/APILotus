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

$customerID = $dbApi->getPaymentCustomerID($subDomain);
$subscriptionID = $dbApi->getPaymentSubscriptionID($subDomain);
$limit = 100;

if ($customerID != '' && $subscriptionID != '')
{
	$stripeApi = StripeApi::getInstance();
	$invoices = $stripeApi->listInvoices($customerID, $subscriptionID, $limit);

	echo json_encode(array('success', $invoices));
	return;
}

echo 'error';

?>