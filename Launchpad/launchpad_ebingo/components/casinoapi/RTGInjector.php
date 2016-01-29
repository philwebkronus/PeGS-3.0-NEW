<?php
Yii::import('application.modules.launchpad.components.casinoapi.AbstractCasino');
/**
 * API for RTG
 * @package application.modules.launchpad.components.casinoapi
 * @author bryan
 */
class RTGInjector extends AbstractCasino
{
    /**
     * Set caching of connection
     * @var boolean 
     */
    private $_caching = 0;
    
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
     * Certificate key file passphrase
     * @var string
     */
    private $_passPhrase = '';    
    
    /**
     * Actual response from webservice (xml format)
     * @var string 
     */
    private $_actual_response;
    
    private $_pid = null;
    private $_hash_password = null;
    private $_session_id = null;
    private $_status;
    private $_transactionID = null;

    /**
     *
     * @var RTGInjector 
     */
    private static $_instance = null;
    
    /**
     * @var array 
     */
    private $_configuration = array();

    /**
     * Constructor
     */
    private function __construct() {}
    
    /**
     *
     * @return RTGInjector 
     */
    public static function app() 
    {
        if(self::$_instance == null)
            self::$_instance = new RTGInjector();
        return self::$_instance;
    }
    
    /**
     *
     * @param string $account
     * @param string|int $amount
     * @param string $tracking1
     * @param string $tracking2
     * @param string $tracking3
     * @param string $tracking4
     * @return string|bool 
     */
    public function deposit($account,$amount, $lpTransactionID, $tracking1,$tracking2,$tracking3,$tracking4, $terminal_pwd = '')
    {
        if($tracking1 === null)
            throw new CException("Please set tracking1 in deposit rtg");
        if($tracking2 === null)
            throw new CException("Please set tracking2 in deposit rtg");
        if($tracking3 === null)
            throw new CException("Please set tracking3 in deposit rtg");
        if($tracking4 === null)
            throw new CException("Please set tracking4 in deposit rtg");        
            
        if(strpos($amount, '.') == false) {
            $amount = number_format($amount,2);
        }
        
        if(!isset($this->_session_id[$account]))
            $this->_login($account);
        
        $data = array(
            'casinoID'=>1,
            'PID'=>$this->_pid[$account],
            'methodID'=>$this->_configuration['deposit_method_id'],
            'amount'=>$amount,
            'tracking1'=>$tracking1,
            'tracking2'=>$tracking2,
            'tracking3'=>$tracking3,
            'tracking4'=>$tracking4,
            'sessionID'=>$this->_session_id[$account],
            'userID'=>0,
            'skinID'=>'1',
        );
        
        $response = $this->XML2Array($this->_sendRequest($this->_configuration['service_api'].'/DepositGeneric',$data));
        if(!isset($response['transactionStatus']))
            return 'TRANSACTIONSTATUS_DISAPPROVED';
        $this->_transactionID = $response['transactionID'];
        return $this->_status = $response['transactionStatus'];
    }
    
    /**
     *
     * @param type $default
     * @return type 
     */
    public function getTransactionID($default=null) 
    {
        if($this->_transactionID == null)
            return $default;
        return $this->_transactionID;
    }
    
    /**
     *
     * @param string $account
     * @param string|int $amount
     * @param string $tracking1
     * @param string $tracking2
     * @param string $tracking3
     * @param string $tracking4
     * @return string|bool 
     */
    public function withdraw($account,$amount, $lpTransactionID, $tracking1,$tracking2,$tracking3,$tracking4, $terminal_pwd = '')
    {
        if($tracking1 === null)
            throw new CException("Please set tracking1 in withdraw rtg");
        if($tracking2 === null)
            throw new CException("Please set tracking2 in withdraw rtg");
        if($tracking3 === null)
            throw new CException("Please set tracking3 in withdraw rtg");
        if($tracking4 === null)
            throw new CException("Please set tracking4 in withdraw rtg");          
        
        if(strpos($amount, '.') == false) {
            $amount = number_format($amount,2);
        }        
        
        if(!isset($this->_session_id[$account]))
            $this->_login($account);   
        
        $data = array(
            'casinoID'=>1,
            'PID'=>$this->_pid[$account],
            'methodID'=>  $this->_configuration['withdraw_method_id'],
            'amount'=>$amount,
            'tracking1'=>$tracking1,
            'tracking2'=>$tracking2,
            'tracking3'=>$tracking3,
            'tracking4'=>$tracking4,
            'sessionID'=>$this->_session_id[$account],
            'userID'=>0,
            'skinID'=>'1',
        );
        
        $response = $this->XML2Array($this->_sendRequest($this->_configuration['service_api'].'/WithdrawGeneric',$data));
        
        if(!isset($response['transactionStatus']))
            return 'TRANSACTIONSTATUS_DISAPPROVED';
        $this->_transactionID = $response['transactionID'];
        return $this->_status = $response['transactionStatus'];
    }
    
    /**
     * Get balance of account
     * @param string $account
     * @return string|bool false if cant get balance
     */
    public function getBalance($account)
    {
        $this->_checkConfiguration();
        $this->_pid[$account] = $this->_getPIDFromLogin($account);
        $data = array('casinoID'=>1,'PID'=>$this->_pid[$account],'forMoney'=>'1');
        $response = $this->XML2Array($this->_sendRequest($this->_configuration['service_api'].'/GetAccountBalance',$data));
        if(isset($response['balance'])) {
            $this->_status = true;
            return $response['balance'];
        }
            
        return false;
    }
    
