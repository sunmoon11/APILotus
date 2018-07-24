<?php

require_once '../api/DBApi.php';


$userToken = $_GET['user_token'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$ret = $dbApi->getAffiliateSum($userToken);

if ($ret != array())
{
	echo json_encode($ret);	
	return;
}

echo 'error';

?>