<?php
ini_set('max_execution_time', 0);
include 'web.config.php';
include 'PlayTechAPI.class.php';

$playtechAPI = new PlayTechAPI($URI, $casino, $secretKey);

$conn = explode( ",", $_DBConnectionString[0]);
$oconnectionstring1 = $conn[0];
$oconnectionstring2 = $conn[1];
$oconnectionstring3 = $conn[2];

$dbh = new PDO( $oconnectionstring1, $oconnectionstring2, $oconnectionstring3);

//query all member services
$stmt = "SELECT ServiceID, MID, ServiceUsername, ServicePassword, UserMode, isVIP, 
         VIPLevel FROM tmpmemberservices";

$sth = $dbh->prepare($stmt);
$sth->execute();
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

//iterate creation of membership account in PT
foreach ($result as $val){
    $username = $val['ServiceUsername'];
    $password = $val['ServicePassword'];
    $email = $val['MID'].'@philweb.com.ph';
    $vipLevel = $val['VIPLevel'];
    $lastName = "NA";
    $firstName = "NA";
    $birthDate = "1970-01-01";
    $address = "NA";
    $city = "NA";
    $phone = '123-4567';                               
    $zip = 'NA';
    $countryCode = 'PH';
    
    $playtechAPI->NewPlayer($username, $password, $email, $firstName, 
                $lastName, $birthDate, $address, $city, $countryCode, $phone, 
                $zip, $vipLevel);
}

echo 'Script execution done!';

?>