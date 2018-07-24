<?php

require_once '../api/DBApi.php';


$accountID = $_GET['account_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$ret = $dbApi->deleteAccount($accountID);

if ($ret)
	echo 'success';
else 
	echo 'error';

?>