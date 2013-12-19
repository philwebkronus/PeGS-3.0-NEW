<?php

/**
 * @Author: aqdepliyan
 * @DateCreated: 2013-10-30
 * @Description: Cron job for checking of rewards item inventory (Critical Level)
 */

$config = include('config.php');

$connectionstring = $config["db"]["connectionString"];
$username = $config["db"]["username"];
$password = $config["db"]["password"];

// database connection
$connection = new PDO($connectionstring,$username,$password);
$sql="SELECT ri.RewardItemID, ri.ItemName, ri.AvailableItemCount as CurrentCount, rp.PartnerName, now_usec() as DateTimeToday 
            FROM rewarditems ri
            INNER JOIN ref_partners rp ON rp.PartnerID = ri.PartnerID 
            WHERE ri.RewardID = 1 AND ri.Status IN (1,3)";
$command = $connection->prepare($sql);
$command->execute();
$rewarditemslist = $command->fetchAll(PDO::FETCH_ASSOC);

$IsCriticalArray = array();
$itr1 = 0;

//list all active rewards items and check all used serial codes
foreach ($rewarditemslist as $items => $key) {

    $DateTimeToday = new DateTime($key['DateTimeToday']);
    $key['DateTimeToday'] = $DateTimeToday->format("m/d/Y g:i a");

    $sql="SELECT COUNT(ItemSerialCodeID) as UsedCount
                FROM itemserialcodes
                WHERE Status = 2 AND RewardItemID = ?";
    $command = $connection->prepare($sql);
    $command->bindParam(1, $key["RewardItemID"]);
    $command->execute();
    $rewardscount = $command->fetchAll(PDO::FETCH_ASSOC);
    
    //compute (item current count + used count) * 0.25 to check if the items are in 75% critical state
    if(count($rewardscount) > 0){
        $key["IsCritical"] = (int)(((int)$key["CurrentCount"] + (int)$rewardscount[0]["UsedCount"]) * $config["params"]["CriticalLvl"]);
    } else {
        $key["IsCritical"] = (int)((int)$key["CurrentCount"] * $config["params"]["CriticalLvl"]);
    }

    //identify a particular item meets the 75 % critical state
    if(((int)$key["CurrentCount"] <= (int)$key["IsCritical"]) && (int)$key["CurrentCount"] != 0){
        $IsCriticalArray[$itr1] = $key;
        $itr1++;
    }
}

$countcritical = count($IsCriticalArray);

$EmailSender = $config["params"]["EmailSender"];
$Recipient = $config["params"]["EmailRecipient"];
$EmailRecipient = implode(";", $Recipient);

//check if a particular item is in critical level then send an email alert
if($countcritical > 0){
    foreach ($IsCriticalArray as $IsCritical) {
        $ItemName = $IsCritical["ItemName"];
        $PartnerName = $IsCritical["PartnerName"];
        $DateTime = $IsCritical["DateTimeToday"];
        $subject  = "[Reward Inventory Critical] Membership Rewards Program";
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .=  "From: ".$EmailSender."\r\n";

        $detail = "<body>
                                <p>Hi,</p>
                                <p>This is to inform you that as of $DateTime, the inventory of $ItemName of $PartnerName has reached the 75% item redemption critical level.</p>
                                <p>Thanks. </p>
                                <p>**This is an automated e-mail.</p>
                            </body>";

        mail($EmailRecipient, $subject, $detail, $headers);
        
        //Insert Auto Email Logs
        $query = "INSERT INTO  autoemaillogs(AEmailID, SentToAID, SentToCCAID, SentToBCCAID, Message, DateSent, SentByAID)
                            VALUES(:aemailid, :senttoaid, :senttoccaid, :senttobccaid, :message, now_usec(), :sentbyaid)";
        $sql = $connection->prepare($query);
        $sql->bindParam(":aemailid", 2);
        $sql->bindParam(":senttoaid", null);
        $sql->bindParam(":senttoccaid", null);
        $sql->bindParam(":senttobccaid", null);
        $sql->bindParam(":message", $detail);
        $sql->bindParam(":sentbyaid", 1);
        $sql->execute();
    } 
}

unset($IsCritical, $IsCriticalArray, $rewarditemslist);

?>
