<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'PCWS',
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
			'errorAction'=>'pcws',
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
                'deposit' => 'localhost/PCWS/index.php/pcws/deposit',
                'withdraw' => 'localhost/PCWS/index.php/pcws/withdraw',
                'getbalance' => '172.16.102.174/PCWS/index.php/pcws/getbalance',
                'updateterminal' => '172.16.102.174/PCWS/index.php/pcws/deposit',
                'getcomppoints' => '172.16.102.174/PCWS/index.php/pcws/getcomppoints',
                'addcomppoints' => '172.16.102.174/PCWS/index.php/pcws/addcomppoints',
                'rtg_certkey_dir'=>'/var/www/ForceTAPI/protected/config/RTGClientCerts/',
                'abbottcashierapi'=>'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
                'abbottplayerapi'=>'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/player.asmx',
                'prefix' => 'ICSA-',
                'onrequesttimeout' =>  false, //set false for testing
                'maxrequestprocesstime' => '1', //by hour
                'depositmethodid' => 503,
                'withdrawmethodid' => 502,
                'SystemCode'=>array('kadmin' => '4996816', 'kcashier' => '497912152', 'lliter' => '4881052', 'gkapi' => '439941', 'madmin' => '4896816', 'mportal' => '48452098','pcws' => '4761','sapi' => '41941'),
                'unlock'=>'172.16.102.174/PCWS/index.php/pcws/unlock',
                'forcelogout'=>'localhost/PCWS/index.php/pcws/forcelogout',
                'deductcomppoints' => '172.16.102.174/PCWS/index.php/pcws/deductcomppoints',

	),
);
