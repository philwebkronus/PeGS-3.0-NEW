<?php
/*
 * (Version 2 -- separate login from cashier)
 * Modules: For Login, Forgot Password, Change Password, Forgot Username 
 * Created by : Edson L. Perez
 * Date Created : January 30, 2012
 *
 * Description: Handles the processes, conditions, redirection, and actions from views
 *
 */

include "../sys/class/Login.class.php";
require '../sys/core/init.php';

//create object 
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
      if(isset($_POST['page']))
      {
          $vpage = $_POST['page'];
          $new_sessionid = '';
          switch($vpage)
          {
              //is login
              case 'LoginPage':
                  if((isset($_POST['txtusername'])) && (isset($_POST['txtpassword'])))
                  {
                      $vusername = trim($_POST['txtusername']);
                      $vpassword = trim($_POST['txtpassword']);
                      $browser = $_POST['browser'];
                      $version = $_POST['version'];
                      $chrome = $_POST['chrome'];
                      $vdesignation = null;
                      
                      $isstatus = $ologin->checkstatus($vusername);
                      
                      //check account status if active
                      if($isstatus['ctrstatus'] > 0)
                      {
                          //select LoginAttempts field to get the value to be incremented for every invalid attempt    
                          $resultloginattempt = $ologin->queryattempt($vusername);    
                          $loginattempt = $resultloginattempt['LoginAttempts'];

                          #loginattempt > 3 exit
                          if($loginattempt >= 3)
                          {
                              // $msg = "Access denied. You cannot login for a few minutes"; change as per requested by QA  09/05
                              $msg = "Access Denied.Please contact system administrator to have your account unlocked.";
                              $ologin->close();
                              header("Location: ../login.php?mess=".$msg); //close connection redirect to login page
                          }
                          else
                          {
                              $result=$ologin->login($vusername,$vpassword);
                              
                              if(isset($result['AccountTypeID']) && ($result['AccountTypeID'] == 15 || $result['AccountTypeID'] == 17))
                              {
                                   $ologin->deletesession($aid);    
                                   session_cache_expire();
                                   session_destroy();
                                   $msg = "User has no access rights.";
                                   $ologin->close();
                                   header("Location: ../login.php?mess=".$msg);
                              }
                              //Check username and password if exists
                              if(($vusername == $result['UserName']) && (sha1($vpassword) == $result['Password']))
                              {
                                   $aid = $result['AID'];    
                                   $racctype = $result['AccountTypeID'];
                                   
                                   //check if with existing session               
                                   $isexistsession = $ologin->checksession($aid);     
                                   if($isexistsession > 0)
                                   {
                                        $ologin->deletesession($aid);    
                                        session_cache_expire();
                                        session_destroy(); 
                                   }

                                   $rdesignation = $ologin->getDesignation($aid);
                                   $raccountname = $ologin->getacctypename($racctype);

                                   session_start();   
                                   foreach ($rdesignation as $results)
                                   {
                                       $vdesignation = $results['DesignationName'];
                                   }

                                   $_SESSION['uname'] = trim($result['UserName']);
                                   $_SESSION['accID'] = $aid;
                                   $_SESSION['designation'] = $vdesignation;
                                   $_SESSION['acctype'] = $racctype;
                                   $_SESSION['accname'] = $raccountname['Name'];
                                   $_SESSION['mid']  = "";                
                                   //$_SESSION['browser'] = $browser;
                                   //$_SESSION['version'] = $version;
                                   //$_SESSION['chrome'] = $chrome;

                                   if($_SESSION['acctype'] == 4)
                                   {
                                        $ologin->deletesession($aid);    
                                        session_cache_expire();
                                        session_destroy();
                                        $msg = "You are not allowed to login on the admin module";
                                        $ologin->close();
                                        header("Location: ../login.php?mess=".$msg);
                                   }
//                                   elseif ($_SESSION['acctype'] != 4 && ($browser == "true"  ||  $chrome == "true" ))
//                                   {                    
//                                        $ologin->deletesession($aid);    
//                                        session_cache_expire();
//                                        session_destroy();
//                                        $msg = "Please use Mozilla Firefox to view the website.";
//                                        $ologin->close();
//                                        header("Location: ../login.php?mess=".$msg);
//                                   }
                                   else 
                                   {
                                        $vsiteid = $ologin->getSiteID($aid);
                                        $_SESSION['AccountSiteID'] = $vsiteid['SiteID'];              
                                        $old_sessionid = session_id();
                                        session_regenerate_id();
                                        $new_sessionid = session_id();     
                                        $_SESSION['sessionID'] = $new_sessionid ;  

                                        //updates loginattempt = 0, lastlogin and passkey
                                        $loginattempt = 0;                    
                                        $updatedrow = $ologin->updateonlogin($loginattempt, $date, $vusername);

                                        //insert sessionid in sessionaccounts
                                        $result=$ologin->insertsession($aid, $new_sessionid, $date);     
                                        if($result > 0)
                                        {    
                                            $path = $ologin->getpath($_SESSION['acctype']);        
                                            
                                            if(is_bool($path) && !$path){
                                                $msg = "No Access Found in Kronus";
                                                //close connection redirect to login page
                                                $ologin->close();
                                                header("Location: ../login.php?mess=".$msg);
                                            }
                                            
                                            //insert to audittrail table
                                            $transdetails = $gsysversion;
                                            $ologin->logtoaudit($new_sessionid, $aid, $transdetails, $date, $ipaddress,'1');
                                            //redirect to index & close connection
                                            $ologin->close();  
                                            header("Location: ../".$path);    
                                        }
                                        else
                                        {  
                                            $msg = "Session not created";
                                            //close connection redirect to login page
                                            $ologin->close();
                                            header("Location: ../login.php?mess=".$msg);
                                        }
                                    }
                               }
                               //if password length is < 8 update login attempts
                               elseif(strlen($vpassword) < 8)
                               {
                                    $loginattempts = $loginattempt + 1;
                                    //update account->LogniAttempts                
                                    $updatedrow =$ologin->updateattempt($loginattempts , $vusername);
                                    if($loginattempts >= 3)
                                    {
                                        $msg = "Access Denied.Please contact system administrator to have your account unlocked.";
                                    }
                                    else
                                    {
                                        $msg = "Please enter your password. Minimum of 8 alphanumeric.";
                                    }

                                    $vaid = $ologin->getaid($vusername);
                                    $aid = $vaid['AID'];
                                    //insert to audittrail table
                                    $transdetails = "No. of Login Attempt/s ".$loginattempts;
                                    
                                    if($aid > 0)
                                        $ologin->logtoaudit($new_sessionid, $aid, $transdetails, $date, $ipaddress,'5');
                                    
                                    $ologin->close();
                                    header("Location: ../login.php?mess=".$msg);
                               }
                               else
                               {
                                    if($_SESSION['acctype'] != 15){
                                       $loginattempts = $loginattempt + 1;
                                       $updatedrow =$ologin->updateattempt($loginattempts , $vusername); //update account->LoginAttempts
                                    
                                    if($loginattempts >= 3)
                                    {
                                        $msg = "Access Denied.Please contact system administrator to have your account unlocked.";
                                    }
                                    else
                                    {
                                        $msg = "Inactive account. Maximum of 3 login attempts only";
                                    }

                                    $transdetails = "No. of Login Attempt/s ".$loginattempts;
                                    $vaid = $ologin->getaid($vusername);
                                    $aid = $vaid['AID'];
                                    
                                    }
                                    else{
                                        $msg = "User has no access rights.";
                                    }
                                    
                                    if($aid > 0)
                                        $ologin->logtoaudit($new_sessionid, $aid, $transdetails, $date, $ipaddress, '5');
                                    
                                    $ologin->close();
                                    header("Location: ../login.php?mess=".$msg);
                               }
                          }
                          
                      }
                      else
                      {
                          $vaid = $ologin->getaid($vusername);
                          $vpasswordenc = sha1($vpassword);
                          //added for terminated account lbt 06/26/2012
                          $isterminated = $ologin->checktermstatus($vusername);
                          if( $isterminated['ctrstatus'] > 0)
                          {
                              $msg = "Access Denied.You do not allow to access the system.";
                              $transdetails = "Terminated account, trying to access the system";
                              $aid = $vaid['AID'];
                              if($aid > 0){
                                  $ologin->logtoaudit($new_sessionid, $aid, $transdetails, $date, $ipaddress, '5');
                              }
                              $ologin->close();
                              header("Location: ../login.php?mess=".$msg);                               
                          }
                          else 
                          {
                             $ispwdchange = $ologin->checkpwdexpired($vaid['AID'], $vpasswordenc);

                              //check if for change password, then redirect to Update Password
                              if($ispwdchange['ctrpwd'] > 0)
                              {
                                  $msg = "Your password has been expired. Please update your password";
                                  $transdetails = "Username ".$vusername;
                                  if($vaid['AID'] > 0)
                                      $ologin->logtoaudit($new_sessionid, $vaid['AID'], $transdetails, $date, $ipaddress, 65); //insert in audittrail
                                  $ologin->close();
                                  header("Location: ../UpdatePassword.php?mess=".$msg."&username=".urlencode($vusername).'&password='.urlencode($vpasswordenc).'&aid='.urlencode($vaid['AID']));
                              }
                              // if credentials are incorrect, update attempt count
                              elseif($ispwdchange['ctrpwd'] == 0)
                              {
                                  $resultloginattempt = $ologin->queryattempt($vusername);    
                                  $loginattempt = $resultloginattempt['LoginAttempts'];
                                  $loginattempts = $loginattempt + 1;
                                  $updatedrow =$ologin->updateattempt($loginattempts , $vusername); //update account->LoginAttempts
                                  #loginattempt > 3 exit
                                  if($loginattempts >= 3)
                                  {
                                      $msg = "Access Denied.Please contact system administrator to have your account unlocked.";
                                  }
                                  else
                                  {
                                      $msg = "Invalid username or password. Maximum of 3 login attempts only";
                                  }
                                  $transdetails = "No. of Login Attempt/s ".$loginattempts;
                                  $aid = $vaid['AID'];
                                  if($aid > 0){
                                      $ologin->logtoaudit($new_sessionid, $aid, $transdetails, $date, $ipaddress, '4'); // change from 5 to 4 lbt 06/26/2012
                                  }
                                  $ologin->close();
                                  header("Location: ../login.php?mess=".$msg); //close connection redirect to login page
                              }
                              else
                              {
                                  $msg = "Account status must be active to login on the system";
                                  $ologin->close();
                                  header("Location: ../login.php?mess=".$msg); //close connection redirect to login page
                              }
                          }
 
                      }
                  }
                  else
                  {
                      $msg = "Login: Invalid Fields";
                      $ologin->close();
                      header("Location: ../login.php?mess=".$msg); //close connection redirect to login page
                  }
              break;
              case 'UpdatePassword':
                  if((isset($_POST['chngeuser'])) && (isset($_POST['txtoldpassword']))  && (isset($_POST['txtnewpassword'])))
                  {
                       $vusername = $_POST['chngeuser'];
                       $voldpassword = $_POST['txtoldpassword'];
                       $vnewpassword = $_POST['txtnewpassword'];
                       $vaid = $_POST['txtaid'];

                       $vhashpassword= sha1($vnewpassword);
                       //check if txtusername and txtoldpassword exists       
                       $result = $ologin->updatepwd($vusername,$voldpassword);
                       if(count($result) > 0)
                       {
                           //update changepassword field  and password field          
                           $updatedrow = $ologin->resetpassword($vhashpassword, $vusername, $vaid);
                           if($updatedrow > 0)
                           {
                               $msg = "Success in updating password";
                               $transdetails = "Username ".$vusername;
                               $ologin->logtoaudit($new_sessionid, $vaid, $transdetails, $date, $ipaddress,'3'); //insert in audittrail
                           }
                           else
                           {
                               $msg = "Error in updating password";
                           }
                       }
                       else
                       {
                           $msg = "Username or password does not exist";
                       }
                  }
                  else
                  {
                      $msg = "Update Password: Invalid Fields";
                  }
                  $ologin->close();
                  header("Location: ../login.php?mess=".$msg);
              break;
              case 'ForgotPassword':
                  if(isset($_POST['txtemailforpass']))
                  {
                      //get values from forgotpass.php
                      $vemail = $_POST['txtemailforpass'];
                      $vformatemail = preg_replace("/[0-9]+$/", "", $vemail);
                      $result = $ologin->checkemail($vemail); //check if email exist
                      $vusername = $result['UserName'];
                      $statusValue = $ologin->getStatus($vusername);
                      $isemailexist = $result['count'];
                      if($isemailexist > 0)
                      {
                          if($result['acctype'] != 17){
                            if($statusValue['Status']  == 1 || $statusValue['Status']  == 6)
                            {
                                  $vaid = $ologin->getaid($vusername); //get account ID
                                  $time = Date("m-d-y h:i:s");
                                  $newhashedpass = sha1("temppassword".$time);
                                  $ologin->temppassword($newhashedpass, $vusername, $vemail); //update chagepassword field  and password field
                                  $to = $vformatemail;
                                  $subject = 'Forgot Password';
                                  $message = "
                                               <html>
                                               <head>
                                                       <title>Forgot Password</title>
                                               </head>
                                               <body>
                                                    <i>Hi $vusername</i>,
                                                    <br/><br/>
                                                        Your password has been reset on $time.
                                                    <br/><br/>
                                                        It is advisable that you change your password upon log-in.
                                                    <br/><br/>
                                                        Please click through the link provided below to log-in to your account.
                                                    <br/><br/>

                                                    <div>
                                                        <b><a href='http://$servername/UpdatePassword.php?username=".urlencode($vusername)."&password=".urlencode($newhashedpass)."&aid=".urlencode($vaid['AID'])."'>Forgot password</a></b>
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
                                     // Check if message is sent or not
                                     if($sentEmail == 1)
                                     {
                                         $msg = "Your password has been sent to you through your email";
                                         $transdetails = "Username ".$vusername." Email ".$vemail;
                                         $ologin->logtoaudit($new_sessionid, $vaid['AID'], $transdetails, $date, $ipaddress, 63); //insert in audittrail
                                     }
                                     else
                                     {
                                         $msg = "Message sending failed";
                                     }
                                     $ologin->close();
                                     header("Location: ../login.php?mess=".$msg);
                            } else {
                                     $msg = "Account is inactive or terminated";
                                     $ologin->close();
                                     header("Location: ../forgotpass.php?mess=".$msg);
                            }
                          }
                          else {
                                $msg = "Unauthorized account type";
                                $ologin->close();
                                header("Location: ../forgotpass.php?mess=".$msg);
                          }
                      }
                      else
                      {
                          $msg = "Email Address does not exists";
                          $ologin->close();
                          header("Location: ../forgotpass.php?mess=".$msg);
                      }
                  }
                  else
                  {
                      $msg = "Forgot Password: Invalid Fields";
                      $ologin->close();
                      header("Location: ../login.php?mess=".$msg);
                  }
              break;
              case 'ForgotUsername':
                  if(isset($_POST['txtemailforuser']))
                  {
                      $vemail = $_POST['txtemailforuser'];
                      $vformatemail = preg_replace("/[0-9]+$/", "", $vemail);  
                      $result = $ologin->checkemail($vemail); //check if email exists
                      $vusername = $result['UserName'];
                      $statusValue = $ologin->getStatus($vusername);
                      $vaid = $ologin->getaid($vusername); //get account ID
                      $isemailexist = $result['count'];
                      if($isemailexist > 0 )
                      {
                          if($result['acctype'] != 17){
                            if($statusValue['Status'] == 1 || $statusValue['Status'] == 6)
                            {
                                  $to = $vformatemail;
                                  $subject = 'Forgot Username';
                                  $message = "
                                                   <html>
                                                   <head>
                                                           <title>Forgot Username</title>
                                                   </head>
                                                   <body>
                                                        <i>Hi </i> ,
                                                        <br/><br/>
                                                            Your username is <b>$vusername</b>
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
                                         // Check if message is sent or not
                                         if($sentEmail == 1)
                                         {
                                             $msg = "Your username has been sent to you through your email";
                                             $transdetails = "Username ".$vusername." Email ".$vemail;
                                             $ologin->logtoaudit($new_sessionid, $vaid['AID'], $transdetails, $date, $ipaddress, 62); //insert in audittrail
                                         }
                                         else
                                         {
                                             $msg = "Message sending failed";
                                         }
                                         $ologin->close();
                                         header("Location: ../login.php?mess=".$msg);
                            } else {
                                     $msg = "Account is inactive or terminated";
                                     $ologin->close();
                                     header("Location: ../forgotuser.php?mess=".$msg);
                            }
                          }
                          else{
                            $msg = "Unauthorized account type";
                            $ologin->close();
                            header("Location: ../forgotuser.php?mess=".$msg);
                          }
                      }
                      else
                      {
                          $msg = "Email Address does not exists";
                          $ologin->close();
                          header("Location: ../forgotuser.php?mess=".$msg);
                      }
                  }
                  else
                  {
                      $msg = "Forgot Username: Invalid Fields";
                      $ologin->close();
                      header("Location: ../login.php?mess=".$msg);
                  }
              break;
              case 'ChangePassword':
                  if((isset($_POST['txtusername'])) && ( isset($_POST['txtemail'])))
                  {
                      //get all values from changepass.php
                      $vusername = $_POST['txtusername'];
                      $vemail = $_POST['txtemail'];
                      $vformatemail = preg_replace("/[0-9]+$/", "", $vemail);  
                      
                      //select record with entered email and username
                      $result = $ologin->checkusernameandemail($vusername,$vemail);
                      $statusValue = $ologin->getStatus($vusername);
                      $resultcheckifexist = $result['count'];    
                      if($resultcheckifexist > 0)
                      {
                        if($result['acctype'] != 17){
                            if($statusValue['Status'] == 1 || $statusValue['Status'] == 6)
                            {
                                  $vaid = $ologin->getaid($vusername); //get account ID
                                  $time = Date("m-d-y h:i:s");
                                  //reset password
                                  $newhashedpass = sha1("temppassword".$time);
                                  //update chagepassword field  and password field
                                  $ologin->temppassword($newhashedpass, $vusername, $vemail);
                                  //email
                                   $to = $vformatemail;
                                   $subject = 'Change Password';
                                   $message = "
                                                 <html>
                                                 <head>
                                                         <title>Change Password</title>
                                                 </head>
                                                 <body>
                                                      <i>Hi $vusername</i>,
                                                      <br/><br/>
                                                          Your password has been reset on $time.
                                                      <br/><br/>
                                                          It is advisable that you change your password upon log-in.
                                                      <br/><br/>
                                                          Please click through the link provided below to log-in to your account.
                                                      <br/><br/>

                                                      <div>
                                                           <b><a href='http://$servername/UpdatePassword.php?username=".urlencode($vusername)."&password=".urlencode($newhashedpass)."&aid=".urlencode($vaid['AID'])."'>Change password</a></b>
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
                                       // Check if message is sent or not
                                       if($sentEmail == 1){
                                           $msg = "Your password has been sent to you through your email";
                                           $transdetails = "Username ".$vusername." Email ".$vemail;
                                           $ologin->logtoaudit($new_sessionid, $vaid['AID'], $transdetails, $date, $ipaddress, 64); //insert in audittrail
                                       }
                                       else{
                                           $msg = "Message sending failed";
                                       }
                                       $ologin->close();
                                       header("Location: ../login.php?mess=".$msg);
                            } else {
                                     $msg = "Account is inactive or terminated";
                                     $ologin->close();
                                     header("Location: ../changepass.php?mess=".$msg);
                            }
                        }
                        else {
                            $msg = "Unauthorized account type";
                            $ologin->close();
                            header("Location: ../changepass.php?mess=".$msg);
                        }
                      }
                      else
                      {
                         $msg = "Email and username did not match";
                         $ologin->close();
                         header("Location: ../changepass.php?mess=".$msg);
                      }    
                  }
                  else
                  {
                      $msg = "Change Password: Invalid Fields";
                      $ologin->close();
                      header("Location: ../login.php?mess=".$msg);
                  }
              break;
              case 'CheckChangePassword':
                  $vaid = $_POST['aid'];
                  $ischeck = $ologin->checkpwdpermission($vaid);
                  if($ischeck['ctrpwd'] == 0)
                  {
                     header('HTTP/1.1 401 Unauthorized');
                     echo "Your password has been updated";
                     $ologin->close();
                     exit;
                  }
              break;
              default:
                  $msg = "Page not found";
                  $ologin->close();
                  header("Location: ../login.php?mess=".$msg);
              break;
          }
      }
      else
      {
          $msg = "Page not found";
          $ologin->close();
          header("Location: ../login.php?mess=".$msg);
      }
}
else
{
    $msg = "Not Connected";
    header("Location: ../login.php?mess=".$msg);
}
?>