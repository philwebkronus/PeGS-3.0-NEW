<?php
include_once "init.php";

$conn = explode( ",", $_DBConnectionString[0]);
    $oconnectionstring1 = $conn[0];
    $oconnectionstring2 = $conn[1];
    $oconnectionstring3 = $conn[2];
    
$DBH = new PDO( $oconnectionstring1, $oconnectionstring2, $oconnectionstring3);

if($DBH){
    
    //count the number of pending terminal transactions
    $stmt = $DBH->prepare("SELECT COUNT(TerminalID) Count FROM pendingterminaltransactioncount");
    $stmt->execute();
    $pendingcount = $stmt->fetch(PDO::FETCH_ASSOC);
    $pendingcount = $pendingcount['Count'];

    //count the number of pending user transactions
    $stmt2 = $DBH->prepare("SELECT COUNT(LoyaltyCardNumber) Count FROM pendingusertransactioncount");
    $stmt2->execute();
    $pendingcountub = $stmt2->fetch(PDO::FETCH_ASSOC);
    $pendingcountub = $pendingcountub['Count'];

    $vcount = 0;

    //mail parameters
    
    $subject = "Pending Casino Transaction";
    $headers =  "From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html"; 
    $header1 = " 
    e-Games Alert - Kronus Pending terminal transactions found.                
            ";
    $header2 = " 
    e-Games Alert - Kronus Pending user transactions found.                
            ";
    $closingmsg = " 
    This email and any attachments are confidential and may also be privileged. If you are not the addressee, do not disclose, copy, 
    circulate or in any other way use or rely on the information contained in this email or any attachments. If received in error, 
    notify the sender immediately and delete this email and any attachments from your system. Any opinions expressed in this message 
    do not necessarily represent the official positions of PhilWeb Corporation. Emails cannot be guaranteed to be secure or error 
    free as the message and any attachments could be intercepted, corrupted, lost, delayed, incomplete or amended. PhilWeb Corporation 
    and its subsidiaries do not accept liability for damage caused by this email or any attachments and may monitor email traffic.                
            ";

    $detail2 = "";
    $detail = "";

    //check if there are pending terminal transactions
    if($pendingcount > 0){
        //get terminalid, transaction count from pendingterminaltransactioncount table
        $stmt = $DBH->prepare("SELECT TerminalID, TransactionCount FROM pendingterminaltransactioncount");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
                $terminalid1 = $row['TerminalID'];
                $tranpendingcount = $row['TransactionCount'];

                //get Terminal Code, SiteID from terminals table using terminalid
                $stmt = $DBH->prepare("SELECT TerminalCode, SiteID FROM terminals WHERE TerminalID = ?");
                $stmt->bindParam(1, $terminalid1);
                $stmt->execute();
                $terminal = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($terminal as $row1) {
                $terminalcodez = $row1['TerminalCode'];
                $siteid = $row1['SiteID'];
                }

                //get SiteCode, and Site Name from sites table
                $stmt = $DBH->prepare("SELECT SiteCode, SiteName FROM sites WHERE SiteID = ?");
                $stmt->bindParam(1, $siteid);
                $stmt->execute();
                $site = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($site as $value) {
                    $sitename = $value['SiteName'];
                    $sitecode = $value['SiteCode'];
                }

                //get Date Created from pendingterminaltransactions table
                $stmt = $DBH->prepare("SELECT ServiceID, DateCreated FROM pendingterminaltransactions WHERE TerminalID = ?");
                $stmt->bindParam(1, $terminalid1);
                $stmt->execute();
                $pendingdetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($pendingdetails as $valuez) {
                    $service = $valuez['ServiceID'];
                    $datecreated = $valuez['DateCreated'];
                };

                $stmt = $DBH->prepare("SELECT ServiceName FROM ref_services WHERE ServiceID = ?");
                $stmt->bindParam(1, $service);
                $stmt->execute();
                $servicename = $stmt->fetch(PDO::FETCH_ASSOC);
                $servicename = $servicename['ServiceName'];
                

                //format site code
                $sitecode = substr($sitecode, strlen($terminalcode));
                
                $var1 = "$terminalcode"."$sitecode";
                
                $terminalcodez = substr($terminalcodez, strlen($var1));
                $detail .= "
                    <html>
                   <body>
                   <br/><br/>
                        Site Code: $sitecode
                   <br/>
                        Site Name : $sitename
                   <br/>
                        Terminal : $terminalcodez
                   <br/>
                        Casino : $servicename
                   <br/>         
                        Date : $datecreated
                   <br/>
                        Number of attempt/s : $tranpendingcount
                   <br/><br/>
                   </body>
                   </html>";
            }
            $message = "
                        $header1
                        $detail
                        $closingmsg";
        
    }

    //check if there are pending user transactions.
    if($pendingcountub > 0)
    {
         //get loyalty card number and transaction count from pendingusertransactioncount table
        $stmt2 = $DBH->prepare("SELECT LoyaltyCardNumber, TransactionCount FROM pendingusertransactioncount");
        $stmt2->execute();
        $data2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);     
        
        foreach ($data2 as $row2) {
                $loyaltycardnumber = $row2['LoyaltyCardNumber'];
                $tranpendingcountub = $row2['TransactionCount'];

                //get TerminalID, DateCreated from pendingterminaltransactioncount table
                $stmt3 = $DBH->prepare("SELECT TerminalID, DateCreated FROM pendingusertransactions WHERE LoyaltyCardNumber = ?");
                $stmt3->bindParam(1, $loyaltycardnumber);
                $stmt3->execute();
                $pendingub = $stmt3->fetchAll(PDO::FETCH_ASSOC);

                foreach ($pendingub as $row1) {
                    $terminalid = $row1['TerminalID'];
                    $datecreated = $row1['DateCreated'];
                }

                //get SiteID, and Terminal Code from terminals table
                $stmt4 = $DBH->prepare("SELECT SiteID, TerminalCode FROM terminals WHERE TerminalID = ?");
                $stmt4->bindParam(1, $terminalid);
                $stmt4->execute();
                $pendingub2 = $stmt4->fetchAll(PDO::FETCH_ASSOC);

                foreach ($pendingub2 as $row4) {
                    $siteid = $row4['SiteID'];
                    $terminalcodes = $row4['TerminalCode'];
                }

                //get Site Code, SiteName, from sites table
                $stmt5 = $DBH->prepare("SELECT SiteCode, SiteName FROM sites WHERE SiteID = ?");
                $stmt5->bindParam(1, $siteid);
                $stmt5->execute();
                $site = $stmt5->fetchAll(PDO::FETCH_ASSOC);
                foreach ($site as $value) {
                    $sitename = $value['SiteName'];
                    $sitecode = $value['SiteCode'];
                }


                //format site code               
                $sitecode = substr($sitecode, strlen($terminalcode));
                
                $var1 = "$terminalcode"."$sitecode";
                
                $terminalcode = substr($terminalcodes, strlen($var1));
                
                $detail2 .= "
                    <html>
                   <body>
                   <br/><br/>
                        Site Code: $sitecode
                   <br/>
                        Site Name : $sitename
                   <br/><br/>
                        Terminal : $terminalcode
                   <br/>
                        Date : $datecreated
                   <br/>
                        Number of attempt/s : $tranpendingcountub
                   <br/><br/>

                   </body>
                   </html>";
            }
            $message2 = "
               $header2 
               $detail2
               $closingmsg";
    }

    //send email alert to AS group email recipient
    while($vcount < count($groupas))
    {
        $to = $groupas[$vcount];
        
        //check if there are pending terminal transactions
        if($pendingcount > 0){
            mail($to, $subject, $message, $headers);
        }
        

        //check if there are pending user transactions
        if($pendingcountub > 0)
        {
            mail($to, $subject, $message2, $headers);
        }
        
        $vcount++;
    }
    unset($pendingcount, $pendingcountub, $data, $data2,
            $message, $message2);
}
else{
    echo "can't connect to the database";
}

$DBH = null; //close the connection
?>