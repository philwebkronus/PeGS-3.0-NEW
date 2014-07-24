<?php

/**
 * @author Edson Perez
 * @date January 24, 2014
 * @purpose consume RTG Player API
 */

include 'CasinoAPI/RealtimeGamingCashierAPI2.class.php';

$cashierUrl = 'https://125.5.1.18/ABBOTTRUVMANSYWPLMXI/processor/ProcessorAPI/Cashier2.asmx';
$certFilepath = '/var/www/AbottAPITest/19/cert.pem';
$keyFilePath = '/var/www/AbottAPITest/19/key.pem';

$cashierAPI = new RealtimeGamingCashierAPI($cashierUrl, $certFilepath, $keyFilePath, '');

$login = 'TESTACCT18';
$password = 'PASSWORD01';
//$login = '10011584';
//$password = '1JPTY4';
$casinoID = 1;

////GetPIDFromLogin
//$PID = $cashierAPI->GetPIDFromLogin($login);
//$result = $PID;

//GetAccountInfoByPID
//$PID = '10000335';
//$result = $cashierAPI->GetAccountInfoByPID(1, $PID);

//Getbalance
$PID = '10000335';
$result = $cashierAPI->GetAccountBalance(1, $PID);

//Login
//$PID = '10000335';
//$password = 'PASSWORD01';
//$hashedPassword = sha1( $password );
//$resultlogin = $cashierAPI->Login(1, $PID, $hashedPassword, 1, $_SERVER[ 'HTTP_HOST' ]);
//
//$sessionID = $resultlogin["LoginResult"];
//var_dump($sessionID);exit;

//Deposit
//$PID = '10000335';
//$methodID = '503';
//$amount = '500';
//$tracking1 = '1021318';
//$tracking2 = 'D';
//$tracking3 = '1524';
//$tracking4 = '167';
//$sessionID = 16;
//$result = $cashierAPI->DepositGeneric(1, $PID, $methodID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionID);


//WithdrawGeneric
//$PID = '10000335';
//$methodID = 502;
//$amount = '1000';
//$tracking1 = '1021319';
//$tracking2 = 'W';
//$tracking3 = '1524';
//$tracking4 = '167';
//$sessionID = 17;
//$result = $cashierAPI->WithdrawGeneric($casinoID, $PID, $methodID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionID);


//TrackingInfoTransactionSearch
//$PID = '10000320';
//$tracking1 = '';
//$tracking2 = '';
//$tracking3 = '';
//$tracking4 = '';
//$result = $cashierAPI->TrackingInfoTransactionSearch($PID, $tracking1, $tracking2, $tracking3, $tracking4);

var_dump($result);
?>
