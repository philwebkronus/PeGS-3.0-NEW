<?php
// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Stacker API', //Application Programming Interface
        'defaultController'=>'wsStackerApiInvoker/overview',
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
            //db connection for stackermanagement
		'db'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=172.16.102.157;dbname=stackermanagement',
			'emulatePrepare' => true,
			'username' => 'pegsconn',
			'password' => 'pegsconnpass',
			'charset' => 'utf8',
		),
            //db connection for npos
                'db2'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=172.16.102.157;dbname=npos',
			'emulatePrepare' => true,
			'username' => 'pegsconn',
			'password' => 'pegsconnpass',
			'charset' => 'utf8',
		),
            //db connection for loayltydb
                'db3'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=172.16.102.157;dbname=loyaltydb',
			'emulatePrepare' => true,
			'username' => 'pegsconn',
			'password' => 'pegsconnpass',
			'charset' => 'utf8',
		),
            //db connection for vouchermanagement
                'db4'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=172.16.102.157;dbname=vouchermanagement',
			'emulatePrepare' => true,
			'username' => 'pegsconn',
			'password' => 'pegsconnpass',
			'charset' => 'utf8',
		),
            //db connection for membershipsystem
                'db5'=>array(
                            'class'=>'CDbConnection',
                            'connectionString' => 'mysql:host=172.16.102.157;dbname=membership',
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
                    'errorAction'=>'wsStackerApi',
                 ),
                 'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					//'class'=>'CFileLogRoute',
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
        // using Yii::app()->params['paramName']
	'params'=>array(
            'SitePrefix'=>'',
            //for EGM
            'SitePrefix2'=>'ICSA-',
            'DenominationPrefix'=>'PhP ',
            'allowedAmount'=>0,
        //Voucher management system API
            'voucher_source'=>1,
            'verify_ticket'=>'http://vapi.dev.local/index.php/Wsvoucher/verifyTicket',
            'use_voucher_new'=>'http://vapi.dev.local/index.php/Wsvoucher/useTicket',
            'add_ticket'=>'http://vapi.dev.local/index.php/Wsvoucher/addTicket',
            ),
);