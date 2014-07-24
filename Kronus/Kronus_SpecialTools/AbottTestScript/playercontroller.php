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


if($_POST['method'] == 1){
    
$login = $_POST['username'];
$password = $_POST['password'];
$hashedPassword = sha1($password);
//$hashedPassword = 'TM8DGT45';
$country = 'PH';
$casinoID = 1;
$fname = 'NA';
$lname = 'NA';
$email = $_POST['email'];
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
$retval = $rtgWcfPlayerApi->createTerminalAccount($login, $password, $aid, $country, $casinoID, 
        $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, $city, $state, 
        $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, $putInAffPID, 
        $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $playerClass);
    
}
elseif($_POST['method'] == 2){

//Change Password
$password = $_POST['password'];
$newpassword = $_POST['newpassword'];
$arrChangePwd = array('Login'=>$login,'OldPassword'=>$password,'NewPassword'=>$newpassword);
$retval = $rtgWcfPlayerApi->changePlayerPassword($login, $password, $newpassword);
    
}
elseif($_POST['method'] == 3){

//Get Player Class
$pid = $_POST['pid'];
$arrGetPlayerClass = array('PID'=>$pid);
$retval = $rtgWcfPlayerApi->getPlayerClasification($pid);

}
elseif($_POST['method'] == 4){

//Change Player Class
$playerClassID = $_POST['playerclassid']; //0 - Regular / New ; 1 - High Roller; 2 - VIP 
$pid = $_POST['pid'];
$userID = 0;
$arrChangePlayer = array('PID'=>$pid,'playerClassID'=>$playerClassID,'UserID'=>$userID);
$retval = $rtgWcfPlayerApi->changePlayerClasification($pid, $playerClassID, $userID);
    
}
else{
    $retval = 'Muling ulitin!';
}

var_dump( $retval );

?>
