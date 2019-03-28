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
define ( 'RTGCerts_DIR', '/var/www/kronus.admin.v15.costello/public/views/sys/config/RTGClientCerts/'); //staging

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
global $_virtual_un_prefix;
global $_virtual_prefix_ewallet;
global $_virtual_email;
global $_virtual_email_ew;
global $Pcws;
global $referer;
global $deploymentDate;
global $siteamtlength;

$_DBConnectionString[0] = "mysql:host=172.16.102.157;dbname=npos,nposconn,npos"; //live DB
$_DBConnectionString[1] = "mysql:host=172.16.102.157;dbname=npos,nposconn,npos"; //replication report DB
$_DBConnectionString[2] = "mysql:host=172.16.102.157;dbname=stackermanagement,pegsconn,pegsconnpass"; //replication report DB
$_DBConnectionString[3] = "mysql:host=172.16.102.157;dbname=vouchermanagement,pegsconn,pegsconnpass";
$_DBConnectionString[4] = "mysql:host=172.16.102.157;dbname=loyaltydb,pegsconn,pegsconnpass";
$_DBConnectionString[5] = "mysql:host=172.16.102.157;dbname=membership,pegsconn,pegsconnpass";

define('referrer',"https://kronus.admin.v15.costello/login.php");
$terminalcode = "ICSA-";
$cutoff_time = "06:00:00"; //cutoff time set for report
$maxcashier = 3;
$mrforloyalty = false; // manual redemption for loyalty
$gaddeddate = '+1 day'; //Add +1 day on end date of reports
//$cashierlogpath = "/var/www/cashier.pagcoregames.com/app/frontend/logs/";
//$cashierlogpath = "/var/www/Kronus_UB/cashier2.pagcoregames.com/app/frontend/logs/";
$gpasscode_len = 4;
$maxterminals = 10; //used for limiting the chosen terminals for Change Terminal Password - AS
$gsysversion = 'admin2_ub.pagcoregames.com'; //used for logging of current version used by admin
$casinoGH_url = 'http://172.16.102.35/dashboardv2/kronusghpagcor.php';
$cardinfo = 'http://172.16.102.174/membership.rewards.v02/API/cardinquiry.php'; //for getting membership card information
//$gcashierupdate = "https://cashier.pagcoregames.com/index.php?r=updatepassword&"; //prod
//$gadminupdate = "https://admin.pagcoregames.com/UpdatePassword.php?"; //prod
$gcashierupdate = "http://172.16.102.174/Kronus_UB/kronus.cashier.abbott/index.php?r=updatepassword&"; //staging
$gadminupdate = "http://172.16.102.174/Kronus_UB/kronus.admin.abbott/public/views/UpdatePassword.php?"; //staging
//$launchPadLogPath = '/var/www/Launchpad_UB/protected/runtime/'; //path for launchpad logs
//$adminlogpath = "/var/www/Kronus_UB/admin.abbott.version/public/views/sys/log/";
$siteamtlength = 3;
$adminVersion = "Kronus ver 6.0";

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
$_ServiceAPI[17] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx';
$_ServiceAPI[18] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx';
$_ServiceAPI[19] = 'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/processor/processorapi/cashier.asmx';  //RTG Naboo
$_ServiceAPI[20] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx'; //RTG - Costello e-Bingo V12 (TB)
$_ServiceAPI[21] = 'https://125.5.14.8/PHPCOSTELLOVTOWYHEDT/processor/processorapi/cashier.asmx'; //RTG - Costello V15 (TB)
//$_ServiceAPI[21] = 'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/processor/processorapi/cashier.asmx'; //RTG - Costello V15 (TB) Naboo
$_ServiceAPI[22] = 'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/processor/processorapi/cashier.asmx'; //RTG - Costello e-Bingo V15(TB)

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
$_PlayerAPI[17] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx';
$_PlayerAPI[18] = 'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/RTG.Services/Player.svc?wsdl';
$_PlayerAPI[19] = 'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/RTG.Services/Player.svc?wsdl'; //RTG Naboo
$_PlayerAPI[20] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx'; //RTG - Costello e-Bingo V12 (TB)
$_PlayerAPI[21] = 'https://125.5.14.8/PHPCOSTELLOVTOWYHEDT/RTG.Services/Player.svc?wsdl'; //RTG - Costello V15 (TB)
//$_PlayerAPI[21] = 'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/RTG.Services/Player.svc?wsdl'; //RTG - Costello V15 (TB) naboo
$_PlayerAPI[22] = 'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/RTG.Services/Player.svc?wsdl'; //RTG - Costello e-Bingo V15(TB)

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
$_LobbyAPI[14] = '';
$_LobbyAPI[15] = '';
$_LobbyAPI[16] = '' ;
$_LobbyAPI[17] = '' ;
$_LobbyAPI[18] = 'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/CasinoAPI/Games.asmx'; //RTG Naboo
$_LobbyAPI[19] = 'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/CasinoAPI/Games.asmx'; //RTG Naboo
$_LobbyAPI[20] = 'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx'; //RTG e-BINGO - v12(TB)
$_LobbyAPI[21] = 'https://125.5.14.8/PHPCOSTELLOVTOWYHEDT/CasinoAPI/Games.asmx'; //RTG Costello - v15(TB)
//$_LobbyAPI[21] = 'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/CasinoAPI/Games.asmx'; //RTG Costello - v15(TB) naboo
$_LobbyAPI[22] = 'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/CasinoAPI/Games.asmx'; //RTG Costello e-Bingo - v15(TB)

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

