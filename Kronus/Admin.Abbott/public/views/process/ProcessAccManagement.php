<?php

/*
 * Created by : Lea Tuazon
 * Date Created : June 2, 2011
 *
 * Modified By: Edson L. Perez
 */
include __DIR__."/../sys/class/AccountManagement.class.php";
require  __DIR__."/../sys/core/init.php";

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

$oaccount = new AccountManagement($_DBConnectionString[0]);
$connected = $oaccount->open();
$nopage = 0;
if($connected)
{    
/************ SESSION CHECKING **************/        
   $isexist=$oaccount->checksession($aid);
   if($isexist == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $oaccount->close();
      if($oaccount->isAjaxRequest())
      {
          header('HTTP/1.1 401 Unauthorized');
          echo "Session Expired";
          exit;
      }
      header("Location: login.php?mess=".$msg);
   }    
   $isexistsession =$oaccount->checkifsessionexist($aid ,$new_sessionid);
   if($isexistsession == 0)
   {
      session_destroy();
      $msg = "Not Connected";
      $oaccount->close();
      header("Location: login.php?mess=".$msg);
   }
/************ END SESSION CHECKING **********/        
   
   //checks if account was locked 
   $islocked = $oaccount->chkLoginAttempts($aid);
   if(isset($islocked['LoginAttempts'])){
      $loginattempts = $islocked['LoginAttempts'];
      if($loginattempts >= 3){
          $oaccount->deletesession($aid);
          session_destroy();
          $msg = "Not Connected";
          $oaccount->close();
          header("Location: login.php?mess=".$msg);
          exit;
      }
   }
   
   $accountypes = array();
   //determine the list of types to be displayed in Account Types combo box
   switch($_SESSION['acctype'])
   {
       case 1:
           //admin
           $accountypes = $oaccount->getallaccounttypes(1);
           $accountypes=array_merge($accountypes,$oaccount->getallaccounttypes(5)); //Top-up
           $accountypes=array_merge($accountypes,$oaccount->getallaccounttypes(6)); //CS
           $accountypes=array_merge($accountypes,$oaccount->getallaccounttypes(8)); //PEGS
           $accountypes=array_merge($accountypes,$oaccount->getallaccounttypes(9)); //AS
           $accountypes=array_merge($accountypes,$oaccount->getallaccounttypes(12)); //Finance
           $accountypes=array_merge($accountypes,$oaccount->getallaccounttypes(13)); //Marketing
       break;
       case 2:
           //Operator
           $accountypes = $oaccount->getallaccounttypes(3); //supervisor    
           $accountypes=array_merge($accountypes,$oaccount->getallaccounttypes(4)); //cashier
       break;       
       case 8:
           //PEGS Ops
           $accountypes = $oaccount->getallaccounttypes(2); //operator
           //$accountypes = array_merge($accountypes,$oaccount->getallaccounttypes(10)); //liason disabled 02-08-12
           $accountypes = array_merge($accountypes,$oaccount->getallaccounttypes(11)); //PAGCOR
           $accountypes = array_merge($accountypes,$oaccount->getallaccounttypes(7)); //Standalone Terminal Monitoring
           $accountypes = array_merge($accountypes,$oaccount->getallaccounttypes(3)); //supervisor    
           $accountypes = array_merge($accountypes,$oaccount->getallaccounttypes(4)); //cashier
       break;       
       case 10:
            //Liason
           $accountypes = $oaccount->getallaccounttypes(3);
           $accountypes=array_merge($accountypes,$oaccount->getallaccounttypes(4)); 
       break;
       default:
           $accountypes = $oaccount->getallaccounttypes(0);
   }   
   $_SESSION['acctypes'] = $accountypes; //session variable for the account type selection  
   
//   $sitelist = array();
//   $sitelist = $oaccount->getallsites();
//   $_SESSION['sites'] = $sitelist; //session variable for the sites name selection
   
   //for pegs operations the sites that will populate the combobox are the unassigned site
   $unassignedsite = array();
   $unassignedsite = $oaccount->getsitenoowner($_SESSION['acctype']);
   $_SESSION['sites'] = $unassignedsite; //session variable for the sites name selection
   
   $operatorsite = $oaccount->getSiteID($aid);
   $_SESSION['opssite'] = $operatorsite['SiteID']; //session variable for operators account(to get their specific site)
   
   $designationlist = array();
   $designationlist = $oaccount->getdesignations();
   $_SESSION['designations'] = $designationlist; //session variable for designation of admin accounts
   
   $rsitesowned = $oaccount->viewsitebyowner($aid);
   $_SESSION['pegsowned'] = $rsitesowned;//session varible to get all sites owned by operator
   
//   $rsiteliason = $oaccount->getsiteliason();
//   $rnoliason = $oaccount->getsitenoliason($rsiteliason);
//   $_SESSION['liason'] = $rnoliason;
   
   //for pagination of accounts  (accountview.php)
   if(isset ($_POST['accpage']) == "Paginate")
   {
       $page = $_POST['page']; // get the requested page
       $limit = $_POST['rows']; // get how many rows we want to have into the grid
       $vacctype = $_POST['cmbacctype'];
       
       $rcount = array();
       switch($_SESSION['acctype'])
       {
          case 1:
               //admin
              $rcount1 = $oaccount->countviewaccounts(0, $vacctype, 0);              
              $rcount = $rcount1['count'];
          break;
          case 2:
               //Operator
               $rcount1 = $oaccount->countviewaccounts(0, $vacctype, $_SESSION['pegsowned']);
               $rcount = $rcount1['count'];
          break;
          case 8:
               //PEGS Ops
               $rcount1 = $oaccount->countviewaccounts(0, $vacctype, 0);
               $rcount = $rcount1['count'];
          break;
          /*
          case 10:
               //Liason
               $rcount1 = $oaccount->countviewaccounts(0, $vacctype, $_SESSION['pegsowned']);
               $rcount = $rcount1['count'];
          break;
           * 
           */
          default:
               $rcount1 = $oaccount->countviewaccounts(0, 0, 0);
               $rcount = $rcount1['count'];
      }
       $count = $rcount;
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
       
       //session variable for viewing of all accounts
       $allaccs = array();
       switch($_SESSION['acctype'])
       {
         case 1:
               //admin
               $allaccs = $oaccount->viewlimitaccounts(0, $vacctype, $start, $limit, 0);
         break;
         case 2:
               //Operator
              $allaccs = $oaccount->viewlimitaccounts(0, $vacctype, $start, $limit, $_SESSION['pegsowned']);
         break;
   
         case 8:
               //PEGS Ops
              $allaccs = $oaccount->viewlimitaccounts(0, $vacctype, $start, $limit, 0);
         break;
         /* disabled 02-08-12
         case 10:
               //Liason
               $allaccs = $oaccount->viewlimitaccounts(0, $vacctype, $start, $limit, $_SESSION['pegsowned']);
          * 
          */
         break;
         default:
               $allaccs = $oaccount->viewlimitaccounts(0, 0, $start, $limit, 0);
       }
       if(count($allaccs) > 0)
       {
         $i = 0;
         $responce->page = $page;
         $responce->total = $total_pages;
         $responce->records = $count;
         
         foreach($allaccs as $vview) 
         {
              $rstatus = $vview['Status'];
              $accID = $vview['AID'];
              $vstatname = $oaccount->showstatusname($rstatus);
              $responce->rows[$i]['id']=$vview['AID'];
              $responce->rows[$i]['cell']=array($vview['UserName'],$vview['Name'],$vview['Email'],$vview['Address'],$vstatname, "<input type=\"button\" value=\"Update Details\" onclick=\"window.location.href='process/ProcessAccManagement.php?accid=$accID'+'&page='+'ViewAccount';\"/>");
              $i++;
         }
       }
       else
       {
         $i = 0;
         $responce->page = $page;
         $responce->total = $total_pages;
         $responce->records = $count;
         $msg = "Account Management: No returned result";
         $responce->msg = $msg;
       }
       echo json_encode($responce);
       unset($allaccs);
       unset($rcount);
       $oaccount->close();
       exit;
   }
   
   //pagination for accountunlock
   if(isset ($_POST['unlockpage'])== "UnlockAccounts")
   {
       $page = $_POST['page']; // get the requested page
       $limit = $_POST['rows']; // get how many rows we want to have into the grid
       $vacctype = $_POST['cmbacctype'];
       
       $rcount = array();
       
       //count, loginattempts GT 3
       $rcount1 = $oaccount->countloginattempts($vacctype, $_SESSION['pegsowned'],$_SESSION['acctype']);    
       $rcount = $rcount1['count'];
       $count = $rcount;
       
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
       
       $accattempt = array();
       $accattempt = $oaccount->viewloginattempts($vacctype, $_SESSION['pegsowned'],$_SESSION['acctype'], $start, $limit); //view accounts w/ GT 3
       if(count($accattempt) > 0)
       {
         $i = 0;
         $responce->page = $page;
         $responce->total = $total_pages;
         $responce->records = $count;
         
         foreach($accattempt as $vview) 
         {
              $accID = $vview['AID'];
              $vaccname = $vview['UserName'];
              $responce->rows[$i]['id']=$vview['AID'];
              $responce->rows[$i]['cell']=array($vaccname,$vview['Name'], "<input type=\"button\" value=\"Unlock\" onclick=\"window.location.href='process/ProcessAccManagement.php?accname=$vaccname&accid=$accID'+'&unlockpage='+'AccountUnlock';\"/>");
              $i++;
         }
       }
       else
       {
         $i = 0;
         $responce->page = $page;
         $responce->total = $total_pages;
         $responce->records = $count;
         $msg = "Unlock Accounts: No returned result";
         $responce->msg = $msg;
       }
       echo json_encode($responce);
       unset($rcount);
       unset($accattempt);
       $oaccount->close();
       exit;
   }
   
   //for pagination of accounts (terminateaccount.php)
   if(isset ($_POST['TerminatePage']) == "TerminateAccount")
   {
       $page = $_POST['page']; // get the requested page
       $limit = $_POST['rows']; // get how many rows we want to have into the grid
       $vacctype = $_POST['cmbacctype'];
       //session variable for viewing of all accounts (counts)
       $rcount = array();
       switch($vacctype)
       {
          case 1:
               //admin
              $rcount1 = $oaccount->countviewaccounts(0, $vacctype, 0);              
              $rcount = $rcount1['count'];
          break;
          case 2:
               //Operator
               $rcount1 = $oaccount->countviewaccounts(0, $vacctype, 0);
               $rcount = $rcount1['count'];
          break;
          case 8:
               //PEGS Ops
               $rcount1 = $oaccount->countviewaccounts(0, $vacctype, 0);
               $rcount = $rcount1['count'];
          break;
          /* disabled 02-08-12
          case 10:
              //Liason
               $rcount1 = $oaccount->countviewaccounts(0, $vacctype, $_SESSION['pegsowned']);
               $rcount = $rcount1['count'];
          break;
           * 
           */
          default:
               $rcount1 = $oaccount->countviewaccounts(0, 0, 0);
               $rcount = $rcount1['count'];
      }
       $count = $rcount;
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
       
       //session variable for viewing of all accounts
       $allaccs = array();
       switch($_SESSION['acctype'])
       {
         case 1:
               //admin
               $allaccs = $oaccount->viewlimitaccounts(0, $vacctype, $start, $limit, 0);
         break;
         case 2:
               //Operator
              $allaccs = $oaccount->viewlimitaccounts(0, $vacctype, $start, $limit, $_SESSION['pegsowned']);
         break;
         case 8:
               //PEGS Ops
              $allaccs = $oaccount->viewlimitaccounts(0, $vacctype, $start, $limit, 0);
         break;
         /* disabled 02-08-12
         case 10:
              //Liason
               $allaccs = $oaccount->viewlimitaccounts(0, $vacctype, $start, $limit, $_SESSION['pegsowned']);
         break;
          * 
          */
         default:
               $allaccs = $oaccount->viewlimitaccounts(0, 0, $start, $limit, 0);
       }
       if(count($allaccs) > 0)
       {
         $i = 0;
         $responce->page = $page;
         $responce->total = $total_pages;
         $responce->records = $count;
         
         foreach($allaccs as $vview) 
         {
              $rstatus = $vview['Status'];
              $accID = $vview['AID'];
              switch($rstatus)
              {
                  case 0:
                      $vstatname = "Inactive";
                  break;
                  case 1:
                      $vstatname = "Active";
                  break;
                  case 2:
                      $vstatname = "Suspended";
                  break;
                  case 3:
                      $vstatname = "Locked(Attempts)";
                  break;
                  case 4:
                      $vstatname = "Locked(Admin)";
                  break;
                  case 5:
                      $vstatname = "Terminated";
                  break;
                  case 6:
                      $vstatname = "Password Expired";
                  break;
              }

              $vaccname = $vview['UserName'];
              $responce->rows[$i]['id']=$accID;
              $responce->rows[$i]['cell']=array($vview['UserName'],$vview['Name'],$vview['Email'],$vview['Address'],$vstatname, "<input type=\"button\" class=\"btnterminate\" value=\"Terminate Account\" onclick=\"window.location.href='process/ProcessAccManagement.php?accname=$vaccname&accid=$accID'+'&terminate='+'TerminateAccount';\" />");
              $i++;
         }
       }
       else
       {
         $i = 0;
         $responce->page = $page;
         $responce->total = $total_pages;
         $responce->records = $count;
         $msg = "Account Management: No returned result";
         $responce->msg = $msg;
       }
       echo json_encode($responce);
       unset($rcount);
       unset($allaccs);
       $oaccount->close();
       exit;
   }

   $vdate = $oaccount->getDate();
   $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
   $servername = $_SERVER['HTTP_HOST'];

    if(isset ($_POST['page'])){
        $vpage = $_POST['page'];
        switch ($vpage)
        {
            //page submit from accountview.php
            case 'AccountCreation':
                if(isset ($_POST['txtusername']) && isset($_POST['txtname']) && isset($_POST['txtaddress']) && isset($_POST['txtemail']) && isset($_POST['txtphone']) && isset($_POST['txtmobile']) && isset($_POST['cmbsite']) && isset($_POST['cmbacctype']))
                {
                    $errorphonenumber = 0; 
                    $errormobnumber = 0;
                             
                    //scenario 5 and 3
                    if(strlen($_POST['txtphone']) > 0)
                    {            
                        if(((strlen(trim($_POST['txtctrycode'])) == 0) || (strlen(trim($_POST['txtareacode'])) == 0)) && (strlen($_POST['txtmobile']) == 0))
                        {
                            $errorphonenumber = 1;
                        }

                       //scenario 1 and 2                    
                        if(((strlen(trim($_POST['txtctrycode'])) > 0) || (strlen(trim($_POST['txtareacode'])) > 0)) && (strlen($_POST['txtmobile']) == 0))
                        {            
                            if(strlen(trim($_POST['txtphone'])) == 0)
                            {
                                $errorphonenumber = 1;
                            }
                        }
                    }         

                   //scenario 5 and 3
                    if(strlen($_POST['txtmobile']) > 0)
                    {            
                        if(((strlen(trim($_POST['txtctrycode2'])) == 0) || (strlen(trim($_POST['txtareacode2'])) == 0)) && strlen($_POST['txtphone']) == 0)
                        {
                            $errormobnumber = 1;
                        }

                        //scenario 1 and 2                    
                        if(((strlen(trim($_POST['txtctrycode2'])) > 0) || (strlen(trim($_POST['txtareacode2'])) > 0)) && (strlen($_POST['txtphone']) == 0))
                        {            
                            if(strlen(trim($_POST['txtmobile'])) == 0)
                            {
                                $errormobnumber = 1;
                            }
                        }                     
                    }
                    
                    if(($errorphonenumber == 0) && ($errormobnumber == 0))    
                    {
                        //get all POST variables
                        //check if phone number with country and area code are filled up
                        if((strlen(trim($_POST['txtctrycode'])) > 0) && (strlen(trim($_POST['txtareacode'])) > 0) && (strlen($_POST['txtphone']) > 0))
                        {
                           $vLandline = trim($_POST['txtctrycode']).'-'.trim($_POST['txtareacode']).'-'.trim($_POST['txtphone']);
                        }
                        //check if mobile number with country and area code are filled up
                        if((strlen(trim($_POST['txtctrycode2'])) > 0) && (strlen(trim($_POST['txtareacode2'])) > 0) && (strlen($_POST['txtmobile']) > 0))
                        {
                           $vMobileNumber = trim($_POST['txtctrycode2']).'-'.trim($_POST['txtareacode2']).'-'.trim($_POST['txtmobile']);
                        }
                        
                        $vUserName = trim($_POST['txtusername']);
                        $vPassword = sha1('temppass');
                        $vAccountTypeID = $_POST['cmbacctype'];

                        $vStatus = 1;
                        $vAccountGroupID = null;
                        $vDateLastLogin = null;
                        $vLoginAttempts = 0;
                        $vSessionNoExpire = 0;
                        $vDateCreated = $vdate; //get date and time

                        $vCreatedByAID = 1;
                        $vForChangePassword = 0;

                        $wpkey = $_POST['optpkey'];
                        if($wpkey == 1)
                        {
                            $vWithPasskey = 1;
                            $vPasskey = '12345678';
                            $vdateissued = $vdate;
                            //passkey will expire 8 hours after it was created
                            $vdateexpires = date ( 'Y-m-d H:i:s.u' , strtotime ('+8 hour' , strtotime($vdateissued))); 
                        }
                        else
                        {
                            $vWithPasskey = 0;
                            $vPasskey = null;
                            $vdateissued = null;
                            $vdateexpires = null;
                        }

                        $vAID = $aid; //session id
                        $vName = trim($_POST['txtname']);
                        $vAddress = trim($_POST['txtaddress']);

                        //if corporate account type, email address must be the corporate email
                        if($_SESSION['acctype'] == 1)
                        {
                            $vEmail = trim($_POST['txtcorpemail'])."@philweb.com.ph";
                            $vdesignationID = $_POST['cmbdesignation'];
                        }

                        else{
                            $vEmail = trim($_POST['txtemail']);
                            $vdesignationID = null;
                        }


                        //$vOption1= trim($_POST['txtcto']);
                        $vOption1 = " ";
                        $vOption2= " ";
                        $vSiteID = $_POST['cmbsite'];
                        $isuname = $oaccount->checkusername($vUserName);
                        $isemail = $oaccount->checkemail($vEmail);

                        if($isemail['emailcount'] > 0)
                        {
                            $vctremail = $isemail['emailcount'];
                            $vEmail = $vEmail.$vctremail; //if email exist append number to the last portion of string
                        }
                        
                        if($isuname > 0)
                        {
                            $msg = "Account Creation : User name exist.";
                            $_SESSION['mess'] = $msg;
                            $oaccount->close();
                            header("Location: ../accountcreation.php");
                        }
                        else
                        {
                            $resultid = $oaccount->insertaccount($vUserName,$vPassword,$vAccountTypeID,$vPasskey,$vStatus,$vAccountGroupID,$vDateLastLogin,$vLoginAttempts,
                            $vSessionNoExpire,$vDateCreated,$vCreatedByAID,$vForChangePassword, $vWithPasskey,$vAID,$vName,$vAddress ,$vEmail,$vLandline,
                            $vMobileNumber,$vOption1,$vOption2, $vdesignationID, $vSiteID, $vdateissued, $vdateexpires);
                           if($resultid > 0)
                           {
                              $time = Date("m-d-y h:i:s");
                              $remail = preg_replace("/[0-9]+$/", "", $vEmail);
                              $to = $remail;
                              $subject = 'Change Initial Password';
                              $message = "
                                       <html>
                                       <head>
                                               <title>Change Initial Password</title>
                                       </head>
                                       <body>
                                            <i>Hi Mr/Ms. $vUserName</i>,
                                            <br/><br/>
                                                It is advisable that you change your password upon log-in.
                                            <br/><br/>
                                                Please click through the link provided below to log-in to your account.
                                            <br/><br/>

                                            <div>
                                                <b><a href='http://$servername/UpdatePassword.php?username=".urlencode($vUserName)."&password=".urlencode($vPassword)."&aid=".urlencode($resultid)."'>Change initial password</a></b>
                                            </div>
                                            <br />
                                                For further inquiries, please call our Customer Service hotline at telephone numbers (02) 3383388 or toll free from
                                                PLDT lines 1800-10PHILWEB (1800-107445932)
                                                or email us at <b>customerservice@philweb.com.ph</b>.
                                            <br/><br/>
                                                Thank you and good day!
                                            <br/><br/>
                                            Best Regards,<br/>
                                            PhilWeb Customer Service Team
                                            <br /><br />
                                            This email and any attachments are confidential and may also be
                                            privileged.  If you are not the addressee, do not disclose, copy,
                                            circulate or in any other way use or rely on the information contained
                                            in this email or any attachments.  If received in error, notify the
                                            sender immediately and delete this email and any attachments from your
                                            system.  Any opinions expressed in this message do not necessarily
                                            represent the official positions of PhilWeb Corporation. Emails cannot
                                            be guaranteed to be secure or error free as the message and any
                                            attachments could be intercepted, corrupted, lost, delayed, incomplete
                                            or amended.  PhilWeb Corporation and its subsidiaries do not accept
                                            liability for damage caused by this email or any attachments and may
                                            monitor email traffic.
                                        </body>
                                     </html>";
                             $headers="From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html";
                             $sentEmail = mail($to, $subject, $message, $headers);
                             // Check if email is sent or not
                             if($sentEmail == 1)
                             {
                                $msg = "Account Creation: Account successfully created. You can now change your initial password on your email";
                                //send an email notification to operator account only
                                if($vAccountTypeID == 2)
                                {
                                    $arremail = array($remail);
                                    $sitedetails = $oaccount->getsitename($vSiteID);
                                    foreach($sitedetails as $row)
                                    {
                                        $vsitename = $row['SiteName'];
                                        $vsitecode = $row['SiteCode'];
                                    }
                                    $rsitecode = substr($vsitecode, strlen($terminalcode));
                                    $vtitle = $rsitecode.'-'.$vsitename.' Operator / Designee Account Created';
                                    $vmessage = "$vName <br />
                                                 $rsitecode - $vsitename <br />
                                                 Your Kronus PeGS Operator/Designee account has been created with the following details: <br />
                                                 <b>Name: </b>$vName <br />
                                                 <b>Username: </b> $vUserName <br />
                                                 <b>Email Address: </b> $vEmail <br />
                                                 <b>Contact Number: </b> $vLandline / $vMobileNumber  <br />
                                                 <b>PeGS Assignment: </b> $rsitecode - $vsitename <br />
                                                 <br />
                                                 You can now access the Site Operator Module of Kronus, Philweb's POS System for eGAMES 
                                                 and have the benefit of viewing your PeGS reports. In addition, you can now create your respective
                                                 supervisor and cashier accounts. Please refer to the Kronus training manuals on the step by step
                                                 creation of these accounts.
                                                 If you do not have a copy of Kronus manuals, we will be more than happy to provide you with the one.
                                                 Please request through our Customer Service Hotline. <br />";
                                    $oaccount->emailalerts($vtitle, $arremail, $vmessage);
                                }
                             }
                             else
                             {
                                $msg = "Account Creation : Message sending failed";
                             }

                             //check account type ID to distinguish audit trail function ID
                             switch ($vAccountTypeID)
                             {
                                 //operator
                                 case 2:
                                     $vauditfuncID = 23;
                                 break;
                                 //supervisor
                                 case 3:
                                     $vauditfuncID = 8;
                                 break;
                                 //cashier
                                 case 4:
                                     $vauditfuncID = 11;
                                 break;
                                 //admin accounts (admin, pegsops, topup, cs, as)
                                 default :
                                     $vauditfuncID = 14;
                                 break;
                             }

                             //insert into audit trail
                             $vtransdetails = $resultid; //account id
                             $oaccount->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                         }
                         else
                         {
                           $msg = "Account Creation : Failed to add account";
                         }
                      }
                    }
                    else
                    {
                        $msg = "Account Creation: Invalid country code or area code or contact number";
                    }
                }
                else
                {
                      $msg = "Account Creation: Invalid fields.";
                }
                $nopage= 1;
                $_SESSION['mess'] = $msg;
                $oaccount->close();
                header("Location: ../accountcreation.php");
            break;
            //if page submit from accountedit.php
            case 'AccountsUpdate':
                $errorphonenumber = 0; 
                $errormobnumber = 0;
               //scenario 5 and 3
                if(strlen($_POST['txtphone']) > 0)
                {            
                    if(((strlen(trim($_POST['txtctrycode'])) == 0) || (strlen(trim($_POST['txtareacode'])) == 0)) && (strlen($_POST['txtmobile']) == 0))
                    {
                        $errorphonenumber = 1;
                    }
                    
                   //scenario 1 and 2                    
                    if(((strlen(trim($_POST['txtctrycode'])) > 0) || (strlen(trim($_POST['txtareacode'])) > 0)) && (strlen($_POST['txtmobile']) == 0))
                    {            
                        if(strlen(trim($_POST['txtphone'])) == 0)
                        {
                            $errorphonenumber = 1;
                        }
                    }
                }         

               //scenario 5 and 3
                if(strlen($_POST['txtmobile']) > 0)
                {            
                    if(((strlen(trim($_POST['txtctrycode2'])) == 0) || (strlen(trim($_POST['txtareacode2'])) == 0)) && strlen($_POST['txtphone']) == 0)
                    {
                        $errormobnumber = 1;
                    }
                    
                    //scenario 1 and 2                    
                    if(((strlen(trim($_POST['txtctrycode2'])) > 0) || (strlen(trim($_POST['txtareacode2'])) > 0)) && (strlen($_POST['txtphone']) == 0))
                    {            
                        if(strlen(trim($_POST['txtmobile'])) == 0)
                        {
                            $errormobnumber = 1;
                        }
                    }                     
                }   
                
                if(($errorphonenumber == 0) && ($errormobnumber == 0))    
                {
                    //get all post variables
                     $vAID = trim($_POST['txtaccid']);
                     $vAccountTypeID = trim($_POST['txtacctype']);
                     $vName = trim($_POST['txtname']);
                     $vAddress = trim($_POST['txtaddress']);
                     $emailforcheck = trim($_POST['txtemail2']);
                     $vUserName = trim($_POST['txtusername']);
                     //if corporate account type, email address must be the corporate email
                     if($_SESSION['acctype'] == 1)
                     {
                        $vEmail = trim($_POST['txtcorpemail'])."@philweb.com.ph".$_POST['txtappendnum'];       
                        $emailforcheck = trim($_POST['txtemail2'])."@philweb.com.ph".$_POST['txtappendnum'];
                        $vdesignationID = $_POST['cmbdesignation'];
                     }
                    
                     else
                     {
                        $vEmail = trim($_POST['txtemail']);  
                        $emailforcheck = trim($_POST['txtemail2']);
                        $vdesignationID = null;
                     }
                     
                     //check if phone number with country and area code are filled up
                     if((strlen(trim($_POST['txtctrycode'])) > 0) && (strlen(trim($_POST['txtareacode'])) > 0) && (strlen($_POST['txtphone']) > 0))
                     {
                         $vLandline = trim($_POST['txtctrycode']).'-'.trim($_POST['txtareacode']).'-'.trim($_POST['txtphone']);
                     }
                     //check if mobile number with country and area code are filled up
                     if((strlen(trim($_POST['txtctrycode2'])) > 0) && (strlen(trim($_POST['txtareacode2'])) > 0) && (strlen($_POST['txtmobile']) > 0))
                     {
                         $vMobileNumber = trim($_POST['txtctrycode2']).'-'.trim($_POST['txtareacode2']).'-'.trim($_POST['txtmobile']);
                     }
                     
                     $vOption1 = " ";
                     $vOption2 = " ";
                     
                     $vPasskey = $_POST['optpkey'];
                     
                     //$isuname = $oaccount->checkusername($vUserName);               
                     $ctremail = 0;
                     if($emailforcheck != $vEmail)
                     {
                         $isemail = $oaccount->checkemail($vEmail);
                         $ctremail = $isemail['emailcount'];
                     }
//                     if($isuname > 0)
//                     {
//                        $msg = "Account Update : User name exist.";
//                        $_SESSION['mess'] = $msg;
//                        $oaccount->close();
//                        header("Location: ../accountview.php");
//                     }
                     if($ctremail > 0)
                     {
                        $vEmail = $vEmail.$ctremail; //if email exist append number to the last portion of string
                     }

                     $resultid = $oaccount->updateaccountdetails($vAID, $vAccountTypeID, $vName, $vAddress, $vEmail, $vLandline, $vMobileNumber, $vOption1, $vOption2, $vPasskey, $vdesignationID);

                     if ($resultid > 0) {
                        $msg = "Account Update : Successfully updated";
                        //check account type ID to distinguish audit trail function ID
                        switch ($vAccountTypeID) {
                            //operator
                            case 2:
                                $vauditfuncID = 24;
                                break;
                            //supervisor
                            case 3:
                                $vauditfuncID = 9;
                                break;
                            //cashier
                            case 4:
                                $vauditfuncID = 12;
                                break;
                            //admin accounts (admin, pegsops, topup, cs, as)
                            default :
                                $vauditfuncID = 15;
                                break;
                        }

                        $vnewdetails = array($vName, $vEmail, $vLandline, $vMobileNumber, $vAddress);
                        $newdetails = implode("-", $vnewdetails);
                        $vdateupdated = $vdate;
                        //insert into audit trail
                        $vtransdetails = "old details " . $_POST['txtolddetails'] . " ;new details " . $newdetails;
                        $oaccount->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdateupdated, $vipaddress, $vauditfuncID);

                        //send email alert
                        //list old parameters
                        list($xuname, $xname, $xemail, $xctryphone, $xareaphone, $xphone, $xctrymobile,
                                $xareamobile, $xmobile, $xaddress) = explode("-", $_POST['txtolddetails']);
                        $vtitle = "Changes in Account Profile";
                        $arrAID = array($aid);
                        $raid = $oaccount->getfullname($arrAID); //get full name of an account
                        $ctr = 0;
                        while ($ctr < count($raid)) {
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
                                                    $vtitle -- " . $_SESSION['accname'] . "
                                                <br/><br/>
                                                    From (Old Details) : 
                                                <br /><br />
                                                    Username: " . $xuname . "
                                                <br/>   
                                                    Name: " . $xname . "
                                                <br />
                                                    Email : " . $xemail . "
                                                <br/>
                                                    Landline (With Area Code): " . $xctryphone . " - " . $xareaphone . " - " . $xphone . "
                                                <br/>
                                                    Mobile Number: " . $xctrymobile . " - " . $xareamobile . " - " . $xmobile . "
                                                <br />
                                                    Address: " . $xaddress . "
                                                <br /> <br />
                                                    To (New Details):
                                                <br /><br />
                                                    Username: $vUserName
                                                <br/>   
                                                    Name: $vName
                                                <br />
                                                    Email : $vEmail
                                                <br/>
                                                    Landline (With Area Code): $vLandline
                                                <br/>
                                                    Mobile Number: $vMobileNumber
                                                <br />
                                                    Address: $vAddress
                                                <br /><br />
                                                    Updated Date : $dateformat
                                                <br/><br/>
                                                   Updated By : " . $vupdatedby . "
                                                <br/><br/>                            
                                            </body>
                                          </html>";
                        $oaccount->emailalerts($vtitle, $grouppegs, $vmessage);
                        unset($arrAID);
                    } else {
                        $msg = "Account Update : Account details unchanged";
                    }
                    
                }
                else
                {
                    $msg = "Account Update: Invalid country code or area code or contact number";
                }
                $nopage = 1;
                $_SESSION['mess'] = $msg;
                $oaccount->close();
                header("Location: ../accountview.php");
            break;
            //if page submit from accountstatusupdate.php
            case 'StatusUpdate':
                $vAID = $_POST['txtaccid'];
                $vStatus = $_POST['optstatus'];
                $acctype = $_POST['txtacctype'];
                
                $resultid =$oaccount->updatestatus($vAID,$vStatus);
                if($resultid > 0)
                {
                  $isexist=$oaccount->checksession($vAID);
                  
                  if($isexist > 0)
                  {
                      $oaccount->deletesession($vAID);
                  }
                  
                  if($acctype == 2 && $vStatus <> 1)
                    $msg = "Account Deactivated : Please update the assigned site for this user";
                  else
                    $msg = "Account Update : Status Updated";
                  
                  switch ($_POST['txtacctype'])
                  {
                     //operator
                     case 2:
                           $vauditfuncID = 25;
                     break;
                     //supervisor
                     case 3:
                           $vauditfuncID = 10;
                     break;
                     //cashier
                     case 4:
                           $vauditfuncID = 13;
                     break;
                     //admin accounts (admin, pegsops, topup, cs, as)
                     default :
                           $vauditfuncID = 16;
                     break;
                  }
                  //insert into audit trail --> DbHandler.class.php
                  $vdateupdated = $vdate;
                  $vtransdetails = "Username: ".$_POST['txtaccname'].";From ".$oaccount->showstatusname($_POST['txtoldstatus'])." To ".$oaccount->showstatusname($vStatus);
                  $oaccount->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                  
                  //send email alert
                  $vtitle = "Changes in Account Status";
                  $arrAID = array($aid, $vAID);
                  $raid = $oaccount->getfullname($arrAID);
                  $ctr = 0;
                  
                  //looping to get the updated By and fullname of the account username
                  while($ctr < count($raid))
                  {
                      if(count($raid) == 2)
                      {
                          if($ctr == 0)
                          {
                              $vupdatedby = $raid[$ctr]['Name'];
                          }
                          if($ctr == 1)
                          {
                              $vfullname = $raid[$ctr]['Name'];
                          }
                      }
                      else
                      {
                          if($ctr == 0)
                          {
                              $vupdatedby = $_SESSION['accname'];
                              $vfullname = $raid[$ctr]['Name'];
                          }
                      }
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
                                        $vtitle -- ".$_SESSION['accname']."
                                    <br/><br/>
                                       Username: ".$_POST['txtaccname']."
                                    <br/><br/>
                                       Full Name: ".$vfullname."
                                    <br/><br/>
                                       Remarks:  From ".$oaccount->showstatusname($_POST['txtoldstatus']). " To " .$oaccount->showstatusname($vStatus)."
                                    <br /><br />
                                       Updated Date : $dateformat
                                    <br/><br/>
                                       Updated By : ".$vupdatedby."
                                    <br/><br/>                            
                                </body>
                              </html>";
                  $oaccount->emailalerts($vtitle, $grouppegs, $vmessage);
                  unset($isexist, $vtransdetails, $vdateupdated, $raid, 
                        $dateformat, $vmessage, $arrAID, $vtitle);
                }
                else
                {
                    $msg = "Account Update : Status unchanged";
                }
                
                $oaccount->close();
                $_SESSION['mess'] = $msg;
                unset($vAID, $vStatus, $acctype, $resultid);
                header("Location: ../accountview.php");
            break;
            case 'AccountView':
                $vaccID = $_POST['cmbacc'];
                if($vaccID > 0)
                {
                    $raccounts = array();
                    $raccounts = $oaccount->viewallaccounts($vaccID);
                    echo json_encode($raccounts);
                    unset($raccounts);
                    $oaccount->close();
                    exit;
                }
            break;
            case 'TerminateAccount':
                $vaccID = $_POST['txtaccid'];
                $vstatus = $_POST['optstatus'];
                //commented on : 12/03/12 -> Termination of particular operator only
//                $rsites = $oaccount->viewsitebyowner($vaccID);
//                $site = array();
//                foreach($rsites as $row)
//                {
//                    $vsiteID = $row['SiteID'];
//                    array_push($site, $vsiteID);
//                }
                $rresult = $oaccount->terminatechildaccounts($vstatus, $vaccID);
                if($rresult > 0)
                {
                    $isexist=$oaccount->checksession($vaccID);
                    if($isexist > 0)
                    {
                       $oaccount->deletesession($vaccID);
                    }
                    
                    $msg = "Account successfully terminated";
                    //insert into audit trail --> DbHandler.class.php
                    $vtransdetails = "operator username ".$_POST['txtoperator'];
                    $vauditfuncID = 39;
                    $oaccount->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                }
                else
                {
                    $msg = "Account was already terminated";
                }
                $_SESSION['mess'] = $msg;
                $oaccount->close();
                unset($rresult);
                header("Location: ../terminateaccount.php");
            break;
            case 'LoadSite':
                $vacctype = $_POST['cmbacctype'];
                $rsites = array();
                
                switch ($vacctype)
                {
                    //operator
                    case 2:
                        //$rsiteoptr = $oaccount->getsitenoowner($_SESSION['acctype']);\
                        $rsiteoptr = $oaccount->getallsites();
                        foreach($rsiteoptr as $row)
                        {
                            $rsitecode = substr($row['SiteCode'], strlen($terminalcode));
                            $newarr = array("SiteID" => $row['SiteID'], "SiteCode" => $rsitecode);
                            array_push($rsites, $newarr);
                        }
                    break;
                    //pagcor
                    case 11:
                        //this will pass 1 to get site HO; for pagcor creation of account only
                        $rsites = $oaccount->getsiteho(1);
                    break;
                    //liason or standalone terminal monitoring
                    case 7:
                        $rsiteliason = array();
                        //get all sites that has liason or assigned standalone terminal monitoring
                        $rsiteliason = $oaccount->getsiteacct($vacctype);
                        if(count($rsiteliason) == 0)
                        {
                            $rsiteliason = array(0);
                        }
                        //get all sites without liason and has no assigned standalone terminal monitoring
                        $rnoliason = $oaccount->getsitenoacct($rsiteliason);

                        foreach($rnoliason as $row)
                        {
                            $rsitecode = substr($row['SiteCode'], strlen($terminalcode));
                            $newarr = array("SiteID" => $row['SiteID'], "SiteCode" => $rsitecode);
                            array_push($rsites, $newarr);
                        }
                        unset($rsiteliason);
                    break;
                    //supervisor / cashier 
                    case 3 || 4:
                        $rsitecsh = $oaccount->getallsites();
                        $rsites = array();
                        foreach($rsitecsh as $row)
                        {
                            $rsitecode = substr($row['SiteCode'], strlen($terminalcode));
                            $newarr = array("SiteID" => $row['SiteID'], "SiteCode" => $rsitecode);
                            array_push($rsites, $newarr);
                        }
                        unset($rsitecsh);
                    break;
                }
                echo json_encode($rsites);
                
                unset($rsites);
                $oaccount->close();
                exit;
            break;
            //ajax request: Filter sites by its owner
            case 'OwnedSites':
                $vaid = $_POST['aid'];
                if($vaid > 0){
                    $rsites = $oaccount->viewsitebyowner($vaid);
                    $rsiteaccts = array();
                    foreach($rsites as $val){
                        $vsitecode = substr($val['SiteCode'], strlen($terminalcode));
                        array_push($rsiteaccts, array('SiteCode'=>$vsitecode, 'SiteID'=>$val['SiteID']));
                    }
                    echo json_encode($rsiteaccts);
                    unset($rsites, $rsiteaccts);
                }
                exit;
            break;
            //ajax request : get active and password expired operators only
            case 'ViewActiveOperators':
                $raccounts = $oaccount->getActiveOperator(2);
                echo json_encode($raccounts);
                $oaccount->close();
                exit;
            break;
            //Module For Removal of Assigned site from operator
            case 'RemoveAssignedSite':
                if(isset($_POST['cmboptr']) && isset($_POST['cmbsite'])){
                    $vaid = $_POST['cmboptr'];
                    $vsiteid = $_POST['cmbsite'];
                    if($vaid > 0 && $vsiteid > 0){
                        $isupdated = $oaccount->deactivateSiteAccount($vaid, $vsiteid);
                        if($isupdated > 0){
                            $msg = "RemoveAssignedSite: Site / Pegs was successfully removed";
                             //insert into audit trail
                            $vtransdetails = ";SiteID = ".$vsiteid." Operator ID = ".$vaid; 
                            $vauditfuncID = 61;
                            $oaccount->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID);
                        } else {
                            $msg = "RemoveAssignedSite: Error in removing Site / Pegs";
                        }
                    } else{
                       $msg = "RemoveAssignedSite: Invalid fields.";
                    }
                } else{
                   $msg = "RemoveAssignedSite: Invalid fields.";
                }
                $nopage= 1;
                $_SESSION['mess'] = $msg;
                $oaccount->close();
                header("Location: ../siteremoval.php");
            break;
            default:
                if($nopage == 0){
                  $oaccount->close();
                }
        }
    }
    
    //page request from accountview.php
    if(isset($_GET['page']) == 'ViewAccount')
    {
      $vaid = $_GET['accid'];
      $_SESSION['accid'] = $vaid; //session variable for account id

      $raccounts = array();
      $raccounts = $oaccount->viewallaccounts($vaid);
      $rsitesowned = $oaccount->viewsitebyowner($vaid);
      unset($_SESSION['sites']);
      $_SESSION['sites'] =$oaccount->getallsites();
      $_SESSION['accounts'] = $raccounts; //session variable to get the site list
      $_SESSION['sitesowned'] = $rsitesowned;//session varible to get all sites owned by operator
      unset($raccounts);
      $oaccount->close();
      header("Location: ../accountedit.php"); 
    }    
    
    //unlocks an account
    if(isset($_GET['unlockpage']) == 'AccountUnlock')
    {
       $vacct = $_GET['accid'];
       $unlockacc = $oaccount->unlockaccount($vacct);
       if($unlockacc > 0)
       {
           //check access right ID to distinguish audit trail function ID
           switch($_SESSION['acctype'])
           {
               case 1: //admin
                   $vauditfunctionID = 75;
               break;
               case 3: //supervisor
                   $vauditfunctionID = 47;
               break;
               case 6: //customer service
                   $vauditfunctionID = 46;
               break;
           }
         
           $msg = 'Account Login attempts reset';
           $vtransdetails = $_GET['accname']; //account username
           $oaccount->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfunctionID); //insert into audit trail --> DbHandler.class.php
       }
       else
       {
           $msg = 'Cannot reset login attempts';
       }
       $_SESSION['mess'] = $msg;
       $oaccount->close();
       header("Location: ../accountunlock.php"); 
    }
    
    //page request from accountview.php to populate combo boxes
    if(isset($_POST['sendAccID']))
    {
      $vaccID = $_POST['sendAccID'];
      $rallaccs = array();
      switch($_SESSION['acctype'])
      {
          case 1:
               //admin
              $rallaccs = $oaccount->viewaccounts(0, $vaccID, 0);                       
          break;
          case 2:
               //Operator
               $rallaccs = $oaccount->viewaccounts(0, $vaccID, $_SESSION['pegsowned']);    
          break;
          case 8:
               //PEGS Ops
               $rallaccs = $oaccount->viewaccounts(0, $vaccID,0);               
          break;
          case 10 :
              //liason
              $rallaccs = $oaccount->viewaccounts(0, $vaccID, $_SESSION['pegsowned']);    
          break;
          default:
              $rallaccs = $oaccount->viewaccounts(0, 0, 0);              
          break;
      }
      echo json_encode($rallaccs);
      unset($rallaccs);
      $oaccount->close();
      exit;
    }
    
    if(isset($_POST['sendOptID'])){
        $vOptID = $_POST['sendOptID'];
        $resultOptrStatus = array();
        $resultOptrStatus =$oaccount->getOptrStatus($vOptID);
        $statValue = $oaccount->showstatusname($resultOptrStatus['Status']);
        echo $statValue;
        unset($resultOptrStatus);
        $oaccount->close();
        exit;
    }
    
    //populates the accounts comboxes --> accountunlock.php
    if(isset($_POST['accattempt']))
    {
        $vacctype = $_POST['accattempt'];
        $rattempts = array();
        $rattempts = $oaccount->getloginattempts($vacctype, $_SESSION['pegsowned'], $_SESSION['acctype']);
        echo json_encode($rattempts);
        unset($rattempts);
        $oaccount->close();
        exit;
    }
    
    //page request from accountedit.php
    if(isset($_GET['statuspage']) == 'UpdateStatus')
    {
        $vaid = $_GET['accid'];
        $_SESSION['accid'] = $vaid;

        $raccounts = array();
        $raccounts = $oaccount->viewallaccounts($vaid);
        $_SESSION['accounts'] = $raccounts; //session variable to get the site list
        
        unset($raccounts);
        $oaccount->close();
        header("Location: ../accountstatusupdate.php");
    }
    if(isset($_POST['cmbsitename']))
    {
        $vsiteID = $_POST['cmbsitename'];
        $rresult = array();
        $rresult = $oaccount->getsitename($vsiteID);
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
        $oaccount->close();
        exit;
   }
   if(isset ($_GET['terminate']) == "TerminateAccount")
   {
       $vaid = $_GET['accid'];
       $vaccname = $_GET['accname'];
       $_SESSION['accdetails'] = array("AID"=>$vaid, "AccName" => $vaccname); //session variable to pass the account 
       $oaccount->close();
       header("Location: ../terminateaccount.php");
   }
}
else
{
    $msg = "Not Connected";    
    header("Location: login.php?mess=".$msg);
}
?>
