<?php

require_once '../api/LLCrmApi.php';
require_once '../api/DBApi.php';


$crmID = $_GET['crm_id'];					// crm id
$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];
$pageNumber = $_GET['page_number'];			// current page number
$items4Page = $_GET['items_page'];			// item count per page

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}
$response = $dbApi->getAlertHistory($crmID, $fromDate, $toDate, $pageNumber, $items4Page);

echo json_encode(array('success', $crmID, $response));


?>