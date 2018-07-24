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
$enabledFeatures = $_GET['enable_feature'];			// items string seperated by comma

$dbApi = DBApi::getInstance();
//$dbApi->setSubDomain($subDomain);

$ret = $dbApi->updateAdminPanelFeatureEnable($subDomain, $enabledFeatures);

if ($ret)
{
    echo 'success';
    return;
}

echo 'error';

?>