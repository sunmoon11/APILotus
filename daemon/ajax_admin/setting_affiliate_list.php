<?php

require_once '../api/DBApi.php';


$pageNumber = $_GET['page_number'];			// current page number
$items4Page = $_GET['items_page'];			// item count per page

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->getAffiliate($pageNumber, $items4Page);
if ($ret)
{
	echo json_encode($ret);	
	return;
}

echo 'error';

?>