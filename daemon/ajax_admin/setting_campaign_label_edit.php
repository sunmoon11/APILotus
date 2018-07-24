<?php

require_once '../api/DBApi.php';


$crmID = $_GET['crm_id'];
$labelID = $_GET['label_id'];
$labelName = $_GET['label_name'];
$labelShow = $_GET['label_show'];
$labelGoal = $_GET['label_goal'];

// add update table here
$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->updateLabel($labelID, $labelName);
if($ret)
{
    $ret = $dbApi->updateLabelGoal($crmID, $labelID, $labelGoal, $labelShow);
    if ($ret)
    {
        echo 'success';
        return;
    }
}

echo 'error';
?>