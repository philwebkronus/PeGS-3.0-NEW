<?php

/**
 * Wrapper of RTG  Player API using PHP SOAP Client Method
 * @date 02-21-14
 * @author elperez
 */
class RealtimeGamingWCFPlayerAPI {

    /**
     * Holds the web service end point
     * @var string
     */
    private $_url = '';

    /**
     * Set caching of connection
     * @var boolean 
     */
    private $_caching = 0;

    /**
     * User agent
     * @var string
     */
    private $_userAgent = 'PEGS Station Manager';

    /**
     * Path to certificate file
     * @var string
     */
    private $_certFilePath = '';

    /**
     * Path to certificate key file
     * @var string
     */
    private $_keyFilePath = '';

    /**
     * Path to combined certificate + key file
     * @var string 
     */
    private $_cert_keyFilePath = '';

    /**
     * Certificate key file passphrase
     * @var string
     */
    private $_passPhrase = '';

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
     * Error message
     * @var string
     */
    private $_error;

    /**
     * Holds array response
     * @var array
     */
    private $_APIresponse;

    public function __construct() {
        $argv = func_get_args();

        switch (func_num_args()) {
            default:
            case 4: self::__construct1($argv[0], $argv[1], $argv[2]);
                break;
        }
    }

    public function __construct1($url, $certFilePath, $passPhrase = '') {
        $this->_url = $url;
        $this->_cert_keyFilePath = $certFilePath;
        $this->_passPhrase = $passPhrase;
    }

    public function getError() {
        return $this->_error;
    }

    /**
     * Change player clasification (0-New Player, 1-High Roller)
     * @param str $pid
     * @param int $playerClassID
     * @param int $userID
     * @return object | array api response
     */
    public function changePlayerClasification($pid, $playerClassID) {

        $data = array('PID' => $pid, 'playerClassID' => $playerClassID, 'UserID' => 0);

        $method = 'ChangePlayerClass';

        $response = $this->submitRequest($this->_url, $data, $method);

        if (is_object($response)) {
            $this->_APIresponse = $this->XML2Array($response);
        } else {
            $this->_error = "Bad request. Check if API configurations are correct.";
        }

        return $this->_APIresponse;
    }

    /**
     * submit request via SOAP method in PHP
     * @param str $url
     * @param array $data
     * @param str $method
     * @return object | array api response
     */
    private function submitRequest($url, $data, $method) {
        header('Content-Type: text/plain');

        $soapArr = array(
            'trace' => true,
            'exceptions' => true,
            'local_cert' => $this->_cert_keyFilePath, //certificate folder
            'passphrase' => '',
            'ssl_method' => 'SOAP_SSL_METHOD_TLS'
        );

        $response = array();
        try {
            $client = new SoapClient($url, $soapArr);

            $response = $client->$method($data);
        } catch (Exception $e) {
            $this->_error = "Bad request. Check if API configurations are correct";
        }

        return $response;
    }

    /**
     * Formats a XML string to convert into an array
     * @param type $xmlString
     * @return type
     */
    private function XML2Array($xmlString) {
        $json = json_encode($xmlString);

        return json_decode($json, TRUE);
    }

}

?>
