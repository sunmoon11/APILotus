<?php

require_once '../../api/DBApi.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo 'no_cookie';
    return;
}
$user_id = $_SESSION['user_id'];
session_write_close();

$crmID = $_GET['crm_id'];


// add delete table here
$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$result = $dbApi->deleteKKCrmAccount($crmID, $user_id);
if ($result)
	echo 'success';
else 
	echo 'error';

?>