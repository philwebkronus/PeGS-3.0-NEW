<?php

/**
 * This cron is for deleting of EGM Sessions that has been idle for 30 minutes
 * Date Created 10/15/12
 * @author Edson Perez
 */

$_DBConnectionString[0] = "mysql:host=172.16.102.157;dbname=npos,nposconn,npos";
$setMinutes = 30; //default minutes set in order to delete gaming sessions
$setDays = 3; //default no. of days set in order to delete gaming sessions
$connectionString = explode(",", $_DBConnectionString[0]);
$conn1 = $connectionString[0];
$conn2 = $connectionString[1];
$conn3 = $connectionString[2];

$dbh = new PDO($conn1, $conn2, $conn3);

try {
    $sql = 'SELECT LastTransactionDate, TerminalID, EGMMachineInfoId_PK FROM egmsessions';
    $command = $dbh->prepare($sql);
    $command->execute();
    $result = $command->fetchAll(PDO::FETCH_ASSOC);
    
    $vctrresult = count($result);
    
    if($vctrresult > 0){
        $vcounter = 0;
        while($vcounter < $vctrresult){
            $lastTransactionDate = $result[$vcounter]['LastTransactionDate'];
            $terminalID = $result[$vcounter]['TerminalID'];
            $egmMacID = $result[$vcounter]['EGMMachineInfoId_PK'];
            
            //compute the LastTransactionDate in minutes
            $datenow = date("Y-m-d H:i:s.u");
            $vdiffdate = (int)strtotime($datenow) - (int)strtotime($lastTransactionDate); //get the minutes difference
            $years = floor($vdiffdate / (365*60*60*24));
            $months = floor(($vdiffdate - $years * 365*60*60*24) / (30*60*60*24));
            $days = floor(($vdiffdate - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24)); //actual day difference
            $noofmins = round(abs($vdiffdate)/60,2); //actual minute difference
            
            //Is DateLastTransaction no. of minutes exceed / equal to minutes set
            if($days >= $setDays){
                $command = $dbh->prepare('DELETE FROM egmsessions WHERE EGMMachineInfoId_PK = :egm_mac_id');
                $command->bindValue(':egm_mac_id', $egmMacID);
                $command->execute();
            }
            $vcounter++;
        }
        
        unset($sql, $result, $vctrresult, $vcounter);
    }
    
    $dbh = null; //close mysql connection
    
}catch(Exception $e){
    echo $e->getMessage();
    exit;
}
?>
