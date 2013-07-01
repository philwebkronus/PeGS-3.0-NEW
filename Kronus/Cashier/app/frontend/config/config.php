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
        'connection_string'=>'mysql:host=<host>;dbname=<dbname>',
        'username'=>'<username>',
        'password'=>'<password>',
    ),

    'domain'=> <cashier domain>, // localhost

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
        
        //for loyalty 2.0 (membership card)
        'card_inquiry'=>'<GetCardInfo API URL>',
        'transfer_points' => '<TransferPoints API URL>',
        'member_activation' => '<OldToMembership Form URL>',
        'process_points' => '<ProcessPoint API URL>',
        'temp_activation' => '<TempToMembership Form URL>',
        'register_account' => '<Registration Form URL>',
        'loyalty_service'=>7,
        
        // RTG
        'deposit_method_id' =>503,
        'withdrawal_method_id'=>502,
        'rtg_cert_dir'=>'<RTG Certificate Directory>',

        'logout_page'=>'http://'.$_SERVER["SERVER_NAME"].'/kronus_ub/cashier2.pagcoregames.com/index.php?r=logout', // localhost
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
            'https://extdev-devhead-cashier.extdev.eu',
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
            'https://202.44.103.231/GPRIMESZNVJFROAPSERT/CasinoAPI/Games.asmx'),
        
        //credentials for revertbrokengames api
        'revertbroken_api'=>array(
            'URI' => 'https://devhead-webapi.extdev.eu/product/casino/service/backend/casino/playtech800041',
            "REVERT_BROKEN_GAME_MODE" => "cancel",
            "CASINO_NAME" => "playtech800041",
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
        'generate_voucher'=>'<GenerateVoucher API URL>',
        'verify_voucher'=>'<VerifyVoucher API URL>',
        'use_voucher'=>'<UseVoucher API URL>',
        'cancel_voucher'=>'<CancelVoucher API URL>',
        'update_voucher'=>'<UpdateVoucher API URL>',
        'voucher_source'=>3,
        
         //PlayTech (PT)
        'pt_casino_name'=>'playtech800041',
        'pt_secret_key'=>'PhilWeb123',
        'pt_cert_dir'=>'<PT Certificate Directory>',

        //Spyder API
        'SAPI_URI'=>'<Spyder API URL>',
        'SAPI_Type'=>1, //always 1
        'Asynchronous_URI'=>'http://'.$_SERVER["SERVER_NAME"].'/kronus_ub/cashier2.pagcoregames.com/index.php',
        
        // condition in gross hold monitoring
        'green'=>array(200000,400000),
        'orange'=>array(400000,600000),
        'blue'=>600000,
        'red'=>200000,
        'sys_version'=>'cashier_ub.pagcoregames.com',
		'cashier_version'=>3 //current kronus cashier version
    ),
);

