<?php

include "../../sys/class/RptPegsOps.class.php";
require '../../sys/core/init.php';
require '../../sys/class/CTCPDF.php';
require_once "../../sys/class/class.export_excel.php";

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

$orptsdemog = new RptPegsOps($_DBConnectionString[0]);
$orptsdemogected = $orptsdemog->open();

$nopage = 0;
if($orptsdemogected)
{
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $orptsdemog->getDate();
/**************** SESSION CHECKING ****************/
    $isexist=$orptsdemog->checksession($aid);
    if($isexist == 0)
    {
      session_destroy();
      $msg = "Not Connected";
      $orptsdemog->close();
      if($orptsdemog->isAjaxRequest())
       {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
       }
      header("Location: ../views/login.php?mess=".$msg);
    }    
    $isexistsession =$orptsdemog->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
      session_destroy();
      $msg = "Not Connected";
      $orptsdemog->close();
      header("Location: ../views/login.php?mess=".$msg);
    }
/****************END SESSION CHECKING ***************/
    
    //checks if account was locked 
   $islocked = $orptsdemog->chkLoginAttempts($aid);
   if(isset($islocked['LoginAttempts'])){
      $loginattempts = $islocked['LoginAttempts'];
      if($loginattempts >= 3){
          $orptsdemog->deletesession($aid);
          session_destroy();
          $msg = "Not Connected";
          $orptsdemog->close();
          header("Location: login.php?mess=".$msg);
          exit;
      }
   }
   
    if(isset ($_POST['sendIslandID']))
    {
      $vislandID = $_POST['sendIslandID'];
      $resultregions = array();
      $resultregions = $orptsdemog->showregions($vislandID);
      echo json_encode($resultregions);
      unset($resultregions);
      $orptsdemog->close();
      exit;
    }

    elseif(isset($_POST['sendRegionID']))
    {
      $vregionID = $_POST['sendRegionID'];
      $resultprovinces = array();
      $resultprovinces = $orptsdemog->showprovinces($vregionID);
      echo json_encode($resultprovinces);
      unset($resultprovinces);
      $orptsdemog->close();
      exit;
    }

    elseif(isset($_POST['sendProvID']))
    {
      $vprovID = $_POST['sendProvID'];
      $resultcities = array();
      $resultcities = $orptsdemog->showcities($vprovID);
      echo json_encode($resultcities);
      unset($resultcities);
      $orptsdemog->close();
      exit;
    }

    elseif(isset($_POST['sendCityID']))
    {
      $vcityID = $_POST['sendCityID'];
      $resultbrgy = array();
      $resultbrgy = $orptsdemog->showbrgy($vcityID);
      echo json_encode($resultbrgy);
      unset($resultbrgy);
      exit;
   }
   
   elseif(isset($_GET['pdf']) == 'PDFDemographics')
   {
      $completePDFArray = array();
      $vall = 1;
            if(isset ($_GET['island']))
            {
              $vall = 1;  
              $vislandID = $_GET['island'];
              $vregionID = 0;
              $vprovinceID = 0;
              $vcityID = 0;
            }

            elseif(isset($_GET['region']))
            {
              $vall = 1;  
              $vislandID = 0;
              $vregionID = $_GET['region']; 
              $vprovinceID = 0;
              $vcityID = 0;
            }

            elseif(isset($_GET['province']))
            {
              $vall = 1;  
              $vislandID = 0;  
              $vregionID = 0;  
              $vprovinceID = $_GET['province'];
              $vcityID = 0;
            }

            elseif(isset ($_GET['city']))
            {
              $vall = 1;  
              $vislandID = 0;  
              $vregionID = 0;   
              $vprovinceID = 0;
              $vcityID = $_GET['city'];
            }

            elseif(isset($_GET['all']))
            {
              $vall = $_GET['all'];  
              $vislandID = 0;  
              $vregionID = 0;
              $vprovinceID = 0;
              $vcityID = 0;
            }
      $queries = $orptsdemog->exportdemographicpdf($vall, $vislandID, $vregionID, $vprovinceID, $vcityID);
      $pdf = CTCPDF::c_getInstance();
      $pdf->c_commonReportFormat();
      $pdf->c_setHeader('Site Demographics');
      $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
              date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
      $pdf->SetFontSize(10);
      $pdf->c_tableHeader(array('Site / PEGS Name','Site / PEGS Code', 'POS Account'));

      foreach($queries as $row)
      {
          if($row['SiteID'] <> 1)
          {
             $rsitecode = substr($row['SiteCode'], strlen($terminalcode));
             $pdf->c_tableRow(array($row['SiteName'], $rsitecode, $row['POS']));
          }
      }
      
      $pdf->c_tableEnd();
      $vauditfuncID = 40; //export to pdf
      $vtransdetails = "Site Demographics";
      $orptsdemog->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
      $pdf->c_generatePDF('SiteDemographics.pdf'); 
   }
   
   //display values to jqgrid
   elseif(isset ($_POST['demogpage']) == 'SiteDemographics')
   {
            $page = $_POST['page']; // get the requested page
            $limit = $_POST['rows']; // get how many rows we want to have into the grid
            $vall = 1;
            if(isset ($_GET['island']))
            {
              $vall = 1;  
              $vislandID = $_GET['island'];
              $vregionID = 0;
              $vprovinceID = 0;
              $vcityID = 0;
            }

            elseif(isset($_GET['region']))
            {
              $vall = 1;  
              $vislandID = 0;
              $vregionID = $_GET['region']; 
              $vprovinceID = 0;
              $vcityID = 0;
            }

            elseif(isset($_GET['province']))
            {
              $vall = 1;  
              $vislandID = 0;  
              $vregionID = 0;  
              $vprovinceID = $_GET['province'];
              $vcityID = 0;
            }

            elseif(isset ($_GET['city']))
            {
              $vall = 1;  
              $vislandID = 0;  
              $vregionID = 0;   
              $vprovinceID = 0;
              $vcityID = $_GET['city'];
            }

            elseif(isset($_GET['all']))
            {
              $vall = $_GET['all'];  
              $vislandID = 0;  
              $vregionID = 0;
              $vprovinceID = 0;
              $vcityID = 0;
            }
          
            $resultcount = array();
            $resultcount = $orptsdemog->countsitedemographic($vall, $vislandID, $vregionID, $vprovinceID, $vcityID);
            $count = $resultcount['count'];

            //this is for computing the limit
            if($count > 0 ) {
              $total_pages = ceil($count/$limit);
            } else {
              $total_pages = 0;
            }
            if ($page > $total_pages)
            {
              $page = $total_pages;
            }

            $start = $limit * $page - $limit;
            $limit = (int)$limit;
            
            //this is for proper rendering of results, if count is 0 $result is also must be 0
            if($count > 0)
            {
               $result = $orptsdemog->getsitedemographic($vall, $vislandID, $vregionID, $vprovinceID, $vcityID, $start, $limit);
            }
            else{
               $result = 0; 
            }
            if($result > 0)
            {
               $i = 0;
               $responce->page = $page;
               $responce->total = $total_pages;
               $responce->records = $count;
               foreach($result as $vview) 
               {
                    //remove SiteHO

                        $rsitecode = substr($vview['SiteCode'], strlen($terminalcode));
                        $responce->rows[$i]['cell']=array($vview['SiteName'], $rsitecode, $vview['POS']);
                        $i++;

               }
           }
           else
           {
               $i = 0;
               $responce->page = $page;
               $responce->total = $total_pages;
               $responce->records = $count;
               $msg = "Site Demographics: No returned result";
               $responce->msg = $msg;
           }
           echo json_encode($responce);
           unset($result);
           $orptsdemog->close();
           exit;
   }
   elseif(isset($_GET['excel']))
   {
       $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
       $vall = 1;
       if(isset ($_GET['island']))
       {
              $vall = 1;  
              $vislandID = $_GET['island'];
              $vregionID = 0;
              $vprovinceID = 0;
              $vcityID = 0;
       }
       elseif(isset($_GET['region']))
       {
              $vall = 1;  
              $vislandID = 0;
              $vregionID = $_GET['region']; 
              $vprovinceID = 0;
              $vcityID = 0;
       }
       elseif(isset($_GET['province']))
       {
              $vall = 1;  
              $vislandID = 0;  
              $vregionID = 0;  
              $vprovinceID = $_GET['province'];
              $vcityID = 0;
       }
       elseif(isset ($_GET['city']))
       {
              $vall = 1;  
              $vislandID = 0;  
              $vregionID = 0;   
              $vprovinceID = 0;
              $vcityID = $_GET['city'];
       }
       elseif(isset($_GET['all']))
       {
              $vall = $_GET['all'];  
              $vislandID = 0;  
              $vregionID = 0;
              $vprovinceID = 0;
              $vcityID = 0;
       }
      

      //create the instance of the exportexcel format
        $excel_obj = new ExportExcel("$fn");
      //setting the values of the headers and data of the excel file
      //and these values comes from the other file which file shows the data
        $rheaders = array('Site / PEGS Name','Site / PEGS Code','POS Account');
        $completeexcelvalues = array();
        $result = $orptsdemog->exportdemographicpdf($vall, $vislandID, $vregionID, $vprovinceID, $vcityID);
        if(count($result) > 0)
        {                
           foreach($result as $vview)
           {    
              if($vview['SiteID'] <> 1)
              {
                 $rsitecode = substr($vview['SiteCode'], strlen($terminalcode));
                 $excelvalues = array($vview['SiteName'], $rsitecode, $vview['POS']);   
                 array_push($completeexcelvalues, $excelvalues);
              }
           }
        }
        $vauditfuncID = 41; //export to excel
        $vtransdetails = "Site Demographics";
        $orptsdemog->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
        $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
        $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
   }
   
   else{
      $resultislands = array();
      $resultislands = $orptsdemog->showislands();
      $_SESSION['resislands'] = $resultislands;
   }
}
else
{
    $msg = "Not Connected";    
    header("Location: ../views/login.php?mess=".$msg);
}
?>
