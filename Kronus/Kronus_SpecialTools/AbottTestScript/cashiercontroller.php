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


if($_POST['method'] == 1){

//GetPIDFromLogin
$login = $_POST['username'];    
$PID = $cashierAPI->GetPIDFromLogin($login);
$result = $PID;
    
}
elseif($_POST['method'] == 2){

//GetAccountInfoByPID
$PID = $_POST['pid'];
$result = $cashierAPI->GetAccountInfoByPID(1, $PID);
    
}
elseif($_POST['method'] == 4){

//Login
$PID = $_POST['pid'];
$password = $_POST['password'];
$hashedPassword = sha1( $password );
$result = $cashierAPI->Login(1, $PID, $hashedPassword, 1, $_SERVER[ 'HTTP_HOST' ]);

    
}
elseif($_POST['method'] == 3){
    
//Getbalance
$PID = $_POST['pid'];
$result = $cashierAPI->GetAccountBalance(1, $PID);

}
elseif($_POST['method'] == 5){

//Deposit
$PID = $_POST['pid'];
$methodID = '503';
$amount = $_POST['amount'];
$tracking1 = $_POST['tracking1'];
$tracking2 = $_POST['tracking2'];
$tracking3 = $_POST['tracking3'];
$tracking4 = $_POST['tracking4'];
$sessionID = $_POST['sessionid'];
$result = $cashierAPI->DepositGeneric(1, $PID, $methodID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionID);
    
    
}
elseif($_POST['method'] == 6){

//WithdrawGeneric
$PID = $_POST['pid'];
$methodID = 502;
$amount = $_POST['amount'];
$tracking1 = $_POST['tracking1'];
$tracking2 = $_POST['tracking2'];
$tracking3 = $_POST['tracking3'];
$tracking4 = $_POST['tracking4'];
$sessionID = $_POST['sessionid'];
$result = $cashierAPI->WithdrawGeneric(1, $PID, $methodID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionID);

    
}
elseif($_POST['method'] == 7){

//TrackingInfoTransactionSearch
$PID = $_POST['pid'];
$tracking1 = $_POST['tracking1'];
$tracking2 = $_POST['tracking2'];
$tracking3 = $_POST['tracking3'];
$tracking4 = $_POST['tracking4'];
$result = $cashierAPI->TrackingInfoTransactionSearch($PID, $tracking1, $tracking2, $tracking3, $tracking4);
    
}
else{
$result = 'Muling ulitin!!';    
}

var_dump($result);
?>
