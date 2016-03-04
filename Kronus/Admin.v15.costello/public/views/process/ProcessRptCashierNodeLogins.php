<?php
/*
 * Created By: Mark Nicolas Atangan
 * Created On: September 2, 2015
 * Purpose: process for other requested reports (Cashier Node Logins)    
 */
require __DIR__."/../sys/class/RptCashierNodeLogins.class.php";
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

$orptothers = new RptCashierNodeLogins($_DBConnectionString[0]);
$connected = $orptothers->open();

$nopage = 0;
if($connected)
{
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $orptothers->getDate();
/********** SESSION CHECKING **********/
    $isexist=$orptothers->checksession($aid);
    if($isexist == 0)
    {
      session_destroy();
      $msg = "Not Connected";
      $orptothers->close();
      if($orptothers->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
    }    
    $isexistsession =$orptothers->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
      session_destroy();
      $msg = "Not Connected";
      $orptothers->close();
      header("Location: login.php?mess=".$msg);
    }
/********** END SESSION CHECKING **********/
    
    //checks if account was locked 
   $islocked = $orptothers->chkLoginAttempts($aid);
   if(isset($islocked['LoginAttempts'])){
      $loginattempts = $islocked['LoginAttempts'];
      if($loginattempts >= 3){
          $orptothers->deletesession($aid);
          session_destroy();
          $msg = "Not Connected";
          $orptothers->close();
          header("Location: login.php?mess=".$msg);
          exit;
      }
   }

    if(isset($_POST['rptpage']))
    {
        $vrptpage = $_POST['rptpage'];
        switch ($vrptpage)
        {
            case 'CashierNodeLogins':
                $vdatefrom = $_POST['strDate'];
                $vdateto = $_POST['endDate'];
//                $limit = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($vdatefrom)));
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid
                $search = $_POST['_search'];
                $direction = $_POST['sord'];
                if(isset ($_POST['searchField']) && isset($_POST['searchString']))
                {
                   $searchField = $_POST['searchField'];
                   $searchString = $_POST['searchString'];
                }
                else {
                    //count the cashier node logins for pagination and record count
                 $rctraudit = $orptothers->countCashierNodeLogins($vdatefrom, $vdateto);
            $count = $rctraudit['cashierLoginCount'];
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "SiteCode";
                }
   
                if($count > 0 ) {
                      $total_pages = ceil($count/$limit);
                } else {
                      $total_pages = 0;
                }

                if ($page > $total_pages)
                {
                  $page = $total_pages;
                  $start = $limit * $page - $limit;           
                }
                if($page == 0)
                {
                  $start = 0;
                }
                else
                {
                  $start = $limit * $page - $limit;   
                }
                $limit = (int)$limit;
               
                $result = $orptothers->viewCashierNodeLogins($vdatefrom, $vdateto, $start, $limit, $sort, $direction);
                if(count($result) > 0)
                {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   foreach($result as $vview) 
                   {
                       $response->rows[$i]['cell']=array($vview['SiteCode'],$vview['TransDetails']);  
                      $i++;
                   }
        $vauditfuncID = 106; //Viewing of Report
        $vtransdetails = "Report generated Cashier Node Login";
        $orptothers->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
               }
                
               else
               {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   $msg = "Cashier Node Logins: No returned result";
                   $response->msg = $msg;
        $vauditfuncID = 106; //No Returned Result
        $vtransdetails = "Report Cashier Node Login: No Returned result";
        $orptothers->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
               }
               }
               
                echo json_encode($response);
                unset($result);
               $orptothers->close();
               exit;
            break;
        }
    }
    /***************************** EXPORTING EXCEL STARTS HERE *******************************/
   elseif(isset($_GET['excel']) == "CashierNodeLogins")
   {
       $date = preg_replace('/\s+/', '_', $_GET['fn']);
       $fn = "CashierNodeLogins_for_".$date.".xls"; //this will be the filename of the excel file
      //create the instance of the exportexcel format
        $excel_obj = new ExportExcel("$fn");
      //setting the values of the headers and data of the excel file
      //and these values comes from the other file which file shows the data
        $rheaders = array('SiteCode','Transaction Details');
        $completeexcelvalues = array();
        $vdatefrom = $_GET['DateFrom'];
        $vdateto = $_GET['DateTo'];
        $direction = $_GET['sord'];
        $result = $orptothers->viewCashierNodeLogins($vdatefrom, $vdateto, $start=null, $limit=null, $sort=null, $direction);
        if(count($result) > 0)
        {                
           foreach($result as $vview)
           {    
              $rtransdetails = $vview['TransDetails'];
              $excelvalues = array($vview['SiteCode'], $vview['TransDetails']);   
              array_push($completeexcelvalues, $excelvalues);
           }
        }
        $vauditfuncID = 41; //export to excel
        $vtransdetails = "Export to Excel";
        $orptothers->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
        $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
   }
       /***************************** EXPORTING PDF STARTS HERE *******************************/
   elseif(isset($_GET['pdf']) == 'CashierNodeLogins')
   {
      $completePDFArray = array();
      $vdatefrom = $_GET['DateFrom'];
      $vdateto = $_GET['DateTo'];
      $direction = $_GET['sord'];
      $queries = $orptothers->viewCashierNodeLogins($vdatefrom, $vdateto, $start=null, $limit=null, $sort=null, $direction);
      $pdf = CTCPDF::c_getInstance();
      $pdf->c_commonReportFormat();
      $pdf->c_setHeader('Cashier Node Logins');
      $pdf->html.='<div style="text-align:center;">As of ' . $vdatefrom . '</div>';
      $pdf->SetFontSize(10);
      $pdf->c_tableHeader(array('Site Code', 'Cashier Instance/ Node Logins'));
      
      if(count($queries) > 1)
      {
          foreach($queries as $row)
          {
             $rtransdetails = $row['TransDetails'];
             $pdf->c_tableRow(array($row['SiteCode'],$row['TransDetails']));
          }
      }
      else
      {
          $pdf->html.='<div style="text-align:center;">No Results Found</div>';
      }
      
      $pdf->c_tableEnd();
      $vauditfuncID = 40; //export to pdf
      $vtransdetails = "Export to PDF";
      $orptothers->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
      $pdf->c_generatePDF('CashierNodeLogins'.$vdatefrom.'.pdf'); 
   }
}
else
{
    $msg = "Not Connected";  
    header("Location: login.php?mess=".$msg);    
}
?>
