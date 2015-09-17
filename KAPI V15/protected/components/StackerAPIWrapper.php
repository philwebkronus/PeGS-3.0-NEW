<?php
/**
 * Stacker API Wrapper for KAPI
 * @author Mark Kenneth Esguerra
 * @date September 23, 2014
 */
class StackerAPIWrapper
{
    /**
     * Set caching of connection
     * @var boolean
     */
    private $_caching = FALSE;

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
    
    /**
    * User agent
    * @var string
    */
   private $_userAgent = 'Cashier';

    /**
     * Holds the web service end point 
     * @var string
     */	
    private $_URI;
    
    /**
     * 
     * @var string
     */	
    private $_queryString = '';
     
    /**
    * 
    * @var string
    */	
    private $_fullUri;
    
    /**
    * Holds API Response
    * @var array 
    */
    public $_APIresponse = array();
    
    /**
     * Get Stacker Batch ID for KAPI. Called when detected if there is new stacker session.
     * @param type $terminalname
     * @param type $cardnumber
     * @return type
     */
    public function getStackerBatchID ($terminalname, $cardnumber)
    {
        $this->_URI = Yii::app()->params['get_stacker_batch_id'];
        
        if (!(bool)$this->IsAPIServerOK())
        {
            $message = 'Can\'t connect to VMS System';
            self::throwError($message);
        }
        
        $postdata = json_encode(array("TerminalName" => $terminalname, 
                                      "MembershipCardNumber" => $cardnumber));
        
        $response = $this->SubmitData($this->_URI, $postdata);
        
        if($response[0] == 200){
            $this->_APIresponse = json_decode($response[1], TRUE);
            
            return $this->_APIresponse;
        } 
        else 
        {
            $this->_error = $response[0];
            
            return $this->_error;
        }
        
        
    }
    
    public function IsAPIServerOK()
    {
        $port = 80;
        
        $urlInfo = parse_url($this->_URI);
        
        if ($urlInfo['scheme'] == 'https')
        {
            $port = 443;
        }
        
        return common::isHostReachable($this->_URI, $port);
    }
    
    private function SubmitData( $uri, $postdata)
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
}
?>
