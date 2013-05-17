<?php

  include_once "../../core/init.php";
  

  $conn = explode( ",", $_DBConnectionString[0]);
  $oconnectionstring1 = $conn[0];
  $oconnectionstring2 = $conn[1];
  $oconnectionstring3 = $conn[2];
  $counter = 0;
  $forsend = array();
 
  $time =microtime(true);
  $micro_time=sprintf("%06d",($time - floor($time)) * 1000000);
  $rawdate = new DateTime( date('Y-m-d H:i:s.'.$micro_time, $time) );
  $date = $rawdate->format("Y-m-d H:i:s");
  $hours = 8;
  $minutes = 0;
  $seconds = 0;
  $months = 0;
  $years = 0; 
  $days = 0;
  $totalHours = date('H') + $hours;
  $totalMinutes = date('i') + $minutes;
  $totalSeconds = date('s') + $seconds;
  $totalMonths = date('m') + $months;
  $totalDays = date('d') + $days;
  $totalYears = date('Y') + $years;
  $timeStamp = mktime($totalHours, $totalMinutes, $totalSeconds, $totalMonths, $totalDays, $totalYears);
  $expired = date('Y-m-d H:i:s', $timeStamp);

  $dbh = new PDO($oconnectionstring1, $oconnectionstring2, $oconnectionstring3);
  $stmt = "SELECT AID,DatePasskeyExpires FROM accounts WHERE DatePasskeyExpires <= ? 
           AND AccountTypeID = 4 AND WithPasskey = 1 AND Status = 1";
  $sth = $dbh->prepare($stmt);
  $sth->bindParam(1,$date);
  $result= $sth->execute();
  $res = $sth->fetchAll(PDO::FETCH_ASSOC); 
  $newarr = array(); 
  $arrAID = array();
  if($res)
  { 
    $ctr = 0;    
    while($ctr < count($res))
    {
        $newpasskey = 0;
        $forsend = array();
        $newarr = array();
        $acctforupdate = $res[$ctr]['AID'];
        $counter = 0;
        while ($counter < 8)
        {
          $newpasskey = rand(0,9);
          array_push($forsend,$newpasskey); 
          if(count($forsend) > 1)
          {
             if(count($newarr) == 8)
             {
               break;
             }
             else
             {
               $newarr = array_unique($forsend);
             }
          }
          if(count($newarr) < 8)
          {
            if($counter == 7)
            {
               $counter = $counter -1;
            }
            else
            {
              $counter = $counter + 1;
            }
          }
          else
          {
            $counter = $counter + 1;
          }   
           
        }
         
        $passkey = implode($newarr);
        $stmt = "UPDATE accounts SET Passkey = ?,DatePasskeyIssued = ?,DatePasskeyExpires= ?  WHERE AID =? and WithPasskey = 1";
        $sth = $dbh->prepare($stmt);
        $sth->bindParam(1,$passkey);   
        $sth->bindParam(2,$date );
        $sth->bindParam(3,$expired );
        $sth->bindParam(4,$acctforupdate);
        $updatepass= $sth->execute(); 
        array_push($arrAID, array('AID'=>$acctforupdate));
        $ctr= $ctr + 1;
    }
   
    $ctr = 0;
    $arremail = array();
    //get cashier's account by specific updated AID
    while($ctr < count($arrAID))
    {
        //email to cashier
        $stmt="Select a.UserName,ad.Email,a.Passkey from accountdetails ad
            INNER JOIN accounts a ON a.AID = ad.AID WHERE a.AID = ?";
        $sth = $dbh->prepare($stmt);   
        $sth->bindParam(1,$arrAID[$ctr]['AID']); 
        $result= $sth->execute();
        $foremail = $sth->fetch(PDO::FETCH_ASSOC);
        $vusername = $foremail['UserName'];
        $vemail = $foremail['Email'];
        $vpasskey = $foremail['Passkey'];
        array_push($arremail, array('username' => $vusername, 'email' => $vemail, 'passkey' => $vpasskey));
        $ctr = $ctr + 1;
    }
    
    $ctr = 0;
    while($ctr < count($arremail))
    {
        //send an email notification
        $vto = $arremail[$ctr]['email'];
        $vmail = preg_replace("/[0-9]+$/", "", $vto);
        $vusername = $arremail[$ctr]['username'];
        $vpasskey = $arremail[$ctr]['passkey'];
        $vsubject = 'New Passkey';
        $vtime = Date("m-d-y h:i:s");
        $vservername = $_SERVER['HTTP_HOST'];
        $vmessage = "
                       <html>
                       <head>
                               <title>New Passkey</title>
                       </head>
                       <body>
                            <i>Hi $vusername</i>,
                            <br/><br/>
                                Your passkey has been expired.
                            <br/><br/>
                                Please use the credential below as your <b><i>new passkey</i></b>.
                            <br/><br/>
                            <div>                                     
                                 <b>$vpasskey</b>
                            </div>
                            <br/><br/>
                                For further inquiries, please call our Customer Service Hotline at telephone numbers (02) 3383388 
                            or toll free from PLDT lines 1800-10PHILWEB (1800-107445932) or email us at <b>customerservice@philweb.com.ph</b>.                            
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
        $vheaders="From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html";
        $vsentEmail = mail($vmail, $vsubject, $vmessage, $vheaders);
        // Check if message is sent or not
        if($vsentEmail == 1)
        {
            $vmsg = "Your password has been sent to you through your email";
        }
        else
        {
            $vmsg = "Message sending failed";
        } 
        $ctr = $ctr + 1;
    }  
  }
  unset($sth,$nomatch,$acctforupdate,$counter,$newpasskey,$newarr,$passkey, 
        $stmt,$arrAID, $ctr,$arremail,$result,$foremail,$vemail,
        $vto,$vmail,$vusername,$vpasskey,$vsubject,$vtime,$vservername,
        $vmessage,$vheaders,$vsentEmail);
  $dbh = null;
?>