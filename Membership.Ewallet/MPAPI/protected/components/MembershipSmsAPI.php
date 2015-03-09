<?php

/**
 * e-Games Casino Membership Wrapper
 * SMS Gateway APi
 * @version 1.0
 * @author elperez
 * @datecreated 08-28-13
 */
class MembershipSmsAPI {
    
    private $_apiUrl;
    private $_appId;
    private $_fullUri;
    private $_postData;
    
    public function __construct($apiUrl, $appId) {
        $this->_apiUrl = $apiUrl;
        $this->_appId = $appId;
        
        $this->_fullUri = $apiUrl;
    }
    
    /**
     * Sends SMS for every redemption of raffle coupon
     * @param str $mobileNo
     * @param str $msgTemplateID
     * @param str $couponNo
     * @param str $referenceNo
     * @param str $redeemedNo
     * @param str $trackingId
     * @return string
     */
    public function sendCouponRedemption($mobileNo, $msgTemplateID, $couponNo, 
            $referenceNo, $redeemedNo, $trackingId){
        
        $placeholderValues = array("COUPON_NO"=>$couponNo,"REF_NO"=>$referenceNo,
                                   "REDEEMED_NO"=>$redeemedNo);
        
        $requestParameters = array('app_id'=>$this->_appId,
                                   'to'=>$mobileNo,
                                   'messagetemplate_id'=>$msgTemplateID,
                                   'placeholder_values'=>$placeholderValues,
                                   'tracking_id'=>$trackingId);
        
        $this->_postData = json_encode($requestParameters);
        
        $this->_fullUri = $this->_apiUrl;
        
        $result = $this->submitData($this->_fullUri, $this->_postData);
        
        if($result[0] == 200){
            $response = $this->XML2Array($result[1]);
        } else {
            $response = "HTTP Error";
        }
        
        return $response;
    }
    
    /**
     *  Sends SMS for every redemption of reward coupon (item)
     *@author aqdepliyan 
     * @datecreated: 2013-09-13
     * @param type $mobileNo
     * @param type $msgTemplateID
     * @param type $serialcode
     * @param type $trackingId
     * @return string
     */
    public function sendItemRedemption($mobileNo, $msgTemplateID, $serialcode, $trackingId, $points){
        
    
        
        $placeholderValues = array("SERIAL_CODE"=>"$serialcode");
        
        $requestParameters = array('app_id'=>$this->_appId,
                                   'to'=>$mobileNo,
                                   'messagetemplate_id'=>$msgTemplateID,
                                   'placeholder_values'=>$placeholderValues,
                                   'tracking_id'=>$trackingId);
        
        $this->_postData = json_encode($requestParameters);
        
        $this->_fullUri = $this->_apiUrl;
        
        $result = $this->submitData($this->_fullUri, $this->_postData);
        
        
        
        if($result[0] == 200){
            $response = $this->XML2Array($result[1]);
        } else {
            $response = "HTTP Error";
        }
        
        return $response;
        
    }
    
     /**
     * Sends SMS for every successful registration
     *@author aqdepliyan
     *@datecreated: 2013-08-30
     * @param str $mobileNo
     * @param str $msgTemplateID
     * @param str $couponNo
     * @param str $referenceNo
     * @param str $redeemedNo
     * @param str $trackingId
     * @return string
     */
    public function sendRegistration($mobileNo,$msgTemplateID, $datecreated, $tempcode, $trackingId){
        $code = str_replace("eGames", "", $tempcode);
        $date = date("Y-m-d", strtotime($datecreated));
        $placeholderValues = array("DATE"=>$date,"CODE"=>$code);
        
        $requestParameters = array('app_id'=>$this->_appId,
                                   'to'=>$mobileNo,
                                   'messagetemplate_id'=>$msgTemplateID,
                                   'placeholder_values'=>$placeholderValues,
                                   'tracking_id'=>$trackingId);
        
                               
        
        $this->_postData = json_encode($requestParameters);
        
        
        
        
        $this->_fullUri = $this->_apiUrl;
        
        
        
        $result = $this->submitData($this->_fullUri, $this->_postData);
        
        if($result[0] == 200){
            $response = $this->XML2Array($result[1]);
        } else {
            $response = "HTTP Error";
        }
        
        return $response;
    }
    
    public function sendRegistrationBT($mobileNo,$msgTemplateID, $expiryDate, $couponNumber, $trackingId){
        //$code = str_replace("eGames", "", $tempcode);
        $date = date("Y-m-d", strtotime($expiryDate));
        $placeholderValues = array("DATE"=>$date,"VOUCHER_CODE"=>$couponNumber);
        
        $requestParameters = array('app_id'=>$this->_appId,
                                   'to'=>$mobileNo,
                                   'messagetemplate_id'=>$msgTemplateID,
                                   'placeholder_values'=>$placeholderValues,
                                   'tracking_id'=>$trackingId);
        
                               
        
        $this->_postData = json_encode($requestParameters);
        
        
        
        $this->_fullUri = $this->_apiUrl;
        
        
        
        $result = $this->submitData($this->_fullUri, $this->_postData);

        
        
        
        if($result[0] == 200){
            $response = $this->XML2Array($result[1]);
        } else {
            $response = "HTTP Error";
        }
        
//        var_dump($response);
        return $response;
    }
    
    
    private function submitData($url, $postdata){
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL , $url );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
        curl_setopt( $curl, CURLOPT_FRESH_CONNECT, 1 );
        curl_setopt( $curl, CURLOPT_FORBID_REUSE, 1 );
        curl_setopt( $curl, CURLOPT_HEADER, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_POST, TRUE );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $postdata );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt( $curl, CURLOPT_VERBOSE, TRUE );

        $response = curl_exec( $curl );

        $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

        curl_close( $curl );

        return array($http_status, $response);
    }
    
    private function XML2Array( $json )
    {
        return json_decode( $json, TRUE );
    }
}

?>
