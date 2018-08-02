<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/3/2018
 * Time: 5:17 AM
 */

require_once '../api/LLCrmHook.php';
require_once '../api/DBApi.php';


$crmID = $_GET['crm_id'];
$crmGoal = $_GET['crm_goal'];

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

    $llcrmHook = new LLCrmHook();
    if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
    {
        $today = date('m/d/Y');
        $yesterday = date('m/d/Y', strtotime('-1 day'));
        $year_start = date('01/01/Y');

        if (!$dbApi->checkCrmResult($crmID, $year_start, $today))
            $dbApi->deleteCrmResult($crmID, $year_start, $yesterday);

        $date_thisyear = $llcrmHook->getCrmSalesBreakDown($token, $year_start, $today, $crmID);
        $dbApi->addCrmResults($crmID, $crmGoal, $date_thisyear, $year_start, $today);

        return true;
    }
}

?>