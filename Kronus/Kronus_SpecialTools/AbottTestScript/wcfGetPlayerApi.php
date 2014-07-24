<?php 

Class test {

}

?>
<?php
    
/**
     * Path to certificate file
     * @var string
     */
    $_certFilePath = 'var/www/AbottAPITest/13/cert.pem';

    /**
     * Path to certificate key file
     * @var string
     */
    $_keyFilePath = 'var/www/AbottAPITest/13/key.pem';
echo 'Call the service using GET <br>';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/RTG.Services/Player.svc/CreatePlayer?fname=MASTER&lname=POGI");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', 'Accept: application/xml'));
curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt( $ch, CURLOPT_SSLCERTTYPE, 'PEM' );
curl_setopt( $ch, CURLOPT_SSLCERT, $_certFilePath );
curl_setopt( $ch, CURLOPT_SSLKEYTYPE, 'PEM' );
curl_setopt( $ch, CURLOPT_SSLKEY, $_keyFilePath );
$result = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
print_r($result);

echo '<br>';
echo '<br>Call the service using POST <br>';
$login = 'ITDEVTEST990';
$password = 'password';
$transmitObject = array('Player'=>array("Login"=>$login, "Password"=>$password),
              'ThirdPartyDataSync' => true,
              'UserID' => 0,
              'MapToAffID'=>false,
              'CalledFromCasino'=>0);
$jsonObject = json_encode($transmitObject);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/RTG.Services/Player.svc/CreatePlayer");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: text/xml; charset=utf-8' ) );
curl_setopt($ch, CURLOPT_POSTFIELDS, $transmitObject);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt( $ch, CURLOPT_SSLCERTTYPE, 'PEM' );
curl_setopt( $ch, CURLOPT_SSLCERT, $_certFilePath );
curl_setopt( $ch, CURLOPT_SSLKEYTYPE, 'PEM' );
curl_setopt( $ch, CURLOPT_SSLKEY, $_keyFilePath );
$result = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
var_dump($result);
var_dump($status);exit;
?>
