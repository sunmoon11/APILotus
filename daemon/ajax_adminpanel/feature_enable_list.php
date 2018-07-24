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

$dbApi = DBApi::getInstance();
//$dbApi->setSubDomain($subDomain);

$items = $dbApi->getFeatureEnableList($subDomain);
echo json_encode(array('success', $items));

return;

?>