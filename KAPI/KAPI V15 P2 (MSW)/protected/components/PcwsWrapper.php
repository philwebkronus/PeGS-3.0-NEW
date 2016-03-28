<?php
/*
 * @Author Joene Floresca
 * @Desc Wrapper for PCWS API
 * @Date 02-02-2015
 * @Updated by Mark Nicolas Atangan
 * @Date 09-28-2015
 */

//Check if the class has been already declared
if (class_exists('CasinoLogger')) {
    require_once 'CasinoLogger.php';
} 
    
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
    //private $_uri_resetpinh   = 'http://172.16.102.174/PCWS/index.php/pcws/changepin';
//    private $_uri_resetpinh   = '192.168.41.78/pcws/index.php/pcws/changepin';
//    private $_uri_forcelogout = 'http://172.16.102.174/PCWS/index.php/pcws/forcelogout';
    private $_accessdate;
    private $_dt;
    private $_tkn;
    private $_username;
    private $_uri_resetpin;
    private $_uri_forcelogout;
   
    public function __construct($username,$usercode) {
        //App::getParam('pcws_getComppoints');
        $this->_accessdate = date("Y-m-d H:i:s");
//        $this->_accessdate = date("2015-02-15 12:00:00");
        $date1= new DateTime($this->_accessdate);
        $this->_dt = $date1->format('YmdHis');
        $this->_username =  $username;
        $this->_tkn = sha1($this->_dt.$usercode);
    }
   
    public function curlApi($uri, $postdata, $methodname,$logger)
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
        
        //$logger = new CasinoLogger();
        if($response != ""){
            $message = "[$methodname] Token: $this->_tkn, Output: ".print_r($response,true);     
            $logger->logger($message, "Response", "", true);
        }
        
        return array( $http_status, $response );
    }
    
    public function resetPin($url, $CardNumber){
        $postdata = json_encode(array('CardNumber'=>$CardNumber,'SystemUsername'=>  $this->_username, 'AccessDate'=>  $this->_accessdate,
                                'Token'=>  $this->_tkn, 'ActionCode' => 0));

        $logger = new CasinoLogger();
        $methodname = "ResetPIN";     
        $message = "[$methodname] Input: ".print_r($postdata,true);     
        $logger->logger($message, "Request", "", true);
        $result = $this->curlApi($url, $postdata, $methodname,$logger);
        if(!$result[1]){
            $result[1] = '{"changePin":{"TransactionMessage":"Can\'t connect to API.","ErrorCode":1}}';
            $message = "[$methodname] Token: $this->_tkn, Output: ".print_r($result[1],true);     
            $logger->logger($message, "Response", "", true);
        }
        
        return json_decode($result[1], true);
    }
    
    public function logoutLaunchPad($url, $Login)
    {
        $postdata = json_encode(array('Login'=>$Login, 
            'SystemUsername'=> $this->_username, 
            'AccessDate'=>  $this->_accessdate, 
            'Token'=> $this->_tkn));
        
        $logger = new CasinoLogger();
        $methodname = "ForceLogout";
        $message = "[$methodname] Input: ".print_r($postdata,true);     
        $logger->logger($message, "Request", "", true);

        $result = $this->curlApi($url, $postdata,$methodname,$logger);

        return json_decode($result[1], true);
    }
    
    public function removeSession($url, $terminalCode, $cardNumber) {
        $postdata = json_encode(array('TerminalCode'=>$terminalCode, 
            'CardNumber' => $cardNumber, 
            'SystemUsername'=> $this->_username, 
            'AccessDate'=>  $this->_accessdate, 
            'Token'=> $this->_tkn));
        
        $logger = new CasinoLogger();
        $methodname = "Remove Session";
        $message = "[$methodname] Input: ".print_r($postdata,true);     
        $logger->logger($message, "Request", "", true);

        $result = $this->curlApi($url, $postdata,$methodname,$logger);

        return json_decode($result[1], true);
    }
    //function for getting Comp Points
    public function getComppoints($url, $cardNumber)  {
        $postdata = json_encode(array('SystemUsername'=> $this->_username, 
            'AccessDate'=>  $this->_accessdate, 
            'Token'=> $this->_tkn,
            'CardNumber' => $cardNumber));
        
        $logger = new CasinoLogger();
        $methodname = "Get Comp points";
        $message = "[$methodname] Input: ".print_r($postdata,true);     
        $logger->logger($message, "Request", "", true);

        $result = $this->curlApi($url, $postdata,$methodname,$logger);

        return json_decode($result[1], true);
    }
    public function getBalance($url, $cardNumber, $serviceID) {
        $postdata = json_encode(array('SystemUsername'=> $this->_username, 
            'AccessDate'=>  $this->_accessdate, 
            'Token'=> $this->_tkn,
            'CardNumber' => $cardNumber, 
            'ServiceID' => $serviceID));
        
        $logger = new CasinoLogger();
        $methodname = "GetBalance";
        $message = "[$methodname] Input: ".print_r($postdata,true);     
        $logger->logger($message, "Request", "", true);

        $result = $this->curlApi($url, $postdata,$methodname,$logger);

        return json_decode($result[1], true);
    }
    /**
     * Force Logout Wrapper
     * @param type $login
     * @param type $serviceID
     * @return type
     */
    public function forceLogoutGen($url, $login, $serviceID) {
        $postdata = json_encode(array('SystemUsername'=> $this->_username, 
            'AccessDate'=>  $this->_accessdate, 
            'Token'=> $this->_tkn,
            'Login' => $login, 
            'ServiceID' => $serviceID));
        
        $logger = new CasinoLogger();
        $methodname = "ForceLogoutGen";
        $message = "[$methodname] Input: ".print_r($postdata,true);     
        $logger->logger($message, "Request", "", true);

        $result = $this->curlApi($url, $postdata,$methodname,$logger);

        return json_decode($result[1], true);
    }
}

?>
