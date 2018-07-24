<?php

require_once '../api/DBApi.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo json_encode(array('no_cookie'));
    return;
}
// session timeout
$now = time();
if ($now - $_SESSION['last_activity'] > 9660)
{
    session_unset();
    session_destroy();
    header("Location: ./login.php");
    return;
}
$_SESSION['last_activity'] = time();
if (isset($_COOKIE[session_name()]))
    setcookie(session_name(), $_COOKIE[session_name()], time() + 9660);
if ($_SESSION['last_activity'] - $_SESSION['created'] > 9660)
{
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

$userId = $_SESSION['user_id'];
session_write_close();

$affiliateID = $_GET['affiliate_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}
$response = $dbApi->getAffiliateSetting($affiliateID, $userId);

if ($response != array())
{
	echo json_encode(array('success', $affiliateID, $response));
	return;
}

echo json_encode(array('error', $affiliateID));

?>