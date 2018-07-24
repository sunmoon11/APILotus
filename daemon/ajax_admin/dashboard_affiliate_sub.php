<?php

require_once '../api/DBApi.php';


$affiliateID = $_GET['affiliate_id'];
$userToken = $_GET['user_token'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}
$ret = $dbApi->getAffiliateSumPerCrm($userToken, $affiliateID);

if ($ret)
{
	echo json_encode(array('success', $affiliateID, $ret));	
	return;
}

echo json_encode(array('error', $affiliateID));

?>