<?php

require_once '../api/DBApi.php';


$type = $_GET['type'];
$address = $_GET['address'];
$status = $_GET['status'];
$chatID = $_GET['chat_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->addAlertReceiver($type, $address, $status, $chatID);
if ($ret)
	echo 'success';
else
	echo 'error';

?>