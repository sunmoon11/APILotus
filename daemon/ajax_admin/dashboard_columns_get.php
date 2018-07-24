<?php

require_once '../api/DBApi.php';


// Getting userID on cookie
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo json_encode(array('no_cookie'));
    return;
}
$userID = $_SESSION['user_id'];
session_write_close();

$dbApi = DBApi::getInstance();
$subDomain = $dbApi->getSubDomain();

if ($subDomain == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

//$showItems = $dbApi->getDashboardShowColumns($userID);
$showItems = $dbApi->getAdminPanelDashboardShowColumns($subDomain);
echo json_encode(array('success', $showItems));

return;

?>