<?php

class PCWSAPI{
    
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
    
    public function Deposit($cardnumber, $serviceid, $paymenttype, $amount, $siteid, $aid, $systemusername, $tracenumber, $referencenumber, $couponCode=null, $paymentTrackingID=null){
        
        $url = Mirage::app()->param['pcwsdeposit'];
        
        $syscode = empty(Mirage::app()->param['SystemCode'][$systemusername])?'':Mirage::app()->param['SystemCode'][$systemusername];
        $accessdate = date('Y-m-d H:i:s');
        $dt = date('YmdHis');
        $tkn = sha1($dt.$syscode);
        $amount = str_replace( ',', '', $amount );
        
        $postdata = json_encode(array('ServiceID'=>$serviceid, 'CardNumber'=>$cardnumber, 'Amount'=>$amount, 'PaymentType'=>$paymenttype, 'SiteID'=>$siteid, 'AID'=>$aid,
                                                                            'SystemUsername'=>$systemusername, 'AccessDate'=>$accessdate, 'Token'=>$tkn, 'TraceNumber'=>$tracenumber, 'ReferenceNumber'=>$referencenumber, 'CouponCode'=>$couponCode, 'PaymentTrackingID'=>$paymentTrackingID));
        
        $methodname = "Deposit";
        $data = print_r($postdata,true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata,$methodname,$tkn);
        
        return $result;
    }
    
    
//    public function Withdraw($cardnumber, $serviceid, $amount, $siteid, $aid, $systemusername, $idchecked, $csvalidated){
//        
//        $url = Mirage::app()->param['pcwswithdraw'];
//        
//        $syscode = empty(Mirage::app()->param['SystemCode'][$systemusername])?'':Mirage::app()->param['SystemCode'][$systemusername];
//        $accessdate = date('Y-m-d H:i:s');
//        $dt = date('YmdHis');
//        $tkn = sha1($dt.$syscode);
//        $amount = str_replace( ',', '', $amount );
//        
//        $postdata = json_encode(array('IDChecked'=> $idchecked , 'CSChecked' => $csvalidated, 'ServiceID'=>$serviceid, 'CardNumber'=>$cardnumber, 'Amount'=>$amount, 'SiteID'=>$siteid, 'AID'=>$aid, 
//            'SystemUsername'=>$systemusername, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
//       
//        $methodname = "Withdraw";
//        $data = print_r($postdata,true);
//        $message = "[$methodname] Input: $data";
//        logger($message, "Request", '', true);
//        $result = $this->SubmitData($url, $postdata,$methodname,$tkn);
//        
//        return $result;
//    }
    
