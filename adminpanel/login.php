<?php

include_once '../daemon/api/DBApi.php';


if (isset($_POST['signin']))
{
    $dbApi = DBApi::getInstance();
    $user = $dbApi->validateAdminPanelAccount($_POST['user_name'], $_POST['password']);
    
    if ($user != array())
    {
        session_start();
        $_SESSION['ap_user_id'] = $user[1];
        $_SESSION['ap_user_name'] = $user[2];           // user display name
        session_write_close();

        header("Location: ./home.php");
    }
}


?>


<!DOCTYPE html>
<html>
<head>
	<title>Admin Panel for API Lotus</title>
	<meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../css/limelightcrm.css" rel="stylesheet" type="text/css" />
	<link rel="icon" href="../images/favicon.png" type="image/x-icon">
</head>

<body>
    <div class="crm_login_container">
        <div class="crm_login_logo">
            <img src="../images/logo_login.png" style="width: 200px; height: 158px">
            <br /><label>(Admin Panel)</label>
        </div>
        <div class="crm_login_seperator"></div>
        <div style="height:5px;"></div>
        <form class="form-signin" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
            <h5 class="form-signin-heading">User Name</h5>
            <label for="username" class="sr-only">User Name</label>
            <input type="text" id="username" name="user_name" class="form-control" required="" autofocus="">
            <div style="height:15px;"></div>
            <h5 class="form-signin-heading">Password</h5>
            <label for="inputPassword" class="sr-only">Password</label>
            <input type="password" id="inputPassword" name="password" class="form-control" required="">         
            <div style="height:30px;"></div>
            <button name="signin" class="btn btn-lg btn_signin btn-block" type="submit">Sign In</button>
        </form>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../bootstrap/js/bootstrap.min.js"></script>
</body>

</html>
