<?php
global $_DBConnectionString, $URI_PlayerAPI, $URI_CashierAPI, $deposit_method_id, $withdrawal_method_id, $cert_file_path, $key_file_path, $cert_key_file_path;


$_DBConnectionString[0] = "mysql:host=172.16.102.157;dbname=membership,pegsconn,pegsconnpass";

//RTG Configuration Settings

//URI [Player API and Cashier API]
$URI_PlayerAPI = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/RTG.Services/Player.svc?wsdl';
$URI_CashierAPI = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx';

//Additional Configurations
$deposit_method_id =503;
$withdrawal_method_id=502;
$isCaching = FALSE;

//Certificates Location
$cert_file_path = "/var/www/Kronus_UB/admin.abbott.version/public/views/sys/config/RTGClientCerts/19/cert.pem"; //Local location of RTG Certs
$key_file_path = "/var/www/Kronus_UB/admin.abbott.version/public/views/sys/config/RTGClientCerts/19/key.pem"; //Local location of RTG Certs
$cert_key_file_path = "/var/www/Kronus_UB/admin.abbott.version/public/views/sys/config/RTGClientCerts/19/cert-key.pem"; //Local location of RTG Certs

?>
