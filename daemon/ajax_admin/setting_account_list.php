<?php

require_once '../api/DBApi.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo 'no_cookie';
    return;
}
$userID = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
session_write_close();

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$accountList = $dbApi->getAllUsers();

if($userRole != 9)
{
	foreach ($accountList as $value) 
	{
		if($value[0] == $userID)
		{
			echo json_encode(array($value));
			return;			
		}
	}
} 
else 
{
	if ($accountList != null)
	{
		echo json_encode($accountList);
		return;
	}
}

echo 'error';

?>