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
define ( 'RTGCerts_DIR', 'C://xampp/htdocs/Kronus.UB.New/admin.abbott.genesis.version/public/views/sys/config/RTGClientCerts/'); //staging

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

$_DBConnectionString[0] = "mysql:host=172.16.102.157;dbname=npos,nposconn,npos"; //live DB
$_DBConnectionString[1] = "mysql:host=172.16.102.157;dbname=npos,nposconn,npos"; //replication report DB
$_DBConnectionString[2] = "mysql:host=172.16.102.157;dbname=stackermanagement,pegsconn,pegsconnpass"; //replication report DB
$_DBConnectionString[3] = "mysql:host=172.16.102.157;dbname=vouchermanagement,pegsconn,pegsconnpass"; //replication report DB


$terminalcode = "ICSA-";
$cutoff_time = "06:00:00"; //cutoff time set for report
$maxcashier = 3;
$mrforloyalty = false; // manual redemption for loyalty
$gaddeddate = '+1 day'; //Add +1 day on end date of reports
//$cashierlogpath = "/var/www/cashier.pagcoregames.com/app/frontend/logs/";
$cashierlogpath = "C://xampp/htdocs/Kronus.UB.New/cashier.abbott.genesis.version/app/frontend/logs/";
$gpasscode_len = 3;
$maxterminals = 10; //used for limiting the chosen terminals for Change Terminal Password - AS
$gsysversion = 'admin2_ub.pagcoregames.com'; //used for logging of current version used by admin
$casinoGH_url = 'http://172.16.102.35/dashboardv2/kronusghpagcor.php';
$cardinfo = 'http://192.168.28.108/membership.rewards/API/cardinquiry.php'; //for getting membership card information

//$gcashierupdate = "https://cashier.pagcoregames.com/index.php?r=updatepassword&"; //prod
//$gadminupdate = "https://admin.pagcoregames.com/UpdatePassword.php?"; //prod
$gcashierupdate = "http://192.168.28.108/Kronus.UB.New/cashier.abbott.genesis.version/index.php?r=updatepassword&"; //staging
$gadminupdate = "http://192.168.28.108/Kronus.UB.New/admin.abbott.genesis.version/public/views/UpdatePassword.php?"; //staging
$launchPadLogPath = 'C://xampp/htdocs/Launchpad_GTC/protected/runtime/'; //path for launchpad logs
$adminlogpath = "C://xampp/htdocs/Kronus.UB.New/admin.abbott.genesis.version/public/views/sys/log/";

define('CUT_OFF','06:00:00');

$_CAPIUsername = 'philweb_capi';
$_CAPIPassword = 'test1234$';
$_CAPIPlayerName = 'capi';
$_CAPIEventID = 1003;

$_ServiceAPI[0] = 'https://202.44.100.29/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx';
$_ServiceAPI[1] = 'https://202.44.100.28/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx';
$_ServiceAPI[2] = 'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx';
$_ServiceAPI[3] = 'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx';
$_ServiceAPI[4] = 'https://202.44.100.29/ALPHAKI98TUI5AMINAS2/processor/processorapi/cashier.asmx';
$_ServiceAPI[5] = 'https://202.44.100.28/GAMMAOP98TUI5JMNDES2/processor/processorapi/cashier.asmx';
$_ServiceAPI[6] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx';
$_ServiceAPI[7] = 'https://cashier-dev.egamescasino-ss.ph';
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
$_ServiceAPI[18] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx';

$_PlayerAPI[0] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[1] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[2] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[3] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[4] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[5] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[6] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[7] = 'https://cashier-dev.egamescasino-ss.ph';
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
$_PlayerAPI[18] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/RTG.Services/Player.svc?wsdl';

$_LobbyAPI[0] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx';
$_LobbyAPI[1] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx';
$_LobbyAPI[2] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx';
$_LobbyAPI[3] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx';
$_LobbyAPI[4] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx';
$_LobbyAPI[5] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx';
$_LobbyAPI[6] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx';
$_LobbyAPI[7] = '';
$_LobbyAPI[8] = '';
$_LobbyAPI[9] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx';
$_LobbyAPI[10] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx';
$_LobbyAPI[11] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx';
$_LobbyAPI[12] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx';
$_LobbyAPI[13] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx';

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

$_MicrogamingUserType = 0; //Real Player
$_MicrogamingCurrency = 23; // PHP Philippine Peso
$_MicrogamingMethod = 'ChangeBalanceEx';

//playtech data
$_ptcasinoname='egamesqa';
$_ptsecretkey='playtech';
$_ptcurrency = 'PHP';

//PT VIP Level
$_ptreg = 2;
$_ptvip = 3;

$groupemail_daily = array('aqdepliyan@philweb.com.ph'); //list of emails for daily buy in report
$groupemaildb = array('aqdepliyan@philweb.com.ph');
$grouppegs = array('aqdepliyan@philweb.com.ph'); //list of emails for PEGS Email
$groupas = array('aqdepliyan@philweb.com.ph'); //list of emails for AS Group Email

// condition in grosshold monitoring
define('_GREEN_1',200000);
define('_GREEN_2',400000);
define('_ORANGE_1',400000);
define('_ORANGE_2',600000);
define('_BLUE_',600000);
define('_RED_',200000);
define('base_gh', 200000);

//MAX balance for redemption
$_maxRedeem = "999,999.00";
//virtual cashier email
$_virtual_email = 'no-reply@philweb.com.ph';
$_virtual_un_prefix = 'CSHREGM';
?>
