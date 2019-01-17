<?php
/**
 * Created by PhpStorm.
 * User: zaza
 * Date: 7/27/2018
 * Time: 5:08 AM
 */

require_once '../api/LLCrmHook.php';
require_once '../api/DBApi.php';


$crmList = $_GET['crm_list'];
$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$arrayCrm = array();

foreach ($crmList as $crm) {
    $crmID = $crm[0];
    $crmGoal = $crm[7];

    if ($dbApi->checkCrmResult($crmID, $fromDate, $toDate)) {
        $crm_result = $dbApi->getCrmResult($crmID, $fromDate, $toDate);
        if ('error' == $crm_result)
            $arrayCrm[] = json_encode(array('error', $crmID));
        else if (0 == count($crm_result))
            $arrayCrm[] = json_encode(array('no_result', $crmID));
        else
            $arrayCrm[] = json_encode(array('success', $crmID, $crmGoal, $crm_result));
    }
    else {
        $llcrmHook = new LLCrmHook();
        if (($token = $llcrmHook->login($crmID, $crm[2], $crm[3], $crm[4])) != null)
        {
            $response = $llcrmHook->getCrmSalesBreakDown($token, $fromDate, $toDate, $crmID);

            if ($response != 'error')
                $arrayCrm[] = json_encode(array('success', $crmID, $crmGoal, $response));
            else
                $arrayCrm[] = json_encode(array('error', $crmID));
        }
    }
}

echo json_encode($arrayCrm);

?>