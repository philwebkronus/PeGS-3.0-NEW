<?php
// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Cron', 
        //'defaultController'=>'mPapiInvoker/overview',
	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
                //'application.controllers.*',
	),

//	'modules'=>array(
//		// uncomment the following to enable the Gii tool
//		/*
//		'gii'=>array(
//			'class'=>'system.gii.GiiModule',
//			'password'=>'Enter Your Password Here',
//		 	// If removed, Gii defaults to localhost only. Edit carefully to taste.
//			'ipFilters'=>array('127.0.0.1','::1'),
//		),
//		*/
//	),

	// application components
	'components'=>array(

                 'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					//'class'=>'CFileLogRoute',
                                        'class'=>'CFileLogRouteModified',
                                        'logfile'=>'cron.log',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				
				array(
					'class'=>'CFileLogRouteModified',
                                        'logfile'=>'cron_trace.log',
                                        'levels'=>'trace',
				),
				
			),
		),
                //db connection
                'db'=>array(
                    'class'=>'CDbConnection',
                    'connectionString' => 'mysql:host=172.16.102.157;dbname=membership',
                    'emulatePrepare' => true,
                    'username' => 'membershipapi',
                    'password' => 'YTgERUImQoddm9iDd0Es',
                    'charset' => 'utf8',
                )
	),
        // using Yii::app()->params['paramName']
	'params'=>array(
            'SitePrefix'=>'',
            'SMSURI' => 'http://rtmessagegw.egamescasino.ph/send',
            'app_id' => 'EGAMES',
            'DenominationPrefix'=>'PhP ',
            'allowedAmount'=>0,
        
            ),
);