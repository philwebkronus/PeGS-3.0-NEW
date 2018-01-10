<?php

/*
 * @author      Claire Marie C. Tamayo
 * @createdon   10/16/2017
 * @purpose     Base Class for calling the Player API of Habanero
 */

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

    public function __construct($URI, $brandID, $APIkey, $currencyCode) {
        $this->_url = $URI;
        $this->_brandID = $brandID;
        $this->_apiKey = $APIkey;
        $this->_currencyCode = $currencyCode;
    }

    public function HabaneroGetGames() {
        //get important fields in array then convert to JSON string.
        $jsonRequestBodyObject = array("BrandId" => $this->_brandID, "APIKey" => $this->_apiKey);
        $data_string = json_encode($jsonRequestBodyObject);

        $result = $this->submitCurlRequest($URI . '/getgames', $data_string);

        //convert JSON string response to object
        if ($result[0] == 200) {
            $this->_APIresponse = $this->XML2Array($result[1]);
            $this->_APIresponse = array('getgamesmethodResult' => $this->_APIresponse);
        } else {
            $this->_error = "HTTP " . $result[0];
        }
        return $this->_APIresponse;
    }

    //public function CreatePlayer($PlayerIP, $UserAgent, $Username, $Password)
    public function LoginOrCreatePlayer($PlayerIP, $UserAgent, $Username, $Password, $PlayerRank) {
        $KeepExistingToken = false;

        $jsonRequestBodyObject = array("BrandId" => $this->_brandID, "APIKey" => $this->_apiKey, "PlayerHostAddress" => $PlayerIP,
            "UserAgent" => $UserAgent, "KeepExistingToken" => $KeepExistingToken, "Username" => $Username, "Password" => $Password,
            "CurrencyCode" => $this->_currencyCode, "PlayerRank" => $intPlayerRank);

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

    public function DepositPlayerMoney($Username, $Password, $Amount, $RequestId) {
        $jsonRequestBodyObject = array("BrandId" => $this->_brandID, "APIKey" => $this->_apiKey, "Username" => $Username, "Password" => $Password,
            "CurrencyCode" => $this->_currencyCode, "Amount" => $Amount, "RequestId" => $RequestId);
        $data_string = json_encode($jsonRequestBodyObject);

        $result = $this->submitCurlRequest($this->_url . '/DepositPlayerMoney', $data_string);

        if ($result[0] == 200) {
            $this->_APIresponse = $this->XML2Array($result[1]);
            $this->_APIresponse = array('depositmethodResult' => $this->_APIresponse);
        } else {
            $this->_error = "HTTP " . $result[0];
        }
        return $this->_APIresponse;
    }

    public function WithdrawPlayerMoney($Username, $Password, $Amount, $RequestId) {
        $WithdrawAll = false;

        $jsonRequestBodyObject = array("BrandId" => $this->_brandID, "APIKey" => $this->_apiKey, "Username" => $Username, "Password" => $Password,
            "CurrencyCode" => $this->_currencyCode, "Amount" => $Amount, "WithdrawAll" => $WithdrawAll, "RequestId" => $RequestId);
        $data_string = json_encode($jsonRequestBodyObject);

        $result = $this->submitCurlRequest($this->_url . '/WithdrawPlayerMoney', $data_string);

        if ($result[0] == 200) {
            $this->_APIresponse = $this->XML2Array($result[1]);
            $this->_APIresponse = array('withdrawmethodResult' => $this->_APIresponse);
        } else {
            $this->_error = "HTTP " . $result[0];
        }
        return $this->_APIresponse;
    }

    public function QueryTransfer($RequestId) {
        $jsonRequestBodyObject = array("BrandId" => $this->_brandID, "APIKey" => $this->_apiKey, "RequestId" => $RequestId);
        $data_string = json_encode($jsonRequestBodyObject);

        $result = $this->submitCurlRequest($this->_url . '/QueryTransfer', $data_string);

        if ($result[0] == 200) {
            $this->_APIresponse = $this->XML2Array($result[1]);
            $this->_APIresponse = array('querytransmethodResult' => $this->_APIresponse);
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

    public function LogoutPlayer($Username, $Password) {
        $jsonRequestBodyObject = array("BrandId" => $this->_brandID, "APIKey" => $this->_apiKey, "Username" => $Username, "Password" => $Password);
        $data_string = json_encode($jsonRequestBodyObject);

        $result = $this->submitCurlRequest($this->_url . '/LogOutPlayer', $data_string);

        if ($result[0] == 200) {
            $this->_APIresponse = $this->XML2Array($result[1]);
            $this->_APIresponse = array('logoutplayermethodResult' => $this->_APIresponse);
        } else {
            $this->_error = "HTTP " . $result[0];
        }
        return $this->_APIresponse;
    }

    private function submitCurlRequest($url, $data) {

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

        $response = curl_exec($ch);

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return array($http_status, $response);
    }

    private function XML2Array($xmlString) {
        return json_decode($xmlString, TRUE);
    }

    public function GetError() {
        return $this->_error;
    }

}

?>