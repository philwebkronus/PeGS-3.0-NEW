<?php
include 'CasinoAPI/RealtimeGamingWCFPlayerAPI.class.php';

header( 'Content-Type: text/plain' );

$soapArr = array(
	'trace' => true,
	'exceptions' => true,
	'local_cert' => '/var/www/AbottAPITest/19/cert-key.pem',
	'passphrase' => ''
);


//$playerUrl = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/RTG.Services/Player.svc?wsdl';
$playerUrl = 'https://125.5.1.18/ABBOTTRUVMANSYWPLMXI/RTG.Services/Player.svc?wsdl';
//$localCert = '/var/www/AbottAPITest/13/cert-key.pem';
$localCert = '/var/www/AbottAPITest/19/cert-key.pem';

$rtgWcfPlayerApi = new RealtimeGamingWCFPlayerAPI($playerUrl, $localCert, '');

//$retval = $client->GetPID( array( 'Login' => 'ICSA-TST01' ) );

$login = 'TESTACCT19';
$password = 'PASSWORD01';
$hashedPassword = sha1($password);
//$hashedPassword = 'TM8DGT45';
$country = 'PH';
$casinoID = 1;
$fname = 'NA';
$lname = 'NA';
$email = 'TESTACCT19@yopmail.com';
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


//Create Player
//$retval = $rtgWcfPlayerApi->createTerminalAccount($login, $password, $aid, $country, $casinoID, 
//        $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, $city, $state, 
//        $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, $putInAffPID, 
//        $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $playerClass);

//Change Password
//$password = 'PASSWORD01';
//$newpassword = 'PASSWORD02';
//$arrChangePwd = array('Login'=>$login,'OldPassword'=>$password,'NewPassword'=>$newpassword);
//$retval = $rtgWcfPlayerApi->changePlayerPassword($login, $password, $newpassword);

//Get Player Class
$pid = '10000335';
$arrGetPlayerClass = array('PID'=>$pid);
$retval = $rtgWcfPlayerApi->getPlayerClasification($pid);

//Change Player Class
//$playerClassID = 1; //0 - Regular / New ; 1 - High Roller; 2 - VIP 
//$pid = '10000331';
//$userID = 0;
//$arrChangePlayer = array('PID'=>$pid,'playerClassID'=>$playerClassID,'UserID'=>$userID);
//$retval = $rtgWcfPlayerApi->changePlayerClasification($pid, $playerClassID, $userID);


var_dump( $retval );

?>
