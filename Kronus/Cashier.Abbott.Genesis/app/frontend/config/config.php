<?php

$http = 'http';

if( isset( $_SERVER[ 'HTTPS' ] ) )
{
    if ( $_SERVER[ "HTTPS" ] == "on" ) 
    {
        $http = 'https';
    }
}

return array(
    // DATABASE 
    'db'=>array(
        'connection_string'=>'mysql:host=172.16.102.157;dbname=npos',
        'username'=>'nposconn',
        'password'=>'npos',
    ),
    'db2'=>array(
        'connection_string'=>'mysql:host=172.16.102.157;dbname=stackermanagement',
        'username'=>'pegsconn',
        'password'=>'pegsconnpass',
    ),
    'db3'=>array(
        'connection_string'=>'mysql:host=172.16.102.157;dbname=vouchermanagement',
        'username'=>'pegsconn',
        'password'=>'pegsconnpass',
    ),
    //'domain'=>$http . '://'.$_SERVER["SERVER_NAME"].'/index.php', // stagging
    'domain'=>'http://'.$_SERVER["SERVER_NAME"].'/Kronus_GenesisAbbott/cashier.genesis.abbott/index.php', // localhost
    // LOGS
    'logs'=>array(
        // path for the log file
        'log_path'=>Mirage::app()->getAppPath() . DIRECTORY_SEPARATOR . 'logs',
        // will rotate only in prod mode
        'rotate'=>true,
        // day or filesize
        'rotate_by'=>'day',
        // per day
        'interval'=>1,
        // per MB
//        'filesize'=>1,
    ),
    'shutdown'=>array(
        array('class'=>'WebLogger','method'=>'log','file'=>Mirage::app()->getAppPath().'/extensions/web_logger/WebLogger.php','runnable'=>MIRAGE_DEBUG),
    ),
    // sub modules
    'sub_modules'=>array('cron','monitoring','sapi'),
    'params'=>array(
	'port'=>8888, // port use in screen blocking,
        'pegsstationerrormsg'=>'There is a problem in Terminal Blocker',
        'failed_unlock'=>'Failed to Unlock the Terminal',
        'failed_lock'=>'Failed to Lock the Terminal',


	// heartbeat rate 5 sec
	'heartbeat_rate'=>300000,
        // minimun length of password
        'min_pass_len'=>8,
        // alloweds account type for standalone monitoring
        'standalone_allowed_type'=>array(2,7),
        'allowed_acctype'=>array(4,2,7),
        'ASgroup'=>'elperez@philweb.com.ph', //AS Group Email
	'enable_screenblocking'=>false, //enabling of screen blocking for Netoptio
        'enable_spyder_call'=>true, //enabling of call lock | unlock in Spyder
        
	'BGI_ownerID'=>1,

        //Loyalty 1.6 Staging
//        'card_inquiry'=>'http://192.168.20.8/rewardspoints/apiproxy/cardinquiry.php',
//        'register_account'=>'http://192.168.20.8/rewardspoints/rewardspointsAPI/registration.php',
//        'add_points'=>'http://192.168.20.8/rewardspoints/apiproxy/addpoints.php',
//        'withdraw'=>'http://192.168.20.8/rewardspoints/apiproxy/withdraw.php',
//        'loyalty_portal'=>'http://192.168.20.8:8114/',	
//	  'loyalty_service'=>7,
        
        //for loyalty 2.0 (membership card)
        'card_inquiry'=>'http://192.168.28.55/membership/membership.rewards/API/cardinquiry.php',
        'transfer_points' => 'http://192.168.28.55/membership/membership.rewards/API/transferpoints.php',
        'member_activation' => 'http://192.168.28.55/membership/membership.rewards/memberactivation.php',
        'process_points' => 'http://192.168.28.55/membership/membership.rewards/API/addpoints.php',
        'temp_activation' => 'http://192.168.28.55/membership/membership.rewards/tempmemberactivation.php',
        'register_account' => 'http://192.168.28.55/membership/membership.rewards/registration.php',
        'loyalty_service'=>7,
        
        // RTG
        'deposit_method_id' =>503,
        'withdrawal_method_id'=>502,
        'rtg_cert_dir'=>'/var/www/Kronus/admin.pagcoregames.com/public/views/sys/config/RTGClientCerts/',
        //'logout_page'=>$http . '://'.$_SERVER["SERVER_NAME"].'/index.php?r=logout',
        'logout_page'=>'http://'.$_SERVER["SERVER_NAME"].'/Kronus_GenesisAbbott/cashier.genesis.abbott/index.php?r=logout', // localhost
        'cut_off'=>'06:00:00',
        'terminal_per_page'=>10,
        'service_api'=>array(
            'https://202.44.100.29/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
            'https://202.44.100.28/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
            'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx',
            'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx',
            'https://202.44.100.29/ALPHAKI98TUI5AMINAS2/processor/processorapi/cashier.asmx',
            'https://202.44.100.28/GAMMAOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx',
            'https://cashier-dev.egamescasino-ss.ph',
            //'https://cashier1.megasportcasino.com', //old pt api url
            //'https://entservices.totalegame.net/EntServices.asmx?WSDL', //old mg url
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
        'game_api' => array(
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            '',
            '',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            '',
            '',
            '',
            'https://202.44.103.231/GPRIMESZNVJFROAPSERT/CasinoAPI/Games.asmx',
            'https://202.44.103.231/GPRIMESZNVJFROAPSERT/CasinoAPI/Games.asmx'
        ),
        
        //credentials for revertbrokengames api
        'revertbroken_api'=>array(
            'URI' => 'https://webapi-dev.egamescasino-ss.ph/product/casino/service/backend/casino/egamesqa',
            "REVERT_BROKEN_GAME_MODE" => "cancel",
            "CASINO_NAME" => "egamesqa",
            "PLAYER_MODE" => "real"
        ),
        
        'service_api_caching'=>array(
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            2000=>false
        ),
        
        //Microgaming (MG)
        'microgaming_currency'=>9,
        'mgcapi_user_type'=>0,
        'mgcapi_trans_method'=>'ChangeBalanceEx',
        'mgcapi_username'=>'philweb_capi',
        'mgcapi_password'=>'test1234$',
        'mgcapi_playername'=>'capi',
        'mgcapi_event_id'=>array(
            '10001', //Deposit
            '10002', //Reload
            '10003', //Withdraw
 
         ),
        
        //Voucher management system
        'generate_voucher'=>'http://192.168.28.55/vouchermanagementsystem/VMS/index.php/voucherAPI/generate/',
        'verify_voucher'=>'http://192.168.28.55/vouchermanagementsystem/VMS/index.php/wsvoucher/verify/',
        'use_voucher'=>'http://192.168.28.55/vouchermanagementsystem/VMS/index.php/wsvoucher/use/',
        'cancel_voucher'=>'http://192.168.28.55/vouchermanagementsystem/VMS/index.php/voucherAPI/cancel/',
        'update_voucher'=>'http://192.168.28.55/vouchermanagementsystem/VMS/index.php/voucherAPI/update/',
        'voucher_source'=>3,
        
         //PlayTech (PT)
        'pt_casino_name'=>'egamesqa',
        'pt_secret_key'=>'playtech',
        'pt_cert_dir'=>'/var/www/Kronus_New/admin2_ub.pagcoregames.com/public/views/sys/config/PTClientCerts/',

        //Spyder API
        'SAPI_URI'=>'http://192.168.28.62/sapi/index.php',
        'SAPI_Type'=>1, //always 1
        'Asynchronous_URI'=>'http://'.$_SERVER["SERVER_NAME"].'/Kronus_GenesisAbbott/cashier.genesis.abbott/index.php',
        
        // condition in gross hold monitoring
        'green'=>array(200000,400000),
        'orange'=>array(400000,600000),
        'blue'=>600000,
        'red'=>200000,
        'sys_version'=>'cashier_ub.pagcoregames.com',
        'cashier_version'=>3, //current kronus cashier version
        'temp_code'=>'eGames',
        'is_Genesis'=>1, //checking for Genesis 1-true 0 -false
    ),
);
