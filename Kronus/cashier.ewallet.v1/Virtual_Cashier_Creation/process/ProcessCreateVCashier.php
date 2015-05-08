<?php

include "../model/CreateVCashier.php";

$vcashier = new CreateVCashier('mysql:host=10.0.103.21;dbname=npos,genkronus,qQr@Mnt76R88u3hMObSK');
$connected = $vcashier->open();
$vdate = $vcashier->getDate();

if($connected)
{
    if(isset($_POST['page']))
    {
        $vpage = $_POST['page'];
           switch($vpage)
           {
               case "CreateVirtualCashier":

                   $siteids = $vcashier->getAllSites();
                   
                   foreach ($siteids as $value) {
                       $siteid = $value['SiteID'];
                       
                       $count = $vcashier->checkVirtualCashier($siteid);
                       
                       if($count['Count'] == 0){
                                $sitecode = $vcashier->getSiteCode($siteid);

                                 //removes the "icsa-" code
                                  $rsiteCode = preg_replace('/^ICSA-/', '', $sitecode);
                                  if(is_array($rsiteCode)){
                                      $rsiteCode = $rsiteCode['SiteCode'];
                                  }

                                      $siteCode = $rsiteCode;
                                      $vLandline = '';
                                      $vMobileNumber = ''; 

                                      $username = 'CSHREGM'.$siteCode.'15';
                                      $sha = substr(sha1($username), -3);
                                      $UserName = 'CSHREGM'.$siteCode.$sha;
                                      $vUserName = strtoupper($UserName);
                                      
                                      $time = Date("m-d-y h:i:s");
                                      //reset password
                                      $vPassword = sha1("temppassword".$time);
                                      $vAccountTypeID = 15;

                                      $vStatus = 1;
                                      $vAccountGroupID = null;
                                      $vDateLastLogin = null;
                                      $vLoginAttempts = 0;
                                      $vSessionNoExpire = 0;
                                      $vDateCreated = $vdate; //get date and time

                                      $vCreatedByAID = 1;
                                      $vForChangePassword = 0;

                                      $vWithPasskey = 0;
                                      $vPasskey = null;
                                      $vdateissued = null;
                                      $vdateexpires = null;

                                      $vAID = 1; //session id
                                      $vName = 'Electronic Gaming Machine - '.$siteCode;
                                      $vAddress = 'Makati City';

                                      $vEmail = 'no-reply@philweb.com.ph';
                                      $vdesignationID = null;

                                      //$vOption1= trim($_POST['txtcto']);
                                      $vOption1 = " ";
                                      $vOption2= " ";
                                      $vSiteID = $siteid;

                                      $vcashier->insertaccount($vUserName,$vPassword,$vAccountTypeID,$vPasskey,$vStatus,$vAccountGroupID,$vDateLastLogin,$vLoginAttempts,
                                          $vSessionNoExpire,$vDateCreated,$vCreatedByAID,$vForChangePassword, $vWithPasskey,$vAID,$vName,$vAddress ,$vEmail,$vLandline,
                                          $vMobileNumber,$vOption1,$vOption2, $vdesignationID, $vSiteID, $vdateissued, $vdateexpires);
                       }
                       
                   }
                    
                        $msg = "Virtual Cashier successfully created";
                    
                   
                   echo json_encode($msg);

               break;
               case "CreateVirtualCashierEwallet":

                   $siteids = $vcashier->getAllSites();
                   foreach ($siteids as $value) {
                       $siteid = $value['SiteID'];
                       
                       $count = $vcashier->checkVirtualCashierEwallet($siteid);
                       
                       if($count['Count'] == 0){
                                $sitecode = $vcashier->getSiteCode($siteid);

                                 //removes the "icsa-" code
                                  $rsiteCode = preg_replace('/^ICSA-/', '', $sitecode);
                                  if(is_array($rsiteCode)){
                                      $rsiteCode = $rsiteCode['SiteCode'];
                                  }

                                      $siteCode = $rsiteCode;
                                      $vLandline = '';
                                      $vMobileNumber = ''; 

                                      $username = ' CSHREWVC'.$siteCode.'17';
                                      $sha = substr(sha1($username), -3);
                                      $UserName = ' CSHREWVC'.$siteCode.$sha;
                                      $vUserName = strtoupper($UserName);
                                      
                                      $time = Date("m-d-y h:i:s");
                                      //reset password
                                      $vPassword = sha1("temppassword".$time);
                                      $vAccountTypeID = 17;

                                      $vStatus = 1;
                                      $vAccountGroupID = null;
                                      $vDateLastLogin = null;
                                      $vLoginAttempts = 0;
                                      $vSessionNoExpire = 0;
                                      $vDateCreated = $vdate; //get date and time

                                      $vCreatedByAID = 1;
                                      $vForChangePassword = 0;

                                      $vWithPasskey = 0;
                                      $vPasskey = null;
                                      $vdateissued = null;
                                      $vdateexpires = null;

                                      $vAID = 1; //session id
                                      $vName = 'Ewallet virtual cashier - '.$siteCode;
                                      $vAddress = 'Makati City';

                                      $vEmail = 'no-reply@philweb.com.ph';
                                      $vdesignationID = null;

                                      //$vOption1= trim($_POST['txtcto']);
                                      $vOption1 = " ";
                                      $vOption2= " ";
                                      $vSiteID = $siteid;

                                      $vcashier->insertaccount($vUserName,$vPassword,$vAccountTypeID,$vPasskey,$vStatus,$vAccountGroupID,$vDateLastLogin,$vLoginAttempts,
                                          $vSessionNoExpire,$vDateCreated,$vCreatedByAID,$vForChangePassword, $vWithPasskey,$vAID,$vName,$vAddress ,$vEmail,$vLandline,
                                          $vMobileNumber,$vOption1,$vOption2, $vdesignationID, $vSiteID, $vdateissued, $vdateexpires);
                       }
                       
                   }
                    
                        $msg = "Virtual Cashier successfully created";
                    
                   
                   echo json_encode($msg);

               break;
           }
           $vcashier->close();
        exit;
    }
}
?>
