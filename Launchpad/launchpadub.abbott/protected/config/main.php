<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Web Application',
	'defaultController'=>'launchpad',
	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		'launchpad',
                'managerss',
		// uncomment the following to enable the Gii tool
		
//		'gii'=>array(
//			'class'=>'system.gii.GiiModule',
//			'password'=>'bryan',
//		 	// If removed, Gii defaults to localhost only. Edit carefully to taste.
//			'ipFilters'=>array('127.0.0.1','::1'),
//		),
		
	),

	// application components
	'components'=>array(
                'session'=>array(
                    'sessionName'=>'LPsession'
                ),
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
		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),
		// uncomment the following to use a MySQL database
		
//		'db'=>array(
//			'connectionString' => 'mysql:host=172.16.102.35;dbname=npos',
//			'emulatePrepare' => true,
//			'username' => 'nposconn',
//			'password' => 'npos',
//			'charset' => 'utf8',
//                        'enableParamLogging'=>true,
//                        'enableProfiling'=>true,
//		),
		
		'errorHandler'=>array(
			// use 'site/error' action to display errors
                    'errorAction'=>'site/error',
                ),
            
                'enableParamLogging'=>true,
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
                            array(
                                    'class'=>'CFileLogRouteModified',
                                    'levels'=>'error, warning',
                            ),
                            array(
                                    'class'=>'CFileLogRouteCasino',
                                    'levels'=>'RESPONSE, REQUEST',
//                                    'category'=>'launchpad.components.casinoapi.*',
                            ),
				// uncomment the following to show log messages on web pages
				
//				array(
//					'class'=>'CWebLogRoute',
////                                        'showInFireBug'=>true,
//				),
				
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
	),
);