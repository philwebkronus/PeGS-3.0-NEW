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
);

//PT Configuration Settings
$_CONFIG["pt_casino_name"] = 'egamesqa';
$_CONFIG["pt_secret_key"] = 'playtech';
//PT VIP Level
$_CONFIG["ptreg"] = '2';
$_CONFIG["ptvip"] = '3';

//Image Path For Reward items
$_CONFIG['rewarditem_imagepath'] = "http://".$_SERVER["SERVER_NAME"]."/membershipsystem/images/rewarditems/";

//Define Maximum Item Quantity that can be avail by the player.
//$_CONFIG['item_maxquantity'] = 5
?>
