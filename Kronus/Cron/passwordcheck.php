<?php
   require 'init.php';
   
/*
 * Cron for sending email notification if password is about to expired
 */
    
    $conn = explode( ",", $_DBConnectionString[0]);
    $oconnectionstring1 = $conn[0];
    $oconnectionstring2 = $conn[1];
    $oconnectionstring3 = $conn[2];
    $servername = $_SERVER['HTTP_HOST'];
    //select accounts with datechanged, username, aid
    $dbh = new PDO( $oconnectionstring1, $oconnectionstring2, $oconnectionstring3);
    $stmt = "SELECT p.AID,a.UserName, MAX(p.DateChanged) as DateChanged from passwordcheck p
            INNER JOIN accounts a ON a.AID = p.AID
            WHERE a.Status = 1 AND a.AccountTypeID NOT IN (15,19) AND a.AID = 2
            group by p.AID order by p.DateChanged ASC";
    $sth = $dbh->prepare($stmt);
    $sth->execute();
    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
    $vrescount = count($result);
    //$vrescount = 1;
    if($vrescount > 0)
    {
        $vcounter = 0;
        while($vcounter < $vrescount)
        {
            $vaid = $result[$vcounter]['AID'];   
            $vdatechange = $result[$vcounter]['DateChanged'];
            $datenow = date("Y-m-d H:i:s");
            $vdiffdate = (int)strtotime($datenow) - (int)strtotime($vdatechange); //get the day difference
            $vctrdate = round($vdiffdate/86400); //actual day difference
            $noofdays = (int)$vctrdate;
            //get an email address
            $stmt = "SELECT a.UserName,ad.Email, a.AccountTypeID FROM accounts a
                INNER JOIN accountdetails ad ON a.AID = ad.AID WHERE a.AID = ?";
            $sth = $dbh->prepare($stmt);
            $sth->bindParam(1,$vaid);
            $sth->execute();
            $resemail = $sth->fetch(PDO::FETCH_LAZY);
            $acctypeid = $resemail['AccountTypeID'];
            //check if cashier
            if($acctypeid == 4)
            {
                $updateurl = $gcashierupdate;
            }
            else
            {
                $updateurl = $gadminupdate;
            }
            $vemail = preg_replace("/[0-9]+$/", "", $resemail['Email']); //remove numbers on the end of the string
            $vto = $vemail;
            $vusername = $resemail['UserName'];
            $vsubject = 'Password Expired';
            $vtime = Date("m-d-y h:i:s");
            $vmessage = "";    
            //reset password
            $vnewhashedpass = sha1("temppassword".$vtime);
            $vheaders="From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html";
            
            //if expiration date was due(exceeded 30 days), update accounts table and send email alert to the user
            if($noofdays >= 30)
            {
                $stmt = "UPDATE accounts SET Status = 6 ,ForChangePassword = 0 WHERE AID = ?";
                $sth = $dbh->prepare($stmt);
                $sth->bindParam(1,$vaid);
                $sth->execute();
                
                $vmessage = "
                           <html>
                           <head>
                                   <title>Password Expired</title>
                           </head>
                           <body>
                                <i>Hi $vusername</i>,
                                <br/><br/>
                                    Your password has been expired.
                                <br/><br/>
                                    It is advisable that you change your password upon log-in.
                                <br/><br/>
                                    Please click through the link provided below to log-in to your account.
                                <br/><br/>

                                <div>                                     
                                     <b><a href='".$updateurl."username=".urlencode($vusername)."&password=".urlencode($vnewhashedpass)."&aid=".urlencode($vaid)."'>Change password</a></b>
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
                $vsentEmail = mail($vto, $vsubject, $vmessage, $vheaders);
                // Check if message is sent or not
                if($vsentEmail <> 1){
                    echo "Message sending failed";
                }
                else{echo "Sent";}
            }
            $vcounter++;
        }      
    }
    unset($stmt,$vrescount,$vcounter,$vaid,$vdatechange,$datenow,$vdiffdate,$vctrdate,
          $resemail,$acctypeid,$updateurl,$vemail,$vto,$vusername,$vsubject,
          $vtime,$vmessage,$vnewhashedpass,$vheaders,$noofdays,$vsentEmail);
    $dbh = null; //close the connection
?>
