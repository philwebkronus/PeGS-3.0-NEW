<?php

define ( 'ENVIRONMENT', 'DEV' ); // DEV, PROD
define ( 'DEBUG', true );
define ( 'CUSTOM_ERROR_HANDLER', false );
define ( 'LOCALE', 'En-Us' );
define ( 'TIME_ZONE', 'Asia/Manila' );
define ( 'TMP_DIR', ROOT_DIR . 'sys/tmp/' );
define ( 'LOG_DIR', ROOT_DIR . 'sys/log/' );
define ( 'HTTPS', ( empty( $_SERVER[ 'HTTPS' ] ) || strtolower( $_SERVER[ 'HTTPS' ] ) == 'off' ? false : true ) );
define ( 'BASE_URL', '' );
define ( 'SYSLOG', false );
define ( 'ADMIN_EMAIL', 'itswebadmin@gmail.com' );
//define ( 'RTGCerts_DIR', '/var/www/admin.pagcoregames.com/public/views/sys/config/RTGClientCerts/'); //production
define ( 'RTGCerts_DIR', '/var/www/kronus_ub/admin2_ub.pagcoregames.com/public/views/sys/config/RTGClientCerts/'); //staging

global $_DBConnectionString;
global $terminalcode;
global $cutoff_time;
global $varrbracket;
global $maxcashier;
global $gaddeddate; 
global $cashierlogpath;
global $gcashierupdate;
global $gadminupdate;
global $_MicrogamingMethod;
global $gpasscode_len;
global $maxterminals;
global $launchPadLogPath;
global $bigreloadcron;
global $gsysversion;

//Development DB
$_DBConnectionString[0] = "mysql:host=172.16.102.157;dbname=npos,nposconn,npos"; 

//Demo DB
//$_DBConnectionString[0] = "mysql:host=172.16.102.156;dbname=npos,nposconn,npos";

$terminalcode = "ICSA-";
$cutoff_time = "06:00:00"; //cutoff time set for report
$maxcashier = 3;
$mrforloyalty = false; // manual redemption for loyalty
$gaddeddate = '+1 day'; //Add +1 day on end date of reports
//$cashierlogpath = "/var/www/cashier.pagcoregames.com/app/frontend/logs/";
$cashierlogpath = "/var/www/kronus_ub/cashier2.pagcoregames.com/app/frontend/logs/";
$gpasscode_len = 3;
$maxterminals = 10; //used for limiting the chosen terminals for Change Terminal Password - AS
$gsysversion = 'admin2_ub.pagcoregames.com'; //used for logging of current version used by admin
$casinoGH_url = 'http://172.16.102.35/dashboardv2/kronusghpagcor.php';
$cardinfo = 'http://192.168.20.8:8092/API/cardinquiry.php';

//$gcashierupdate = "https://cashier.pagcoregames.com/index.php?r=updatepassword&"; //prod
//$gadminupdate = "https://admin.pagcoregames.com/UpdatePassword.php?"; //prod
$gcashierupdate = "http://192.168.30.97/Kronus_UB/cashier2.pagcoregames.com/index.php?r=updatepassword&"; //staging
$gadminupdate = "http://192.168.30.97/Kronus_UB/admin2_ub.pagcoregames.com/public/views/UpdatePassword.php?"; //staging
$launchPadLogPath = '/var/www/launchpad_ub/protected/runtime/'; //path for launchpad logs

define('CUT_OFF','06:00:00');

$_ServiceAPI[0] = 'https://202.44.100.29/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx';
$_ServiceAPI[1] = 'https://202.44.100.28/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx';
$_ServiceAPI[2] = 'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx';
$_ServiceAPI[3] = 'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx';
$_ServiceAPI[4] = 'https://202.44.100.29/ALPHAKI98TUI5AMINAS2/processor/processorapi/cashier.asmx';
$_ServiceAPI[5] = 'https://202.44.100.28/GAMMAOP98TUI5JMNDES2/processor/processorapi/cashier.asmx';
$_ServiceAPI[6] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx';
$_ServiceAPI[7] = 'https://extdev-devhead-cashier.extdev.eu';
//$_ServiceAPI[8] = 'https://entservices.totalegame.net/EntServices.asmx?WSDL';
$_ServiceAPI[8] = array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238);
$_ServiceAPI[9] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx';
$_ServiceAPI[10] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx';
$_ServiceAPI[11] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx';
$_ServiceAPI[12] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx';
$_ServiceAPI[13] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx';
$_ServiceAPI[14] = array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238); 
$_ServiceAPI[15] = array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238); 
$_ServiceAPI[16] = array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238);
$_ServiceAPI[17] = 'https://202.44.103.231/GPRIMESZNVJFROAPSERT/processor/ProcessorAPI/Cashier.asmx';

$_PlayerAPI[0] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[1] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[2] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[3] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[4] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[5] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[6] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[7] = 'https://extdev-devhead-cashier.extdev.eu';
$_PlayerAPI[8] = 'https://entservices.totalegame.net/EntServices.asmx?WSDL';
$_PlayerAPI[9] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/casinoapi/player.asmx';
$_PlayerAPI[10] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/casinoapi/player.asmx';
$_PlayerAPI[11] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/casinoapi/player.asmx';
$_PlayerAPI[12] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/casinoapi/player.asmx';
$_PlayerAPI[13] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[14] = array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238); 
$_PlayerAPI[15] = array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238); 
$_PlayerAPI[16] = array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238);
$_PlayerAPI[17] = 'https://202.44.103.231/GPRIMESZNVJFROAPSERT/CasinoAPI/player.asmx';

$_ServiceAPICaching[0] = false;
$_ServiceAPICaching[1] = false;
$_ServiceAPICaching[2] = false;
$_ServiceAPICaching[3] = false;
$_ServiceAPICaching[4] = false;
$_ServiceAPICaching[5] = false;
$_ServiceAPICaching[7] = false;
$_ServiceAPICaching[9] = false;
$_ServiceAPICaching[10] = false;
$_ServiceAPICaching[11] = false;
$_ServiceAPICaching[12] = false;
$_ServiceAPICaching[13] = false;

$_ServiceAPICaching[8] = false;
$_ServiceAPICaching[2000] = false;

//MG Data
$_MicrogamingUserType = 0; //Real Player
$_MicrogamingCurrency = 23; // PHP Philippine Peso
$_MicrogamingMethod = 'ChangeBalanceEx';
$_CAPIUsername = 'philweb_capi';
$_CAPIPassword = 'test1234$';
$_CAPIPlayerName = 'capi';
$_CAPIEventID = 1003;

//playtech data
$_ptcasinoname='playtech800041';
$_ptsecretkey='PhilWeb123';
$_ptcurrency = 'PHP';

$grouppegs = array('elperez@philweb.com.ph'); //list of emails for PEGS Email
$groupas = array('elperez@philweb.com.ph'); //list of emails for AS Group Email

//MAX balance for redemption
$_maxRedeem = "999,999.00";
?>