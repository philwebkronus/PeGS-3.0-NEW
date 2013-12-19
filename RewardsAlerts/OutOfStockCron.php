<?php

/**
 * @Author: aqdepliyan
 * @DateCreated: 2013-11-05
 * @Description: Cron job for checking of rewards item inventory (Out-Of-Stock)
 */

$config = include('config.php');

$connectionstring = $config["db"]["connectionString"];
$username = $config["db"]["username"];
$password = $config["db"]["password"];

// database connection
$connection = new PDO($connectionstring,$username,$password);
$sql="SELECT ri.RewardItemID, ri.IsMystery, ri.ItemName, ri.AvailableItemCount as CurrentCount, rp.PartnerName, now_usec() as DateTimeToday 
            FROM rewarditems ri
            INNER JOIN ref_partners rp ON rp.PartnerID = ri.PartnerID 
            WHERE ri.RewardID = 1 AND ri.Status IN (1,3)";

$command = $connection->prepare($sql);
$command->execute();
$rewarditemslist = $command->fetchAll(PDO::FETCH_ASSOC);

$IsOutOfStockArray = array();
$itr2 = 0;

//list all active rewards items and check all used serial codes
foreach ($rewarditemslist as $items => $key) {

    $DateTimeToday = new DateTime($key['DateTimeToday']);
    $key['DateTimeToday'] = $DateTimeToday->format(" m/d/Y g:i a");

    //check if item current count is zero
    if((int)$key["CurrentCount"] == 0){
        $IsOutOfStockArray[$itr2] = $key;
        $itr2++;
    }
}

$countoutofstock = count($IsOutOfStockArray);

$EmailSender = $config["params"]["EmailSender"];
$Recipient = $config["params"]["EmailRecipient"];
$EmailRecipient = implode(";", $Recipient);

//check if there are items that has out of stack
if($countoutofstock > 0){
    
    foreach ($IsOutOfStockArray as $IsOutOfStock) {
        
        if($IsOutOfStock["IsMystery"] != 1){
            //tag item status as expired
            $query = "UPDATE rewarditems SET Status = 3 WHERE RewardItemID = ?";
            $sql = $connection->prepare($query);
            $sql->bindParam(1,$IsOutOfStock["RewardItemID"]);
            $sql->execute();
        }
 
        $ItemName = $IsOutOfStock["ItemName"];
        $PartnerName = $IsOutOfStock["PartnerName"];
        $DateTime = $IsOutOfStock["DateTimeToday"];
        
        $subject  = "[Reward is Out-Of-Stock] Membership Rewards Program";
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .=  "From: ".$EmailSender."\r\n";

        $detail = "<body>
                        <p>Hi,</p>
                        <p>This is to inform you that as of $DateTime, the inventory of $ItemName of $PartnerName has been fully redeemed. The reward item is effectively out-of-stock and needs to be replenished.</p>
                        <p>Thanks. </p>
                        <p>**This is an automated e-mail.</p>
                   </body>";

        mail($EmailRecipient, $subject, $detail, $headers);
        
        //Insert Auto Email Logs
        $query = "INSERT INTO  autoemaillogs(AEmailID, SentToAID, SentToCCAID, SentToBCCAID, Message, DateSent, SentByAID)
                            VALUES(:aemailid, :senttoaid, :senttoccaid, :senttobccaid, :message, now_usec(), :sentbyaid)";
        $sql = $connection->prepare($query);
        $sql->bindParam(":aemailid", 3);
        $sql->bindParam(":senttoaid", null);
        $sql->bindParam(":senttoccaid", null);
        $sql->bindParam(":senttobccaid", null);
        $sql->bindParam(":message", $detail);
        $sql->bindParam(":sentbyaid", 1);
        $sql->execute();
        
    }
}

unset($IsOutOfStock, $IsOutOfStockArray, $rewarditemslist);

?>
