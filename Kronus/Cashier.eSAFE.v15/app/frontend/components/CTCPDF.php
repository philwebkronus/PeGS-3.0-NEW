<?php
require_once 'tcpdf/config/lang/eng.php';
require_once 'tcpdf/tcpdf.php';

/**
 * File Creation Date: Aug. 11, 2011
 * File Name: CTCPDF.php
 * Original Author: Bryan Salazar
 * Description: Extend tcpdf and add method
 */
class CTCPDF extends TCPDF {

   public $html='';

   /**
    *
    * @return CTCPDF 
    */
   public static function c_getInstance() {
      $pdf = new CTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true,
              'UTF-8', false);
      return $pdf;
   }

   public function c_removeHeadFoot() {
      $this->setPrintHeader(false);
      $this->setPrintFooter(false);
   }

   public function c_commonReportFormat() {
      $this->c_removeHeadFoot();
      $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
      $this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
      $this->SetFont('helvetica', '', 14, '', true);
      $this->AddPage();
   }

   public function c_getHtml() {
      return $this->html;
   }

   public function c_setHeader($header) {
      $this->html.= '<div style="text-align:center;"><h2><b>' . $header .'</b></h2></div>';
   }

   public function c_tableHeader($header=array()) {
      $this->html.= '<table><thead><tr>';
      foreach($header as $h) {
         $this->html.='<th><b>' . $h . '</b></th>';
      }
      $this->html.='</tr></thead>';
   }
   
   public function c_tableHeader2($header=array()) {
      $this->html.= '<style type="text/css">table{border:solid 1px #000;border-collapse:collapse;} td{border:solid 1px #000}</style><table ><thead><tr>';
      foreach($header as $h) {
         if(isset($h['align'])) {
             $this->html.='<th style="text-align:'.$h['align'].'"><b>' . $h['value'] . '</b></th>';
         } else {
             $this->html.='<th style="text-align:center"><b>' . $h['value'] . '</b></th>';
         }
         
      }
      $this->html.='</tr></thead>';
   }

   public function c_tableRow($row = array()) {
      $this->html.='<tr>';
      foreach($row as $cell) {
         $this->html.='<td>' . $cell . '</td>';
      }
      $this->html.='</tr>';
   }
   
   public function c_tableRow2($row = array()) {
      $this->html.='<tr>';
      foreach($row as $cell) {
         
         if(isset($cell['align'])) {
            $this->html.='<td style="text-align:'.$cell['align'].'">' . $cell['value'] . '</td>';  
         } else {
            $this->html.='<td style="text-align:left">' . $cell['value'] . '</td>'; 
         }
             
      }
      $this->html.='</tr>'; 
   }
   
   public function c_tableRow3($row=array()) {
      $this->html.='<tr>';
      foreach($row as $cell) {
         $align = 'text-align:left';
         if(isset($cell['align'])) {
             $align = 'text-align:'.$cell['align'];
         }
         $colspan = '';
         if(isset($cell['colspan'])) {
             $colspan = 'colspan="'.$cell['colspan'].'"';
         }
         $this->html.='<td '.$colspan.' style="'.$align.'">' . $cell['value'] . '</td>'; 
      }
      $this->html.='</tr>'; 
   }

   public function c_tableEnd() {
      $this->html.='</table>';
   }

   public function c_generatePDF($filename,$html=null) {
      if($html != null)
         $this->html = $html;
      
      $this->writeHTML($this->html, true, false, true, false, '');
      $this->Output($filename, 'D');
   }
}