<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'Voucher API',
    // preloading 'log' component
    'preload' => array('log'),
    // autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.components.*',
    ),
    'modules' => array(
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
    'components' => array(
        'user' => array(
            // enable cookie-based authentication
            'allowAutoLogin' => true,
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
        'urlManager' => array(
            'urlFormat' => 'path',
            'rules' => array(
                'post/<id:\d+>/<title:.*?>' => 'post/view',
                'posts/<tag:.*?>' => 'post/index',
                // REST patterns
                array('api/list', 'pattern' => 'api/<model:\w+>', 'verb' => 'GET'),
                array('api/view', 'pattern' => 'api/<model:\w+>/<id:\d+>', 'verb' => 'GET'),
                array('api/update', 'pattern' => 'api/<model:\w+>/<id:\d+>', 'verb' => 'PUT'),
                array('api/delete', 'pattern' => 'api/<model:\w+>/<id:\d+>', 'verb' => 'DELETE'),
                array('api/create', 'pattern' => 'api/<model:\w+>', 'verb' => 'POST'),
                // Other controllers
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ),
        'db' => array(
            //'connectionString' => 'mysql:host=localhost;dbname=vouchermanagement',
            //Development
            'connectionString' => 'mysql:host=172.16.102.157;dbname=vouchermanagement',
            'emulatePrepare' => true,
            'username' => 'pegsconn',
            'password' => 'pegsconnpass',
            'charset' => 'utf8',
        ),
        'db2' => array(
            //'connectionString' => 'mysql:host=localhost;dbname=npos',
            //Development
            'connectionString' => 'mysql:host=172.16.102.157;dbname=npos',
            'emulatePrepare' => true,
            'username' => 'pegsconn',
            'password' => 'pegsconnpass',
            'charset' => 'utf8',
            'class' => 'CDbConnection',
        ),
        'db3' => array(
            //'connectionString' => 'mysql:host=localhost;dbname=npos',
            //Development
            'connectionString' => 'mysql:host=172.16.102.157;dbname=loyaltydb',
            'emulatePrepare' => true,
            'username' => 'pegsconn',
            'password' => 'pegsconnpass',
            'charset' => 'utf8',
            'class' => 'CDbConnection',
        ),
        'db4' => array(
            //'connectionString' => 'mysql:host=localhost;dbname=npos',
            //Development
            'connectionString' => 'mysql:host=172.16.102.157;dbname=stackermanagement',
            'emulatePrepare' => true,
            'username' => 'pegsconn',
            'password' => 'pegsconnpass',
            'charset' => 'utf8',
            'class' => 'CDbConnection',
        ),
        'db5' => array(
            //'connectionString' => 'mysql:host=localhost;dbname=npos',
            //Development
            'connectionString' => 'mysql:host=172.16.102.157;dbname=spyder',
            'emulatePrepare' => true,
            'username' => 'pegsconn',
            'password' => 'pegsconnpass',
            'charset' => 'utf8',
            'class' => 'CDbConnection',
        ),
        'CURL' => array(
            'class' => 'application.extensions.curl.CurlController',
        //you can setup timeout,http_login,proxy,proxylogin,cookie, and setOPTIONS
        ),
        // uncomment the following to use a MySQL database
        /*
          'db'=>array(
          'connectionString' => 'mysql:host=localhost;dbname=testdrive',
          'emulatePrepare' => true,
          'username' => 'root',
          'password' => '',
          'charset' => 'utf8',
          ),
         */
        'errorHandler' => array(
            // use 'site/error' action to display errors
            'errorAction' => 'Wsvoucher',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
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
    'params' => array(
        // this is used in contact page
        'adminEmail' => 'webmaster@example.com',
        'sessionTimeoutSeconds' => 1440,
        //Token Validator for EGM requests
        'validateTokenURL' => 'http://192.168.30.97/KAPI/index.php/wsGaming/validatetoken',
        'cutofftimestart' => '06:00:00',
        'cutofftimeend' => '05:59:59',
        'sitePrefix' => 'ICSA-',
        'allowedAmount' => 0,
        'dateIntervalEnabled' => 'enabled',
        'dateInterval' => '30 days',
        'itdbaspportemail' => array('gvjagolino@philweb.com.ph'),
        'siteChecking' => 'enabled',
        'terminalChecking' => 'enabled',
        'cardNumberChecking' => 'enabled',
        'expirationChecking' => 'enabled',
        'ticket_increment' => '00',
        'constant_delimiter' => '342637',
        'time_stamp' => '23:59:59', //replace 00:00:00 with H:i:s if current timestamp is needed
    ),
);
