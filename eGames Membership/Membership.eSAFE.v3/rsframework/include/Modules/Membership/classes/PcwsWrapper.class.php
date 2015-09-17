<?php
/*
 * @Author Joene Floresca
 * @Desc Wrapper for PCWS API
 * @Date 02-02-2015
 */
Class PcwsWrapper extends BaseEntity
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
    private $_accessdate;
    private $_dt;
    private $_tkn;
    private $_username;
    private $_logger;
    
    public function __construct() {
        //App::getParam('pcws_getComppoints');
        App::LoadCore("Logger.class.php");
        
        $this->_accessdate = date("Y-m-d H:i:s");
        $this->_dt2        = new DateTime($this->_accessdate);
        $this->_dt         = $this->_dt2->format('YmdHis');
        $this->_username =  array('madmin','mportal');
        $this->_tkn[$this->_username[0]] = sha1($this->_dt.'4896816');
        $this->_tkn[$this->_username[1]] = sha1($this->_dt.'48452098');
        
        $this->_logger = new Logger();
        $this->_logger->setPrefix("Pcws");
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
       $postdata = json_encode(array('CardNumber'=>$CardNumber,'SystemUsername'=>  $this->_username[$index], 'AccessDate'=>  $this->_accessdate, 
                                'Token'=>  $this->_tkn[$this->_username[$index]]));
       $this->_logger->_logRequest("GetCompPoints", $postdata);
       $result = $this->curlApi(App::getParam('getcomppoints'), $postdata);
       $this->_logger->_logResponse("GetCompPoints", $result[1]);
       
       return json_decode($result[1], true);
    }
    
    
    
    public function deductCompPoints($CardNumber,$amt, $siteID, $index)
    {
       $postdata = json_encode(array('CardNumber'=>$CardNumber, 'Amount'=>$amt, 'SiteID'=>$siteID, 'SystemUsername'=>  $this->_username[$index], 'AccessDate'=>  $this->_accessdate, 
                                'Token'=>  $this->_tkn[$this->_username[$index]]));
       $this->_logger->_logRequest("DeductCompPoints", $postdata);
       $result = $this->curlApi(App::getParam('deductcomppoints'), $postdata);
       $this->_logger->_logResponse("DeductCompPoints", $result[1]);
       
       return json_decode($result[1], true);
    }
    
    
    public function changePin($cardnumber,$oldPin,$newPin,$index)
    {
        $postdata = json_encode(array('CardNumber'=>$cardnumber,'CurrentPin'=>$oldPin,'NewPin'=>$newPin,'SystemUsername'=>  $this->_username[$index], 'AccessDate'=>  $this->_accessdate, 
                                'Token'=>  $this->_tkn[$this->_username[$index]],'ActionCode'=>1));
        $this->_logger->_logRequest("ChangePin", $postdata);
        $result = $this->curlApi(App::getParam('changepin'), $postdata);
        $this->_logger->_logResponse("ChangePin", $result[1]);
       
       return json_decode($result[1], true);
        
    }
    
    public function checkpin($cardnumber,$pin,$index)
    {
        $postdata = json_encode(array('CardNumber'=>$cardnumber,'PIN'=>$pin,'SystemUsername'=>  $this->_username[$index], 'AccessDate'=>  $this->_accessdate, 
                                'Token'=>  $this->_tkn[$this->_username[$index]]));
        
        $this->_logger->_logRequest("CheckPin", $postdata);
        $result = $this->curlApi(App::getParam('checkpin'), $postdata);
        $this->_logger->_logResponse("CheckPin", $result[1]);
       
       return json_decode($result[1], true);
        
    }
    
    public function esafeconversion($cardnumber, $password, $pin, $confirmPIN, $index)
    {

       $postdata = json_encode(array('CardNumber'=>$cardnumber,'Password'=>$password,'PIN'=>$pin, 'ConfirmPIN'=>$confirmPIN,
           'SystemUsername'=>  $this->_username[$index], 'AccessDate'=>  $this->_accessdate,  'Token'=>  $this->_tkn[$this->_username[$index]]));
       
        $this->_logger->_logRequest("EsafeConversion", $postdata);
        $result = $this->curlApi(App::getParam('esafeconversion'), $postdata);
        $this->_logger->_logResponse("EsafeConversion", $result[1]);
       
       return json_decode($result[1], true);
        
    }
    
    public function addCompPoints($cardnumber, $SiteID, $ServiceID, $Amount, $index)
    {
        $postdata = json_encode(array(
            'CardNumber'=>$cardnumber,
            'SiteID'=>$SiteID,
            'ServiceID'=>$ServiceID,
            'Amount'=>$Amount,
            'SystemUsername'=>  $this->_username[$index], 
            'AccessDate'=>  $this->_accessdate, 
            'Token'=>  $this->_tkn[$this->_username[$index]]));
        $this->_logger->_logRequest("AddCompPoints", $postdata);
        $result = $this->curlApi(App::getParam('addcomppoints'), $postdata);
        $this->_logger->_logResponse("AddCompPoints", $result[1]);
       
       return json_decode($result[1], true);
    }  
}
?>

