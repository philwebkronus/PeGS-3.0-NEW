<?php

global $_CONFIG;

$_CONFIG["debug"] = true;
$_CONFIG["devmode"] = true;

$_CONFIG["cutofftime"] = '06:00:00';
$_CONFIG["cutofftimeend"] = '05:59:59';
$_CONFIG["images_directory"] = ROOT_DIR . 'include/items/images/';
$_CONFIG["uploaded_images"] = ROOT_DIR . 'include/uploaded-images/';
$_CONFIG["terms-conditions"] = "http://www.egamescasino.ph/terms-and-conditions/";

//credentials for sms integration
$_CONFIG['SMSURI'] = "http://rtmessagegw.egamescasino.ph/send";
$_CONFIG['app_id'] = "EGAMES";

//Player API URL's
$_CONFIG["player_api"] = array(
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx',
    'https://cashier-dev.egamescasino-ss.ph',
    'https://entservices.totalegame.net/EntServices.asmx?WSDL',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/casinoapi/player.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/casinoapi/player.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/casinoapi/player.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/casinoapi/player.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx',
    array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238),
    array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238),
    array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238),
    'https://202.44.103.231/GPRIMESZNVJFROAPSERT/CasinoAPI/player.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/RTG.Services/Player.svc?wsdl',
);


$_CONFIG["service_api"] = array(
    'https://202.44.100.29/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
    'https://202.44.100.28/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
    'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx',
    'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx',
    'https://202.44.100.29/ALPHAKI98TUI5AMINAS2/processor/processorapi/cashier.asmx',
    'https://202.44.100.28/GAMMAOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx',
    'https://cashier-dev.egamescasino-ss.ph',
    //$_ServiceAPI[8] = 'https://entservices.totalegame.net/EntServices.asmx?WSDL';
    array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238),
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
     'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx',
     array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238),
     array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238),
     array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238),
     'https://202.44.103.231/GPRIMESZNVJFROAPSERT/processor/ProcessorAPI/Cashier.asmx',
     'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
);
//Game API URL's
$_CONFIG["game_api"] = array(
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
    'https://202.44.103.231/GPRIMESZNVJFROAPSERT/CasinoAPI/Games.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
);

//Player API URL's
$_CONFIG["lobby_api"] = array(
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
    '',
    '',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
);

//PT Configuration Settings
$_CONFIG["pt_casino_name"] = 'egamesqa';
$_CONFIG["pt_secret_key"] = 'playtech';
//PT VIP Level
$_CONFIG["ptreg"] = '2';
$_CONFIG["ptvip"] = '3';
//RTG VIP Level
$_CONFIG["rtgreg"] = '0';
$_CONFIG["rtgvip"] = '1';

//Marketing Email
$_CONFIG['MarketingEmail'] = "aqdepliyan@philweb.com.ph";

//Image Path For Reward items
$_CONFIG['rewarditem_imagepath'] = "http://".$_SERVER["SERVER_NAME"]."/membership/rewards.management/images/rewarditems/";
$_CONFIG['extra_imagepath'] = "http://".$_SERVER["SERVER_NAME"]."/membership/membership.rewards/images/rewarditems/";

$_CONFIG['pt_rpt_uri'] = 'https://devadmin.egamescasino-ss.ph';
$_CONFIG['pt_rpt_casinoname'] = '19501';
$_CONFIG['pt_rpt_admin'] = 'ftgapuz';
$_CONFIG['pt_rpt_password'] = '56t7PHKV4J';
$_CONFIG['pt_rpt_code'] = 49451;

$_CONFIG['revertbroken_api]']= array(
    'URI' => 'https://devhead-webapi.extdev.eu/product/casino/service/backend/casino/playtech800041',
    "REVERT_BROKEN_GAME_MODE" => "cancel",
    "CASINO_NAME" => "playtech800041",
    "PLAYER_MODE" => "real"
);


//RTG Configuration Settings
 // RTG
$_CONFIG['deposit_method_id'] =503;
$_CONFIG['withdrawal_method_id']=502;
$_CONFIG['rtg_cert_dir']='/var/www/membership/membership.rewards/rsframework/include/RTGClientCerts/'; //local


//create account data
$_CONFIG['AID'] =  0;
$_CONFIG['country'] = 'PH';
$_CONFIG['password'] = '1HMA1Y';
$_CONFIG['termcode_prefix'] = 'ICSA-';
$_CONFIG['casinoID'] = 1;
$_CONFIG['userID'] = 0;
$_CONFIG['downloadID'] = 0;
$_CONFIG['clientID'] = 1;
$_CONFIG['putInAffPID'] = 0;
$_CONFIG['calledFromCasino'] = 0;
$_CONFIG['currentPosition'] = 0;
$_CONFIG['currency']='PHP';
$_CONFIG['rr_password']='philweb';
$_CONFIG['rr_URI']='https://cashier-dev.egamescasino-ss.ph';

//Define Maximum Item Quantity that can be avail by the player.
//$_CONFIG['item_maxquantity'] = 5
?>
