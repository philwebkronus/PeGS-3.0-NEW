<?php

include "../../sys/class/Login.class.php";
require '../../sys/core/init.php';
ini_set('display_errors',true);
ini_set('log_errors',true);


//create object
//$ologin = new Login('mysql:host=172.16.102.35;dbname=npos','nposconn','npos');
$_SESSION['mid']='';
$ologin = new Login($_DBConnectionString[0]);
//create connection
$openconn = $ologin->open();
//call function to generate date with microsecond
$date = $ologin->getDate();
//get ip address
$ipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
//get server name
$servername = $_SERVER['HTTP_HOST'];
if($openconn)
{

    $checkpasskey = $ologin->checkpasskey($_POST['txtpasskey']);
    if($checkpasskey == 0)
    {
       $msg = "Invalid Passkey";
       header("Location: ../views/login.php?mess=".$msg);
    }
    else
    {
      session_start();
      $old_sessionid = session_id();
      session_regenerate_id();
      $new_sessionid = session_id();
      $_SESSION['userid'] = $_SESSION['accID'] ;
      $vusername = $_SESSION['uname'] ;
      $aid =  $_SESSION['userid'] ;
      $access = $_SESSION['acctype'] ;
      $_SESSION['sessionID'] = $new_sessionid ;
      $_SESSION['acctype'] = $access;
      //check if with existing session               
      $isexistsession = $ologin->checksession($aid);     
      if($isexistsession > 0)
      {
          $ologin->deletesession($aid);    
          session_cache_expire();
          session_destroy();
          //$ologin->close();               
          //header('Location:  ../views/login.php');
      }   
      //updates loginattempt = 0, lastlogin and passkey
      $loginattempt = 0;
      $isadded = 0;
      $vsiteid = $ologin->getSiteID($aid);
      $issiteactive = $ologin->checkifactivesite($vsiteid['SiteID']);
      if($issiteactive['isactive'] > 0)
      {
          $updatedrow = $ologin->updateonlogin($loginattempt, $date, $vusername);
          //insert sessionid in sessionaccounts
          $result=$ologin->insertsession($aid, $new_sessionid, $date);
          if($result > 0)
          {
              if($_SESSION['smacid'] <> "")
              {
                  $path = $ologin->getpath($_SESSION['acctype']);
                  $ctrmachine = $ologin->checkmachineid($_SESSION['smachineid']);
                  $iscomputerexist = $ologin->checkcomputercredential($_SESSION['smachineid'],$_SESSION['AccountSiteID'] );
                  
                  //check if same machine ID but different site;
                  if(($ctrmachine['ctrmachine'] > 0) && ($ctrmachine['POSAccountNo'] <> $_SESSION['AccountSiteID']))
                  {
                      $msg = "Conflicting Machine ID. Please inform Customer Service";
                      session_destroy();
                      $ologin->close();
                      header("Location: ../views/login.php?mess=".$msg);
                  }
                  else
                  {
                      $isadded = 0;
                      //check if computer credential exist; if not exist, it must be added
                      if($iscomputerexist['CashierMachineInfoId_PK'] == 0 || $iscomputerexist['CashierMachineInfoId_PK'] == null)
                      {
                          $ctrcashier = $ologin->checkcashiermachine($_SESSION['AccountSiteID']); //get number of allowed cashier  per site
                          $issite = $ologin->checksitecount($_SESSION['AccountSiteID']);
                          $ctrsite = $issite['ctrsite'];
                          
                          //before adding, check if cashier machine count is greater than the number of pos account / site
                          if($ctrcashier['CashierMachineCount'] > $ctrsite)
                          {
                               $isadded = $ologin->addcomputercredential($_SESSION['scpuid'] ,$_SESSION['scpuname'],
                                    $_SESSION['sbiosid'],$_SESSION['smbid'],$_SESSION['sosid'] ,$_SESSION['smacid'],
                                    $_SESSION['sipid'],$_SESSION['sguid'] ,$_SESSION['AccountSiteID'], $_SESSION['smachineid'],$date);
                          }
                          else
                          {
                               $msg = "Please inform Customer Service to adjust the number of cashier terminal for this site";
                               session_destroy();
                               $ologin->close();
                               header("Location: ../views/login.php?mess=".$msg);
                          }
                      }
                  }
                  if($isadded > 0 || $iscomputerexist['CashierMachineInfoId_PK'] > 0)
                  {
                      //insert to audittrail table
                      $transdetails = "Login -".$aid;
                      $ologin->logtoaudit($new_sessionid, $aid, $transdetails, $date, $ipaddress, "1");

                      //redirect to index & close connection
                      $ologin->close();
                      header("Location: ".$path);
                  }
                  else
                  {
                      $ologin->deletesession($aid);    
                      session_cache_expire();
                      session_destroy();
                      $ologin->close();
                      $msg = "Login:Invalid computer credential";
                      header("Location: ../views/login.php?mess=".$msg);
                  }
              }
              else
              {
                  $ologin->deletesession($aid);    
                  session_cache_expire();
                  session_destroy();
                  $ologin->close();
                  $msg = "Login: MAC Address is empty";
                  header("Location: ../views/login.php?mess=".$msg);
              }
          }
          else
          {
             session_destroy();
             $msg = "Passkey: Session not created";
             //close connection redirect to login page
             $ologin->close();
             header("Location: ../views/login.php?mess=".$msg);
          }
      }    
      else
      {
          session_destroy();
          $msg = "Inactive Site";
          //close connection redirect to login page
          $ologin->close();
          header("Location: ../views/login.php?mess=".$msg);
      }
   }
}
else
{
   $msg =  "Not connected";
   header("Location: ../views/login.php?mess=".$msg);
}
?>

