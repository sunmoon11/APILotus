<?php

require_once '../api/DBApi.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo 'no_cookie';
    return;
}
$accountId = $_SESSION['user_id'];
session_write_close();

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$crmList = $dbApi->getAllActiveCrmsByAccountId($accountId);
// $crmList = $dbApi->getAllCrm();
if ($crmList != null)
	echo json_encode($crmList);
else 
	echo 'error';


?>