<?php

require_once '../api/DBApi.php';


$alertID = $_GET['alert_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$ret = $dbApi->deleteAlertItem($alertID);
if ($ret)
	echo 'success';
else 
	echo 'error';

?>