<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Reward Items Management',
        'defaultController'=>'login/login',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'password',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
		
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
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		
//		'db'=>array(
//			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
//		),
		// uncomment the following to use a MySQL database
		'db'=>array(
            //Development
            'connectionString' => 'mysql:host=<hostname>;dbname=<database name>',
			'emulatePrepare' => true,
			'username' => '<database username>',
			'password' => '<database password>',
			'charset' => 'utf8',
		),
		
        'db2'=>array(
            //Development
            'connectionString' => 'mysql:host=<hostname>;dbname=<database name>',
			'emulatePrepare' => true,
			'username' => '<database username>',
			'password' => '<database password>',
			'charset' => 'utf8',
            'class' => 'CDbConnection',
		),
       
        
         'widgetFactory'=>array(
                        'widgets'=>array(
                            'CJuiDialog' => array(
                                'cssFile'=>'jquery-ui-1.9.2.custom.css',
                                'theme'=>'redmond',
                                'themeUrl'=>'/rewards.management/css', //theme path
                            ),
                            'CJuiDatePicker' => array(
                                'cssFile'=>'jquery-ui-1.9.2.custom.css',
                                'theme'=>'redmond',
                                'themeUrl'=>'/rewards.management/css',  //theme path
                            ),
                            'CJuiDateTimePicker' => array(
                                'cssFile'=>'jquery-ui-1.9.2.custom.css',
                                'theme'=>'redmond',
                                'themeUrl'=>'/rewards.management/css',  //theme path
                            ),
                        ),
                 ),
        
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'login/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
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
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
        //'idletimelogout'=>'600000', //10 minutes (1000 milliseconds = 1 second)
        'idletimelogout'=>'600000',
        'autologouturl'=>'http://localhost/rewards.management/index.php?r=login/logout',
        'marketingemail'=>array('<marketin email address>'),
        'pageGridLimit'=>array(10,20,30), // drop down page limit in jqgrid
        'initialLimit'=> 10, // initial page limit in jqgrid
	),
);
