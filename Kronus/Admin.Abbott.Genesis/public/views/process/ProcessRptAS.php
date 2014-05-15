<?php
/**
 * Created By: Edson L. Perez
 * Created On: October 28, 2011
 * Purpose: process for other requested reports (audit trail)
 */

include __DIR__."/../sys/class/ApplicationSupport.class.php";
require  __DIR__."/../sys/core/init.php";
require __DIR__.'/../sys/class/CTCPDF.php';
require_once __DIR__."/../sys/class/class.export_excel.php";

$aid = 0;
if(isset($_SESSION['sessionID']))
{
    $new_sessionid = $_SESSION['sessionID'];
}
else 
{
    $new_sessionid = '';
}
if(isset($_SESSION['accID']))
{
    $aid = $_SESSION['accID'];
}

$appsupport = new ApplicationSupport($_DBConnectionString[0]);
$connected = $appsupport->open();

$nopage = 0;
if($connected)
{
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $appsupport->getDate();
/********** SESSION CHECKING **********/
    $isexist=$appsupport->checksession($aid);
    if($isexist == 0)
    {
      session_destroy();
      $msg = "Not Connected";
      $appsupport->close();
      if($appsupport->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
    }    
    $isexistsession =$appsupport->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
      session_destroy();
      $msg = "Not Connected";
      $appsupport->close();
      header("Location: login.php?mess=".$msg);
    }
/********** END SESSION CHECKING **********/
    
    //checks if account was locked 
   $islocked = $appsupport->chkLoginAttempts($aid);
   if(isset($islocked['LoginAttempts'])){
      $loginattempts = $islocked['LoginAttempts'];
      if($loginattempts >= 3){
          $appsupport->deletesession($aid);
          session_destroy();
          $msg = "Not Connected";
          $appsupport->close();
          header("Location: login.php?mess=".$msg);
          exit;
      }
   }
   
    /***************************** EXPORTING EXCEL STARTS HERE *******************************/
   if(isset($_GET['excel']) == "MCFHistory")
   {
       
       $datefrom = $_GET['DateFrom'];
       $dateto = $_GET['DateTo'];
       $site= $_GET['Site'];
       $terminal = $_GET['Terminal'];
       $status = $_GET['Status'];
       
       $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
      //create the instance of the exportexcel format
        $excel_obj = new ExportExcel("$fn");
      //setting the values of the headers and data of the excel file
      //and these values comes from the other file which file shows the data
        $rheaders = array('Site','Terminal', 'Transaction Type', 'Amount', 'Service Name', 'Transaction Date', 'Status', 'User Mode', 'Fulfilled By' );
        $completeexcelvalues = array();
        
        $dateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($dateto)));
        
        $datefrom = $datefrom.' 06:00:00';
        $dateto = $dateto.' 06:00:00';
         
        $result = $appsupport->exportfulfillmenthistroy($site, $terminal, $status, $datefrom, $dateto);
        
        
        if(count($result) > 0)
        {                
           foreach($result as $vview)
           {    
                switch( $vview['Status'])
                {
                    case 0: $vstatus = 'Pending';break;
                    case 3: $vstatus = 'Fulfillment Approved';break;
                    case 4: $vstatus = 'Fulfillment Denied'; break;   
                    default: $vstatus = 'All'; break;
                } 

                switch($vview['TransactionType'])
                {
                   case 'D': $vtranstype = 'Deposit';break;
                   case 'W': $vtranstype = 'Withdrawal';break;
                   case 'R': $vtranstype = 'Reload';break;
                   case 'RD': $vtranstype = 'Redeposit';break;
                }    

                switch ($vview['UserMode']) {
                    case 0: $usermode = 'Terminal Based'; break;
                    case 1: $usermode = 'User Based'; break;
                    default:
                        break;
                }
                
                $name = $appsupport->getNamebyAid($vview['CreatedByAID']);

                $results2 = $appsupport->getsitecode($site);
                $results2 = $results2['SiteCode'];
                $results = preg_split("/$results2/", $vview['TerminalCode']);

                $sitecode = preg_split("/ICSA-/", $vview['SiteCode']);
              
              $excelvalues = array($sitecode[1],$results[1],$vtranstype, number_format($vview['Amount'],2),$vview['ServiceName'],$vview['TransactionDate'],$vstatus, $usermode,$name);   
              array_push($completeexcelvalues, $excelvalues);
           }
        }
        $vauditfuncID = 41; //export to excel
        $vtransdetails = "";
        $appsupport->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
        $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
   }
   elseif(isset($_GET['pdf']) == 'MCFHistory')
   {
      $completePDFArray = array();
      $datefrom = $_GET['DateFrom'];
      $dateto = $_GET['DateTo'];
      $site= $_GET['Site'];
      $terminal = $_GET['Terminal'];
      $status = $_GET['Status'];
      
      $datefrom = $datefrom.' 06:00:00';
      $dateto = $dateto.' 06:00:00';
      
      $queries = $appsupport->exportfulfillmenthistroy($site, $terminal, $status, $datefrom, $dateto);
      
      $pdf = CTCPDF::c_getInstance();
      $pdf->c_commonReportFormat();
      $pdf->c_setHeader('Manual Casino Fulfillment History');
      $pdf->html.='<div style="text-align:center;">As of ' . $datefrom . ' to '.$dateto.'</div>';
      $pdf->SetFontSize(10);
      $pdf->c_tableHeader(array('Site','Terminal', 'Transaction Type', 'Amount', 'Service Name', 'Transaction Date', 'Status', 'User Mode', 'Fulfilled By' ));
      
      if(count($queries) > 0)
      {
          foreach($queries as $vview)
          {
             switch( $vview['Status'])
                {
                    case 0: $vstatus = 'Pending';break;
                    case 3: $vstatus = 'Fulfillment Approved';break;
                    case 4: $vstatus = 'Fulfillment Denied'; break;   
                    default: $vstatus = 'All'; break;
                } 

                switch($vview['TransactionType'])
                {
                   case 'D': $vtranstype = 'Deposit';break;
                   case 'W': $vtranstype = 'Withdrawal';break;
                   case 'R': $vtranstype = 'Reload';break;
                   case 'RD': $vtranstype = 'Redeposit';break;
                }    

                switch ($vview['UserMode']) {
                    case 0: $usermode = 'Terminal Based'; break;
                    case 1: $usermode = 'User Based'; break;
                    default:
                        break;
                }
                
                $name = $appsupport->getNamebyAid($vview['CreatedByAID']);
                
                $results2 = $appsupport->getsitecode($site);
                $results2 = $results2['SiteCode'];
                $results = preg_split("/$results2/", $vview['TerminalCode']);

                $sitecode = preg_split("/ICSA-/", $vview['SiteCode']);
              
             $pdf->c_tableRow(array($sitecode[1],$results[1],$vtranstype, number_format($vview['Amount'],2),$vview['ServiceName'],$vview['TransactionDate'],$vstatus, $usermode, $name));
          }
      }
      else
      {
          $pdf->html.='<div style="text-align:center;">No Results Found</div>';
      }
      
      $pdf->c_tableEnd();
      $vauditfuncID = 40; //export to pdf
      $vtransdetails = "";
      $appsupport->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
      $pdf->c_generatePDF('MCFHistory_from_'."$datefrom".'_to_'."$dateto".'.pdf'); 
   }
}
else
{
    $msg = "Not Connected";  
    header("Location: login.php?mess=".$msg);    
}
?>
