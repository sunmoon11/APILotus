<?php

require_once '../api/DBApi.php';


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$receiverList = $dbApi->getAlertReceiverList();
if (true)
{
	echo json_encode(array('success', $receiverList));
	return;
}

echo json_encode(array('error'));

?>