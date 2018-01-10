<?php

class HabaneroPlayerAPI {

    //Set caching of connection
    //@var boolean 
    private $_caching = 0;
    //Maximum number of seconds to wait while trying to connect
    //@var integer
    private $_connectionTimeout = 10;
    //Maximum number of seconds before a call timeouts
    //@var integer
    private $_timeout = 500;
    // Error message
    // @var string
    private $_error;
    // Holds array response
    // @var array
    private $_APIresponse;

    public function __construct($urlHabanero, $BrandID = '', $APIkey = '') {
        $this->_url = $urlHabanero;
        $this->_BrandID = $BrandID;
        $this->_APIKey = $APIkey;
    }

    public function UpdatePlayerPassword($Username, $NewPassword) {
        $jsonRequestBodyObject = array("BrandId" => $this->_BrandID, "APIKey" => $this->_APIKey, "Username" => $Username, "NewPassword" => $NewPassword);
        $data_string = json_encode($jsonRequestBodyObject);

        $result = $this->submitCurlRequest($this->_url . '/UpdatePlayerPassword', $data_string);

        //convert JSON string response to object
        //$updatepasswordmethodResult = json_decode($result);
        //return $updatepasswordmethodResult;

        if ($result[0] == 200) {
            $this->_APIresponse = $this->XML2Array($result[1]);
            $this->_APIresponse = array('updatepasswordmethodResult' => $this->_APIresponse);
        } else {
            $this->_error = "HTTP " . $result[0];
        }
        return $this->_APIresponse;
    }

    private function XML2Array($xmlString) {
        //$xml = simplexml_load_string($xmlString);
        //$json = json_encode($xml);
        //return json_decode($json, TRUE);
        return json_decode($xmlString, TRUE);
    }

    private function submitCurlRequest($url, $data) {
        //POST data to the JSON Web service URL
        //url format is <webservice url>/jsonapi/<methodname>        
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_FRESH_CONNECT, $this->_caching);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt( $ch, CURLOPT_POST, FALSE );
        //curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );

        $response = curl_exec($ch);

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        //return $response;
        return array($http_status, $response);
    }

}

?>
