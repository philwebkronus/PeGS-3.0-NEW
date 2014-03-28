<?php
return array(
    // table: ref_services, field: ServiceName
    // check if service name has this type
    'casino_type'=>array(
        'rtg','mg','pt'
    ),
    'progressive_jackpot'=>'http://pj.pagcoregames.com/external/getdata.php?serverid=',
    
    // for checking
    'vip'=>'VIP',
    
    // location of casino in display from left to right
    'casino_position'=>array(
        'check_by'=>'Code',// Code(MM,SW) or type(rtg,mg,pt) base on casino_type configuration
//      'check_by'=>'type',
        'position'=>array(
            'MM','VV','SS',
//            'SW','VV','MM',
//            'rtg','mg','pt',
        )
    ),
    
    'db'=>array(
        'connectionString' => 'mysql:host=172.16.102.157;dbname=npos',
        'emulatePrepare' => true,
        'username' => 'nposconn',
        'password' => 'npos',
        'charset' => 'utf8',
    ),
    
    //Connection for membership
    'db2'=>array(
        'connectionString' => 'mysql:host=172.16.102.157;dbname=membership',
        'emulatePrepare' => true,
        'username' => 'pegsconn',
        'password' => 'pegsconnpass',
        'charset' => 'utf8',
    ),
       
    'registry_path'=>array(
        'terminalCode'=>'HKEY_LOCAL_MACHINE\\\SOFTWARE\\\Launchpad\\\terminal\\\terminalCode',
    ),
    
    'registry_path2'=>array(
        'terminalCode'=>'HKEY_CURRENT_USER\\\SOFTWARE\\\PhilWeb Corporation\\\PEGS Launchpad\\\Terminal\\\TerminalCode',
    ),
    
    'interval_is_login'=>5000,
    
    'enable_blocker'=>true,
    
    'heart_beat'=>10000,
    
    'bot_path'=>'file:///C:/Program%20Files/Unlocker.exe',
    
    'rtg_config'=>array(
        'RTGClientCertsPath'=>__DIR__.'/RTGClientCerts/',
        'RTGClientKeyPath'=>__DIR__.'/RTGClientCerts/',
        'deposit_method_id'=>503,
        'withdraw_method_id'=>502,
    ),
    'mg_config'=>array(
        'currency'=>23, //PHP 
        'mgcapi_user_type'=>1,
        'mgcapi_username'=>'philweb_capi',
        'mgcapi_password'=>'test1234$',
        'mgcapi_playername'=>'capi',
        'mgcapi_trans_method'=>'ChangeBalanceEx',
        'mgcapi_event_id'=>array(
            '10001', //Deposit
            '10003', //Withdraw
        )
    ),
    'pt_config'=>array(
        'pt_casino_name'=>'egamesqa',
        'pt_secret_key'=>'playtech',
        'PTClientCertsPath'=>__DIR__.'/PTClientCerts/',
        'PTClientKeyPath'=>__DIR__.'/PTClientCerts/',
    ),

    'revertbroken_api'=>array(
        'URI' => 'https://webapi-dev.egamescasino-ss.ph/product/casino/service/backend/casino/egamesqa',
        "REVERT_BROKEN_GAME_MODE" => "cancel",
        "CASINO_NAME" => "egamesqa",
        "PLAYER_MODE" => "real"
    ),
    
    'service_api'=>array(
        'https://202.44.100.29/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
        'https://202.44.100.28/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
        'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx',
        'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx',
        'https://202.44.100.29/ALPHAKI98TUI5AMINAS2/processor/processorapi/cashier.asmx',
        'https://202.44.100.28/GAMMAOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
        'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx',
        'https://cashier-dev.egamescasino-ss.ph',
//        'https://entservices.totalegame.net/EntServices.asmx?WSDL',
        array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238), //test environment
        'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
        'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
        'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
        'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',    
        'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx',
        array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238), //test environment
        array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238), // MG Server 1 (NCR)
        array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238), // MG Server 2 (Provincial)
        'https://202.44.103.231/GPRIMESZNVJFROAPSERT/processor/ProcessorAPI/Cashier.asmx',
        'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
    ),
    'game_client'=>array(
        'file:///C:/Program%20Files/ECF%20Test/casino.exe',
        'file:///C:/Program%20Files/ECF%20Test/casino.exe',
        'file:///C:/Program%20Files/ECF%20Test/casino.exe',
        'file:///C:/Program%20Files/ECF%20Test/casino.exe',
        'file:///C:/Program%20Files/ECF%20Test/casino.exe',
        'file:///C:/Program%20Files/ECF%20Test/casino.exe',
        'file:///C:/Program%20Files/ECF%20Test/casino.exe',
//        'file:///C:/iGaming/Casino/vibrantvegas/casinogame.exe',
//        'file:///C:/iGaming/Casino/vibrantvegas/casinogame.exe',
        'file:///C:/Microgaming/Casino/Vibrant%20Vegas/casinogame.exe',
        'file:///C:/Microgaming/Casino/Vibrant%20Vegas/casinogame.exe',
        'file:///C:/Program%20Files/ECF%20Demo/casino.exe',
        'file:///C:/Program%20Files/ECF%20Demo/casino.exe',
        'file:///C:/Program%20Files/ECF%20Demo/casino.exe',
        'file:///C:/Program%20Files/ECF%20Demo/casino.exe',
        'file:///C:/Program%20Files/ECF%20Test/casino.exe',
        'file:///C:/Microgaming/Casino/Vibrant%20Vegas/casinogame.exe',
        'file:///C:/Microgaming/Casino/Vibrant%20Vegas/casinogame.exe',
        'file:///C:/Microgaming/Casino/Vibrant%20Vegas/casinogame.exe',
        'file:///C:/Program%20Files/ECF%20Test/casino.exe',
        'file:///C:/Program%20Files/ECF%20Demo/casino.exe',
    ),
    'game_api' => array(
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
    ),
    
    'screen_saver_interval'=>3000,
    'rssFeedUrl'=>'http://'.$_SERVER["SERVER_NAME"].'/index.php?r=managerss/rss/feed',
    'cabFile'=>'http://'.$_SERVER["SERVER_NAME"].'/controls/PEGS.Terminal.ActiveX.cab',
);
