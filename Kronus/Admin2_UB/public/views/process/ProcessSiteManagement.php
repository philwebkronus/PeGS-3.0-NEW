<?php

/*
 * Created by: Lea Tuazon
 * Date : June 1, 2011
 * 
 * Modified by: Edson L. Perez
 */
include __DIR__."/../sys/class/SiteManagement.class.php";
require  __DIR__."/../sys/core/init.php";

$aid = 0;
if(isset($_SESSION['sessionID']))
{
    $new_sessionid = $_SESSION['sessionID'];
}
if(isset($_SESSION['accID']))
{
    $aid = $_SESSION['accID'];
}

$osite = new SiteManagement($_DBConnectionString[0]);
$connected = $osite->open();

$nopage = 0;
if($connected)
{
/*************SESSION CHECKING ******************/
    $isexist=$osite->checksession($aid);
    if($isexist == 0)
    {
       session_destroy();
       $msg = "Not Connected";
       $osite->close();
       if($osite->isAjaxRequest())
       {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
       }
       header("Location: login.php?mess=".$msg);
    }    
    $isexistsession =$osite->checkifsessionexist($aid ,$new_sessionid);
    if($isexistsession == 0)
    {
       session_destroy();
       $msg = "Not Connected";
       $osite->close();
       header("Location: login.php?mess=".$msg);
    }
/*************END SESSION CHECKING *************/    
    
   //checks if account was locked 
   $islocked = $osite->chkLoginAttempts($aid);
   if(isset($islocked['LoginAttempts'])){
      $loginattempts = $islocked['LoginAttempts'];
      if($loginattempts >= 3){
          $osite->deletesession($aid);
          session_destroy();
          $msg = "Not Connected";
          $osite->close();
          header("Location: login.php?mess=".$msg);
          exit;
      }
   }
   
    $resultsites = array();
    $resultsites = $osite->getsites();
    $resultislands = array();
    $resultislands = $osite->showislands();    
    $resultsitegrp = array();
    $resultsitegrp = $osite->showsitegroups();
   
    //list of accounttype, siteedit.php
    $resultowner = array();
    switch($_SESSION['acctype'] )
    {
        case 8:
             $resultowner = $osite->selectallaccounts(2); //operator
             break;  
    }
    
    $vdate = $osite->getDate();
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);    
    
     //pagination functionality starts here
   if(isset($_POST['sitepage']) == "Paginate")
   {
       $page = $_POST['page']; // get the requested page
       $limit = $_POST['rows']; // get how many rows we want to have into the grid
       $direction = $_POST['sord'];
       
       //for sorting
       if($_POST['sidx'] != "")
       {
         $sort = $_POST['sidx'];
       }
       else
       {
         $sort = "SiteID";
       }
                
       $resultcount = array();
       $resultcount = $osite->countsitedetails();
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
       $result = $osite->viewsitepage(0, $start, $limit, $direction, $sort);

       if(count($result) > 0)
       {
           $i = 0;
           $responce->page = $page;
           $responce->total = $total_pages;
           $responce->records = $count;
           foreach($result as $vview) {
                $rsiteID = $vview['SiteID'];
                $vstatus = $osite->refsitestatusname($vview['Status']);
                $isterminalcode = strstr($vview['SiteCode'], $terminalcode);
                if($isterminalcode == false)
                {
                    $vcode= $vview['SiteCode'];
                }
                else{
                    $vcode = substr($vview['SiteCode'], strlen($terminalcode));
                }
                $responce->rows[$i]['id']=$vview['SiteName'];
                //topup module: if account will be suspended
                if(isset($_POST['updatestatus']) == '2')
                {
                   $responce->rows[$i]['cell']=array($rsiteID, $vview['SiteName'],$vcode, $vview['POS'], $vview['SiteDescription'],$vview['SiteAddress'],$vstatus, "<input type=\"button\" value=\"Update Status\" onclick=\"window.location.href='process/ProcessSiteManagement.php?siteid=$rsiteID'+'&statuspage='+'UpdateStatus';\"/>");
                   $i++;
                }
                else
                {
                   $responce->rows[$i]['cell']=array($rsiteID, $vview['SiteName'],$vcode, $vview['POS'], $vview['SiteDescription'],$vview['SiteAddress'],$vstatus, "<input type=\"button\" value=\"Update Details\" onclick=\"window.location.href='process/ProcessSiteManagement.php?siteid=$rsiteID'+'&page='+'ViewSite';\"/>");
                   $i++;
                }
           }
       }
       else
       {
           $i = 0;
           $responce->page = $page;
           $responce->total = $total_pages;
           $responce->records = $count;
           $msg = "Site Management: No returned result";
           $responce->msg = $msg;
       }
       echo json_encode($responce);
       unset($result);
       $osite->close();
       exit;
   }
   if(isset($_POST['page']))
   {
      $vpage =  $_POST['page'];
      switch ($vpage)
       {
           case 'SiteCreation':
                if(isset($_POST['txtsitecode']) && isset($_POST['cmbislands']) && isset($_POST['cmbregions'])  && isset($_POST['cmbprovinces']) && isset($_POST['cmbcity'])
                 && isset($_POST['cmbbrgy']) && isset($_POST['txtsiteaddress']) && isset ($_POST['txtpasscode']))
                {
                    //get all fields from sitecreation.php
                   $vSiteName = trim($_POST['txtsitename']);
                   $vSiteCode = $terminalcode.trim($_POST['txtsitecode']);
                   //if with selected account id then $vOwnerAID != null , else it must be set to null
                   $vOwnerAID = null;
                   $vStatus = 1; //always 1
                   $vSiteDescription = trim($_POST['txtsitedesc']);
                   //$vSiteAlias= trim($_POST['txtsitealias']);
                   $vSiteAlias = '';
                   $vIslandId=$_POST['cmbislands'];
                   $vRegionID=$_POST['cmbregions'];
                   $vProvinceID=$_POST['cmbprovinces'];
                   $vCityID=$_POST['cmbcity'];
                   $vBarangayID=$_POST['cmbbrgy'];
                   $vSiteAddress=trim($_POST['txtsiteaddress']);
                   $vsiteCTO = trim($_POST['txtcto']);
                   $vpasscode = trim($_POST['txtpasscode']);
                   $istestsite = $_POST['opttest']; //indicates if site is test or live
                   $vcontactno = trim($_POST['txtctrycode']).'-'.trim($_POST['txtareacode']).'-'.trim($_POST['txtphone']);
                   
                   $rpass = str_pad($vpasscode, 3, "0", STR_PAD_LEFT); //pads the inputted passcode by 3

                   $rdenomdefaults = array();
                   $rdenomdefaults = $osite->getdefaultdenoms();
                   
                   //this will check if site code is unique
                   $ctrsitecode = $osite->checksitecode($vSiteCode);
                   if($ctrsitecode['count'] > 0)
                   {
                        $msg = "Site Creation : Site Code exist.";
                   }
                   else
                   {
                       //this will insert to site table
                       $siteid = $osite->insertinsite($vSiteName,$vSiteCode,$vOwnerAID,$vStatus,$vSiteDescription,
                           $vSiteAlias,$vIslandId,$vRegionID,$vProvinceID,$vCityID,$vBarangayID,
                           $vSiteAddress,$vsiteCTO,$rpass, $vdate, $aid, $rdenomdefaults, $istestsite, $vcontactno);
                          
                       if($siteid == 0) 
                       {
                          $msg = "Site/PEGS Creation: Error in creating site/pegs accounts";
                       }
                       else 
                       {
                          $msg = "Site/PEGS Creation : Sites/PEGS successfully created";
                          
                          //insert into audit trail
                          $vtransdetails = "Site ID ".$siteid;
                          $vauditfuncID = 26;
                          $osite->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                          
                          //Send email alerts if site was created
                          $vtitle = "New PEGS was created.";
                          $dateformat = date("Y-m-d h:i:s A", strtotime($vdate)); //formats date on 12 hr cycle AM / PM 
                          $arrisland = array($vIslandId);
                          $arrregion = array($vRegionID);
                          $arrprovince = array($vProvinceID);
                          $arrcity = array($vCityID);
                          $arrbrgy = array($vBarangayID);
                          
                          $rlocation = $osite->getlocationname($arrisland, $arrregion, $arrprovince, $arrcity, $arrbrgy);
                          
                          $visland = '';
                          $vregion = '';
                          $vprovince = '';
                          $vcity = '';
                          $vbrgy = '';
                          if(count($rlocation) > 0){
                              foreach($rlocation as $val){
                                 $visland = $val['IslandName'];
                                 $vregion = $val['RegionName'];
                                 $vprovince = $val['ProvinceName'];
                                 $vcity = $val['CityName'];
                                 $vbrgy = $val['BarangayName'];
                              }
                          }
                          
                          $vupdatedby = '';
                          $arrAID = array($aid);
                          $rsite = $osite->getfullname($arrAID);
                          if(count($rsite) > 0){
                             $vupdatedby = $rsite[0]['Name'];
                          }
                          
                          $vmessage = "<html>
                                           <head>
                                                  <title>$vtitle</title>
                                           </head>
                                           <body>
                                                <br/><br/>
                                                  PEGS Name : $vSiteName
                                                <br />
                                                  PEGS Code : $vSiteCode
                                                <br />
                                                  Island : $visland
                                                <br />
                                                  Region : $vregion
                                                <br />
                                                  Province : $vprovince
                                                <br />
                                                  City : $vcity
                                                <br />
                                                  Barangay : $vbrgy
                                                <br />
                                                   Address: $vSiteAddress
                                                <br />
                                                   Contact Number: $vcontactno
                                                <br /><br />
                                                  Date Created: $dateformat
                                                <br/><br/>
                                                  Created By : $vupdatedby
                                                <br/><br/>           
                                           </body>
                                       </html>";
                          $osite->emailalerts($vtitle, $grouppegs, $vmessage);
                       }
                   }
                   unset($rdenomdefaults, $vmessage, $rlocation, $arrisland,
                          $arrregion, $arrprovince, $arrcity, $arrbrgy, $arrAID,
                          $rsite);
                }
                else
                {
                      $msg = "Site Creation: Invalid fields.";
                }
                $nopage= 1;
                $_SESSION['mess'] = $msg;
                $osite->close();
                header("Location: ../sitecreation.php");
          break;
          case 'SiteUpdate':
              $vSiteName = trim($_POST['txtsitename1']);
              $vSiteCode =  $terminalcode.trim($_POST['txtsitecode']);
              
              if(isset($_POST['cmbsiteowner']))
              {
                  $vOwnerAID = $_POST['cmbsiteowner'];
                  $voldownerAID = $_POST['txtoldowner']; //this will be use to check if owner was change
              }
              else
              {
                  $vOwnerAID = null; //if with selected account id then $vOwnerAID != null , else it must be set to null
              }
              $vSiteID = $_POST['txtsiteid'];
              //$vSiteGroupID = $_POST['cmbsitegrp'];
              $vSiteGroupID = 1;
              $vSiteDescription = trim($_POST['txtsitedesc']);
              //$vSiteAlias = $_POST['txtsitealias'];
              $vSiteAlias = null;
              $vIslandId = $_POST['cmbislands'];
              $vRegionID = $_POST['cmbregions'];
              $vProvinceID = $_POST['cmbprovinces'];
              $vCityID = $_POST['cmbcity'];
              $vBarangayID = $_POST['cmbbrgy'];
              $vSiteAddress = trim($_POST['txtsiteaddress']);
              $vCTO = trim($_POST['txtcto']);
              $vpasscode = trim($_POST['txtpasscode']);
              $vstatus = 1; //status to activate on siteaccounts
              $istestsite = $_POST['opttest']; //indicates if site is test or live
              $chksitecode = $terminalcode.trim($_POST['chksitecode']);
              $vctrsite = 0;
              $vcontactno = trim($_POST['txtctrycode']).'-'.trim($_POST['txtareacode']).'-'.trim($_POST['txtphone']);
               //this will check if site code is unique
               if($chksitecode != $vSiteCode)
               {
                  $ctrsitecode = $osite->checksitecode($vSiteCode);
                  $vctrsite = $ctrsitecode['count'];
               }
               
               if($vctrsite > 0)
               {
                    $msg = "Site/PEGS Creation : Site/PEGS Code exist";
               }
               else
               {
                   $siteid = $osite->updatesitedetails($vSiteID,$vSiteName,$vSiteCode,$vOwnerAID,$vSiteGroupID ,$vSiteDescription,$vSiteAlias,$vIslandId,$vRegionID,$vProvinceID,$vCityID,$vBarangayID,$vSiteAddress,$vCTO, $vpasscode, $vstatus, $voldownerAID, $istestsite, $vcontactno);
                   if($siteid == 0)
                   {
                     $msg = "Site/PEGS Update : Site/PEGS Accounts Profile unchanged";
                   }
                   else
                   {
                     $msg = "Site/PEGS Update : Site/PEGS profile updated";
                     $arrnewdetails = array($vOwnerAID, $vIslandId, $vRegionID, $vProvinceID, $vCityID, $vBarangayID, $vSiteName, $vSiteCode, $vSiteDescription, $vCTO, $vSiteAddress, $vpasscode, $vcontactno, $istestsite);
                     $newdetails = implode(",", $arrnewdetails);
                     //insert into audit trail
                     $vtransdetails = "old details ".$_POST['txtolddetails']." ;new details ".$newdetails;
                     $vdateupdated = $vdate;
                     $vauditfuncID = 27;
                     $osite->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdateupdated, $vipaddress, $vauditfuncID);
                     
                     //send email alert
                     $vtitle = "Changes in Site Profile";
                     
                     //lists all the old details
                     list($xownerID, $xislandID, $xregionID, $xprovID, $xcityID, $xbrgyID, $xsname, 
                             $xscode, $xsdesc, $xcto, $xaddress, $xpasscode, $xctrycode, $xareacode, 
                             $xcontactno, $xistest) = explode("-", $_POST['txtolddetails']);
                     $arrAID = array($aid, $voldownerAID, $vOwnerAID);
                     $rsite = $osite->getfullname($arrAID);
                     $ctr = 0;
                     while($ctr < count($rsite))
                     {
                         if(count($rsite) == 3)
                         {
                             if($ctr == 0)
                             {
                                $vupdatedby = $rsite[$ctr]['Name'];
                             }
                             if($ctr == 1)
                             {
                                 $vxowner = $rsite[$ctr]['Name'];
                             }
                             if($ctr == 2)
                             {
                                 $vowner = $rsite[$ctr]['Name'];
                             }
                         }
                         else
                         {
                             if($ctr == 0)
                             {
                                $vupdatedby = $rsite[$ctr]['Name'];
                             }
                             else
                             {
                                 $vxowner = $rsite[$ctr]['Name'];
                                 $vowner = $vxowner;
                             }
                         }
                         $ctr++;
                     }
                     
                     $arrisland = array($xislandID, $vIslandId);
                     $arrregion = array($xregionID, $vRegionID);
                     $arrprovince = array($xprovID, $vProvinceID);
                     $arrcity = array($xcityID, $vCityID);
                     $arrbrgy = array($xbrgyID, $vBarangayID);
                     $rlocation = $osite->getlocationname($arrisland, $arrregion, $arrprovince, $arrcity, $arrbrgy);
                     $ctr1 = 0;
                     
                     //looping to get old & new (island, region, province, city, barangay,)
                     while($ctr1 < count($rlocation))
                     {
                         //if changes made on the locations dropdown boxes
                         if(count($rlocation) == 2)
                         {
                             if($ctr1 == 0)
                             {
                                 $vxisland = $rlocation[$ctr1]['IslandName'];
                                 $vxregion = $rlocation[$ctr1]['RegionName'];
                                 $vxprovince = $rlocation[$ctr1]['ProvinceName'];
                                 $vxcity = $rlocation[$ctr1]['CityName'];
                                 $vxbrgy = $rlocation[$ctr1]['BarangayName'];
                             }
                             if($ctr1 == 1)
                             {
                                 $visland = $rlocation[$ctr1]['IslandName'];
                                 $vregion = $rlocation[$ctr1]['RegionName'];
                                 $vprovince = $rlocation[$ctr1]['ProvinceName'];
                                 $vcity = $rlocation[$ctr1]['CityName'];
                                 $vbrgy = $rlocation[$ctr1]['BarangayName'];
                             }
                         }
                         else
                         {
                             $vxisland = $rlocation[$ctr1]['IslandName'];
                             $vxregion = $rlocation[$ctr1]['RegionName'];
                             $vxprovince = $rlocation[$ctr1]['ProvinceName'];
                             $vxcity = $rlocation[$ctr1]['CityName'];
                             $vxbrgy = $rlocation[$ctr1]['BarangayName'];
                             $visland = $rlocation[$ctr1]['IslandName'];
                             $vregion = $rlocation[$ctr1]['RegionName'];
                             $vprovince = $rlocation[$ctr1]['ProvinceName'];
                             $vcity = $rlocation[$ctr1]['CityName'];
                             $vbrgy = $rlocation[$ctr1]['BarangayName'];
                         }
                         $ctr1++;
                     }
                     
                     $dateformat = date("Y-m-d h:i:s A", strtotime($vdateupdated)); //formats date on 12 hr cycle AM / PM 
                     $vmessage = "
                                 <html>
                                   <head>
                                          <title>$vtitle</title>
                                   </head>
                                   <body>
                                        <br/><br/>
                                            $vtitle -- $vSiteCode
                                        <br/><br/>
                                            From (Old Details) : 
                                        <br /><br />
                                            Operator: $vxowner
                                        <br/>   
                                            Island: $vxisland
                                        <br />
                                            Region: $vxregion
                                        <br/>
                                            Province: $vxprovince
                                        <br/>
                                            City: $vxcity
                                        <br/>
                                            Barangay: $vxbrgy
                                        <br />
                                            Address: $xaddress
                                        <br/>
                                            e-Games Name: $xsname
                                        <br/>
                                            e-Games Code: $xscode
                                        <br/>
                                            e-Games Description: $xsdesc
                                        <br/>
                                            Contact Number: $xctrycode - $xareacode - $xcontactno
                                        <br/>
                                            Passcode: $xpasscode
                                       <br/>
                                            CTO: $xcto
                                        <br /> <br />
                                            To (New Details):
                                         <br /><br />
                                            Operator: $vowner
                                        <br/>   
                                            Island: $visland
                                        <br />
                                            Region: $vregion
                                        <br/>
                                            Province: $vprovince
                                        <br/>
                                            City: $vcity
                                        <br/>
                                            Barangay: $vbrgy
                                        <br />
                                            Address: $vSiteAddress
                                        <br/>
                                            e-Games Name: $vSiteName
                                        <br/>
                                            e-Games Code: $vSiteCode
                                        <br/>
                                            e-Games Description: $vSiteDescription
                                        <br/>
                                            Contact Number: $vcontactno
                                        <br/>
                                            Passcode: $vpasscode
                                        <br/>
                                            CTO: $vCTO
                                        <br /><br />
                                            Updated Date : $dateformat
                                        <br/><br/>
                                           Updated By : $vupdatedby
                                        <br/><br/>                            
                                    </body>
                                  </html>";
                     $osite->emailalerts($vtitle, $grouppegs, $vmessage);
                     //unset arrays used
                     unset($arrnewdetails);
                     unset($arrAID);
                     unset($arrisland);
                     unset($arrregion);
                     unset($arrprovince);
                     unset($arrcity);
                     unset($arrbrgy);
                     unset($rlocation);
                     
                     //for assigning of sites into operator account's
                     if($voldownerAID <> $vOwnerAID)
                     {
                         $raccdetails = $osite->viewallaccounts($vOwnerAID);
                         foreach($raccdetails as $row)
                         {
                             $vName = $row['Name'];
                             $vUserName = $row['UserName'];
                             $vEmail = $row['Email'];
                             $vLandline = $row['LandLine'];
                             $vMobileNumber = $row['MobileNumber'];
                             
                         }
                         $remail = preg_replace("/[0-9]+$/", "", $vEmail);
                         $arremail = array($remail);
                         $rsitecode = substr($vSiteCode, strlen($terminalcode));
                         $vtitle = $rsitecode . '-' . $vSiteName . ' Operator / Designee Account Created';
                         $vmessage = "$vName <br />
                                     $rsitecode - $vSiteName <br />
                                     Your Kronus e-Games Operator/Designee account has been created with the following details: <br />
                                     <b>Name: </b>$vName <br />
                                     <b>Username: </b> $vUserName <br />
                                     <b>Email Address: </b> $vEmail <br />
                                     <b>Contact Number: </b> $vLandline / $vMobileNumber  <br />
                                     <b>e-Games Assignment: </b> $rsitecode - $vSiteName <br />
                                     <br />
                                     You can now access the Site Operator Module of Kronus, Philweb's POS System for e-Games 
                                     and have the benefit of viewing your e-Games reports. In addition, you can now create your respective
                                     supervisor and cashier accounts. Please refer to the Kronus training manuals on the step by step
                                     creation of these accounts.
                                     If you do not have a copy of Kronus manuals, we will be more than happy to provide you with the one.
                                     Please request through our Customer Service Hotline. <br />";
                         $osite->emailalerts($vtitle, $arremail, $vmessage);
                     }
                   }
               }
              
               $nopage = 1;

               //redirect to site view page with corresponding popup message
               $osite->close();
               $_SESSION['mess']= $msg;
               header("Location: ../siteview.php?");
        break;
        //if page submit from sitestatusupdate.php
        case 'UpdateStatus':
            $vSiteID = $_POST['txtsiteid'];
            $vStatus = $_POST['optstatus'];
            
            //check if account type is topup, then redirect to topup (suspension of accounts);
            // else redirect to pegs(viewing of accounts)
            if($_SESSION['acctype'] == 5)
            {
                $path = "suspendaccount.php";
                $vauditfuncID = 48;
            }
            else
            {
                $path = "siteview.php";
                $vauditfuncID = 28;
            }
            
            $arrsite = array($vSiteID);
            $resultid = $osite->changestatus($arrsite,$vStatus);
            if($resultid > 0)
            {
               $msg = "Site/PEGS Update: Status/PEGS successfully changed";
               $vsitecode = $osite->getsitecode($vSiteID);
               //insert into audit trail --> DbHandler.class.php
               $vdateupdated = $vdate;
               $vtransdetails = "sitecode ".$vsitecode['SiteCode']." From ".$osite->refsitestatusname($_POST['txtoldstat'])." To ".$osite->refsitestatusname($vStatus);
               $osite->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdateupdated, $vipaddress, $vauditfuncID);
               
               //send email alert
               $vtitle = "Changes in Site Status";
               $arraid = array($aid);
               $raid = $osite->getfullname($arraid); //get full name of an account
               $ctr = 0;
               while($ctr < count($raid))
               {
                  $vupdatedby = $raid[$ctr]['Name'];
                  $ctr++;
               }
               $dateformat = date("Y-m-d h:i:s A", strtotime($vdateupdated)); //formats date on 12 hr cycle AM / PM 
               $vmessage = "
                             <html>
                               <head>
                                      <title>$vtitle</title>
                               </head>
                               <body>
                                    <br/><br/>
                                        $vtitle
                                    <br/><br/>
                                       Site Code: ".$vsitecode['SiteCode']."
                                    <br/><br/>
                                       Remarks:  From ".$osite->refsitestatusname($_POST['txtoldstat']). " To " .$osite->refsitestatusname($vStatus)."
                                    <br /><br />
                                       Updated Date : $dateformat
                                    <br/><br/>
                                       Updated By : ".$vupdatedby."
                                    <br/><br/>                            
                                </body>
                              </html>";
               $osite->emailalerts($vtitle, $grouppegs, $vmessage);
               unset($arrsite, $resultid, $vsitecode, $vdateupdated, $vtransdetails, 
                     $vtitle, $arraid, $raid, $dateformat, $vmessage);
            }
            else
            {
               $msg = "Site/PEGS Update: Site/PEGS status unchanged";
            }
            
            $osite->close();
            $_SESSION['mess']= $msg;
            unset($vSiteID, $vStatus);
            header("Location: ../$path");
        break;
        case 'SiteView':
            $vsiteID = $_POST['cmbsite'];
            $rsitecode = $osite->getsitecode($vsiteID); //get the sitecode first
            $resultsearchsite = array();
            $sites = array();
            $resultsearchsite = $osite->viewsitedetails($vsiteID);
            foreach($resultsearchsite as $row)
            {
                $rsiteID = $row['SiteID'];
                $rsiteCode = $row['SiteCode'];
                $rsitedesc = $row['SiteDescription'];
                $rsiteaddress = $row['SiteAddress'];
                $rstatus = $row['Status'];
                $rsitename = $row['SiteName'];
                $sitecode = $terminalcode;
                
                //search if the sitecode was found on the terminalcode
                if(strstr($rsiteCode, $terminalcode) == false)
                {
                    $rsiteCode = $row['SiteCode'];
                }
                else
                {
                    //removes the "icsa-" code
                    $rsiteCode = substr($rsiteCode, strlen($terminalcode));
                }

                //create a new array to populate the combobox
                $newvalue = array("SiteID" => $rsiteID, "SiteCode" => $rsiteCode, "SiteDescription" => $rsitedesc, "SiteAddress" => $rsiteaddress, "SiteName" => $rsitename, "Status" => $rstatus);
                array_push($sites, $newvalue);
                unset($newvalue);
            }
            echo json_encode($sites);
            unset($resultsearchsite);
            unset($sites);
            $osite->close();
            exit;
        break;
        case 'UpdateDenomination':
          $vregmininitial = $_POST['cmbmininitial'];
          $vregmaxinitial = $_POST['cmbmaxinitial'];
          $vinitialreg = $_POST['txtinitialreg'];
          
          $vregminreload = $_POST['cmbminregular'];
          $vregmaxreload = $_POST['cmbmaxregular'];
          $vreloadreg = $_POST['txtreloadreg'];
          
          $vvipmininitial = $_POST['cmbmininitvip'];
          $vvipmaxinitial = $_POST['cmbmaxinitvip'];
          $vinitialvip = $_POST['txtinitialvip'];
          
          $vvipminreload = $_POST['cmbminrelvip'];
          $vvipmaxreload = $_POST['cmbmaxrelvip'];
          $vreloadvip = $_POST['txtreloadvip'];
          
          $newdetails = implode(",", array($vinitialreg, $vregmininitial, $vregmaxinitial, 
                         $vreloadreg, $vregminreload, $vregmaxreload, $vinitialvip, $vvipmininitial, $vvipmaxinitial,
                         $vreloadvip, $vvipminreload, $vvipmaxreload));
          $vsiteID = $_POST['cmbsitename'];
          $rdenomdefaults = array('initialreg' => array('DenominationName' => $vinitialreg, 'MinInitialValue' => $vregmininitial, 'MaxInitialValue' => $vregmaxinitial), 
              'reloadreg' => array('DenominationName' => $vreloadreg, 'MinInitialValue' => $vregminreload, 'MaxInitialValue' => $vregmaxreload), 
              'initialvip' => array('DenominationName' => $vinitialvip, 'MinInitialValue' => $vvipmininitial, 'MaxInitialValue' => $vvipmaxinitial), 
              'reloadvip' => array('DenominationName' => $vreloadvip, 'MinInitialValue' => $vvipminreload, 'MaxInitialValue' => $vvipmaxreload));
          $rdenomresult = $osite->updatedenomination($rdenomdefaults, $vsiteID, $aid);
          if($rdenomresult > 0)
          {
              $msg = "Site Denomination: Successfully updated";
              //insert into audit trail
              $vtransdetails = "sitecode ".$_POST['sitecode']." ;old denominations".$_POST['txtolddetails']." ;new denominations ".$newdetails;
              $vauditfuncID = 42;
              $osite->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
          }
          else{
              $msg = "Site Denomination: Details unchanged";
          }
          $_SESSION['mess'] = $msg;
          unset($_SESSION['site']);
          unset($_SESSION['denomination']);
          unset($_SESSION['denomamount']);
          unset($rdenomdefaults);
          $osite->close();
          header("Location: ../updatedenom.php");
        break;
    
        //get site denominations
        case 'SiteDenomination':
            if(isset ($_POST['cmbsitename']))
            {
                $vsiteID = $_POST['cmbsitename'];
                $rdenominations = $osite->getsitedenoms($vsiteID);
                 
                if(count($rdenominations) == 0)
                {
                     $msg = "No Denominations found for this site";
                     $_SESSION['mess'] = $msg;
                     $osite->close();
                     header("Location: ../updatedenom.php");
                }
                else
                {
                     $_SESSION['denomtable'] = "Enabled";
                     $_SESSION['denomination'] = $rdenominations;
                     $_SESSION['site'] = array($vsiteID, $_POST['txtsitecode']);
                     $rdenomamt = $osite->getdenomamounts();
                     $_SESSION['denomamount'] = $rdenomamt;
                     unset($rdenomamt);
                     $osite->close();
                     header("Location: ../updatedenom.php?table=enable");
                }
            }
        break;
        default:
            if($nopage == 0){ 
               $osite->close();
            }
      }
   }
   //page request from siteview.php
   elseif(isset($_GET['page'])=='ViewSite')
   {
        $vsiteID = $_GET['siteid'];
        $_SESSION['siteid'] = $vsiteID;
        $resultsite = array();
        $resultsite = $osite->viewsitedetails($vsiteID);
        
        if(count($resultsite) > 0)
        {
            foreach ($resultsite as $row)
            {
                $vislandID = $row['IslandID'];
                $vregiondID = $row['RegionID'];
                $vprovinceID = $row['ProvinceID'];
                $vcityID = $row['CityID'];
            }
            
            //get corresponding regions, provinces,cities, barangays
            $rregions = $osite->showregions($vislandID);
            $rprovince = $osite->showprovinces($vregiondID);
            $rcities = $osite->showcities($vprovinceID);
            $rbarangays = $osite->showbrgy($vcityID);
            
            $arrdemographics = array("Regions"=>$rregions,"Provinces"=>$rprovince,"Cities"=>$rcities,"Barangays"=>$rbarangays);
            
            
            $_SESSION['ressitedet']= $resultsite; //sesion variable for a particular site details
            $_SESSION['demographics'] = $arrdemographics; //session variable to get other demographics choices
            
            unset($arrdemographics);
            unset($resultsite);
            $osite->close();
            header("Location: ../siteedit.php"); 
        }
        else
        {
            $msg = "No Details found for this site";
            $_SESSION['mess'] = $msg;
            $osite->close();
            header("Location: ../siteview.php"); 
        }
   }
    //page request from siteedit.php
   elseif(isset($_GET['statuspage']) == 'UpdateStatus')
   {
        $vsiteID = $_GET['siteid'];
        $_SESSION['siteid'] = $vsiteID;
        $resultsearchsite = array();
        $resultsearchsite = $osite->viewsitedetails($vsiteID);
        $_SESSION['ressitedet']= $resultsearchsite;
        unset($resultsearchsite);
        $osite->close();
        header("Location: ../sitestatusupdate.php");
   }
   //restore all denomination amounts into its default settings
   elseif(isset ($_POST['restore']) == 'RestoreChanges')
   {
       $rdenomdefaults = array();
       $rdenomdefaults = $osite->getdefaultdenoms();
       
       if(count($rdenomdefaults) == 0)
       {
         $rdenomvalues= "No Denominations found for this site";
       }
       else
       {
         $rdenomvalues->initialreg = $rdenomdefaults[0];
         $rdenomvalues->reloadreg = $rdenomdefaults[1];
         $rdenomvalues->initialvip = $rdenomdefaults[2];
         $rdenomvalues->reloadvip = $rdenomdefaults[3];     
       }       
       echo json_encode($rdenomvalues);
       unset($rdenomdefaults);
       $osite->close();
       exit;
   }
   elseif(isset($_POST['sendIslandID']))
   {
       $vislandID = $_POST['sendIslandID'];
       $resultregions = array();
       $resultregions = $osite->showregions($vislandID);
       echo json_encode($resultregions);
       unset($resultregions);
       $osite->close();
       exit;
   }
   elseif(isset($_POST['sendRegionID']))
   {
       $vregionID = $_POST['sendRegionID'];
       $resultprovinces = array();
       $resultprovinces = $osite->showprovinces($vregionID);
       echo json_encode($resultprovinces);
       unset($resultprovinces);
       $osite->close();
       exit;
   }
   elseif(isset($_POST['sendProvID']))
   {
       $vprovID = $_POST['sendProvID'];
       $resultcities = array();
       $resultcities = $osite->showcities($vprovID);
       echo json_encode($resultcities);
       unset($resultcities);
       $osite->close();
       exit;
   }
   elseif(isset($_POST['sendCityID']))
   {
       $vcityID = $_POST['sendCityID'];
       $resultbrgy = array();
       $resultbrgy = $osite->showbrgy($vcityID);
       echo json_encode($resultbrgy);
       unset($resultbrgy);
       $osite->close();
       exit;
   }
    elseif(isset($_POST['sendOwnerID']))
   {
       $vownerAID = $_POST['sendOwnerID'];
       $resultOwnerStatus = array();
       $resultOwnerStatus =$osite->getOwnerStatus($vownerAID);
       switch ($resultOwnerStatus['Status']){
            case '1':
                $statValue = 'Active';
                break;
            case '6':
                $statValue = 'Password Expired';
                break;
        }
       echo $statValue;
       unset($resultOwnerStatus);
       $osite->close();
       exit;
   }
   elseif(isset($_POST['cmbsitename']))
   {
        $vsiteID = $_POST['cmbsitename'];
        $rresult = array();
        $rresult = $osite->getsitename($vsiteID);
        
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
        $osite->close();
        exit;
   }
   else
   {
       $_SESSION['viewsites'] = $resultsites;
       $_SESSION['resislands'] = $resultislands;
       $_SESSION['sitegrp'] = $resultsitegrp;
       $_SESSION['owner'] = $resultowner;
   }
}  
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>
