<?php

require_once '../models/LPConfig.php';
include_once '../Helper/Logger.class.php';

Class MzapiWrapper {

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

    /*
     * Required variables to call MZ API 
     */
    private $_tkn;
    private $_username;
    private $_logpath;

    public function __construct() {

        $this->_accessdate = date("Y-m-d H:i:s");
        $this->_logpath = LPConfig::app()->params['logpath'];
    }

    public function transferWallet($TerminalCode, $ServiceID, $Usermode) {
        $postdata = json_encode(array('TerminalCode' => $TerminalCode, 'ServiceID' => $ServiceID, 'Usermode' => $Usermode));

        $logger = new Logger($this->_logpath);
        $methodname = "TransferWallet";
        $data = print_r($postdata, true);
        $message = "[$methodname] Input: $data";
        $logger->mzapirequestlog($message, "Request");

        $result = $this->curlApi(LPConfig::app()->params['uriTransferWallet'], $postdata, $methodname);

        $decode = json_decode($result[1], true);
        return $decode['TransferWallet'];
    }

    public function curlApi($uri, $postdata, $methodname) {
        $curl = curl_init($uri);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, $this->_caching);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        // Data+Files to be posted
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $logger = new Logger($this->_logpath);
        $data = print_r($response, true);

        $result = $this->checkResponse($data);

        $message = "[$methodname] AccessDate: $this->_accessdate, Output: $result";
       $logger->mzapirequestlog($message, "Response");

        return array($http_status, $response);
    }

    public function checkResponse($data) {
        $obj = json_decode($data);
        if ($obj === null) {
            $pattern = "/<p class=\"message\">([\w\W]*?)<\/p>/";
            preg_match($pattern, $data, $matches);
            $result = $matches[1];
            return trim($result);
        }
        return $data;
    }

}
?>


