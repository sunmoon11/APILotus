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

$subscriptionID = $dbApi->getPaymentSubscriptionID($subDomain);

if ($subscriptionID != '')
{
	$stripeApi = StripeApi::getInstance();
	$subscription = $stripeApi->retrieveSubscription($subscriptionID);

	echo json_encode(array('success', $subscription));
	return;
}

echo 'error';

?>