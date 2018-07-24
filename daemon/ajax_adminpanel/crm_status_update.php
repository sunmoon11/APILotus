<?php

require_once '../api/DBApi.php';

session_start();
if (!isset($_SESSION['ap_user_id']) || $_SESSION['ap_user_id'] == "")
{
    session_write_close();
    echo 'no_cookie';
    return;
}
session_write_close();

$subDomain = $_GET['sub_domain'];
$crmID = $_GET['crm_id'];
$paused = $_GET['paused'];			// 1 or 0

$dbApi = DBApi::getInstance();
$dbApi->setSubDomain($subDomain);
$ret = $dbApi->updateCRMPaused($crmID, $paused);

if ($ret)
{
	echo 'success';
	return;
}

echo 'error';

?>