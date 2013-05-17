<?php
/**
 * Created By: Edson L. Perez
 * Created On: October 28, 2011
 * Purpose: process for other requested reports (audit trail)
 */

include __DIR__."/../sys/class/RptOthers.class.php";
require  __DIR__."/../sys/core/init.php";
require __DIR__.'/../sys/class/CTCPDF.php';
require_once __DIR__."/../sys/class/class.export_excel.php";

$aid = 0;
if(isset($_SESSION['sessionID']))
{
    $new_sessionid = $_SESSION['sessionID'];
}
if(isset($_SESSION['accID']))
{
    $aid = $_SESSION['accID'];
}

$orptothers = new RptOthers($_DBConnectionString[0]);
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
            case 'AuditTrail':
                $vdatefrom = $_POST['strDate'];
                //$vdateto = $_POST['endDate'];
                $vdateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($vdatefrom)));
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid
                $search = $_POST['_search'];
                $direction = $_POST['sord'];
                if(isset ($_POST['searchField']) && isset($_POST['searchString']))
                {
                   $searchField = $_POST['searchField'];
                   $searchString = $_POST['searchString'];
                }
                
                //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "AID";
                }
                
                switch($_SESSION['acctype'])
                {
                    //validate if account type is operator, then get its child accounts
                    case 2:
                        $vacctypes = array('2', '3', '4', '10');
                    break;
                    //validate if account type is supervisor, then get its child accounts
                    case 3:
                        $vacctypes = array('3', '4');
                    break;   
                    default:
                        $vacctypes = array($_SESSION['acctype']);
                    break;
                }
                
                $vchildacc = $orptothers->getchildaccounts($aid);
                
                $rctraudit = $orptothers->countaudittrail($vdatefrom, $vdateto, $vacctypes, $vchildacc);
                $count = $rctraudit['ctraudit'];
                
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

                $result = $orptothers->viewaudittrail($vdatefrom, $vdateto, $vacctypes, $vchildacc, $start, $limit, $sort, $direction);
                if(count($result) > 0)
                {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   foreach($result as $vview) 
                   {
                      $vtransdetails = $vview['AuditFunctionName']." ".$vview['TransDetails'];
                      $response->rows[$i]['id'] = $vview['AID'];
                      $response->rows[$i]['cell']=array($vview['UserName'], $vtransdetails, $vview['TransDateTime'],  
                                                        $vview['RemoteIP']);  
                      $i++;
                   }
               }
               else
               {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   $msg = "Audit Trail: No returned result";
                   $response->msg = $msg;
               }
               echo json_encode($response);
               unset($result);
               $orptothers->close();
               exit;
            break;
        }
    }
    /***************************** EXPORTING EXCEL STARTS HERE *******************************/
   elseif(isset($_GET['excel']) == "AuditTrail")
   {
       $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
      //create the instance of the exportexcel format
        $excel_obj = new ExportExcel("$fn");
      //setting the values of the headers and data of the excel file
      //and these values comes from the other file which file shows the data
        $rheaders = array('User Name','Transaction Details', 'Transaction Date', 'IP Address');
        $completeexcelvalues = array();
        $vdatefrom = $_GET['DateFrom'];
        $vdateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($vdatefrom)));
        
        //validate if account type is operator, then get its child accounts
        switch($_SESSION['acctype'])
        {
            case 2:
                $vacctype = array('2', '3', '4', '10');
            break;
            case 3:
                $vacctype = array('3', '4');
            break;
            default:
                $vacctype = array($_SESSION['acctype']);
            break;
        }
        
        $vchildacc = $orptothers->getchildaccounts($aid);
         
        $result = $orptothers->viewaudittrail($vdatefrom, $vdateto, $vacctype, $vchildacc, $start=null, $limit=null, $sort=null, $direction=null);
        if(count($result) > 0)
        {                
           foreach($result as $vview)
           {    
              $rtransdetails = $vview['AuditFunctionName']." ".$vview['TransDetails'];
              $excelvalues = array($vview['UserName'], $rtransdetails, $vview['TransDateTime'], $vview['RemoteIP']);   
              array_push($completeexcelvalues, $excelvalues);
           }
        }
        $vauditfuncID = 41; //export to excel
        $vtransdetails = "";
        $orptothers->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
        $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
   }
   elseif(isset($_GET['pdf']) == 'AuditTrail')
   {
      $completePDFArray = array();
      $vdatefrom = $_GET['DateFrom'];
      $vdateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($vdatefrom)));
        
      //validate if account type is operator, then get its child accounts
      switch($_SESSION['acctype'])
      {
        case 2:
            $vacctype = array('2', '3', '4', '10');
        break;
        case 3:
            $vacctype = array('3', '4');
        break;
        default:
            $vacctype = array($_SESSION['acctype']);
        break;
      }
      
      $vchildacc = $orptothers->getchildaccounts($aid);
      $queries = $orptothers->viewaudittrail($vdatefrom, $vdateto, $vacctype, $vchildacc, $start=null, $limit=null, $sort=null, $direction=null);
      $pdf = CTCPDF::c_getInstance();
      $pdf->c_commonReportFormat();
      $pdf->c_setHeader('Audit Trail');
      $pdf->html.='<div style="text-align:center;">As of ' . $vdatefrom . '</div>';
      $pdf->SetFontSize(10);
      $pdf->c_tableHeader(array('User Name','Transaction Details', 'Transaction Date', 'IP Address'));
      
      if(count($queries) > 1)
      {
          foreach($queries as $row)
          {
             $rtransdetails = $row['AuditFunctionName']." ".$row['TransDetails'];
             $pdf->c_tableRow(array($row['UserName'], $rtransdetails, $row['TransDateTime'], $row['RemoteIP']));
          }
      }
      else
      {
          $pdf->html.='<div style="text-align:center;">No Results Found</div>';
      }
      
      $pdf->c_tableEnd();
      $vauditfuncID = 40; //export to pdf
      $vtransdetails = "";
      $orptothers->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
      $pdf->c_generatePDF('AuditTrail.pdf'); 
   }
}
else
{
    $msg = "Not Connected";  
    header("Location: login.php?mess=".$msg);    
}
?>
