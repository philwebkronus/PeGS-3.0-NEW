<?php

/**
 * Description of testing
 * Date Created 09 13, 11 1:13:51 PM
 * @author Bryan Salazar
 */
include_once "sys/core/init.php";
//include_once "sys/class/cashierhandler.class.php";
include_once "sys/class/CasinoAPIHandler.class.php";

$serverId = $_GET[ 'sid' ];
$login = $_GET[ 'login' ];

$configuration = array( 'URI' => $_ServiceAPI[$serverId-1],
                        'isCaching' => FALSE,
                        'isDebug' => TRUE,
                        'certFilePath' => RTGCerts_DIR . $serverId  . '/cert.pem',
                        'keyFilePath' => RTGCerts_DIR . $serverId  . '/key.pem',
                        'depositMethodId' => 503,
                        'withdrawalMethodId' => 502 );

var_dump($configuration);
$_CasinoAPIHandler = new CasinoAPIHandler( CasinoAPIHandler::RTG, $configuration );

echo '<br />';

if ( (bool)$_CasinoAPIHandler->IsAPIServerOK() )
{
        var_dump($_CasinoAPIHandler->GetBalance( $login ));
}
else
{
        echo 'Server not available.';
}


?>

