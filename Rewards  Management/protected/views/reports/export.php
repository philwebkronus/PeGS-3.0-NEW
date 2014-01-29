<?php
/**
 * Export to PDF and Excel
 * Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * Sep-4-13
 * Philweb Corp.
 *
 */
//include FPDF Class
Yii::import('application.extensions.*');
require_once('fpdf/fpdf.php');

$audittrailmodel = new AuditTrailModel();

$type       = Yii::app()->request->getParam('type');
$title      = Yii::app()->request->getParam('title');
$datefrom   = Yii::app()->request->getParam('datefrom');
$dateto     = Yii::app()->request->getParam('dateto');
$coverage   = Yii::app()->request->getParam('coverage');
$rewardtype = Yii::app()->request->getParam('rewardtype');
$filter     = Yii::app()->request->getParam('filter');
$particular = Yii::app()->request->getParam('particular');
$player     = Yii::app()->request->getParam('playerclass');
$header     = Yii::app()->request->getParam('header');
$values     = Yii::app()->request->getParam('values');
//Export Graphs to PDF
if ($type == "pdf")
{

    class PDF extends FPDF
    {
        // Page header
        function Header()
        {
            // Arial bold 15
            $this->SetFont('Arial','B',15);
            // Move to the right
            $this->Cell(80);
            // Title
            // Line break
            $this->Ln(20);
        }

        // Page footer
        function Footer()
        {
            // Position at 1.5 cm from bottom
            $this->SetY(-15);
            // Arial italic 8
            $this->SetFont('Arial','I',8);
            // Page number
            $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
        }
    }
    //Add graphs in the PDF File.
    $pdf = new PDF('P','mm','A4');
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(40,1, $title); //Title
    $pdf->Ln(10);
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(10,1, $coverage. " Statistics");
    $pdf->Ln(10);
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(10,1, "From: ".$datefrom."   To: ".$dateto);
    $pdf->Ln(10);
    $pdf->Image(Yii::app()->basePath."/../images/graph.png");
    //Reward Type
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(45,5, 'Reward Type: ');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(11,5, $rewardtype);
    $pdf->Ln(10);
    //Filter By
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(45,5, 'Filter By: ');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(13,5, $filter);
    $pdf->Ln(10);
    //Particular
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(45,5, 'Particular: ');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(15,5, $particular);
    $pdf->Ln(10);
    //Player Classification
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(45,5, 'Player Classification: ');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(17,5, $player);
    $pdf->Ln(10);

    $pdf->SetDisplayMode('real','default');
    //Output the PDF
    $pdf->Output("","D");
    //Log to Audit Trail
    $audittrailmodel->logEvent(RefAuditFunctionsModel::EXPORT_TO_PDF, "Export to PDF", array('SessionID' => Yii::app()->session['SessionID'],
                                                     'AID' => Yii::app()->session['AID']));
    //Force download the pdf file
    $file_name = 'exports/export-to-pdf.pdf';
    header("Content-disposition: attachment; filename=\"$title.pdf\"");
    header('Content-Type: application/pdf');
    header("Content-Transfer-Encoding: Binary");
    header('Content-Length: ' . filesize($file_name));
    
    readfile($file_name);
    exit();
}
//Export Data to Excel
else if ($type == "excel")
{
    //Log to Audit Trail
    $audittrailmodel->logEvent(RefAuditFunctionsModel::EXPORT_TO_EXCEL, "Export to Excel", array('SessionID' => Yii::app()->session['SessionID'],
                                                     'AID' => Yii::app()->session['AID']));
    //Force Download to Excel File
    $title = str_replace(" ", "_", $title);
    $excel_obj = new ExportExcel("$title.xls");
    $excel_obj->setHeadersAndValues($header, $values);
    unset($header);
    unset($values);
    $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
    exit();
}
?>
