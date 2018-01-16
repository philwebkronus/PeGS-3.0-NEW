<?php

/*
 * @author      Claire Marie C. Tamayo
 * @createdon   10/16/2017
 * @purpose     Base Class for calling the Player API of Habanero
 */

class HabaneroPlayerAPI {

    //Web Service URL
    //private $_url = "https://ws-pw.insvr.com/jsonapi"; //OLD
    private $_url = "http://ws-pw.insvr.com/jsonapi"; //NEW
    //Designated BrandID for PW
    //private $_brandID = '88b4dd39-6190-486c-a8b4-1869fab61e58'; //OLD
    private $_brandID = 'e13b5650-16de-e711-a950-288023b998c7'; //NEW
    //Designated APIKey for PW
    //private $_apiKey = '57AFC170-2904-414A-8B32-1761E76E56E0'; //OLD
    private $_apiKey = '5BC838D5-E208-40CD-B378-EDA14B32E9A1'; //NEW
    //Currency Code [Philippine Peso]
    private $_currencyCode = 'PHP';
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

    private function XML2Array($xmlString) {
        //$xml = simplexml_load_string($xmlString);
        //$json = json_encode($xml);
        //return json_decode($json, TRUE);
        return json_decode($xmlString, TRUE);
    }

    //public function CreatePlayer($PlayerIP, $UserAgent, $Username, $Password)
    public function CreatePlayer($PlayerIP, $UserAgent, $Username, $Password, $PlayerRank) {
        $KeepExistingToken = false;

        $jsonRequestBodyObject = array("BrandId" => $this->_brandID, "APIKey" => $this->_apiKey, "PlayerHostAddress" => $PlayerIP,
            "UserAgent" => $UserAgent, "KeepExistingToken" => $KeepExistingToken, "Username" => $Username, "Password" => $Password,
            "CurrencyCode" => $this->_currencyCode, "PlayerRank" => $PlayerRank);
        $data_string = json_encode($jsonRequestBodyObject);

        $result = $this->submitCurlRequest($this->_url . '/LoginOrCreatePlayer', $data_string);

        if ($result[0] == 200) {
            $this->_APIresponse = $this->XML2Array($result[1]);
            $this->_APIresponse = array('createplayermethodResult' => $this->_APIresponse);
        } else {
            $this->_error = "HTTP " . $result[0];
        }
        return $this->_APIresponse;
    }

    public function UpdatePlayerPassword($Username, $NewPassword) {
        $jsonRequestBodyObject = array("BrandId" => $this->_brandID, "APIKey" => $this->_apiKey, "Username" => $Username, "NewPassword" => $NewPassword);
        $data_string = json_encode($jsonRequestBodyObject);

        $result = $this->submitCurlRequest($this->_url . '/UpdatePlayerPassword', $data_string);

        if ($result[0] == 200) {
            $this->_APIresponse = $this->XML2Array($result[1]);
            $this->_APIresponse = array('updatepasswordmethodResult' => $this->_APIresponse);
        } else {
            $this->_error = "HTTP " . $result[0];
        }
        return $this->_APIresponse;
    }

    public function QueryPlayer($Username, $Password) {
        $jsonRequestBodyObject = array("BrandId" => $this->_brandID, "APIKey" => $this->_apiKey, "Username" => $Username, "Password" => $Password);
        $data_string = json_encode($jsonRequestBodyObject);

        $result = $this->submitCurlRequest($this->_url . '/QueryPlayer', $data_string);

        if ($result[0] == 200) {
            $this->_APIresponse = $this->XML2Array($result[1]);
            $this->_APIresponse = array('queryplayermethodResult' => $this->_APIresponse);
        } else {
            $this->_error = "HTTP " . $result[0];
        }
        return $this->_APIresponse;
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

