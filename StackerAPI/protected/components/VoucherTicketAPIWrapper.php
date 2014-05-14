<?php
/**
 * Date Created 10 25 2013 3:30:05 PM <pre />
 * Description of VoucherTicketAPIWrapper
 * @author jshernandez
 */
class VoucherTicketAPIWrapper
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
     * Verify if voucher is for claiming 
     * This was called in deposit and reload transaction
     * @param str $vouchercode
     * @param int $aid
     * @param int $source
     * @param str $trackingId optional
     * @return str | array
     */
    public function validateVoucher($terminalName, $ticketCode, $aid, $source, $cardnumber, $trackingId = ''){
        
        $this->_URI = Yii::app()->params['verify_ticket'];
        
        if (!(bool)$this->IsAPIServerOK()) {
            $message = 'Can\'t connect to VMS System';
            self::throwError($message);
        }
           
        $postdata = CJSON::encode(array('TerminalName'=>$terminalName, 'VoucherTicketBarcode'=>$ticketCode,
            'Source'=>$source, 'AID'=>$aid, 'TrackingID'=>$trackingId, 'MembershipCardNumber'=>$cardnumber));
       
        $response = $this->SubmitData($this->_URI, $postdata);
       
        if($response[0] == 200){
            $this->_APIresponse = json_decode($response[1], TRUE);

            return $this->_APIresponse;
            
        } else {
            $this->_error = $response[0];

            return $this->_error;
        }
    }
    
    
    
    public function addTicket($terminalName, $amount, $aid, $source, $cardnumber, $purpose, $stackerBatchID, $trackingId = '', $voucherTicketBarcode){
        
        $this->_URI = Yii::app()->params['add_ticket'];
        
        if (!(bool)$this->IsAPIServerOK()) {
            $message = 'Can\'t connect to VMS System';
            self::throwError($message);
        }
           
        $postdata = CJSON::encode(array('TerminalName'=>$terminalName, 'Amount'=>$amount,
            'Source'=>$source, 'AID'=>$aid, 'TrackingID'=>$trackingId, 'MembershipCardNumber'=>$cardnumber, 'Purpose'=>$purpose, 'StackerBatchID'=>$stackerBatchID, 'VoucherTicketBarcode'=>$voucherTicketBarcode));
        $response = $this->SubmitData($this->_URI, $postdata);

            if($response[0] == 200){
            $this->_APIresponse = json_decode($response[1], TRUE);
            
            return $this->_APIresponse;
            } else {
            $this->_error = $response[0];
            
            return $this->_error;
            }
    }
    
    
    /**
     * Claims a voucher, this was called in deposit and reload transaction
     * @param int $aid
     * @param str $trackingID
     * @param str $vouchercode
     * @param int $terminalID
     * @param int $source
     * @return str | array
     */
    public function useTicket($terminalName, $vouchercode, $aid, $source, $trackingID, $cardNumber, $amount){
        $this->_URI = Yii::app()->params['use_voucher_new'];
        
        if (!(bool)$this->IsAPIServerOK()) {
            $message = 'Can\'t connect to VMS System';
            self::throwError($message);
        }
        
        $postData = json_encode(array('AID'=>(int)$aid,
                                      'TrackingID'=>$trackingID,
                                      'VoucherTicketBarcode'=>$vouchercode,
                                      'TerminalName'=>$terminalName,
                                      'Source'=>(int)$source,
                                      'MembershipCardNumber'=>$cardNumber,
                                      'Amount'=>$amount));
        $response = $this->SubmitData($this->_URI, $postData);
       
            if($response[0] == 200){
            $this->_APIresponse = json_decode($response[1], TRUE);
            
            return $this->_APIresponse;
            } else {
            $this->_error = $response[0];
            
            return $this->_error;
            }
    }
    
    
    /**
     * Verify if voucher is for claiming 
     * This was called in deposit and reload transaction
     * @param str $vouchercode
     * @param int $aids
     * @param int $source
     * @param str $trackingId optional
     * @return str | array
     */
    public function verifyTicket($vouchercode, $terminalname, $aid, $source, $cardnumber, $trackingId = ''){
        $this->_URI = Yii::app()->params['verify_ticket'];
        
        if (!(bool)$this->IsAPIServerOK()) {
            $message = 'Can\'t connect to VMS System';
            self::throwError($message);
        }
        
        $postData = json_encode(array('VoucherTicketBarcode'=>$vouchercode,
                                      'AID'=>$aid,
                                      'TrackingID'=>$trackingId,
                                      'MembershipCardNumber'=>$cardnumber,
                                      'Source'=>$source,
                                      'TerminalName'=>$terminalname));
        
        $response = $this->SubmitData($this->_URI, $postData);
        
            if($response[0] == 200){
            $this->_APIresponse = json_decode($response[1], TRUE);
            
            return $this->_APIresponse;
            } else {
            $this->_error = $response[0];
            
            return $this->_error;
            }
    }
    
    
    /**
     * Checks if API endpoint is reachable
     *
     * @param none
     * @return boolean
     */
    public function IsAPIServerOK()
    {
        $port = 80;
        
        $urlInfo = parse_url( $this->_URI );        

        if ( $urlInfo[ 'scheme' ] == 'https' )
        {
            $port = 443;
        }

        return common::isHostReachable( $this->_URI, $port );
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
    
    protected function throwError($message) {
        header('HTTP/1.0 404 Not Found');
        echo $message;
        Yii::app()->end();
    }
    
}
?>
