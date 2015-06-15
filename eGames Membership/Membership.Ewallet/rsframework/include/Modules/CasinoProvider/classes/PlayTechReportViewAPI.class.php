<?php
#Name: PlayTechReportViewAPI.class.php
#Author: WEBiTS
#Version: 1.0.0
#Copyright 2013 PhilWeb Corporation

class PlayTechReportViewAPI {
    const VERSION = '1.0.0';

    /**
    * Set caching of connection
    * @var boolean
    */
    private $_caching = FALSE;

    /**
    * User agent
    * @var string
    */
    private $_userAgent = 'WEBiTS';

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
    * Holds the web service end point
    * @var string
    */
    private $_URI;

    private $_casino = '';

    private $_admin = '';
    private $_password = '';

    function __construct( $URI, $casino, $admin, $password, $userAgent = '' ) {
        if ( $URI ) {
            $this->_URI = $URI;
        } else {
            throw new Exception( '$URI not defined' );
        }

        if ( $casino ) {
            $this->_casino = $casino;
        } else {
            throw new Exception( '$casino not defined' );
        }

        if ( $admin ) {
            $this->_admin = $admin;
        } else {
            throw new Exception( '$admin not defined' );
        }

        if ( $password ) {
            $this->_password = $password;
        } else {
            throw new Exception( '$password not defined' );
        }

        $this->_userAgent = $userAgent;
    }

    public function export( $reportCode, $action, $params ) {
        $fullUri = $this->_URI . '/report_view.php?casino=' . $this->_casino .
                '&action=' . $action .
                '&reportcode=' . $reportCode .
                '&admin=' . $this->_admin .
                '&password=' . $this->_password;

        foreach ( $params as $key => $value ) {
            $fullUri = $fullUri . "&" . $key . "=" . $value;
        }

        $result = $this->SubmitData( $fullUri );
        
        if($result[0] == 200){

        if (strpos($result[1], 'ERROR') !== FALSE){
            $response = $result[1];
        }
        else{
            $arrresponse = $this->XML2Array($result[1]);
            
            if(isset($arrresponse['row']['column'])){
                $response = array("Email"=>$arrresponse['row']['column'][0],
                                  "PlayerCode"=>$arrresponse['row']['column'][1],
                                  "VipLevel"=>$arrresponse['row']['column'][2],
                                  "Frozen"=>$arrresponse['row']['column'][3],
                                  "LanguageCode"=>$arrresponse['row']['column'][4],
                                  "AdvertiserName"=>$arrresponse['row']['column'][5],
                                  "CountryName"=>$arrresponse['row']['column'][6],
                                  "Firstname"=>$arrresponse['row']['column'][7],
                                  "Lastname"=>$arrresponse['row']['column'][8]);
            } else {
                $response = "Invalid username";
            }
        }
    
            
        } else {
            $response = "HTTP Error ".$response[0];
        }
        
        return $response;
    }

    private function SubmitData( $URI )
    {
        $curl = curl_init( $URI );

        curl_setopt( $curl, CURLOPT_FRESH_CONNECT, $this->_caching );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout );
        curl_setopt( $curl, CURLOPT_TIMEOUT, $this->_timeout );
        curl_setopt( $curl, CURLOPT_USERAGENT, $this->_userAgent );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_POST, FALSE );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );

        $response = curl_exec( $curl );

        $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

        curl_close( $curl );

        return array( $http_status, $response );
    }
    
    private function XML2Array($xmlString) {
        $xml = simplexml_load_string($xmlString);

        $json = json_encode($xml);

        return json_decode($json, TRUE);
    }
}

?>