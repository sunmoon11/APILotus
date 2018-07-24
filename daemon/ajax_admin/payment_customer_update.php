<?php

require_once '../api/DBApi.php';
require_once '../api/StripeApi.php';


$customerID = $_GET['customer_id'];

session_start();
if (isset($_SESSION['sub_domain']))
	$subDomain = $_SESSION['sub_domain'];
else
	$subDomain = '';
session_write_close();

if ($subDomain != '')
{
    $dbApi = DBApi::getInstance();
    $ret = $dbApi->updatePaymentCustomerID($subDomain, $customerID);

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