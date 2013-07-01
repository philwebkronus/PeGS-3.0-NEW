<?php

$http = 'http';

if( isset( $_SERVER[ 'HTTPS' ] ) )
{
    if ( $_SERVER[ "HTTPS" ] == "on" ) 
    {
        $http = 'https';
    }
}

return array(
    // DATABASE 
    'db'=>array(
        'connection_string'=>'mysql:host=<host>;dbname=<dbname>',
        'username'=>'<username>',
        'password'=>'<password>',
    ),
    'domain'=>$http . '://'.$_SERVER["SERVER_NAME"].'/kronus.prod/cashier2.pagcoregames.com/index.php', // stagging
    // LOGS
    'logs'=>array(
        // path for the log file
        'log_path'=>Mirage::app()->getAppPath() . DIRECTORY_SEPARATOR . 'logs',
        // will rotate only in prod mode
        'rotate'=>true,
        // day or filesize
        'rotate_by'=>'day',
        // per day
        'interval'=>1,
        // per MB
//        'filesize'=>1,
    ),
    'shutdown'=>array(
        array('class'=>'WebLogger','method'=>'log','file'=>Mirage::app()->getAppPath().'/extensions/web_logger/WebLogger.php','runnable'=>MIRAGE_DEBUG),
    ),
    // sub modules
    'sub_modules'=>array('cron','monitoring'),
    'params'=>array(
	'port'=>8888, // port use in screen blocking,
        'pegsstationerrormsg'=>'There is a problem in Terminal Blocker',
        'failed_unlock'=>'Failed to Unlock the Terminal',
        'failed_lock'=>'Failed to Lock the Terminal',


	// heartbeat rate 5 sec
	'heartbeat_rate'=>300000,
        // minimun length of password
        'min_pass_len'=>8,
        // alloweds account type for standalone monitoring
        'standalone_allowed_type'=>array(2,7),
        'allowed_acctype'=>array(4,2,7),
        'ASgroup'=>'elperez@philweb.com.ph', //AS Group Email
	'enable_screenblocking'=>false, //enabling of screen blocking
	'BGI_ownerID'=>1,
        // LOYALTY
        'card_inquiry'=>'http://192.168.20.8/rewardspoints/apiproxy/cardinquiry.php',
        'register_account'=>'http://192.168.20.8/rewardspoints/rewardspointsAPI/registration.php',
        'add_points'=>'http://192.168.20.8/rewardspoints/apiproxy/addpoints.php',
        'withdraw'=>'http://192.168.20.8/rewardspoints/apiproxy/withdraw.php',
        'loyalty_portal'=>'http://192.168.20.8:8114/',	
	'loyalty_service'=>7,
        // RTG
        'deposit_method_id' =>503,
        'withdrawal_method_id'=>502,
        'rtg_cert_dir'=>'<path-to-rtgcert>',
        'logout_page'=>$http . '://'.$_SERVER["SERVER_NAME"].'/kronus.prod/cashier2.pagcoregames.com/index.php?r=logout',
        'cut_off'=>'06:00:00',
        'terminal_per_page'=>10,
        'service_api'=>array(
	    'https://202.44.100.29/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
            'https://202.44.100.28/GJKILOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
            'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx',
            'https://202.44.102.3/SIGMAQZWXECRVTBYNUMI/processor/processorapi/cashier.asmx',
            'https://202.44.100.29/ALPHAKI98TUI5AMINAS2/processor/processorapi/cashier.asmx',
            'https://202.44.100.28/GAMMAOP98TUI5JMNDES2/processor/processorapi/cashier.asmx',
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx',
            'https://cashier1.megasportcasino.com',
            //'https://entservices.totalegame.net/EntServices.asmx?WSDL',
            array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238), //test environment
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
            'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',    
            'https://202.44.102.31/ECFTESTFDGUFTUGEHVHF/processor/processorapi/cashier.asmx',
            array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238), //test environment
            array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238), // MG Server 1 (NCR)
            array('https://api18.valueactive.eu/philweb1_CAPI/CasinoAPI.aspx', 2238), // MG Server 2 (Provincial)
	    'https://202.44.103.231/GPRIMESZNVJFROAPSERT/processor/ProcessorAPI/Cashier.asmx'
	 ),
        'player_api'=>array(
            '',
            '',
            '',
            '',
            '',
            '',
            'https://172.16.102.16/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx?wsdl',
            '',
            '',
            '',
            'https://172.16.102.16/ECFDEMOFDGEFNPGEMFOQ/casinoapi/player.asmx?wsdl',
            'https://172.16.102.16/ECFDEMOFDGEFNPGEMFOQ/casinoapi/player.asmx?wsdl',
            'https://172.16.102.16/ECFDEMOFDGEFNPGEMFOQ/casinoapi/player.asmx?wsdl',
            'https://172.16.102.16/ECFDEMOFDGEFNPGEMFOQ/casinoapi/player.asmx?wsdl',
            'https://172.16.102.16/ECFTESTFDGUFTUGEHVHF/CasinoAPI/player.asmx?wsdl',
        ),
        'service_api_caching'=>array(
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            2000=>false
        ),
        'microgaming_currency'=>9,
        'mgcapi_user_type'=>0,
        'mgcapi_trans_method'=>'ChangeBalanceEx',
        'mgcapi_username'=>'philweb_capi',
        'mgcapi_password'=>'test1234$',
        'mgcapi_playername'=>'capi',
//        'mgcapi_serverid'=>2238,
        'mgcapi_event_id'=>array(
            '10001', //Deposit
            '10002', //Reload
            '10003', //Withdraw
  
      ),
//        'microgaming_currency'=>9,
        // condition in gross hold monitoring
        'green'=>array(200000,400000),
        'orange'=>array(400000,600000),
        'blue'=>600000,
        'red'=>200000,
        'cashier_version'=>2 //current kronus cashier version
    ),
);
