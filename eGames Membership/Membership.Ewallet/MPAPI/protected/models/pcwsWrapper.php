<?php
/*
 * @Author Joene Floresca
 * @Desc Wrapper for PCWS API
 * @Date 02-02-2015
 */
Class PcwsWrapper extends Controller
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
//    private $_uri_resetpinh= 'http://172.16.102.174/PCWS/index.php/pcws/changepin';
    private $_uri_resetpinh = 'http://192.168.41.78/pcws/index.php/pcws/changepin';
    private $_accessdate;
    private $_dt;
    private $_tkn;
    private $_username;
   
    public function __construct() {
        //App::getParam('pcws_getComppoints');
        $this->_accessdate = date("Y-m-d H:i:s");
        $this->_dt         = date('YmdHis');
        $this->_username =  array('kadmin');
        $this->_tkn[$this->_username[0]] = sha1($this->_dt.'4996816');
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
    
    public function resetPin($CardNumber, $index){
        $postdata = json_encode(array('CardNumber'=>$CardNumber,'SystemUsername'=>  $this->_username[$index], 'AccessDate'=>  $this->_accessdate,
                                'Token'=>  $this->_tkn[$this->_username[$index]], 'ActionCode' => 0));
        $result = $this->curlApi($this->_uri_resetpinh, $postdata);
        return json_decode($result[1], true);
    }
   
   
}
?>