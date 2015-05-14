<?php
// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Membership Portal API', //Application Programming Interface
        'defaultController'=>'mPapiInvoker/overview',
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
                //db connection for npos
                'db5'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=;dbname=npos',
			'emulatePrepare' => true,
			'username' => 'membershipapi',
			'password' => '',
			'charset' => 'utf8',
		),
                //db connection for vouchermanagement
                'db6'=>array(
                        'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=;dbname=vouchermanagement',
			'emulatePrepare' => true,
			'username' => 'membershipapi',
			'password' => '',
			'charset' => 'utf8',
		),
                'CURL' =>array(
                        'class' => 'application.extensions.curl.CurlController',
                     //you can setup timeout,http_login,proxy,proxylogin,cookie, and setOPTIONS
                 ),
                 'session' =>array(
                        'class' => 'system.web.CDbHttpSession',
                     //you can setup timeout,http_login,proxy,proxylogin,cookie, and setOPTIONS
                 ),
                 'errorHandler'=>array(
                                // use 'site/error' action to display errors
                    'errorAction'=>'MPapi',
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
            'SMSURI' => 'http://rtmessagegw.egamescasino.ph/send',
            'app_id' => 'EGAMES',
            'DenominationPrefix'=>'PhP ',
            'allowedAmount'=>0,
            //'extra_imagepath' => "172.16.102.174/membership.rewards/images/rewarditems/",
            //'extra_imagepath' => "http://www.egamescasino.ph/wp-content/uploads/2013/05/",
            'extra_imagepath' => "http://membership.egamescasino.ph/images/rewarditems/",
            'rewarditem_imagepath' => "172.16.102.174/rewards.management/images/rewarditems/",
            'MarketingEmail' => 'fdlsison@philweb.ph',
            //'url' => 'http://localhost/MPAPI/index.php/MPapi/', //local url
            'urlMPAPI' => 'http://172.16.102.174/mpapi.dev.local/index.php/MPapi/', //staging
            'SessionTimeOut' => 45.00,
            'urlVerify' => 'http://membershipsb.genesis.local/verify.php',
            'getcomppoints' => 'http://172.16.102.174/PCWS/index.php/pcws/getcomppoints',
            'getbalance' => 'http://172.16.102.174/PCWS/index.php/pcws/getbalance',
            'deductcomppoints' => 'http://172.16.102.174/PCWS/index.php/pcws/deductcomppoints',
            'SystemCode'=>array('madmin' => '4896816', 'mportal' => '48452098','pcws' => '4761'),
            'SysUsernameMA' => 'madmin',
            'SysUsernameMP' => 'mportal'
            ),
);