<?php

require_once '../api/DBApi.php';


$labelID = $_GET['label_id'];


// add delete table here
$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->deleteLabel($labelID);
if ($ret)
	echo 'success';
else
	echo 'error';

?>