<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'KAPI', //Kronus Application Programming Interface
        'defaultController'=>'wsKapiInvoker/overview',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
                'application.controllers.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'Enter Your Password Here',
		 	// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
		*/
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format
		/*
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		*/
                'urlManager'=>array(
                    'urlFormat'=>'path',
                    'rules'=>array(
                        'post/<id:\d+>/<title:.*?>'=>'post/view',
                        'posts/<tag:.*?>'=>'post/index',
                        // REST patterns
                        array('api/list', 'pattern'=>'api/<model:\w+>', 'verb'=>'GET'),
                        array('api/view', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'GET'),
                        array('api/update', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'PUT'),
                        array('api/delete', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'DELETE'),
                        array('api/create', 'pattern'=>'api/<model:\w+>', 'verb'=>'POST'),
                        // Other controllers
                        '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
                    ),
                ),
            
                //put kronus database conn string here
		'db'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=172.16.102.157;dbname=npos',
			'emulatePrepare' => true,
			'username' => 'pegsconn',
			'password' => 'pegsconnpass',
			'charset' => 'utf8',
		),
            
                //put loyalty database conn string here
                'db2'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=172.16.102.157;dbname=loyaltydb',
			'emulatePrepare' => true,
			'username' => 'pegsconn',
			'password' => 'pegsconnpass',
			'charset' => 'utf8',
		),
            
                //put membership database conn string here
		'db3'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=172.16.102.157;dbname=membership',
			'emulatePrepare' => true,
			'username' => 'pegsconn',
			'password' => 'pegsconnpass',
			'charset' => 'utf8',
		),
                'db4'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=172.16.102.157;dbname=stackermanagement',
			'emulatePrepare' => true,
			'username' => 'pegsconn',
			'password' => 'pegsconnpass',
			'charset' => 'utf8',
		),
                'db5'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=172.16.102.157;dbname=vouchermanagement',
			'emulatePrepare' => true,
			'username' => 'pegsconn',
			'password' => 'pegsconnpass',
			'charset' => 'utf8',
		),
                'CURL' =>array(
                        'class' => 'application.extensions.curl.CurlController',
                     //you can setup timeout,http_login,proxy,proxylogin,cookie, and setOPTIONS
                 ),
		
		'errorHandler'=>array(
			// use 'site/error' action to display errors
                'errorAction'=>'wsGaming/error',
        ),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRouteModified',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
                'trans_details_tag'=>'EGM',
                'deposit_method_id' =>503,
                'withdrawal_method_id'=>502,
                'rtg_cert_dir'=>'C://Apache2.2/htdocs/kronus-egm/kapi-v15/protected/config/RTGClientCerts/',
        
                'BGI_ownerID'=>1,
                'allowminimumamount'=>true,
            
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
            
                'pt_casino_name'=>'egamesqa',
                'pt_secret_key'=>'playtech',
                'pt_cert_dir'=>'/var/www/Kronus_UB/admin2_ub.pagcoregames.com/public/views/sys/config/PTClientCerts/',

                 //Casino URL
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
                    'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx'
                ),
        
                //Casino Game URL
                'game_api' => array(
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
                    '',
                ),
        
                //credentials for revertbrokengames api
                'revertbroken_api'=>array(
                    'URI' => 'https://webapi-dev.egamescasino-ss.ph/product/casino/service/backend/casino/egamesqa',
                    "REVERT_BROKEN_GAME_MODE" => "cancel",
                    "CASINO_NAME" => "egamesqa",
                    "PLAYER_MODE" => "real"
                ),        
        
                //Membership API
                'mem_card_inquiry'=>'http://172.16.102.174/membership.rewards/API/cardinquiry.php',
                'mem_transfer_points' => 'http://172.16.102.174/membership.rewards/API/transferpoints.php',
                'member_activation' => 'http://172.16.102.174/membership.rewards/memberactivation.php',
                'mem_process_points' => 'http://172.16.102.174/membership.rewards/API/addpoints.php',
                'mem_temp_activation' => 'http://172.16.102.174/membership.rewards/tempmemberactivation.php',
                'mem_register_account' => 'http://172.16.102.174/membership.rewards/registration.php',
                'mem_loyalty_service'=>7,        
            
                //Voucher management system API
                'voucher_source'=>1,
                'verify_ticket'=>'http://192.168.28.218/vouchermanagementsystem/VMS/index.php/Wsvoucher/verifyTicket',
                'use_voucher_new'=>'http://192.168.28.218/vouchermanagementsystem/VMS/index.php/Wsvoucher/useTicket',
                'get_voucher'=>'http://localhost/voucher-api/index.php/Wsvoucher/addTicket',
            
