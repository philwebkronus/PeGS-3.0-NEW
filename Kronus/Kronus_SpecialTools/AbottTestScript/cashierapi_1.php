<?php

/**
 * @author Edson Perez
 * @date January 24, 2014
 * @purpose consume RTG Player API
 */

include 'CasinoAPI/RealtimeGamingCashierAPI2.class.php';

$cashierUrl = 'https://125.5.1.18/ABBOTTRUVMANSYWPLMXI/RTG.Services/Cashier2.asmx';
$certFilepath = 'var/www/AbottAPITest/13/cert.pem';
$keyFilePath = 'var/www/AbottAPITest/13/key.pem';

$cashierAPI = new RealtimeGamingCashierAPI($cashierUrl, $certFilepath, $keyFilePath, '');

$login = 'TESTACCT01';
$password = 'password';
//$login = '10011584';
//$password = '1JPTY4';
$casinoID = 1;

////GetPIDFromLogin
$PID = $cashierAPI->GetPIDFromLogin($login);
$result = $PID;

//GetAccountInfoByPID
//$PID = '10011632';
//$result = $cashierAPI->GetAccountInfoByPID(1, $PID);

var_dump($result);
?>
