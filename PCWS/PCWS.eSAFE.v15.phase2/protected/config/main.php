<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'PCWS.eSAFE.v15',
        'defaultController'=>'pcwsInvoker/overview',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
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
            // uncomment the following to show log messages on web pages
            /*
		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),
		// uncomment the following to use a MySQL database
		*/
		'db'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=172.16.102.157;dbname=npos',
			'emulatePrepare' => true,
			'username' => 'pegsconn',
			'password' => 'pegsconnpass',
			'charset' => 'utf8',
		),
            
                'db2'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=172.16.102.157;dbname=membership',
			'emulatePrepare' => true,
			'username' => 'pegsconn',
			'password' => 'pegsconnpass',
			'charset' => 'utf8',
		),
                
                'db3'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=172.16.102.157;dbname=loyaltydb',
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
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
                                                                                        'class'=>'CFileLogRouteModified',
                                                                                        'levels'=>'error, warning',
                                                                                ),
                                                                                array(
                                                                                        'class'=>'CFileRequestLogRoute',
                                                                                        'levels'=>'request, response',
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
		// this is used in contact page
                'adminEmail'=>'webmaster@example.com',
                'deposit' => 'https://pcws.esafe.v15.dev.local/index.php/pcws/deposit',
                'withdraw' => 'https://pcws.esafe.v15.dev.local/index.php/pcws/withdraw',
                'getbalance' => 'https://pcws.esafe.v15.dev.local/index.php/pcws/getbalance',
                'updateterminal' => 'https://pcws.esafe.v15.dev.local/index.php/pcws/deposit',
                'getcomppoints' => 'https://pcws.esafe.v15.dev.local/index.php/pcws/getcomppoints',
                'addcomppoints' => 'https://pcws.esafe.v15.dev.local/index.php/pcws/addcomppoints',
                'rtg_certkey_dir'=>'/var/www/PCWS.eSAFE.v15/protected/config/RTGClientCerts/',
                'cashierapi'=>array(
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
                            'https://125.5.1.20/ABBOTTRUVMANSYWPLMXI/processor/ProcessorAPI/Cashier.asmx', //RTG V15
                ),
                'playerapi'=>array(
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
                            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/player.asmx',
                            'https://125.5.1.20/ABBOTTRUVMANSYWPLMXI/CasinoAPI/player.asmx', //RTG V15
                ),
                'prefix' => 'ICSA-',
                'onrequesttimeout' =>  false, //set false for testing
                'maxrequestprocesstime' => '5', //by hour
                'maxPinAttempts' => 3,
                'depositmethodid' => 503,
                'withdrawmethodid' => 502,
                'SystemCode'=>array('kadmin' => '4996816', 'kcashier' => '497912152', 'lliter' => '4881052', 'gkapi' => '439941', 'madmin' => '4896816', 'mportal' => '48452098','pcws' => '4761','genspy' => '4356145'),
                'unlock'=>'https://pcws.esafe.v15.dev.local/index.php/pcws/unlock',
                'forcelogout'=>'https://pcws.esafe.v15.dev.local/index.php/pcws/forcelogout',
                'deductcomppoints' => 'https://pcws.esafe.v15.dev.local/index.php/pcws/deductcomppoints',
                'createsession'=>'https://pcws.esafe.v15.dev.local/index.php/pcws/createsession',
                'changepin'=>'https://pcws.esafe.v15.dev.local/index.php/pcws/changepin',
                'gettermsandcondition'=>'https://pcws.esafe.v15.dev.local/index.php/pcws/gettermsandcondition',
                
                //RTG Naboo
                'UBCasinoServiceID' =>20, //19 - Abbott v1, 20 - Abbott v2
                'SkinNamePlatinum' => 'P-Esafe (Modern)',
                'SkinNameNonPlatinum' => 'Abbott2',
                'Isloyaltypoints' => 1,
                'isAllowedWhitelisting' => false,
    ),
);