        public function Withdraw($cardnumber, $serviceid, $amount, $siteid, $aid, $systemusername){
        
        $url = Mirage::app()->param['pcwswithdraw'];
        
        $syscode = empty(Mirage::app()->param['SystemCode'][$systemusername])?'':Mirage::app()->param['SystemCode'][$systemusername];
        $accessdate = date('Y-m-d H:i:s');
        $dt = date('YmdHis');
        $tkn = sha1($dt.$syscode);
        $amount = str_replace( ',', '', $amount );
        
        $postdata = json_encode(array('ServiceID'=>$serviceid, 'CardNumber'=>$cardnumber, 'Amount'=>$amount, 'SiteID'=>$siteid, 'AID'=>$aid, 
            'SystemUsername'=>$systemusername, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $methodname = "Withdraw";
        $data = print_r($postdata,true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata,$methodname,$tkn);
        
        return $result;
    }
    
    public function Lock($systemusername, $login,$serviceid){
        $url = Mirage::app()->param['pcwsforcelogout'];
        
        $syscode = empty(Mirage::app()->param['SystemCode'][$systemusername])?'':Mirage::app()->param['SystemCode'][$systemusername];
        $accessdate = date('Y-m-d H:i:s');
        $dt = date('YmdHis');
        $tkn = sha1($dt.$syscode);
        
        $postdata = json_encode(array('Login'=>$login,'SystemUsername'=>$systemusername,'ServiceID'=>$serviceid, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $methodname = "Lock";
        $data = print_r($postdata,true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata,$methodname,$tkn);
        
        return $result;
    }
    
    public function CheckPin($cardnumber, $pin, $systemusername){
        
        $url = Mirage::app()->param['pcwscheckpin'];
        
        $syscode = empty(Mirage::app()->param['SystemCode'][$systemusername])?'':Mirage::app()->param['SystemCode'][$systemusername];
        $accessdate = date('Y-m-d H:i:s');
        $dt = date('YmdHis');
        $tkn = sha1($dt.$syscode);
        
        $postdata = json_encode(array('CardNumber'=>$cardnumber, 'PIN'=>$pin, 'SystemUsername'=>$systemusername, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $methodname = "CheckPin";
        $data = print_r($postdata,true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata,$methodname,$tkn);
        
        return $result;
    }
    
    
    public function UnLock($systemusername, $terminalCode, $serviceID, $cardNumber){
        $url = Mirage::app()->param['pcwsunlock'];
        
        $syscode = empty(Mirage::app()->param['SystemCode'][$systemusername])?'':Mirage::app()->param['SystemCode'][$systemusername];
        $accessdate = date('Y-m-d H:i:s');
        $dt = date('YmdHis');
        $tkn = sha1($dt.$syscode);
        
        $postdata = json_encode(array('TerminalCode'=>$terminalCode,'ServiceID'=>$serviceID, 'CardNumber'=>$cardNumber,
            'SystemUsername'=>$systemusername, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $methodname = "Unlock";
        $data = print_r($postdata,true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata,$methodname,$tkn);
        
        return $result;
    }
    
    
    public function AddCompPoints($systemusername, $cardNumber, $siteid, $serviceID, $amount){
        $url = Mirage::app()->param['pcwsaddcomppoints'];
        $PointValue = Mirage::app()->param['conversion_value'];
        $EquivalentPoint = Mirage::app()->param['conversion_equivalentpoint'];
        $Points = ( $amount / $PointValue) * $EquivalentPoint;
        
        $syscode = empty(Mirage::app()->param['SystemCode'][$systemusername])?'':Mirage::app()->param['SystemCode'][$systemusername];
        $accessdate = date('Y-m-d H:i:s');
        $dt = date('YmdHis');
        $tkn = sha1($dt.$syscode);
        
        $postdata = json_encode(array('CardNumber'=>$cardNumber,'ServiceID'=>$serviceID, 'SiteID'=>$siteid, 'Amount'=>$Points,
            'SystemUsername'=>$systemusername, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $methodname = "AddCompPoints";
        $data = print_r($postdata,true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata,$methodname,$tkn);
        
        return $result;
    }
    /**
     * Create Session API Wrapper
     * @author Mark Kenneth Esguerra
     * @date May 12, 2015
     * 
     */
    public function CreateSession($systemusername, $terminalCode, $serviceID, $cardNumber) {
        $url = Mirage::app()->param['pcwscreatesession'];
        
        $syscode = empty(Mirage::app()->param['SystemCode'][$systemusername])?'':Mirage::app()->param['SystemCode'][$systemusername];
        $accessdate = date('Y-m-d H:i:s');
        $dt = date('YmdHis');
        $tkn = sha1($dt.$syscode);
        
        $postdata = json_encode(array('TerminalCode'=>$terminalCode,'ServiceID'=>$serviceID, 'CardNumber'=>$cardNumber,
            'SystemUsername'=>$systemusername, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $methodname = "Create Session";
        $data = print_r($postdata,true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata,$methodname,$tkn);
        
        return $result;
    }
      
     /**
     * Change Password API Call
     * @author John Aaron Vida
     * @date June 02, 2016
     * 
     */
    public function ChangePassword($systemusername, $login, $serviceID, $usermode, $source) {
        $url = Mirage::app()->param['pcwschangepassword']; 
        
        $syscode = empty(Mirage::app()->param['SystemCode'][$systemusername])?'':Mirage::app()->param['SystemCode'][$systemusername];
        $accessdate = date('Y-m-d H:i:s');
        $dt = date('YmdHis');
        $tkn = sha1($dt.$syscode);
        
        $postdata = json_encode(array('Login'=>$login,'ServiceID'=>$serviceID, 'Usermode'=>$usermode,'Source'=>$source,
            'SystemUsername'=>$systemusername, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $methodname = "Change Password";
        $data = print_r($postdata,true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata,$methodname,$tkn);
        
        return $result;
    } 
    
    private function SubmitData( $uri, $postdata,$methodname,$token)
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
            //curl_setopt( $curl, CURLOPT_SSLVERSION, 3 );
            curl_setopt( $curl, CURLOPT_SSL_CIPHER_LIST, 'TLSv1' );
            
            // Data+Files to be posted
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
            $response = curl_exec( $curl );

            $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

            curl_close( $curl );
            
            $data = print_r($response,true);
            $message = "[$methodname] Token: $token Output: $data";
            logger($message, "Response", '', true);

            return array( $http_status, $response );
    }
}
?>
