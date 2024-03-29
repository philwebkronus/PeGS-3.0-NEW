<?php
/**
 * Method / Action of VMS (Voucher Management System)
 * @author aqdepliyan, elperez
 * @dateupdated 10/24/13 add mid and siteid, also 
 */

class VoucherManagement {
    
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


    private function InitQueryString()
    {
            $this->_queryString = '';
            $this->_queryString = '?';
    }
    
    protected function throwError($message) {
        header('HTTP/1.0 404 Not Found');
        echo $message;
        Mirage::app()->end();
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
    public function verifyVoucher($vouchercode, $aid, $source, $trackingId = ''){
        $this->_URI = Mirage::app()->param['verify_voucher'];
        
        if (!(bool)$this->IsAPIServerOK()) {
            $message = 'Can\'t connect to VMS System';
            logger($message . ' CouponCode=' . $vouchercode . ' AID='.$aid);
            self::throwError($message);
        }
        
//        $this->InitQueryString();
//        $this->_queryString = $this->_queryString.'vouchercode='.$vouchercode;
//        $this->_queryString = $this->_queryString.'&aid='.$aid;
//        $this->_queryString = $this->_queryString.'&trackingid='.$trackingId;
//        $this->_queryString = $this->_queryString.'&source='.$source;
//        
//        $this->_fullUri = $this->_URI;
//        $this->_fullUri = $this->_fullUri.'/'.$this->_queryString;
//        $response = $this->SubmitData($this->_fullUri);
        
        $postData = json_encode(array('vouchercode'=>$vouchercode,
                                      'aid'=>$aid,
                                      'trackingid'=>$trackingId,
                                      'source'=>$source));
        
        $response = $this->SubmitData($this->_URI, $postData);
        
        if($response[0] == 200){
            $this->_APIresponse = $this->json2Array($response[1]);
        } else {
            $this->_error = $response[0];
        }
        return $this->_APIresponse;
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
    public function useVoucher($aid, $trackingID, $vouchercode, $terminalID, 
                               $source, $siteID, $mid){
        $this->_URI = Mirage::app()->param['use_voucher'];
        
        if (!(bool)$this->IsAPIServerOK()) {
            $message = 'Can\'t connect to VMS System';
            logger($message . ' CouponCode=' . $vouchercode . ' AID='.$aid);
            self::throwError($message);
        }
        
//        $this->InitQueryString();
//        $this->_queryString = $this->_queryString.'aid='.$aid;
//        $this->_queryString = $this->_queryString.'&trackingid='.$trackingID;
//        $this->_queryString = $this->_queryString.'&vouchercode='.$vouchercode;
//        $this->_queryString = $this->_queryString.'&terminalid='.$terminalID;
//        $this->_queryString = $this->_queryString.'&source='.$source;
//        $this->_queryString = $this->_queryString.'&siteid='.$siteID;
//        $this->_queryString = $this->_queryString.'&mid='.$mid;
//        
//        $this->_fullUri = $this->_URI;
//        $this->_fullUri = $this->_fullUri.'/'.$this->_queryString;
//        
//        $response = $this->SubmitData($this->_fullUri);
        
        $postData = json_encode(array('aid'=>$aid,
                                      'trackingid'=>$trackingID,
                                      'vouchercode'=>$vouchercode,
                                      'terminalid'=>$terminalID,
                                      'source'=>$source,
                                      'siteid'=>$siteID,
                                      'mid'=>$mid));
        
        $response = $this->SubmitData($this->_URI, $postData);
        if($response[0] == 200){
            $this->_APIresponse = $this->json2Array($response[1]);
        } else {
            $this->_error = $response[0];
        }
                
        return $this->_APIresponse;
    } 
    
    private function SubmitData( $URI, $postdata )
    {
            $curl = curl_init( $URI );

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
        
    private function json2Array( $xmlString )
    {
        return json_decode( $xmlString, TRUE );
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

        if (isset($urlInfo[ 'scheme' ])  && $urlInfo[ 'scheme' ] == 'https')
        {
            $port = 443;
        }

        return common::isHostReachable( $this->_URI, $port );
    }
}

?>
