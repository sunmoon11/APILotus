<?php

require_once '../api/DBApi.php';

$crmID = $_GET['crm_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$labelList = $dbApi->getLabelsAndGoalsByCrm($crmID);
if ($labelList != array())
{
	echo json_encode($labelList);
	return;
}

echo 'error';

?>