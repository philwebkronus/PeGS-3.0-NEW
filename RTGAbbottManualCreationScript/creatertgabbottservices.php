<?php
ini_set('max_execution_time', 0);
include 'web.config.php';
include 'RealtimeGamingWCFPlayerAPI.class.php';
include 'RealtimeGamingCashierAPI2.class.php';

$RTGAbbottPlayerAPI = new RealtimeGamingWCFPlayerAPI($URI_PlayerAPI, $cert_key_file_path);
$RTGAbbottCashierAPI = new RealtimeGamingCashierAPI($URI_CashierAPI, $cert_file_path, $key_file_path, $isCaching);

$conn = explode( ",", $_DBConnectionString[0]);
$oconnectionstring1 = $conn[0];
$oconnectionstring2 = $conn[1];
$oconnectionstring3 = $conn[2];

$dbh = new PDO( $oconnectionstring1, $oconnectionstring2, $oconnectionstring3);

$startTime = microtime(true);

//query all member services
$stmt = "SELECT ServiceID, MID, ServiceUsername, ServicePassword, HashedServicePassword, UserMode, isVIP, Status 
                FROM memberservices_temp";

$sth = $dbh->prepare($stmt);
$sth->execute();
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

//iterate creation of membership account in RTG Abbott
foreach ($result as $val){
    
    //Check if the Service ID is RTG Abbott and not yet created in RTGAbbott  backend.
    if($val['ServiceID'] == 19 && $val['Status'] == 1){

        //Formatting Username for RTG Abbott [Format: [random no.]+ "0000" + MID]
        $arrChunk = chunk_split($val['ServiceUsername'], 4);

        //check if the first 4-digit of the username starts with 0000
        if($arrChunk[0] == "0000"){
            //if true, replace the "0000" to random 4-digit
            $randomnum = mt_rand(1000,9999);
            $serviceuser = substr($val['ServiceUsername'],4);
            $username = $randomnum.$serviceuser;
        } else {
            //if false, retain the username fetch from the database.
            $username = $val['ServiceUsername'];
        }
        
        //Parameters to be inserted in memberservices table
        $MID = $val['MID'];
        $serviceid = $val['ServiceID'];
        $usermode = $val['UserMode'];
        $isVIP = $val['isVIP'];
        $status = $val['Status'];
        
        //Update the current username in memberservices_temp table
        $update = "UPDATE memberservices_temp SET ServiceUsername = '$username' WHERE MID = $MID"; 
        $upd = $dbh->prepare($update);
        $upd->execute();

        //Identifier if the account is created successfully (0-No, 1-Yes)
        $iscreated = 0;

        //Preparer required parameters for RTG Abbott User-Based Account Creation
        $email = $val['MID'].'@philweb.com.ph';
        $lastName = "NA";
        $firstName = "NA";
        $birthDate = "1981-01-01";
        $address1 = "PH";
        $address2 = "";
        $city = "NA";
        $phone = '3385599';                               
        $zip = 'NA';
        $countryCode = 'PH';
        $AID =  0;
        $casinoID = 1;
        $userID = 0;
        $downloadID = 0;
        $clientID = 1;
        $putInAffPID = 0;
        $calledFromCasino = 0;
        $currentPosition = 0;
        $ip = '';
        $mac = '';
        $province = '';
        $agentID = '';
        $thirdPartyPID = '';
           
        //Find the GeneratedPasswordBatchID for specific MID
         $stmt2 = "SELECT gpb.GeneratedPasswordBatchID FROM generatedpasswordbatch gpb 
                    WHERE gpb.MID = $MID";
        
        $sth2 = $dbh->prepare($stmt2);
        $sth2->execute();
        $result2 = $sth2->fetchAll(PDO::FETCH_ASSOC);
        
        $genpassbatchid = '';
        if(isset($result2[0]['GeneratedPasswordBatchID']))
            $genpassbatchid = $result2[0]['GeneratedPasswordBatchID'];
        
        //If GeneratedPasswordBatchID is empty, select a unused GeneratedPasswordBatchID
        if (empty($genpassbatchid)) {
            $stmt3 = "SELECT gpb.GeneratedPasswordBatchID FROM generatedpasswordbatch gpb
                    WHERE gpb.Status = 0 LIMIT 1";
            
            $sth3 = $dbh->prepare($stmt3);
            $sth3->execute();
            $result3 = $sth3->fetchAll(PDO::FETCH_ASSOC);
            $genpassbatchid = $result3[0]['GeneratedPasswordBatchID'];
        }

        //Get the password and hashed password tag in the GeneratedPasswordBatchID you fetch from the database.
        $stmt4 = "SELECT gpp.PlainPassword, gpp.EncryptedPassword FROM generatedpasswordpool gpp
                            WHERE gpp.GeneratedPasswordBatchID = $genpassbatchid AND ServiceGroupID = 4";
        
        $sth4 = $dbh->prepare($stmt4);
        $sth4->execute();
        $result4 = $sth4->fetchAll(PDO::FETCH_ASSOC);
        
        
        //Check if there is a Plain Password and Hashed Password tag for specific Service Group ID
        if((isset($result4[0]['PlainPassword']) && !empty($result4[0]['PlainPassword'])) && (isset($result4[0]['EncryptedPassword']) && !empty($result4[0]['EncryptedPassword']))){
            $password = $result4[0]['PlainPassword'];
            $hashedpassword = strtoupper($result4[0]['EncryptedPassword']);

            //Check if the user already exists
            $PID = $RTGAbbottCashierAPI->GetPIDFromLogin($username);

            if(empty($PID["GetPIDFromLoginResult"])){

                //Create Account to RTG Abbott Backend
                $apiResult = $RTGAbbottPlayerAPI->createTerminalAccount($username, $password, $AID, $countryCode, $casinoID, $firstName, $lastName, 
                                                                                                    $email, $phone, $phone, $address1, $address2, $city, $province, 
                                                                                                    $zip, $ip, $mac, $userID, $downloadID, $birthDate, $clientID, 
                                                                                                    $putInAffPID, $calledFromCasino, $hashedpassword, $agentID, 
                                                                                                    $currentPosition, $thirdPartyPID);
            }
            
            //Change Player Classification based on isVIP value from the database, default PClass = 0 [New].
            if(isset($apiResult["CreatePlayerResult"]["HasErrors"]) && !$apiResult["CreatePlayerResult"]["HasErrors"]){
                if($val['isVIP'] == 1){
                    $isVIP = 1;
                    $PID = $RTGAbbottCashierAPI->GetPIDFromLogin($username);
                    $changePlayerClass = $RTGAbbottPlayerAPI->changePlayerClasification($PID["GetPIDFromLoginResult"], $isVIP, $userID);
                }
                $iscreated = 1;
            } else if(!empty($PID["GetPIDFromLoginResult"])){
                if($val['isVIP'] == 1){
                    $isVIP = 1;
                    $changePlayerClass = $RTGAbbottPlayerAPI->changePlayerClasification($PID["GetPIDFromLoginResult"], $isVIP, $userID);
                }
                $iscreated = 1;
            }

            //Check if the account is successfully created, if yes update the generatedpasswordbatch and tag the MID that used the passwords.
            //Insert the newly created account to memberservices table and update the memberservices_temp table status for the specific MID as 2-Created, 1-Not Yet Created.
            if($iscreated == 1){
                $stmt5 = "SELECT COUNT(GeneratedPasswordBatchID) AS Count FROM generatedpasswordbatch WHERE MID = $MID";   
                $sth5 = $dbh->prepare($stmt5);
                $sth5->execute();
                $result5 = $sth5->fetchAll(PDO::FETCH_ASSOC);
                $isexist = $result5[0]['Count'];
                
                //Check if Generated the Password Batch ID is already tag to the specific MID, if not update and tag it to its appropriate MID
                //Insert the newly created account in memberservices and Update the status of the record in memberservices_temp to 2-Created, 1-Not Yet Created
                if($isexist == 0){
                    $dbh->beginTransaction();
                    $stmt6 = "UPDATE generatedpasswordbatch SET DateUsed = now_usec(), MID = $MID, Status = 1 WHERE GeneratedPasswordBatchID = $genpassbatchid"; 
                    $sth6 = $dbh->prepare($stmt6);
                    if($sth6->execute()){
                        $chck = $dbh->prepare("SELECT COUNT(MemberServiceID) AS Exist FROM memberservices WHERE ServiceID = 19 AND MID = $MID");
                        $chck->execute();
                        $exist = $chck->fetchAll(PDO::FETCH_ASSOC);
                        $isexist = $exist[0]['Exist'];
                        if($isexist == 0){
                            $stmt7 = "INSERT INTO memberservices (ServiceID, MID, ServiceUsername, ServicePassword, 
                                            HashedServicePassword, UserMode, DateCreated, isVIP, VIPLevel, Status) 
                                            VALUES ($serviceid, $MID, '$username', '$password', '$hashedpassword', $usermode, now(6), $isVIP, $isVIP, $status)";
                        } else {
                            $stmt7 ="UPDATE memberservices SET ServicePassword = '$password', HashedServicePassword = '$hashedpassword' 
                                                WHERE MID = $MID AND ServiceID = 19";
                        }
                        $sth6 = $dbh->prepare($stmt7);
                        if($sth6->execute()){
                            $stmt8 = "UPDATE memberservices_temp SET Status = 2 WHERE MID = $MID AND ServiceID = $serviceid";
                             $sth6 = $dbh->prepare($stmt8);
                             if($sth6->execute()){
                                 $dbh->commit();
                             } else {
                                 $dbh->rollBack();
                             }
                        } else {
                            $dbh->rollBack();
                        }
                    } else {
                        $dbh->rollBack();
                    }
                     
                } else {
                    $dbh->beginTransaction();
                    $chck = $dbh->prepare("SELECT COUNT(MemberServiceID) AS Exist FROM memberservices WHERE ServiceID = 19 AND MID = $MID");
                    $chck->execute();
                    $exist = $chck->fetchAll(PDO::FETCH_ASSOC);
                    $isexist = $exist[0]['Exist'];
                    if($isexist == 0){
                        $stmt7 = "INSERT INTO memberservices (ServiceID, MID, ServiceUsername, ServicePassword, 
                                        HashedServicePassword, UserMode, DateCreated, isVIP, VIPLevel, Status) 
                                        VALUES ($serviceid, $MID, '$username', '$password', '$hashedpassword', $usermode, now(6), $isVIP, $isVIP, $status)";
                    } else {
                        $stmt7 ="UPDATE memberservices SET ServicePassword = '$password', HashedServicePassword = '$hashedpassword' 
                                            WHERE MID = $MID AND ServiceID = 19";
                    }
                    $sth6 = $dbh->prepare($stmt7);
                    if($sth6->execute()){
                        $stmt8 = "UPDATE memberservices_temp SET Status = 2 WHERE MID = $MID AND ServiceID = $serviceid";
                         $sth6 = $dbh->prepare($stmt8);
                         if($sth6->execute()){
                             $dbh->commit();
                         } else {
                             $dbh->rollBack();
                         }
                    } else {
                        $dbh->rollBack();
                    }
                }
            } else { continue; }
        } else { break; echo 'No Password and Hashed Password for this MID: '.$MID; }
    } else { continue; }
    
}

$endTime = microtime(true);
$max_execution_time = ($endTime - $startTime) / 60;
echo 'Script execution done!';
echo "<br />";
echo 'Total Execution Time : '.$max_execution_time;
?>