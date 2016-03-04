<?php

include __DIR__."/../sys/class/CasinoManagement.class.php";
require __DIR__.'/../sys/core/init.php';

ini_set('display_errors',true);
ini_set('log_errors',true);

if (!isset($_SESSION))
{
    session_start();
}
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

$ocasino= new CasinoManagement($_DBConnectionString[0]);
$connected = $ocasino->open();
if($connected)
{     
   $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
   $vdate = $ocasino->getDate();    
/********** SESSION CHECKING **********/    
   $isexist=$ocasino->checksession($aid);
   if($isexist == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $ocasino->close();
      if($ocasino->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
   } 
   
   $isexistsession =$ocasino->checkifsessionexist($aid ,$new_sessionid);
   if($isexistsession == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $ocasino->close();
      header("Location: login.php?mess=".$msg);
   }
/********** END SESSION CHECKING **********/

   //checks if account was locked 
   $islocked = $ocasino->chkLoginAttempts($aid);
   if(isset($islocked['LoginAttempts'])){
      $loginattempts = $islocked['LoginAttempts'];
      if($loginattempts >= 3){
          $ocasino->deletesession($aid);
          session_destroy();
          $msg = "Not Connected";
          $ocasino->close();
          header("Location: login.php?mess=".$msg);
          exit;
      }
   }
   
    //pagination functionality starts here
   if(isset($_POST['servicepage']) == "Paginate")
   {
       $page = $_POST['page']; // get the requested page
       $limit = $_POST['rows']; // get how many rows we want to have into the grid
       $direction = $_POST['sord'];
       $id = $_POST['cmbservicegrp'];
       //for sorting
       if($_POST['sidx'] != "")
       {
         $sort = $_POST['sidx'];
       }
       else
       {
         $sort = "ServiceID";
       }
                
       $resultcount = array();
       $resultcount = $ocasino->countservicedetails($id);
       $count = $resultcount['count'];

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
       
        if($count > 0)
        {
            $result = $ocasino->viewservicepage($id, $start, $limit, $direction, $sort); 
        }
        else {
            $result = 0;
        }
       

       if(count($result) > 0)
       {
           $i = 0;
           $responce->page = $page;
           $responce->total = $total_pages;
           $responce->records = $count;
           foreach($result as $vview) {
                $rserviceID = $vview['ServiceID'];
                //for selecting status
                $vstatus = $ocasino->refservicestatusname($vview['Status']);
                //for selecting usermode
                $rusermode = $ocasino->serviceusermode($vview['UserMode']);
                
                
                   $responce->rows[$i]['cell']=array($rserviceID,$vview['ServiceName'], $vview['Alias'], $vview['Code'], $vview['ServiceDescription'],$rusermode
                       ,$vstatus, $vview['ServiceGroupName'], "<input type=\"button\" value=\"Edit Details\" onclick=\"window.location.href='process/ProcessCasinoMgmt.php?serviceid=$rserviceID'+'&page='+'ViewService';\"/>");
                   $i++;
           }
       }
       else
       {
           $i = 0;
           $responce->page = $page;
           $responce->total = $total_pages;
           $responce->records = $count;
           $msg = "Casino Service Profile Management: No returned result";
           $responce->msg = $msg;
       }
       echo json_encode($responce);
       unset($result);
       $ocasino->close();
       exit;
   }
   
      
   if(isset($_POST['page']))
   {
      $vpage =  $_POST['page'];
      switch ($vpage)
       {
        // searching by service id  
        case 'ServiceView':
            $servgrpID = $_POST['cmbservicegrp'];
            $servID = $_POST['cmbservice'];
            $resultsearchservice = array();
            $service = array();
            
            $resultsearchservice = $ocasino->viewservicedetails($servgrpID,$servID);
            foreach($resultsearchservice as $row)
            {
                $rserviceID = $row['ServiceID'];
                $rservicegrpID = $row['ServiceGroupID'];
                $rservicename = $row['ServiceGroupName'];
                $rservname = $row['ServiceName'];
                $rsalias= $row['Alias'];
                $rscode = $row['Code'];
                $rservcdesc = $row['ServiceDescription'];
                $rusermode = $ocasino->serviceusermode($row['UserMode']);
                $rstatus = $row['Status'];
                                
                //create a new array to populate the combobox
                $newvalue = array("ServiceID" => $rserviceID, 'ServiceGroupID'=>$rservicegrpID,"ServiceGroupName" => $rservicename, "ServiceName" => $rservname, 
                    "Alias" => $rsalias, "Code" => $rscode, "ServiceDescription" => $rservcdesc, "UserMode" => $rusermode, 
                    "Status" => $rstatus);
                array_push($service, $newvalue);
                unset($newvalue);
            }
            echo json_encode($service);
            unset($resultsearchservice);
            unset($service);
            $ocasino->close();
            exit;
        break;
        
        case 'sendServiceGroupID':
        
            //to post data to terminals combo box (serviceassignment.php)
        $vsiteID = $_POST['cmbservicegrp'];
        $rresult = array();
        $rresult = $ocasino->selectservice($vsiteID);

        $services = array();
        foreach($rresult as $row)
        {
            $rserviceID = $row['ServiceID'];
            $rserviceName = $row['ServiceName'];
            
     
            //create a new array to populate the combobox
              $newvalue = array("ServiceID" => $rserviceID, "ServiceName" => $rserviceName);
              array_push($services, $newvalue);
        }
        echo json_encode($services);
        unset($rresult);
        unset($services);
        $ocasino->close();
        exit;
            
        break;    
        
        //for updating service details
        case 'ServiceUpdate':
            //check if required fields are set
            if(isset($_POST['txtserviceid']) && isset($_POST['txtservicename']) && isset($_POST['txtalias']) && 
               isset($_POST['txtservicecode']) && isset($_POST['txtservcdesc']) && isset($_POST['cmbservicegrp']) && 
               isset($_POST['usermode'])){
                
                    $ServiceID = $_POST['txtserviceid'];
                    $upServiceName = trim($_POST['txtservicename']);
                    $upServiceAlias = trim($_POST['txtalias']);
                    $upServiceCode = trim($_POST['txtservicecode']);
                    $upServiceDesc = trim($_POST['txtservcdesc']);
                    $upServiceGrp = $_POST['cmbservicegrp'];
                    $upServiceMode = $_POST['usermode'];
                    $oldusermode = $_POST['txtoldusermode'];
                    $mode = $upServiceMode;
                    $sessioncount = 0;
                    
                      // check if user mode change
                    if($oldusermode != $upServiceMode)
                    {
                        //then check if has existing sessions
                        $session = $ocasino->checkterminalsession($ServiceID);
                        $sessioncount = $session['count'];
                        
                        //if service has an existing session
                        if($sessioncount > 0)
                        {
                            $mode = $oldusermode;
                            $msg = "Casino Services Update : Mode did not changed. Casino has an existing session.";
                        }
                        else 
                        {
                            $msg = "Casino Services Update : Service Casino updated";
                        }
                    }
                    else 
                    {
                        $msg = "Casino Services Update : Service Casino updated";
                    }
                    
                    //update service details and usermode
                    $serviceid = $ocasino->updateService($upServiceName, $upServiceAlias,
                                                         $upServiceCode,$upServiceDesc ,$upServiceGrp, 
                                                         $mode, $ServiceID);
                    //no records change
                    if($serviceid > 0)
                    {
                      $arrnewdetails = array($upServiceName,$upServiceAlias,$upServiceCode,$upServiceDesc ,$upServiceGrp,$upServiceMode, $ServiceID);
                      $newdetails = implode(",", $arrnewdetails);
                    
                       //insert into audit trail
                      $vtransdetails = "old details ".$_POST['txtolddetails']." ;new details ".$newdetails;
                      $vdateupdated = $vdate;
                      $vauditfuncID = 71;
                      $ocasino->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdateupdated, $vipaddress, $vauditfuncID);
                    }
                    
                    //update service profile
                    else
                    {
                        if($sessioncount == 0)
                            $msg = "Casino Services Update : Service Casino Profile unchanged";
                    }                                                                                             
            }
            else
            {
              $msg = "Casino Services Update : Invalid fields.";
            }
            
            //redirect to site view page with corresponding popup message
            $ocasino->close();
            $_SESSION['mess']= $msg;
            header("Location: ../casinoviewprofile.php?");
        break;
        
        //if page submit from casinostatus.php
        case 'UpdateStatus':
            $vStatus = $_POST['optstatus'];
            
            $vserviceID = $_SESSION['serviceid'];

            $oldstat = $_POST['txtoldstat'];
            
            //check terminalsessions
            $session = $ocasino->checkterminalsession($vserviceID);
            $sessioncount = $session['count'];
            
            //if service has an existing session
            if($sessioncount > 0){
                  $msg = "Casino Services Update : Casino Has Existing Session";
            }
            else {
            
            if($oldstat != $vStatus){
            //change service status
            $resultid = $ocasino->changestatus($vStatus,$vserviceID);
            if($vStatus == 0){
                $stat = 'InActive';
            }
            else {
                $stat = 'Active';
            }
            
            $servicename = $ocasino->getServiceName($vserviceID);
            
            foreach ($servicename as $row) {
                $rserviceName = $row['ServiceName'];
            }
            
            if($resultid > 0)
            {
               //log to audittrail 
               $msg = "Casino Service Update: Service status successfully changed";
               //insert into audit trail --> DbHandler.class.php
               $vdateupdated = $vdate;
               $vauditfuncID = 72;
               $vtransdetails = "Change Status of Casino Service $rserviceName to $stat";
               $ocasino->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdateupdated, $vipaddress, $vauditfuncID);   
            }
            else {
               $msg = "Casino Services Update: Service status unchanged"; 
            }
            }
            else
            {
               $msg = "Casino Services Update: Service status unchanged";
            }
            }
            
            $ocasino->close();
            $_SESSION['mess']= $msg;
            unset($vserviceID, $vStatus);
            header("Location: ../casinoviewprofile.php");
        break;

	}
    }
    elseif(isset($_GET['statuspage']) == 'UpdateStatus')
    {
            $vsiteID = $_GET['serviceid'];
            $_SESSION['serviceid'] = $vsiteID;
            $resultsearchservice = array();
            $resultsearchservice = $ocasino->viewservicedetails2($vsiteID);
            $_SESSION['reservicedet']= $resultsearchservice;
            unset($resultsearchservice);
            $ocasino->close();
            header("Location: ../casinostatus.php");
     }

     //for listing Services via Service ID
   if(isset($_GET['page'])=='ViewService')
   {
        $vserviceID = $_GET['serviceid'];
        $_SESSION['serviceid'] = $vserviceID;
        $resultservice = array();
        $resultservice = $ocasino->viewservicedetails2($vserviceID);
        
        if(count($resultservice) > 0)
        {
            foreach ($resultservice as $row)
            {
                $rserviceID = $row['ServiceID'];
                $rservicegrpID = $row['ServiceGroupID'];
                $rservicename = $row['ServiceGroupName'];
                $rservname = $row['ServiceName'];
                $rsalias= $row['Alias'];
                $rscode = $row['Code'];
                $rservcdesc = $row['ServiceDescription'];
                $rusermode = $row['UserMode'];
                $rusermodename = $row['UserModeName'];
                $rstatus = $row['Status'];
            }
            
            $_SESSION['reservicedet']= $resultservice;
            $_SESSION['UserMode']= $ocasino->getusermodewithid();
            
          unset($resultservice);
            $ocasino->close();
            header("Location: ../casinoeditprofile.php"); 
        }
        else
        {
            $msg = "No Details found for this service group";
            $_SESSION['mess'] = $msg;
            $ocasino->close();
            header("Location: ../casinoviewprofile.php"); 
        }
   }
   

   if(isset($_POST['cmbservicegrp'])){
       
   }  
   else {
       $ocasino->open();
       //for service group listing
        $_SESSION['servicegrpid']  = $ocasino->getrefservicegrpwithid();
        
        $ocasino->close();
   }
   
   
   
}
else
{
     
    
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
   
?>
