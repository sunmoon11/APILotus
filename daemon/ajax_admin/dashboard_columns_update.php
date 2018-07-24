<?php

require_once '../api/DBApi.php';

// Getting userID on cookie
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo 'no_cookie';
    return;
}
$userID = $_SESSION['user_id'];
session_write_close();

$showColumns = $_GET['show_columns'];			// items string seperated by comma
$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$ret = $dbApi->updateDashboardShowColumns($userID, $showColumns);

if ($ret)
{
	echo 'success';
	return;
}

echo 'error';

?>