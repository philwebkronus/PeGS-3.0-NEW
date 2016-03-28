<?php
/**
 * Description of PCWSAPI
 *
 * @author mcatangan
 * @Date 10/02/2015
 * @Purpose For Comp Points Integration
 */
class PCWSAPI {
        
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
    
    public function AddCompPoints($systemusername, $cardNumber, $siteid, $serviceID, $amount){
        $url = Yii::app()->param['pcwsaddcomppoints'];
        $PointValue = Yii::app()->param['conversion_value'];
        $EquivalentPoint = Yii::app()->param['conversion_equivalentpoint'];
        $Points = ( $amount / $PointValue) * $EquivalentPoint;
        
        $syscode = empty(Yii::app()->param['SystemCode'][$systemusername])?'':Yii::app()->param['SystemCode'][$systemusername];
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
            curl_setopt( $curl, CURLOPT_SSLVERSION, 3 );
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
