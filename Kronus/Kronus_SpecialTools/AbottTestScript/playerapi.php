<?php

/**
 * @author Edson Perez
 * @date January 24, 2014
 * @purpose consume RTG Player API
 */

include 'CasinoAPI/RealtimeGamingPlayerAPI.class.php';
include 'CasinoAPI/RealtimeGamingWCFPlayerAPI.class.php';

//$playerUrl = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/casinoapi/player.asmx';
$playerUrl = 'https://125.5.1.18/ABBOTTRUVMANSYWPLMXI/CasinoAPI/player.asmx';
$certFilepath = 'var/www/AbottAPITest/19/cert.pem';
$keyFilePath = 'var/www/AbottAPITest/19/key.pem';

$rtgPlayerApi = new RealtimeGamingPlayerAPI($playerUrl, $certFilepath, $keyFilePath, '');
//$rtgPlayerApi = new RealtimeGamingWCFPlayerAPI($playerUrl, $certFilepath, $keyFilePath, '');

/**Account Info ***/
$login = 'ITDEVTEST040';
$password = 'TM8DGT45';
$hashedPassword = strtoupper(sha1($password));
//$hashedPassword = 'TM8DGT45';
$country = 'PH';
$casinoID = 1;
$fname = 'NA';
$lname = 'NA';
$email = '040@philweb.com.ph';
$dayphone = '123-4567';
$evephone = '';
$addr1 = 'PH';
$addr2 = '';
$city = 'PH';
$state = '';
$zip = '1232';
$ip = '';
$mac = '';
$userID = 0;
$downloadID = 0;
$birthdate = '1970-01-01';
$clientID = 1;
$putInAffPID = 0;
$calledFromCasino = 0;
$agentID = '';
$currentPosition = 0;
$thirdPartyPID = '';
$aid = 0;

//New Account Creation (WCF)
//$response = $rtgPlayerApi->createPlayer($login, $password);

//Account Creation
$response = $rtgPlayerApi->createTerminalAccount($login, $password, $aid, $country, $casinoID, 
        $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, $city, $state, 
        $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, $putInAffPID, 
        $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID);

//Change password
//$newpassword = 'password';
//$oldpassword = 'TM8DGT45';
//$response = $rtgPlayerApi->changePlayerPassword($casinoID, $login, $oldpassword, $newpassword);       
        
        
var_dump($response);
exit;
?>
