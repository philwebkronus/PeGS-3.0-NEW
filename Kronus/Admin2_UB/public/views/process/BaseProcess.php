<?php

/**
 * Date Created 09 28, 11 7:27:39 PM
 * Description of BaseProcess
 * @author Bryan Salazar
 */
//include "../../sys/class/DbReport.class.php";
require __DIR__.'/../sys/core/init.php';

class BaseProcess {
   private static $_connection;
   public static $service_api;
   public static $player_api;
   public static $service_api_caching;
   public static $micro_gaming_currency;
   public static $sitecode; //(icsa-) code
   public static $cutoff;
   public static $gaddeddate;
   public static $capi_username;
   public static $capi_password;
   public static $capi_server_id;
   public static $capi_player;
   public static $ptSecretKey;
   public static $ptcasinoname;
   public $conn;
   
   public function getConnection() {
      return self::$_connection;
   }
   
   public function __construct() {
      
   }
   
    public function render($view,$parameter=array()) {
        $param = $parameter; 
        include '../views/' . $view . '.php';
    }
   
    public static function setConnection($connection) {
        self::$_connection = $connection;
    }
   
    
    /**
     * For testing of paging of jqgrid
     * You can add or subtract array in cell
     * @param type $start
     * @param type $limit
     * @return array 
     */
    public function testPaging($start,$limit) {
        $rows = array();
        for($i=0;$i < 100; $i++) {
            $rows[] = array('id'=>$i,'cell'=>array('foo'.$i,'bar'.$i,'baz'.$i,'fred'.$i));
        }          
        return $rows = array_slice($rows,$start,$limit);      
    }
   
    public function CasinoType($serviceId) {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();  
        $rows = $topup->getRefServices(); 
        $casino = array();
        foreach($rows as $row) {
            $casino[$row['ServiceID']] = $row['ServiceName'];
        }
        return $casino[$serviceId];
    }
    
    public static function setConfig($_ServiceAPI,$_PlayerAPI,$_ServiceAPICaching,
                                     $_MicrogamingCurrency, $terminalcode, $cutoff_time,
                                     $gaddeddate, $_CAPIUsername, $_CAPIPassword, 
                                     $_CAPIPlayerName, $_ptsecretkey, $_ptcasinoname) {
        self::$service_api = $_ServiceAPI;
        self::$player_api = $_PlayerAPI;
        self::$service_api_caching = $_ServiceAPICaching;
        self::$micro_gaming_currency = $_MicrogamingCurrency;
        self::$sitecode = $terminalcode;
        self::$cutoff = $cutoff_time;
        self::$gaddeddate = $gaddeddate;
        self::$capi_username = $_CAPIUsername;
        self::$capi_password = $_CAPIPassword;
        //self::$capi_server_id = $_CAPIServerID;
        self::$capi_player = $_CAPIPlayerName;
        self::$ptSecretKey = $_ptsecretkey;
        self::$ptcasinoname = $_ptcasinoname;
    }
}

class jQGrid {
   public $page;
   public $total;
   public $records;
   public $rows = array();
}

BaseProcess::setConnection($_DBConnectionString[0]);
BaseProcess::setConfig($_ServiceAPI,$_PlayerAPI,$_ServiceAPICaching,$_MicrogamingCurrency, 
                       $terminalcode, $cutoff_time, $gaddeddate, $_CAPIUsername, $_CAPIPassword, 
                       $_CAPIPlayerName,$_ptsecretkey,$_ptcasinoname);
