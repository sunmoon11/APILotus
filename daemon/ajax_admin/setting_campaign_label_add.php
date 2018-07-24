<?php

require_once '../api/DBApi.php';


$labelName = $_GET['label_name'];

// add insert table here
$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->addLabel($labelName);
if ($ret)
	echo 'success';
else 
	echo 'error';

?>