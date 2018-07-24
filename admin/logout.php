<?php

session_start();
if(isset($_SESSION['user']))
{
    unset($_SESSION['user']);
    unset($_SESSION['sub_domain']);
	session_destroy();
}

header("Location: ./index.php");

?>