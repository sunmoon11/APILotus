<?php

require_once '../api/DBApi.php';


$accountID = $_GET['account_id'];
$Password = $_GET['password'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$ret = $dbApi->updateAccountPassword($accountID, $Password);
if ($ret)
	echo 'success';
else
	echo 'error';

?>