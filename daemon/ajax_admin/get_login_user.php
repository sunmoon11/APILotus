<?php

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo json_encode(array('no_cookie'));
    return;
}
$userID = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userRole = $_SESSION['role'];
session_write_close();

echo json_encode(array($userID, $userName, $userRole));

?>