$group1 = array('taalcantara@philweb.ph'); //list of emails for GT 50K (bigreload)
$group2 = array('taalcantara@philweb.ph'); //list of emails for GT 100k (bigwinnings)
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
define('_RED2_',100000);
define('base_gh', 200000);

$SAPI = array(
    'endsession' => 'http://172.16.102.176/SpyderServerGenesisSapiStaging/index.php'
);
//MAX balance for redemption
$_maxRedeem = "999,999.00";
$_virtual_un_prefix = 'CSHREGM';
$_virtual_prefix_ewallet = 'CSHREWVC';
$_virtual_email = "virtual_email@yopmail.com";
$_virtual_email_ew = "virtual_email_ew@yopmail.com";
//log Paths

$deploymentDate = "2016-01-13";

//Pcws Url
$Pcws = array(
    'systemusername' => 'kadmin',
    'systemcode' => '4996816',
    'resetpin' => 'https://pcws.esafe.v15.costello/index.php/pcws/changepin',
    'forcelogout' => 'https://pcws.esafe.v15.costello/index.php/pcws/forcelogout',
    'removesession' => 'https://pcws.esafe.v15.costello/index.php/pcws/removesession',
    'forcelogoutgen' => 'https://pcws.esafe.v15.costello/index.php/pcws/forcelogoutgen'
);

//RTG Naboo Skin Locator Name
$_SkinNamePlatinum = 'Esafeskinegames Naboo';
$_SkinNameNonPlatinum = 'Naboo';

//adding a new path to a system eg. $cashierlogpath[1]="path"
$cashierlogpath[0] = "C:/Apache24/htdocs/Kronus_UB/cashier.genesis.abbott.bbx/app/frontend/logs/";
$membership[0] = 'http://172.16.102.174/membership.rewards/rsframework/include/log/'; //path for launchpad logs
$adminlogpath[0] = "http://172.16.102.174/admin.bbx/public/views/sys/log/";
$rewardslogpath[0]="http://172.16.102.174/rewards.management/protected/runtime/";
$KAPIlogpath[0]="http://172.16.102.174/kapi.abbott.dev.local/protected/runtime/";
$STAPIlogtah[0]="http://172.16.102.174/stapi.dev.local/protected/runtime/";
$VAPIlogpath[0]="http://172.16.102.174/vapi.dev.local/protected/runtime/";
$genesislogpath[0]="http://172.16.102.174/stackermgmt.dev.local/protected/runtime/";
$AMPAPIlogpath[0]="http://172.16.102.174/ampapi.dev.local/protected/runtime/";
$BTAMPAPIlogtah[0]="http://172.16.102.174/btampapi.dev.local/protected/runtime/";
$MPAPIlogpath[0]="http://172.16.102.174/mpapi.dev.local/protected/runtime/";
$VMSlogpath[0]="http://172.16.102.174/vms.dev.local/protected/runtime/";
$launchPadLogPath[0] = 'http://172.16.102.174/launchpadgtc.dev.local/protected/runtime/';


$LogPaths['Cashier'] = $cashierlogpath;
$LogPaths['Membership'] = $membership;
$LogPaths['Admin'] = $adminlogpath;
$LogPaths['Rewards'] = $rewardslogpath;
$LogPaths['KAPI'] = $KAPIlogpath;
$LogPaths['VAPI'] = $STAPIlogtah;
$LogPaths['STAPI'] = $VAPIlogpath;
$LogPaths['Genesis'] = $genesislogpath;
$LogPaths['AMPAPI'] = $AMPAPIlogpath;
$LogPaths['MPAPI'] = $MPAPIlogpath;
$LogPaths['VMS'] = $VMSlogpath;
$LogPaths['LaunchPad'] = $launchPadLogPath;


?>