    /**
     * Set configuration
     * @param array $configuration
     * @return array 
     */
    public function setConfiguration(array $configuration)
    {
        return $this->_configuration = $configuration;
    }
    
    /**
     * 
     * @param string $account
     * @return string session id
     */
    private function _login($account) 
    {
        if(!isset($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = 'localhost';
        }
        
        if(!isset($this->_pid[$account]))
            $this->_getPIDFromLogin ($account);
        
        if(!isset($this->_hash_password[$account]))
            $this->_getAccountInfoByPID($account);
        
        $data = array(
            'casinoID'=>1,
            'PID'=>$this->_pid[$account],
            'hashedPassword'=>$this->_hash_password[$account],
            'forMoney'=>1,
            'IP'=>$_SERVER['HTTP_HOST'],
            'skinID'=>'1'
        );
        
        $response = $this->XML2Array($this->_sendRequest($this->_configuration['service_api'].'/Login', $data));
        if(!isset($response[0])) 
            throw new CException("Can't get LoginResult");
        
        return $this->_session_id[$account] = $response[0];
    }
    
    /**
     *
     * @param string $account
     * @return string 
     */
    private function _getPIDFromLogin($account)
    {
        $this->_certFilePath = $this->_configuration['RTGClientCertsPath'];
        $this->_keyFilePath = $this->_configuration['RTGClientKeyPath'];
        $data = array('login'=>$account);
        $response = $this->XML2Array($this->_sendRequest($this->_configuration['service_api'].'/GetPIDFromLogin',$data));
        if(!isset($response[0]))
            throw new CException("Can't get GetPIDFromLoginResult");
        
        return $this->_pid[$account] = $response[0];
    }
    
    /**
     *
     * @param string $account
     * @return string 
     */
    private function _getAccountInfoByPID($account)
    {
        $data = array('casinoID'=>1,'PID'=>  $this->_pid[$account]);
        $response = $this->XML2Array($this->_sendRequest($this->_configuration['service_api'].'/GetAccountInfoByPID',$data));
        if(!isset($response['password'])) 
            throw new CException("Can't get GetAccountInfoByPID");
        return $this->_hash_password[$account] = sha1($response['password']);
    }
    
    /**
     * Convert xml to array
     * @param string $xmlString
     * @return array 
     */
    private function XML2Array($xmlString)
    {
        if($xmlString[0] != '<') {
            return '';
        }
        
        try{
            $xml = simplexml_load_string($xmlString);
        } catch(Exception $e) {
            die('test');
        }
        
        $json = json_encode($xml);
        return json_decode($json, TRUE);
    }    
    
    /**
     *
     * @param string $url
     * @param array $data
     * @return string 
     */
    private function _sendRequest($url,array $data)
    {
        $curl = curl_init( $url . '?' . http_build_query($data) );
        curl_setopt( $curl, CURLOPT_FRESH_CONNECT, $this->_caching );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout);
        curl_setopt( $curl, CURLOPT_TIMEOUT, $this->_timeout );
        curl_setopt( $curl, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_SSLCERTTYPE, 'PEM' );
        curl_setopt($curl, CURLOPT_SSLCERT, $this->_certFilePath);
        curl_setopt( $curl, CURLOPT_SSLKEYTYPE, 'PEM' );
        curl_setopt( $curl, CURLOPT_SSLKEY, $this->_keyFilePath );
        curl_setopt( $curl, CURLOPT_SSLKEYPASSWD, $this->_passPhrase);
        curl_setopt( $curl, CURLOPT_POST, FALSE );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        $response = curl_exec( $curl );
        $this->_actual_response = $response;
        
        $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
        curl_close( $curl );
        return $response;
    }
    
    /**
     * Get actual result from curl
     * @return string
     */
    public function getActualResponse() 
    {
        return $this->_actual_response;
    }
    
    /**
     * Get status from deposit or withdraw. This is better for checking of result
     * @return bool true if TRANSACTIONSTATUS_APPROVED else false 
     */
    public function getGenericStatus() {
        if($this->_status == 'TRANSACTIONSTATUS_APPROVED')
            return true;
        
        return false;
    }
    
    /**
     * Check configuration for rtg
     * @return bool 
     */
    protected function _checkConfiguration()
    {
        if(!isset($this->_configuration['RTGClientCertsPath']))
            throw new CException('Please set "RTGClientCertsPath" in configuration');
        
        if(!is_file($this->_configuration['RTGClientCertsPath']))
            throw new CException('Certificate not found. "'.$this->_configuration['RTGClientCertsPath'].'"');  
        
        if(!isset($this->_configuration['RTGClientKeyPath']))
            throw new CException('Please set "RTGClientKeyPath" in configuration');    
        
        if(!is_file($this->_configuration['RTGClientKeyPath']))
            throw new CException('Key file not found. "'.$this->_configuration['RTGClientKeyPath'].'"');  
        
        if(!isset($this->_configuration['service_api']))
            throw new CException('Please set "service_api" in configuration');
        
        if(!isset($this->_configuration['deposit_method_id']))
            throw new CException('Please set "deposit_method_id" in configuration');
        
        if(!isset($this->_configuration['withdraw_method_id']))
            throw new CException('Please set "withdraw_method_id" in configuration');
        
        return true;
    }
}