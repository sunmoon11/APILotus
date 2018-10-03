<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 10/3/2018
 * Time: 5:28 AM
 */

require_once '../daemon/api/DBApi.php';

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
$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
    die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once '../lib/phpexcel/Classes/PHPExcel.php';


$fileName = 'initial_'.$crmName.'_'.str_replace('/', '.', $fromDate).'-'.str_replace('/', '.', $toDate).'.xls';



// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("ZAZA")
    ->setLastModifiedBy("ZAZA")
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

$objWriter = PHPExcel_IOFactory::createWriter(makeExportData($objPHPExcel, $crmID, $crmName, $fromDate, $toDate), 'Excel5');
$objWriter->save('php://output');

exit;

function makeExportData($phpExcel, $crmID, $crmName, $fromDate, $toDate)
{
    $styleCenter = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        )
    );

    $activeSheet = $phpExcel->setActiveSheetIndex(0);
    $activeSheet->setTitle(str_replace('/', '.', $fromDate) . '-' . str_replace('/', '.', $toDate));
//    $activeSheet->setTitle($crmName);

    $activeSheet->getDefaultStyle()->applyFromArray($styleCenter);

    // set columns width
    $activeSheet->getColumnDimension('A')->setWidth(35);
    $activeSheet->getColumnDimension('B')->setWidth(15);
    $activeSheet->getColumnDimension('C')->setWidth(15);
    $activeSheet->getColumnDimension('D')->setWidth(15);

    $activeSheet->setCellValue('A1', $crmName);
    $activeSheet->setCellValue('B1', 'Approved');
    $activeSheet->setCellValue('C1', 'Declined');
    $activeSheet->setCellValue('D1', 'Initial Rate %');
    cellColor($activeSheet, 'A1:D1', 'FFC7CE', '9C0006', true);

    $dbApi = DBApi::getInstance();
    $campaignList = $dbApi->getInitialReportById($crmID, $fromDate, $toDate);
    $campaignList = json_decode(str_replace("'", '"', $campaignList));
    $row = 1;
    for ($i = 0; $i < sizeof($campaignList); $i++)
    {
        $campaign = $campaignList[$i][0];

        $row++;
        $activeSheet->setCellValue('A'.$row, '('.$campaign[0].') '.$campaign[1]);
        cellColor($activeSheet, 'A'.$row, 'C6EFCE', '006100');
        $activeSheet->setCellValue('B'.$row, $campaign[2]);
        $activeSheet->setCellValue('C'.$row, $campaign[3]);
        $activeSheet->setCellValue('D'.$row, $campaign[4].'%');
        $color = $campaign[4] < 60.0 ? 'FFFF00': '00FF00';
        cellColor($activeSheet, 'D'.$row, $color);

        $affiliates = $campaignList[$i][1];
        for ($j = 0; $j < sizeof($affiliates); $j++) {
            $affiliate = $affiliates[$j][0];

            $row++;
            $activeSheet->setCellValue('A'.$row, '('.$affiliate[0].') '.$affiliate[1]);
            cellColor($activeSheet, 'A'.$row, 'FFEB9C', '9C6500');
            $activeSheet->setCellValue('B'.$row, $affiliate[2]);
            $activeSheet->setCellValue('C'.$row, $affiliate[3]);
            $activeSheet->setCellValue('D'.$row, $affiliate[4].'%');
            $color = $affiliate[4] < 60.0 ? 'FFFF00': '00FF00';
            cellColor($activeSheet, 'D'.$row, $color);

            $sub_affiliates = $affiliates[$j][1];
            for ($k = 0; $k < sizeof($sub_affiliates); $k++) {
                $sub_affiliate = $sub_affiliates[$k];

                $row++;
                $activeSheet->setCellValue('A'.$row, '('.$sub_affiliate[0].') '.$sub_affiliate[1]);
                cellColor($activeSheet, 'A'.$row, 'DDEBF7', '7F7F7F');
                $activeSheet->setCellValue('B'.$row, $sub_affiliate[2]);
                $activeSheet->setCellValue('C'.$row, $sub_affiliate[3]);
                $activeSheet->setCellValue('D'.$row, $sub_affiliate[4].'%');
                $color = $sub_affiliate[4] < 60.0 ? 'FFFF00': '00FF00';
                cellColor($activeSheet, 'D'.$row, $color);
            }
        }
    }

    return $phpExcel;
}

function cellColor($sheet, $cells, $background_color, $font_color='000000', $bold=false){
    $sheet->getStyle($cells)->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => $background_color)
            ),
            'font'  => array(
                'bold'  => $bold,
                'color' => array('rgb' => $font_color),
            )
        )
    );
}
