<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/19/2018
 * Time: 2:33 PM
 */

require_once '../api/DBApi.php';
require_once '../api/LLCrmHook.php';
require_once '../api/LLCrmApi.php';


$crmID = $_GET['crm_id'];
$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];
//$campaignID = $_GET['campaign_id'];
//$aff = $_GET['aff'];
//$f = $_GET['f'];
//$sf = $_GET['sf'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}
$crmList = $dbApi->getActiveCrmById($crmID);

if ($crmList != null)
{
    $crmUrl = $crmList[0];
    $userName = $crmList[1];
    $password = $crmList[2];
    $apiName = $crmList[3];
    $apiPassword = $crmList[4];

    $llcrmHook = new LLCrmHook();
    if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
    {
        $response = $llcrmHook->getSalesReport($token, $fromDate, $toDate, '', '', '', '');
        $result = $llcrmHook->parseSalesReport($response);

        $ret = array();
        foreach ($result as $item)
        {
            if ("Total" == $item[0])
                break;
            $sub_response = $llcrmHook->getSalesReport($token, $fromDate, $toDate, '', "1", $item[0], "0");
            $sub_result = $llcrmHook->parseSalesReport($sub_response);
            $ret[] = array($item[0], array_slice($sub_result, 0, count($sub_result) - 1));
        }

        echo json_encode(array('success', $crmID, $ret));
        return;
    }
}

echo json_encode(array('error', $crmID));
