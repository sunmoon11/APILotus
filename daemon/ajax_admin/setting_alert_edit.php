<?php

require_once '../api/DBApi.php';

$type = $_GET['type'];
$crmID = $_GET['crm_id'];
$level1 = $_GET['level1'];
$level2 = $_GET['level2'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->updateAlertLevel($type, $crmID, $level1, $level2);
if ($ret)
	echo 'success';
else
	echo 'error';

?>