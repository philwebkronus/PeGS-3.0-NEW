<?php
/*
 * @Author Joene Floresca
 * @Desc Wrapper for PCWS API
 * @Date 02-02-2015
 */
class PcwsWrapper
{
    /**
     * Set caching of connection
     * @var boolean
     */
    private $_caching = FALSE;

    /**
     * User agent
     * @var string
     */
    private $_userAgent = 'PEGS Station Manager';

    /**
     * Maximum number of seconds to wait while trying to connect
     * @var integer
     */
    private $_connectionTimeout = 10;

    /**
     * Maximum number of seconds before a call timeouts
     * @var integer
     */
    private $_timeout = 500;
    
    /*
     * Required variables to call PCWS API 
     */
    //private $_uri_getcomppoints =  'http://172.16.102.174/PCWS/index.php/pcws/getcomppoints';
    //private $_uri_changePin =   'http://192.168.41.110/pcws/index.php/pcws/changepin';
    private $_accessdate;
    private $_dt;
    private $_tkn;
    private $_username;
   // private $_logger;
    
    public function __construct() {
        //App::getParam('pcws_getComppoints');
        //App::LoadCore("Logger.class.php");
        
        $this->_accessdate = date("Y-m-d H:i:s");
        $this->_dt         = date('YmdHis');
//        $this->_tkn        = sha1($this->_dt.'4896816');
        $this->_username =  array('madmin','mportal');
        $this->_tkn[$this->_username[0]] = sha1($this->_dt.'4896816');
        $this->_tkn[$this->_username[1]] = sha1($this->_dt.'48452098');
        
//        $this->_logger = new Logger();
//        $this->_logger->setPrefix("APICall");
    }
    
    public function curlApi($uri, $postdata)
    {
        $curl = curl_init( $uri );
        curl_setopt( $curl, CURLOPT_FRESH_CONNECT, $this->_caching );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout );
        curl_setopt( $curl, CURLOPT_TIMEOUT, $this->_timeout );
        curl_setopt( $curl, CURLOPT_USERAGENT, $this->_userAgent );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_POST, TRUE );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
        // Data+Files to be posted
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        $response = curl_exec( $curl );
        $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
        curl_close( $curl );
        
        return array( $http_status, $response );
    }
    
    public function getCompPoints($CardNumber,$index)
    {
       $module = 'GetCompPoints';
       
       $postdata = json_encode(array('CardNumber'=>$CardNumber,'SystemUsername'=>  $this->_username[$index], 'AccessDate'=>  $this->_accessdate, 
                                'Token'=>  $this->_tkn[$this->_username[$index]], 'ServiceID' => Yii::app()->params['serviceid']));
       
       $this->_Log($module, 0, $postdata);
       $result = $this->curlApi(Yii::app()->params['getcomppoints'], $postdata);
       $this->_Log($module, 1, $result[1], $index);
       
       return json_decode($result[1], true);
    }
    
    public function getBalance($CardNumber,$index)
    {
       $module = 'GetBalance';
       $postdata = json_encode(array('CardNumber'=>$CardNumber,'SystemUsername'=>  $this->_username[$index], 'AccessDate'=>  $this->_accessdate, 
                                'Token'=>  $this->_tkn[$this->_username[$index]], 'ServiceID' => Yii::app()->params['serviceid']));
       $this->_Log($module, 0, $postdata);
       $result = $this->curlApi(Yii::app()->params['getbalance'], $postdata);
       $this->_Log($module, 1, $result[1], $index);
       
       return json_decode($result[1], true);
    }
    
    public function deductCompPoints($CardNumber,$amt, $siteID, $index)
    {
       $module = 'DeductCompPoints';
       $postdata = json_encode(array('CardNumber'=>$CardNumber, 'Amount'=>$amt, 'SiteID'=>$siteID, 'SystemUsername'=>  $this->_username[$index], 'AccessDate'=>  $this->_accessdate, 
                                'Token'=>  $this->_tkn[$this->_username[$index]], 'ServiceID' => Yii::app()->params['serviceid']));
       $this->_Log($module, 0, $postdata);
       $result = $this->curlApi(Yii::app()->params['deductcomppoints'], $postdata);
       $this->_Log($module, 1, $result[1], $index);
       
       return json_decode($result[1], true);
    }
    
    private function _Log($module, $type, $data, $index = '') {
        $appLogger = new AppLogger();
        
        if($type == 0) {
            $type = '[request]';
            $message = "[".$module."] Input: ".$data;
        }
        else {
            $type = '[response]';
            $message = "[".$module."] Token:".$this->_tkn[$this->_username[$index]].", Output: ".$data;
        }
        
        $appLogger->log($appLogger->logdate, $type,$message);
    }
    
    
//    public function changePin($cardnumber,$oldPin,$newPin,$index)
//    {
//        $postdata = json_encode(array('CardNumber'=>$cardnumber,'CurrentPin'=>$oldPin,'NewPin'=>$newPin,'SystemUsername'=>  $this->_username[$index], 'AccessDate'=>  $this->_accessdate, 
//                                'Token'=>  $this->_tkn[$this->_username[$index]],'ActionCode'=>1));
//        $this->_logger->_logRequest("ChangePin", $postdata);
//        $result = $this->curlApi($this->_uri_changePin, $postdata);
//        $this->_logger->_logResponse("ChangePin", $result[1]);
//       
//       return json_decode($result[1], true);
//        
//    }
}
?>

