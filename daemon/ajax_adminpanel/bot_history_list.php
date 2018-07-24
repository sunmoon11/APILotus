<?php

require_once '../api/DBApi.php';


$dbApi = DBApi::getInstance();
$botList = $dbApi->getBotHistory();

if ($botList != null)
{
	echo json_encode(array('success', $botList));
	return;
}

echo 'error';

?>