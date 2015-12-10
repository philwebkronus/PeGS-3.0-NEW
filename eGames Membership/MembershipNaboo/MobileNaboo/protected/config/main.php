<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'Membership Portal API', //Application Programming Interface
    'defaultController' => 'interface/register',
    // preloading 'log' component
    'preload' => array('log'),
    // autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.controllers.*',
    ),
    'modules' => array(
    // uncomment the following to enable the Gii tool
    /*
      'gii' => array(
      'class' => 'system.gii.GiiModule',
      'password' => 'password',
      // If removed, Gii defaults to localhost only. Edit carefully to taste.
      'ipFilters' => array('127.0.0.1', '::1'),
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
        //db connection for membership
        'db' => array(
            'class' => 'CDbConnection',
            'connectionString' => 'mysql:host=172.16.102.157;dbname=membership',
            'emulatePrepare' => true,
            'username' => 'pegsconn',
            'password' => 'pegsconnpass',
            'charset' => 'utf8',
        ),
        //db connection for npos
        'db2' => array(
            'class' => 'CDbConnection',
            'connectionString' => 'mysql:host=172.16.102.157;dbname=npos',
            'emulatePrepare' => true,
            'username' => 'pegsconn',
            'password' => 'pegsconnpass',
            'charset' => 'utf8',
        ),
        'CURL' => array(
            'class' => 'application.extensions.curl.CurlController',
        //you can setup timeout,http_login,proxy,proxylogin,cookie, and setOPTIONS
        ),
        'session' => array(
            'class' => 'system.web.CDbHttpSession',
        //you can setup timeout,http_login,proxy,proxylogin,cookie, and setOPTIONS
        ),
        'errorHandler' => array(
            // use 'site/error' action to display errors
            'errorAction' => 'MPapi',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    //'class'=>'CFileLogRoute',
                    'class' => 'CFileLogRouteModified',
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
    // using Yii::app()->params['paramName']
    'params' => array(
        'SitePrefix' => '',
        'SMSURI' => 'https://rtmessagegw.egamescasino.ph/send',
        'app_id' => 'EGAMES',
        'DenominationPrefix' => 'PhP ',
        'deposit_method_id' => 503,
        'withdrawal_method_id' => 502,
        //RTG VIP Level
        'rtgreg' => '0',
        'rtgvip' => '1',
        'rtg_cert_dir' => '/var/www/membership.eSAFE.v15.phase2/rsframework/include/RTGClientCerts/',
        'urlMPAPI' => 'http://172.16.102.174/membership.mobile.naboo/MPAPI_naboo/index.php/MPapi/',
        'urlCashierNaboo' => 'https://202.44.103.231/GPRIMESZNVJFROAPSERT/processor/ProcessorAPI/Cashier.asmx',
        'urlPlayerNaboo' => 'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/CasinoAPI/player.asmx',
        'SessionTimeOut' => 45.00,
        //Player API URL's
        'player_api' => array(
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
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/RTG.Services/Player.svc?wsdl',
            'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/RTG.Services/Player.svc?wsdl', //Naboo
        ),
        //Game API URL's
        "game_api" => array(
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            'https://202.44.103.231/GPRIMESZNVJFROAPSERT/CasinoAPI/Games.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/Games.asmx',
            //'https://125.5.1.20/ABBOTTRUVMANSYWPLMXI/CasinoAPI/Games.asmx' //RTG2
            'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/CasinoAPI/Games.asmx', //RTG Naboo
        ),
        //Player API URL's
        "lobby_api" => array(
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/CasinoAPI/lobby.asmx',
            '',
            '',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/CasinoAPI/lobby.asmx',
            'https://125.5.1.20/ABBOTTRUVMANSYWPLMXI/CasinoAPI/lobby.asmx' //RTG2
        ),
        "service_api" => array(
            'https://202.44.100.29/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
            'https://202.44.100.28/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
            'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx',
            'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx',
            'https://202.44.100.29/ALPHAKI98TUI5AMINAS2/processor/processorapi/cashier.asmx',
            'https://202.44.100.28/GAMMAOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx',
            'https://cashier-dev.egamescasino-ss.ph',
            //$_ServiceAPI[8] = 'https://entservices.totalegame.net/EntServices.asmx?WSDL';
            array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238),
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx',
            array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238),
            array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238),
            array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238),
            'https://202.44.103.231/GPRIMESZNVJFROAPSERT/processor/ProcessorAPI/Cashier.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
            //'https://125.5.1.20/ABBOTTRUVMANSYWPLMXI/processor/ProcessorAPI/Cashier.asmx' //RTG2
            'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/processor/processorapi/cashier.asmx', //RTG Naboo
        ),
        "cashierapi" => array(
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
            //'https://125.5.1.20/ABBOTTRUVMANSYWPLMXI/processor/ProcessorAPI/Cashier.asmx', //RTG Abbottv2
            'https://202.44.103.53/ILMCB6UOP7SXXCAQUC88/processor/processorapi/cashier.asmx', //RTG Naboo
        ),
        //create account data
        'AID' => 0,
        'country' => 'PH',
        'password' => '1HMA1Y',
        'termcode_prefix' => 'ICSA-',
        'casinoID' => 1,
        'userID' => 0,
        'downloadID' => 0,
        'clientID' => 1,
        'putInAffPID' => 0,
        'calledFromCasino' => 0,
        'currentPosition' => 0,
//        'currency' => 'PHP',
//        'rr_password' => 'philweb',
//        'rr_URI' => 'https://cashier-dev.egamescasino-ss.ph',
    )
);
