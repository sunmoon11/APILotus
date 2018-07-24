<?php

require_once '../api/DBApi.php';


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$ret = $dbApi->deleteAlertItemAll();

if ($ret)
	echo 'success';
else 
	echo 'error';

?>