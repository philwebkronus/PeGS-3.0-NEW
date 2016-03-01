<?php
/*
 * @Author Joene Floresca
 * @Desc Wrapper for PCWS API
 * @Date 02-02-2015
 */
require_once '../models/LPConfig.php';
include_once '../Helper/Logger.class.php';

Class PcwsWrapper
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
    private $_uriForceLogout;
    private $_uri_changePin;
    private $_uri_startSession;
    private $_accessdate;
    private $_dt;
    private $_tkn;
    private $_username;
    private $_logpath;
    
    public function __construct() {
        
        $this->_accessdate = date("Y-m-d H:i:s");
        $date1        = new DateTime($this->_accessdate);
        $this->_dt    = $date1->format('YmdHis');
        $this->_username =  LPConfig::app()->params['systemUsername'];
        $this->_tkn = sha1($this->_dt.LPConfig::app()->params['systemCode']);
        $this->_uriForceLogout   = LPConfig::app()->params['uriForceLogout'];
        $this->_uri_changePin    = LPConfig::app()->params['uri_changePin'];
        $this->_uri_startSession = LPConfig::app()->params['uri_startSession'];
        $this->_uri_checkPin = LPConfig::app()->params['uri_checkPin'];
        $this->_logpath = LPConfig::app()->params['logpath'];
    }
    
    public function curlApi($uri, $postdata,$methodname)
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
        
        $logger = new Logger($this->_logpath);
        $data = print_r($response,true);
        $message = "[$methodname] Token: $this->_tkn, Output: $data";
        $logger->apirequestlog($message, "Response");
        
        return array( $http_status, $response );
    }
    

    public function checkPin($cardnumber,$pin)
    {
        $postdata = json_encode(array('CardNumber'=>$cardnumber,'PIN'=>$pin,'SystemUsername'=>  $this->_username, 'AccessDate'=>  $this->_accessdate, 
                                'Token'=> $this->_tkn));
       
        $logger = new Logger($this->_logpath);
        $methodname = "CheckPin";
        $data = print_r($postdata,true);
        $message = "[$methodname] Input: $data";
        $logger->apirequestlog($message, "Request");

        $result = $this->curlApi($this->_uri_checkPin,$postdata,$methodname);
       
        return json_decode($result[1], true);
    }
    
    public function changePin($cardnumber,$oldPin,$newPin)
    {
        
        $postdata = json_encode(array('CardNumber'=>$cardnumber,'CurrentPin'=>$oldPin,'NewPin'=>$newPin,'SystemUsername'=>  $this->_username, 'AccessDate'=>  $this->_accessdate, 
                                 'Token'=>  $this->_tkn,'ActionCode'=>1));
        
        $logger = new Logger($this->_logpath);
        $methodname = "ChangePin";
        $data = print_r($postdata,true);
        $message = "[$methodname] Input: $data";
        $logger->apirequestlog($message, "Request");

        $result = $this->curlApi($this->_uri_changePin, $postdata,$methodname);
       
        return json_decode($result[1], true);
        
    }
    
    public function sessionStart($terminalCode,$serviceID,$cardNumber)
    {
        
        $postdata = json_encode(array('TerminalCode'=>$terminalCode, 'ServiceID'=>$serviceID,
                                        'CardNumber'=>$cardNumber,'SystemUsername'=>$this->_username, 
                                        'AccessDate'=>$this->_accessdate, 'Token'=>$this->_tkn));
        
        $logger = new Logger($this->_logpath);
        $methodname = "Unlock";
        $data = print_r($postdata,true);
        $message = "[$methodname] Input: $data";
        $logger->apirequestlog($message, "Request");

        $result = $this->curlApi($this->_uri_startSession, $postdata,$methodname);
       
        return json_decode($result[1], true);
        
    }
    
        
    public function logoutLaunchPad($UBServiceLogin,$ubServiceID)
    {
        $postdata = json_encode(array('Login'=>$UBServiceLogin,
            'ServiceID'=>$ubServiceID,
            'SystemUsername'=> $this->_username, 
            'AccessDate'=>  $this->_accessdate, 
            'Token'=> $this->_tkn));
        
        $logger = new Logger($this->_logpath);
        $methodname = "ForceLogout";
        $data = print_r($postdata,true);
        $message = "[$methodname] Input: $data";
        $logger->apirequestlog($message, "Request");
        
        $result = $this->curlApi($this->_uriForceLogout, $postdata,$methodname);
        
        return json_decode($result[1], true);
    }
     
    
}
?>

