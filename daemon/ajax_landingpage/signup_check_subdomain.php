<?php

require_once '../api/DBApi.php';


$subDomain = $_GET['sub_domain'];

$dbApi = DBApi::getInstance();
// return sub domain id , if not, return -1;
$ret = $dbApi->checkIfSubdomainRegistered($subDomain);
if ($ret == -1)
    echo json_encode(array('success', false));
else
    echo json_encode(array('success', true));
?>