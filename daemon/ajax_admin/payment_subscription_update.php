<?php

require_once '../api/DBApi.php';
require_once '../api/StripeApi.php';


$subscriptionID = $_GET['subscription_id'];

session_start();
if (isset($_SESSION['sub_domain']))
	$subDomain = $_SESSION['sub_domain'];
else
	$subDomain = '';
session_write_close();

if ($subDomain != '')
{
    $dbApi = DBApi::getInstance();
    $ret = $dbApi->updatePaymentSubscriptionID($subDomain, $subscriptionID);

    if ($ret != '')
    {
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