<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 2018-11-16
 * Time: 11:10 AM
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

$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];
$affiliate = json_decode($_GET['data'], true);

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
    die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once '../lib/phpexcel/Classes/PHPExcel.php';


$fileName = 'billing_'.$affiliate['affiliate_name'].'_'.str_replace('/', '.', $fromDate).'-'.str_replace('/', '.', $toDate).'.xls';

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

$objWriter = PHPExcel_IOFactory::createWriter(makeExportData($objPHPExcel, $affiliate, $fromDate, $toDate), 'Excel5');
$objWriter->save('php://output');


exit;

function makeExportData($phpExcel, $affiliate, $fromDate, $toDate)
{
    $activeSheet = $phpExcel->setActiveSheetIndex(0);
    $activeSheet->setTitle(str_replace('/', '.', $fromDate) . '-' . str_replace('/', '.', $toDate));

    // set columns width
    $activeSheet->getColumnDimension('A')->setWidth(18);
    $activeSheet->getColumnDimension('B')->setWidth(25);
    $activeSheet->getColumnDimension('C')->setWidth(5);
    $activeSheet->getColumnDimension('D')->setWidth(30);
    $activeSheet->getColumnDimension('G')->setWidth(14);

    $activeSheet->setCellValue('A1', 'Affiliate');
    $activeSheet->setCellValue('A2', 'Affiliate ID');
    $activeSheet->setCellValue('A3', 'Week Of');
    $activeSheet->setCellValue('A4', 'Total To Invoice');
    cellColor($activeSheet, 'A1:A4', '5B9BD5', 'FFFFFF', true, $alignment=PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

    $activeSheet->setCellValue('B1', $affiliate['affiliate_name']);
    $activeSheet->setCellValue('B2', $affiliate['afid']);
    $activeSheet->setCellValue('B3', $affiliate['weekof']);
    $activeSheet->setCellValue('B4', $affiliate['tti']);
    $activeSheet->getStyle('B1:B4')->applyFromArray(array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)));

    $activeSheet->setCellValue('D1', 'Offer');
    $activeSheet->setCellValue('E1', 'Sales');
    $activeSheet->setCellValue('F1', 'CPA');
    $activeSheet->setCellValue('G1', 'Total');
    cellColor($activeSheet, 'D1:G1', 'C6EFCE', '006100', true);

    $row = 1;
    $offers = $affiliate['offers'];
    for ($i = 0; $i < sizeof($offers); $i++) {
        $offer = $offers[$i];

        $row++;
        $activeSheet->setCellValue('D'.$row, $offer['offer']);
        $activeSheet->getStyle('D'.$row)->applyFromArray(
            array(
                'font'  => array(
                    'bold'  => true,
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            )
        );
        $activeSheet->setCellValue('E'.$row, $offer['sales'] ? $offer['sales'] : '');
        $activeSheet->getStyle('D'.$row)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            )
        );
        $activeSheet->setCellValue('F'.$row, $offer['cpa']);
        $activeSheet->setCellValue('G'.$row, $offer['total']);
    }

    cellColor($activeSheet, 'A1', '5B9BD5', 'FFFFFF', true, $alignment=PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

    return $phpExcel;
}

function cellColor($sheet, $cells, $background_color, $font_color='000000', $bold=false, $alignment=PHPExcel_Style_Alignment::HORIZONTAL_CENTER){
    $sheet->getStyle($cells)->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => $background_color)
            ),
            'font'  => array(
                'bold'  => $bold,
                'color' => array('rgb' => $font_color),
            ),
            'alignment' => array(
                'horizontal' => $alignment,
            )
        )
    );
}
