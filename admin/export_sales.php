<?php
require_once '../daemon/api/DBApi.php';
require_once '../daemon/api/LLCrmHook.php';
require_once '../daemon/api/LLCrmApi.php';

session_start();
$user = $_SESSION['user'];


if (!isset($user) || $user == '')
{
    header("Location: ./login.php");
    return;
}

// session timeout
$now = time();
if ($now - $_SESSION['last_activity'] > 9660)
{
    session_unset();
    session_destroy();
    header("Location: ./login.php");
    return;
}
$_SESSION['last_activity'] = time();
if (isset($_COOKIE[session_name()]))
    setcookie(session_name(), $_COOKIE[session_name()], time() + 9660);
if ($_SESSION['last_activity'] - $_SESSION['created'] > 9660)
{
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
session_write_close();

// check client ip
$dbApi = DBApi::getInstance();
if(!$dbApi->checkClientIp())
{
    header("Location: ./blockip_alert.php");
    return;
}

$crmID = $_GET['crm_id'];
$crmName = $_GET['crm_name'];
$fromDate = $_GET['fd'];
$toDate = $_GET['td'];
$campaignID = $_GET['cids'];
$aff = $_GET['aff'];
$f = $_GET['f'];
$sf = $_GET['sf'];

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
    die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once '../lib/phpexcel/Classes/PHPExcel.php';


$fileName = 'SalesReport_'.$crmName.'_'.$fromDate.'_'.$toDate.'.xls';



// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                             ->setLastModifiedBy("Maarten Balliauw")
                             ->setTitle("Office 2007 XLSX Test Document")
                             ->setSubject("Office 2007 XLSX Test Document")
                             ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                             ->setKeywords("office 2007 openxml php")
                             ->setCategory("Test result file");

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$fileName.'"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter(makeExportData($objPHPExcel, $crmID, $crmName, $fromDate, $toDate, $campaignID, $aff, $f, $sf), 'Excel5');
$objWriter->save('php://output');

exit;

function makeExportData($phpExcel, $crmID, $crmName, $fromDate, $toDate, $campaignID, $aff, $f, $sf)
{
    $activeSheet = $phpExcel->setActiveSheetIndex(0);
    $activeSheet->setTitle($crmName);

    if ($aff == '')
        $activeSheet->setCellValue('A1', 'Campaign ID');
    else
        $activeSheet->setCellValue('A1', 'Affiliate ID');
    $activeSheet->setCellValue('B1', 'Prospects');
    $activeSheet->setCellValue('C1', 'Initial Customers');
    $activeSheet->setCellValue('D1', 'Conversion Rate');
    $activeSheet->setCellValue('E1', 'Gross Revenue');
    $activeSheet->setCellValue('F1', 'Average Revenue');

    $dbApi = DBApi::getInstance();
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
            $response = $llcrmHook->getSalesReport($token, $fromDate, $toDate, $campaignID, $aff, $f, $sf);
            $result = $llcrmHook->parseSalesReport($response);

            $campaign = false;
            foreach ($result as $item)
            {
                if ($item[8] == 'Sub-Affiliate')
                {
                    $campaign = false;
                    break;
                }
                if ($item[8] == 'Affiliate')
                {
                    $campaign = true;
                    break;
                }
            }

            // get campaign name
            if ($campaign)
            {
                $llcrmApi = LLCrmApi::getInstanceWithCredentials($crmUrl.'/admin/', $apiName, $apiPassword);
                $data = $llcrmApi->getAllCampaign1();
                $ret = array();
                if ($data != null)
                {
                    foreach ($result as $item)
                    {
                        $campaignIDs = $data['ids'];
                        $campaignNames = $data['names'];

                        $index = array_search($item[0], $campaignIDs);
                        $campaignName = $campaignNames[$index];
                        $item[9] = $campaignName;
                        $ret[] = $item;
                    }
                }

                $result = $ret;
            }

            $row = 2;
            for ($j = 0; $j < sizeof($result); $j ++) 
            {
                $activeSheet->setCellValue('A'.$row, $result[$j][0]);
                $activeSheet->setCellValue('B'.$row, $result[$j][1]);
                $activeSheet->setCellValue('C'.$row, $result[$j][2]);
                $activeSheet->setCellValue('D'.$row, $result[$j][3]);
                $activeSheet->setCellValue('E'.$row, $result[$j][4]);
                $activeSheet->setCellValue('F'.$row, $result[$j][5]);

                $row ++;
            }
        }
    }

    return $phpExcel;
}

?>