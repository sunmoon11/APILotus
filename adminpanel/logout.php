<?php

session_start();
if(isset($_SESSION['ap_user_id']))
{
    unset($_SESSION['ap_user_id']);
    unset($_SESSION['ap_user_name']);
    session_destroy();
}

header("Location: ./index.php");

?>