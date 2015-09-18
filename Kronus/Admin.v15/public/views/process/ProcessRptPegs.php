<?php

/*
 * Created By: Edson L. Perez
 * Date Created: September 30, 2011
 * Purpose: process for PEGS Operations reports (Site Listing, Terminal Listing, Site Demographics, Users Listing)
 */

include __DIR__."/../sys/class/RptPegsOps.class.php";
require __DIR__.'/../sys/core/init.php';
require __DIR__.'/../sys/class/CTCPDF.php';
require __DIR__."/../sys/class/class.export_excel.php";

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

$orptpegs = new RptPegsOps($_DBConnectionString[0]);
$connected = $orptpegs->open();

$nopage = 0;
if($connected)
{
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $orptpegs->getDate();
/********** SESSION CHECKING **********/
    $isexist=$orptpegs->checksession($aid);
    if($isexist == 0)
    {
      session_destroy();
      $msg = "Not Connected";
      $orptpegs->close();
      if($orptpegs->isAjaxRequest())
       {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
       }
      header("Location: login.php?mess=".$msg);
    }    
    $isexistsession =$orptpegs->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
      session_destroy();
      $msg = "Not Connected";
      $orptpegs->close();
      header("Location: login.php?mess=".$msg);
    }
/********** END SESSION CHECKING **********/
    
    //checks if account was locked 
   $islocked = $orptpegs->chkLoginAttempts($aid);
   if(isset($islocked['LoginAttempts'])){
      $loginattempts = $islocked['LoginAttempts'];
      if($loginattempts >= 3){
          $orptpegs->deletesession($aid);
          session_destroy();
          $msg = "Not Connected";
          $orptpegs->close();
          header("Location: login.php?mess=".$msg);
          exit;
      }
   }
    
    //this catches post method requests (jQgrid)
    if(isset($_POST['rptpage']))
    {
        $vrptpage = $_POST['rptpage'];
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $search = $_POST['_search'];
        $direction = $_POST['sord'];
        if(isset ($_POST['searchField']) && isset($_POST['searchString']))
        {
           $searchField = $_POST['searchField'];
           $searchString = $_POST['searchString'];
           echo $searchField;
           echo $searchString;
        }
        switch ($vrptpage)
        {
            //for listing of all sites on grid
            case 'RptSiteListing':
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
                
                //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "SiteID";
                }
                
                $rctrsite = $orptpegs->countsitelisting($vall, $vislandID, $vregionID, $vprovinceID, $vcityID);
                $count = $rctrsite['count'];
                
                //this is for computing the limit
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

                else{
                  $start = $limit * $page - $limit;   
                }

                $limit = (int)$limit;

                //this is for proper rendering of results, if count is 0 $result is also must be 0
                if($count > 0)
                {
                   $result = $orptpegs->viewsitelisting($vall, $vislandID, $vregionID, $vprovinceID, $vcityID, $start, $limit, $sort, $direction);
                }
                else{
                   $result = 0; 
                }
                if($result > 0)
                {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   foreach($result as $vview) 
                   {
                        $rsiteID = $vview['SiteID'];
                        $vstatus = $orptpegs->refsitestatusname($vview['Status']);
                        $isterminalcode = strstr($vview['SiteCode'], $terminalcode);
                        if($isterminalcode == false)
                        {
                            $vcode= $vview['SiteCode'];
                        }
                        else{
                            $vcode = substr($vview['SiteCode'], strlen($terminalcode));
                        }
                        
                        //this counts terminal/s per site
                        $rterminals = $orptpegs->countterminalbysite($rsiteID);
                        $allterminal = $rterminals['ctrterminal'];
                        
                        if($allterminal > 0)
                        {
                            $response->rows[$i]['id'] = $rsiteID;
                            $response->rows[$i]['cell']=array($vview['POS'], $vcode, $vview['SiteName'],  
                            $vview['IslandName'], $vview['RegionName'], $vview['ProvinceName'], 
                            $vview['CityName'], $vview['BarangayName'], $vview['SiteAddress'], $vview['ContactNumber'], $vstatus, "<a href=\"#\" onclick=\"window.location.href='process/ProcessRptPegs.php?siteid=$rsiteID'+'&getpage='+'TerminalListing';\" class=\"sitecode\" style=\"text-decoration:underline\">$allterminal</a>");
                        }
                        else 
                        {
                            $response->rows[$i]['id'] = $rsiteID;
                            $response->rows[$i]['cell']=array($vview['POS'], $vcode, $vview['SiteName'],  
                            $vview['IslandName'], $vview['RegionName'], $vview['ProvinceName'], 
                            $vview['CityName'], $vview['BarangayName'], $vview['SiteAddress'], $vview['ContactNumber'], $vstatus, $allterminal);
                        }
                        
                        $i++;
                   }
               }
               else
               {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   $msg = "SIte Listing: No returned result";
                   $response->msg = $msg;
               }
               echo json_encode($response);
               $orptpegs->close();
               exit;
            break;
            //(Terminal Listing Page) this will display terminal code, status, providers and OC MG accounts 
            case 'GetSiteDetails':
                //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                   $sort = "tid";
                }
                $vsiteID = $_POST['siteID'];
                $rsitecode = $orptpegs->getsitecode($vsiteID); //get the sitecode first
                $vpowner = $orptpegs->getownerbysite($vsiteID); //get site owner
                
                foreach ($vpowner as $row)
                {
                    $siteowner = $row['UserName'];
                }
  
                //validate if no owner
                if(count($vpowner) > 0)
                { 
                      $rctrsite = $orptpegs->countterminallisting($vsiteID);
                      $count = $rctrsite['ctrterminal'];

                      //this is for computing the limit
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
                      $results = array();  
                      $results = $orptpegs->getterminalbysite($vsiteID, $start, $limit, $sort, $direction);
                      $rterminals = array();
                  
                      //validate if no  assigned terminals
                      if(count($results) > 0)
                      {
                           $i = 0;
                           $response->page = $page;
                           $response->total = $total_pages;
                           $response->records = $count;

                           foreach($results as $row)
                           {
                              $rterminalID = $row['tid']; 
                              $rterminalCode = $row['tcode'];
                              $vorigstatus = $row['tstat'];
                              $vprovider = $row['ServiceName'];
                              $vocaccount = $row['ServiceTerminalAcct'];
                              //remove the "icsa-[SiteCode]"
                                $rterminalCode = substr($rterminalCode, strlen($rsitecode['SiteCode']));
                            
                              if(trim(strlen($vocaccount) > 0)) 
                              {
                                $vocval =   $vocaccount; 
                              }
                              else
                              {
                                $vocval = 'na';
                              }        

                              if($vorigstatus == 1)
                              {
                                $vstatus = "Active";
                              }
                              else
                              {
                                $vstatus = "Inactive";
                              }
                              $response->rows[$i]['id'] = $rterminalID;
                              $response->rows[$i]['cell'] = array($rterminalCode, $vstatus, $vprovider, $siteowner);                     
                              $i++;
                            }
                      }
                      else
                      {
                          $i = 0;
                          $response->rows[$i]['cell'] = array("na", "na", "na",  "na", "na", $siteowner);
                      }
                }
                else
                {
                   $i = 0;
                   $response->page = 0;
                   $response->total = 0;
                   $response->records = 0;
                   $msg = "No Operator Assigned";
                   $response->msg = $msg;
                }
                echo json_encode($response);
                $orptpegs->close();
                exit;
            break;  
            //for site demographics report
            case 'SiteDemographics':
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
                
                //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "SiteCode";
                }

                $resultcount = array();
                $resultcount = $orptpegs->countsitedemographic($vall, $vislandID, $vregionID, $vprovinceID, $vcityID);
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
                   $result = $orptpegs->getsitedemographic($vall, $vislandID, $vregionID, $vprovinceID, $vcityID, $start, $limit, $sort, $direction);
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
                        $rsitecode = substr($vview['SiteCode'], strlen($terminalcode));
                        $responce->rows[$i]['cell']=array($vview['POS'], $vview['SiteName'], $rsitecode);
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
               $orptpegs->close();
               exit;
            break;
            case 'UserListing':
                //for sorting
                if($_POST['sidx'] != "")
                {
                   $sort = $_POST['sidx'];
                }
                else
                {
                    $sort = "SiteID";
                }
                $vsiteID = $_POST['siteID'];
                $rctruser = $orptpegs->countuserlist($vsiteID);                
                $count = $rctruser['ctruser'];
                
                //this is for computing the limit
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

                else{
                  $start = $limit * $page - $limit;   
                }

                $limit = (int)$limit;

                //this is for proper rendering of results, if count is 0 $result is also must be 0
                if($count > 0)
                {
                   $result = $orptpegs->viewuserlist($vsiteID, $start, $limit, $sort, $direction);
                }
                else{
                   $result = 0; 
                }
                if($result > 0)
                {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   foreach($result as $vview) 
                   {
                        $rsiteID = $vview['SiteID'];
                        $vstatname = $orptpegs->showstatusname($vview['Status']);
                        $vcode = substr($vview['Site Code'], strlen($terminalcode)); //removes ICSA-
                        $response->rows[$i]['id'] = $rsiteID;
                        $response->rows[$i]['cell']=array($vview['POS Account No.'], $vcode, $vview['Site Name'], 
                                                          $vview['Name'], $vview['User Group'], 
                                                          $vview['Date Created'], $vstatname);
                        $i++;
                   }
               }
               else
               {
                   $i = 0;
                   $response->page = $page;
                   $response->total = $total_pages;
                   $response->records = $count;
                   $msg = "User Listing: No returned result";
                   $response->msg = $msg;
               }
               echo json_encode($response);
               $orptpegs->close();
               exit;
            break;
        }
    }
    //this catches get method requests (Exporting Excel / PDF)
    elseif(isset($_GET['getpage']))
    {
        $vgetpage = $_GET['getpage'];
        switch($vgetpage)
        {
            //this creates session to view the site code and pos account number of the chosen site
            case 'TerminalListing':
                $vsiteID = $_GET['siteid'];
                $rsite = $orptpegs->getsitename($vsiteID);
                $arrdetails = array();
                foreach($rsite as $val)
                {
                    $vsitecode = substr($val['SiteCode'], strlen($terminalcode));
                    $arrnew = array("page"=>"GetSiteDetails", "SiteID"=>$vsiteID, "SiteCode"=>$vsitecode, "POS"=>$val['POS']);
                    array_push($arrdetails, $arrnew);
                }
                $_SESSION['details'] = $arrdetails;
                unset($arrdetails);
                $orptpegs->close();
                header("Location: ../rptterminallisting.php");
            break;
            //Terminal Listing Export to excel file
            case 'ListTerminals':
                $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
                $vsiteID = $_GET['siteid'];
                //create the instance of the exportexcel format
                $excel_obj = new ExportExcel("$fn");
                //setting the values of the headers and data of the excel file
                //and these values comes from the other file which file shows the data
                
                $vpowner = $orptpegs->getownerbysite($vsiteID); //get the owner
                $rsitecode = $orptpegs->getsitecode($vsiteID); //get the sitecode first
                
                foreach ($vpowner as $row)
                {
                    $siteowner = $row['UserName'];
                }
                
                if(count($vpowner) > 0)
                {
                    $rsite = $orptpegs->getsitename($vsiteID);
                    $arrdetails = array();
                    foreach($rsite as $val)
                    {
                        $vsitecode = substr($val['SiteCode'], strlen($terminalcode));
                        $vpos = $val['POS'];
                    }
                    $rheaders = array('Site / PEGS Code','POS Account','Terminal Code','Status', 'Service Name','Owner');
                    $result = $orptpegs->getterminalbysite($vsiteID, $start = null, $limit=null, $sort=null, $direction = null);
                    $completeexcelvalues = array();
                    if(count($result) > 0)
                    {                
                       foreach($result as $vview)
                       {
                            $rterminalID = $vview['tid']; 
                            $rterminalCode = $vview['tcode'];
                            $vorigstatus = $vview['tstat'];
                            $vprovider = $vview['ServiceName'];
                            $vocaccount = $vview['ServiceTerminalAcct'];

                            //remove the "icsa-[SiteCode]"
                               $rterminalCode = substr($rterminalCode, strlen($rsitecode['SiteCode']));


                            if(trim(strlen($vocaccount) > 0)) 
                            {
                               $vocval =   $vocaccount; 
                            }
                            else
                            {
                               $vocval = 'na';
                            }        

                            if($vorigstatus == 1)
                            {
                               $vstatus = "Active";
                            }
                            else
                            {
                               $vstatus = "Inactive";
                            }

                            $excelvalues = array($vsitecode, $vpos, $rterminalCode, $vstatus, $vprovider, $siteowner);                     
                            array_push($completeexcelvalues, $excelvalues);
                       }
                    }
                }
                
                $vauditfuncID = 41; //export to excel
                $vtransdetails = "Terminal Listing";
                $orptpegs->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
                $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
                
                unset($completeexcelvalues);
                unset($rheaders);
            break;
            //Site Listing: export to excel
            case 'SiteListing':
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
                
                $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
                //create the instance of the exportexcel format
                $excel_obj = new ExportExcel("$fn");
                //setting the values of the headers and data of the excel file
                //and these values comes from the other file which file shows the data
                $rheaders = array('POS Account','Site / PEGS Name','Site / PEGS Code','Island', 'Region', 'Province', 'City', 'Barangay', 'Address', 'Contact No.','Status', 'Total Terminals');
                $result = $orptpegs->viewsitelisting($vall, $vislandID, $vregionID, $vprovinceID, $vcityID, $start = null, $limit=null, $sort=null, $direction = null);
                $completeexcelvalues = array();
                if(count($result) > 0)
                {                
                   foreach($result as $vview)
                   {
                        $rsiteID = $vview['SiteID'];
                        $vstatus = $orptpegs->refsitestatusname($vview['Status']);
                        $vcode = substr($vview['SiteCode'], strlen($terminalcode));
                        
                        $rterminals = $orptpegs->countterminalbysite($rsiteID);
                        $allterminal = $rterminals['ctrterminal'];
                        $excelvalues = array($vview['POS'], $vview['SiteName'], $vcode,
                                             $vview['IslandName'], $vview['RegionName'], $vview['ProvinceName'], 
                                             $vview['CityName'], $vview['BarangayName'], $vview['SiteAddress'], 
                                             $vview['ContactNumber'], $vstatus, $allterminal);
                        array_push($completeexcelvalues, $excelvalues);
                   }
                }
                $vauditfuncID = 41; //export to excel
                $vtransdetails = "Site Listing";
                $orptpegs->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
                $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
                
                unset($completeexcelvalues);
                unset($rheaders);
            break;
            //Site Demographics Report (Export to Excel)
            case 'ExcelDemographics':
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
                $rheaders = array('POS Account','Site / PEGS Name','Site / PEGS Code');
                $completeexcelvalues = array();
                $result = $orptpegs->exportdemographicpdf($vall, $vislandID, $vregionID, $vprovinceID, $vcityID);
                if(count($result) > 0)
                {                
                   foreach($result as $vview)
                   {    
                      if($vview['SiteID'] <> 1)
                      {
                         $rsitecode = substr($vview['SiteCode'], strlen($terminalcode));
                         $excelvalues = array($vview['POS'],$vview['SiteName'], $rsitecode);   
                         array_push($completeexcelvalues, $excelvalues);
                      }
                   }
                }
                $vauditfuncID = 41; //export to excel
                $vtransdetails = "Site Demographics";
                $orptpegs->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
                $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
            break;
            //Site Demographics Report (Export to PDF)
            case 'PDFDemographics':
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
              
                $queries = $orptpegs->exportdemographicpdf($vall, $vislandID, $vregionID, $vprovinceID, $vcityID);
                $pdf = CTCPDF::c_getInstance();
                $pdf->c_commonReportFormat();
                $pdf->c_setHeader('Site Demographics');
                $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
                      date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
                $pdf->SetFontSize(10);
                $pdf->c_tableHeader(array('POS Account','Site / PEGS Name','Site / PEGS Code'));

                foreach($queries as $row)
                {
                  if($row['SiteID'] <> 1)
                  {
                     $rsitecode = substr($row['SiteCode'], strlen($terminalcode));
                     $pdf->c_tableRow(array($row['POS'],$row['SiteName'], $rsitecode));
                  }
                }

                $pdf->c_tableEnd();
                $vauditfuncID = 40; //export to pdf
                $vtransdetails = "Site Demographics";
                $orptpegs->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                $pdf->c_generatePDF('SiteDemographics.pdf'); 
            break;
            //User Listing: Export to Excel
            case 'UserListing':
                $vsiteID = $_POST['cmbsite'];
                $fn = $_GET['fn'].".xls"; //this will be the filename of the excel file
                //create the instance of the exportexcel format
                $excel_obj = new ExportExcel("$fn");
                //setting the values of the headers and data of the excel file
                //and these values comes from the other file which file shows the data
                $rheaders = array('POS Account','Site / PEGS Code','Site / PEGS Name','Name','User Group','Date Created','Status');
                $result = $orptpegs->viewuserlist($vsiteID, $start = null, $limit = null, $sort=null, $direction=null);
                $completeexcelvalues = array();
                if(count($result) > 0)
                {                
                   foreach($result as $vview)
                   {
                        $rsiteID = $vview['SiteID'];
                        $vstatname = $orptpegs->showstatusname($vview['Status']);
                        $vcode = substr($vview['Site Code'], strlen($terminalcode)); //removes ICSA-
                        $excelvalues = array($vview['POS Account No.'], $vcode, $vview['Site Name'], 
                                                          $vview['Name'], $vview['User Group'], 
                                                          $vview['Date Created'], $vstatname);
                       array_push($completeexcelvalues, $excelvalues);
                   }
                }
                $vauditfuncID = 41; //export to excel
                $vtransdetails = "User Listing";
                $orptpegs->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                $excel_obj->setHeadersAndValues($rheaders, $completeexcelvalues);
                $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
                
                unset($completeexcelvalues);
                unset($rheaders);
            break;
            case 'SiteListingPDF':
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
                
                $result = $orptpegs->viewsitelisting($vall, $vislandID, $vregionID, $vprovinceID, $vcityID, $start = null, $limit=null, $sort=null, $direction = null);
                
                $pdf = CTCPDF::c_getInstance();
                $pdf->c_commonReportFormat();
                $pdf->c_setHeader('Site Listing');
                $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
                $pdf->SetFontSize(10);
                $pdf->c_tableHeader(array('POS Account','Site / PEGS Name','Site / PEGS Code','Island', 'Region', 'Province', 'City', 'Barangay', 'Address', 'Contact No.','Status', 'Total Terminals'));
                
                if(count($result) > 0)
                {                
                   foreach($result as $vview)
                   {
                        $rsiteID = $vview['SiteID'];
                        $vstatus = $orptpegs->refsitestatusname($vview['Status']);
                        $vcode = substr($vview['SiteCode'], strlen($terminalcode));

                        $rterminals = $orptpegs->countterminalbysite($rsiteID);
                        $allterminal = $rterminals['ctrterminal'];
                        $pdfvalues = array($vview['POS'], $vview['SiteName'], $vcode,
                                             $vview['IslandName'], $vview['RegionName'], $vview['ProvinceName'], 
                                             $vview['CityName'], $vview['BarangayName'], $vview['SiteAddress'], 
                                             $vview['ContactNumber'], $vstatus, $allterminal);
                        $pdf->c_tableRow($pdfvalues);
                   }
                }
                else
                {
                    $pdf->html.='<div style="text-align:center;">No Results Found</div>';
                }
                
                $pdf->c_tableEnd();
                $vauditfuncID = 40; //export to pdf
                $vtransdetails = "";
                $orptpegs->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                $pdf->c_generatePDF('SiteListing.pdf'); 
            break;
            case 'ListTerminalsPDF':
                $vsiteID = $_GET['siteid'];
                $pdf = CTCPDF::c_getInstance();
                $pdf->c_commonReportFormat();
                $pdf->c_setHeader('Terminal Listing');
                $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
                $pdf->SetFontSize(10);
                $pdf->c_tableHeader(array('Site / PEGS Code','POS Account','Terminal Code','Status', 'Service Name','Owner'));
                
                $vpowner = $orptpegs->getownerbysite($vsiteID); //get the owner
                $rsitecode = $orptpegs->getsitecode($vsiteID); //get the sitecode first
                
                foreach ($vpowner as $row)
                {
                    $siteowner = $row['UserName'];
                }
                
                if(count($vpowner) > 0)
                {
                    $rsite = $orptpegs->getsitename($vsiteID);
                    $arrdetails = array();
                    foreach($rsite as $val)
                    {
                        $vsitecode = substr($val['SiteCode'], strlen($terminalcode));
                        $vpos = $val['POS'];
                    }
                    
                    $result = $orptpegs->getterminalbysite($vsiteID, $start = null, $limit=null, $sort=null, $direction = null);
                    
                    if(count($result) > 0)
                    {                
                       foreach($result as $vview)
                       {
                            $rterminalID = $vview['tid']; 
                            $rterminalCode = $vview['tcode'];
                            $vorigstatus = $vview['tstat'];
                            $vprovider = $vview['ServiceName'];
                            $vocaccount = $vview['ServiceTerminalAcct'];

                            //remove the "icsa-[SiteCode]"
                               $rterminalCode = substr($rterminalCode, strlen($rsitecode['SiteCode']));


                            if(trim(strlen($vocaccount) > 0)) 
                            {
                               $vocval =   $vocaccount; 
                            }
                            else
                            {
                               $vocval = 'na';
                            }        

                            if($vorigstatus == 1)
                            {
                               $vstatus = "Active";
                            }
                            else
                            {
                               $vstatus = "Inactive";
                            }

                            $pdfvalues = array($vsitecode, $vpos, $rterminalCode, $vstatus, $vprovider, $siteowner);                     
                            $pdf->c_tableRow($pdfvalues);
                       }
                    }
                    else
                    {
                        $pdf->html.='<div style="text-align:center;">No Results Found</div>';
                    }

                    $pdf->c_tableEnd();
                    $vauditfuncID = 40; //export to pdf
                    $vtransdetails = "";
                    $orptpegs->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                    $pdf->c_generatePDF('TerminalListing.pdf'); 
                }
            break;
            case 'UserListingPDF':
                $vsiteID = $_POST['cmbsite'];
                $pdf = CTCPDF::c_getInstance();
                $pdf->c_commonReportFormat();
                $pdf->c_setHeader('User Listing');
                $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .date('F d, Y') . ' ' . date('h:i:s A') .'</div>';
                $pdf->SetFontSize(10);
                $pdf->c_tableHeader(array('POS Account','Site / PEGS Code','Site / PEGS Name','Name','User Group','Date Created','Status'));
                $result = $orptpegs->viewuserlist($vsiteID, $start = null, $limit = null, $sort=null, $direction=null);
                if(count($result) > 0)
                {                
                   foreach($result as $vview)
                   {
                        $rsiteID = $vview['SiteID'];
                        $vstatname = $orptpegs->showstatusname($vview['Status']);
                        $vcode = substr($vview['Site Code'], strlen($terminalcode)); //removes ICSA-
                        $pdfvalues = array($vview['POS Account No.'], $vcode, $vview['Site Name'], 
                                                          $vview['Name'], $vview['User Group'], 
                                                          $vview['Date Created'], $vstatname);
                        $pdf->c_tableRow($pdfvalues);
                   }
                }
                else
                {
                    $pdf->html.='<div style="text-align:center;">No Results Found</div>';
                }

                $pdf->c_tableEnd();
                $vauditfuncID = 40; //export to pdf
                $vtransdetails = "";
                $orptpegs->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                $pdf->c_generatePDF('UserListing.pdf'); 
            break;
        }
    }
    //populates the dropdown box with islands
    elseif(isset ($_POST['sendIslandID']))
    {
      $vislandID = $_POST['sendIslandID'];
      $resultregions = array();
      $resultregions = $orptpegs->showregions($vislandID);
      echo json_encode($resultregions);
      unset($resultregions);
      $orptpegs->close();
      exit;
    }
    //populates the dropdown box with regions
    elseif(isset($_POST['sendRegionID']))
    {
      $vregionID = $_POST['sendRegionID'];
      $resultprovinces = array();
      $resultprovinces = $orptpegs->showprovinces($vregionID);
      echo json_encode($resultprovinces);
      unset($resultprovinces);
      $orptpegs->close();
      exit;
    }
    //populates the dropdown box with provinces
    elseif(isset($_POST['sendProvID']))
    {
      $vprovID = $_POST['sendProvID'];
      $resultcities = array();
      $resultcities = $orptpegs->showcities($vprovID);
      echo json_encode($resultcities);
      unset($resultcities);
      $orptpegs->close();
      exit;
    }
    //populates the dropdown box with cities
    elseif(isset($_POST['sendCityID']))
    {
      $vcityID = $_POST['sendCityID'];
      $resultbrgy = array();
      $resultbrgy = $orptpegs->showbrgy($vcityID);
      echo json_encode($resultbrgy);
      unset($resultbrgy);
      exit;
    }
    //for displaying site name on label
    elseif(isset($_POST['cmbsitename']))
    {
        $vsiteID = $_POST['cmbsitename'];
        $rresult = array();
        $rresult = $orptpegs->getsitename($vsiteID);
        foreach($rresult as $row)
        {
            $rsitename = $row['SiteName'];
            $rposaccno = $row['POS'];
        }
        if(count($rresult) > 0)
        {
            $vsiteName->SiteName = $rsitename;
            $vsiteName->POSAccNo = $rposaccno;
        }
        else
        {
            $vsiteName->SiteName = "";
            $vsiteName->POSAccNo = "";
        }
        echo json_encode($vsiteName);
        unset($rresult);
        $orptpegs->close();
        exit;
    }
    else
    {
        //get all islands
        $resultislands = array();
        $resultislands = $orptpegs->showislands();
        $_SESSION['resislands'] = $resultislands;
        
         //get all sites
        $sitelist = array();
        $sitelist = $orptpegs->getallsites();
        $_SESSION['siteids'] = $sitelist; //session variable for the sites name selection
    }
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>