//                'authenticate_client'=>'http://192.168.28.108/kapi.dev.local/index.php/wsGaming/authenticateclient',
//                'check_active_session'=>'http://192.168.28.108/kapi.dev.local/index.php/wsGaming/checkactivesession',
//                'do_transaction'=>'http://192.168.28.108/kapi.dev.local/index.php/wsGaming/dotransaction',
//                'minmaxinfo_kapi'=>'http://192.168.28.108/kapi.dev.local/index.php/wsGaming/minmaxinfo',
//                'validate_token'=>'http://192.168.28.108/kapi.dev.local/index.php/wsGaming/validatetoken',
//                'deposit'=>'http://192.168.28.108/kapi.dev.local/index.php/wsGaming/deposit',                  
//                'reload'=>'http://192.168.28.108/kapi.dev.local/index.php/wsGaming/reload',
//                'withdraw'=>'http://192.168.28.108/kapi.dev.local/index.php/wsGaming/withdraw',
//                'check_transaction'=>'http://192.168.28.108/kapi.dev.local/index.php/wsGaming/checktransaction',
            
                //KAPI (Invoker) Localhost
//                'authenticate_client'=>'http://localhost/egmwebservice/index.php/wsGaming/authenticateclient',
//                'check_active_session'=>'http://localhost/egmwebservice/index.php/wsGaming/checkactivesession',
//                'do_transaction'=>'http://localhost/egmwebservice/index.php/wsGaming/dotransaction',
//                'minmaxinfo_kapi'=>'http://localhost/egmwebservice/index.php/wsGaming/minmaxinfo',
//                'validate_token'=>'http://localhost/egmwebservice/index.php/wsGaming/validatetoken',
//                'deposit'=>'http://localhost/egmwebservice/index.php/wsGaming/deposit',
//                'reload'=>'http://localhost/egmwebservice/index.php/wsGaming/reload',
//                'withdraw'=>'http://localhost/egmwebservice/index.php/wsGaming/withdraw',
//                'check_transaction'=>'http://localhost/egmwebservice/index.php/wsGaming/checktransaction',
//            
                 //KAPI (Invoker) Staging
//                'authenticate_client'=>'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsGaming/authenticateclient',
//                'check_active_session'=>'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsGaming/checkactivesession',
//                'do_transaction'=>'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsGaming/dotransaction',
//                'minmaxinfo_kapi'=>'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsGaming/minmaxinfo',
//                'validate_token'=>'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsGaming/validatetoken',
//                'deposit'=>'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsGaming/deposit',                  
//                'reload'=>'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsGaming/reload',
//                'withdraw'=>'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsGaming/withdraw',
//                'check_transaction'=>'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsGaming/checktransaction',
                //Localhost
                'get_terminal_info' => 'http://localhost/kronus-egm/kapi-v15/index.php/wsKapi/getterminalinfo', 
                'get_playing_balance' => 'http://localhost/kronus-egm/kapi-v15/index.php/wsKapi/getplayingbalance', 
                'get_membership_info' => 'http://localhost/kronus-egm/kapi-v15/index.php/wsKapi/getmembershipinfo', 
                'check_transaction' => 'http://localhost/kronus-egm/kapi-v15/index.php/wsKapi/checktransaction', 
                'get_login_info' => 'http://localhost/kronus-egm/kapi-v15/index.php/wsKapi/getlogininfo', 
                'start_session' => 'http://localhost/kronus-egm/kapi-v15/index.php/wsKapi/startsession', 
                'reload_session' => 'http://localhost/kronus-egm/kapi-v15/index.php/wsKapi/reloadsession', 
                'redeem_session' => 'http://localhost/kronus-egm/kapi-v15/index.php/wsKapi/redeemsession', 
                'create_egm_session' => 'http://localhost/kronus-egm/kapi-v15/index.php/wsKapi/createegmsession',
                'remove_egm_session' => 'http://localhost/kronus-egm/kapi-v15/index.php/wsKapi/removeegmsession', 
                'get_site_balance' => 'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsKapi/getsitebalance', 
                //Staging
//                  'get_terminal_info' => 'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsKapi/getterminalinfo', 
//                  'get_playing_balance' => 'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsKapi/getplayingbalance', 
//                  'get_membership_info' => 'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsKapi/getmembershipinfo', 
//                  'check_transaction' => 'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsKapi/checktransaction', 
//                  'get_login_info' => 'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsKapi/getlogininfo', 
//                  'start_session' => 'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsKapi/startsession', 
//                  'reload_session' => 'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsKapi/reloadsession', 
//                  'redeem_session' => 'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsKapi/redeemsession', 
//                  'create_egm_session' => 'http://172.16.102.174/kapi.abbott.dev.local/index.php/wsKapi/createegmsession',
                
                //VMS Source Request
                'vms_source'=>1,
                'SitePrefix'=>'ICSA-',
                'MaxRedeemableAmount' => 10000,
                'MinTicketToPrintAmount' => 0.01,
                'ParamDB' => 2 //1 - ON, 2 - OFF
	),
    
);
