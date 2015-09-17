

<?php
// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(

	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Bar Tour App Membership Portal API', //Application Programming Interface
        'defaultController'=>'bTAmpapiInvoker/overview',
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
                //db connection for membership
                'db'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=;dbname=membership',
			'emulatePrepare' => true,
			'username' => 'membershipapi',
			'password' => '',
			'charset' => 'utf8',
		),
                //db connection for loyaltydb
                'db2'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=;dbname=loyaltydb',
			'emulatePrepare' => true,
			'username' => 'membershipapi',
			'password' => '',
			'charset' => 'utf8',
		),
                //db connection for membership_temp
                'db3'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=;dbname=membership_temp',
			'emulatePrepare' => true,
			'username' => 'membershipapi',
			'password' => '',
			'charset' => 'utf8',
		),
                //db connection for rewardsdb
                'db4'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=;dbname=rewardsdb',
			'emulatePrepare' => true,
			'username' => 'membershipapi',
			'password' => '',
			'charset' => 'utf8',
		),
                'db5'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=;dbname=accountsdb',
			'emulatePrepare' => true,
			'username' => 'accountsapi',
			'password' => '',
			'charset' => 'utf8',
                ),


                'CURL' =>array(
                        'class' => 'application.extensions.curl.CurlController',
                     //you can setup timeout,http_login,proxy,proxylogin,cookie, and setOPTIONS
                 ),
                 'errorHandler'=>array(
                        'errorAction'=>'bTAmpapiInvoker/error',
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
            'SMSURI' => 'http://sms.egamescasino.ph/send',
            'app_id' => 'EGAMES',
            'DenominationPrefix'=>'PhP ',
            'allowedAmount'=>0,
            'urlBTAMPAPI'=>'http://<BTAMPAPI DIRECTORY HERE>/index.php/BTAmpapi/',
            'urlMPAPI'=>'http://<MPAPI URL HERE>/index.php/MPapi/'
        ),




);

