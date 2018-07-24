<?php

require_once '../api/DBApi.php';


$receiverID = $_GET['receiver_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->deleteAlertReceiver($receiverID);
if ($ret)
	echo 'success';
else 
	echo 'error';

?>