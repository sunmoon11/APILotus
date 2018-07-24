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
	$stripeApi = StripeApi::getInstance();
	$plan = $stripeApi->retrievePlan();

	echo json_encode(array('success', $plan));
	return;
} 
else 
{
    echo json_encode(array('no_cookie'));
    return;
}

echo json_encode(array('error'));

?>