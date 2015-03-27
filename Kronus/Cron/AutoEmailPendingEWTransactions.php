<?php

include_once "init.php";
include("Autoemail.class.php");
//include_once "CheckPendingTransaction.php";

$conn = explode( ",", $_DBConnectionString[0]);
    $oconnectionstring1 = $conn[0];
    $oconnectionstring2 = $conn[1];
    $oconnectionstring3 = $conn[2];
    
$DBH = new PDO($oconnectionstring1, $oconnectionstring2, $oconnectionstring3);

if($DBH){
   //count the number of pending terminal transactions
    $stmt = $DBH->prepare("SELECT COUNT(MID) Count FROM pendingwallettrans");
    $stmt->execute();
    $pendingcount = $stmt->fetch(PDO::FETCH_ASSOC);
    $pendingcount = $pendingcount['Count'];
    
    $subject = "Pending Casino Transaction";
    $headers =  "From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html"; 
    
    $header = " 
    e-Games Alert - Kronus Pending ewallet transactions found.                
            ";
    
    $closingmsg = " 
    This email and any attachments are confidential and may also be privileged. If you are not the addressee, do not disclose, copy, 
    circulate or in any other way use or rely on the information contained in this email or any attachments. If received in error, 
    notify the sender immediately and delete this email and any attachments from your system. Any opinions expressed in this message 
    do not necessarily represent the official positions of PhilWeb Corporation. Emails cannot be guaranteed to be secure or error 
    free as the message and any attachments could be intercepted, corrupted, lost, delayed, incomplete or amended. PhilWeb Corporation 
    and its subsidiaries do not accept liability for damage caused by this email or any attachments and may monitor email traffic.                
            ";
    
    $detail = "";
    
    if($pendingcount > 0){
        $stmt = $DBH->prepare("SELECT MID, SiteID, DateCreated FROM pendingwallettrans");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($data as $row){
            $MID = $row['MID'];
            $SiteID = $row['SiteID'];
            $DateCreated = $row['DateCreated'];
            $stmt = $DBH->prepare("SELECT EwalletTransID, LoyaltyCardNumber, ServiceID FROM ewallettrans WHERE MID = ? AND SiteID = ?");
            $stmt->bindParam(1, $MID);
            $stmt->bindParam(2, $SiteID);
            $stmt->execute();
            $ewtransid = $stmt->fetch(PDO::FETCH_ASSOC);
            $loyaltycardnumber = $ewtransid['LoyaltyCardNumber'];
            
            //get SiteCode, and Site Name from sites table
            $stmt = $DBH->prepare("SELECT SiteCode, SiteName FROM sites WHERE SiteID = ?");
            $stmt->bindParam(1, $SiteID);
            $stmt->execute();
            $site = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($site as $value) {
                $sitename = $value['SiteName'];
                $sitecode = $value['SiteCode'];
            }
            
            //get ServiceName from ref_services table
            $stmt = $DBH->prepare("SELECT ServiceName FROM ref_services WHERE ServiceID = ?");
            $stmt->bindParam(1, $ewtransid['ServiceID']);
            $stmt->execute();
            $servicename = $stmt->fetch(PDO::FETCH_ASSOC);
            $servicename = $servicename['ServiceName'];
            
            $detail .= "
                   <html>
                   <body>
                   <br/><br/>
                        Site Code: $sitecode
                   <br/>
                        Site Name : $sitename
                   <br/>
                        Casino : $servicename
                   <br/>         
                        Date : $DateCreated
                   <br/>
                        Card Number : $loyaltycardnumber
                   <br/><br/>
                   </body>
                   </html>";        
        }
        $message = "
            $header
            $detail
            $closingmsg";
    }
    else{               
        $detail = "No pending ewallet transactions";
        print $detail;
    }
    $vcount = 0;
    while($vcount < count($groupas))
    {
        $to = $groupas[$vcount];  
        //check if there are pending terminal transactions
        if($pendingcount > 0){
            mail($to, $subject, $message, $headers);
            echo "Sent";
        }
        $vcount++;
    }
    unset($pendingcount, $data, $message);
}
else{
    echo "can't connect to the database";
}

$DBH = null; //close the connection