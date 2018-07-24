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
$showColumns = $_GET['show_columns'];			// items string seperated by comma

$dbApi = DBApi::getInstance();
//$dbApi->setSubDomain($subDomain);

$ret = $dbApi->updateAdminPanelDashboardShowColumns($subDomain, $showColumns);

if ($ret)
{
	echo 'success';
	return;
}

echo 'error';

?>