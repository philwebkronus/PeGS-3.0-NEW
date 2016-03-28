<?php
$path = Yii::getPathOfAlias('application.vendors.nusoap');
require_once $path . '/nusoap.php';
Yii::import('application.modules.launchpad.components.casinoapi.AbstractCasino');
Yii::import('application.vendors.nusoap.nusoap');

/**
 * API for MG
 * @package application.modules.launchpad.components
 * @author Bryan Salazar
 */
class MGInjector extends AbstractCasino{
    
    private $_actual_response;
    
    private $_status;
    /**
     *
     * @var wsdl
     */
    private $_wsdl = null;
    
    /**
     *
     * @var nusoap_client 
     */
    private $_client = null;
    
    /**
     *
     * @var MGInjector 
     */
    private static $_instance = null;
    
    /**
     * @var array 
     */
    private $_configuration = array();   
    
    /**
     *
     * @var int 
     */
    private $_transactionID = null;
    
    private function __construct() {}
    
    /**
     * Get instance of MGInjector
     * @return MGInjector 
     */
    public static function app()
    {
        if(self::$_instance == null)
            self::$_instance = new MGInjector();
        return self::$_instance;
    }
    
    /**
     * Get transaction ID from webservice
     * @param int|string $default
     * @return int|string 
     */
    public function getTransactionID($default = null) 
    {
        if($this->_transactionID == null)
            return $default;
        return $this->_transactionID;
    }
    
    /**
     * Deposit
     * @param string $account
     * @param string|int $amount
     * @return string 'true' or 'false'
     */
    public function deposit($account, $amount, $tracking1=null, $tracking2=null, $tracking3=null, $tracking4=null)
    {
        $amount=str_replace(',', '', $amount);
        $response = $this->_client->call('Deposit',array('accountNumber'=>$account,'amount'=>$amount,'currency'=>$this->_configuration['currency']));
        $this->_actual_response = $response;
        if(!isset($response['DepositResult']['IsSucceed']))
            return 'false';
        $this->_transactionID = $response['DepositResult']['TransactionId'];
        return $this->_status = $response['DepositResult']['IsSucceed'];
    }
    
    /**
     * Get actual response from soap
     * @return type 
     */
    public function getActualResponse() 
    {
        return $this->_actual_response;
    }
    
    /**
     * Get account balance
     * @param string $account
     * @return string balance 
     */
    public function getBalance($account)
    {
        $this->_generateSoap();
        $response =  $this->_client->call( 'GetAccountBalance', array( 'delimitedAccountNumbers' =>$account) );  
        $this->_actual_response = $response;
        if(!isset($response['GetAccountBalanceResult']['BalanceResult']['Balance']))
            return false;
        $this->_status = true;
        return $response['GetAccountBalanceResult']['BalanceResult']['Balance'];
    }
    
    /**
     * Use this if you want to determine if successfully deposit or withdraw
     * @return bool true or false status in withdraw or deposit 
     */
    public function getGenericStatus() 
    {
        return $this->_status;
    }
    
    /**
     * 
     * @param array $configuration configuration for api
     * @return array configuration 
     */
    public function setConfiguration(array $configuration)
    {
        return $this->_configuration = $configuration;
    }
    
    /**
     * Withdraw
     * @param string $account
     * @param string|amount $amount
     * @param string $tracking1 not required no purpose at the meantime
     * @param string $tracking2 not required no purpose at the meantime
     * @param string $tracking3 not required no purpose at the meantime
     * @param string $tracking4 not required no purpose at the meantime
     * @return string 'true' or 'false' 
     */
    public function withdraw($account, $amount, $tracking1=null, $tracking2=null, $tracking3=null, $tracking4=null) 
    {
        $amount=str_replace(',', '', $amount);
        $response = $this->_client->call( 'Withdrawal', array( 'accountNumber' => $account, 'amount'=>$amount));
        $this->_actual_response = $response;
        if(!isset($response['WithdrawalResult']['IsSucceed']))
            return 'false';
        
        $this->_transactionID = $response['WithdrawalResult']['TransactionId'];
        return $response['WithdrawalResult']['IsSucceed'];
    }
    
    /**
     * Check if all configuration are all set
     * @return bool true or throw CException 
     */
    protected function _checkConfiguration() 
    {
        if(!isset($this->_configuration['service_api']))
            throw new CException('Please set "service_api" in configuration');
        
        if(!isset($this->_configuration['session_guid']))
            throw new CException('Please set "session_guid" in configuration');    
        
        if(!isset($this->_configuration['currency']))
            throw new CException('Please set "currency" in configuration');          
        
        return true;
    }
    
    /**
     * Helper method to create soap header
     */
    private function _generateSoap()
    {
        if($this->_client == null) {
            $this->_checkConfiguration();
            $this->_wsdl = new wsdl( $this->_configuration['service_api'], '', '', '', '', 5 );
            $this->_client = new nusoap_client( $this->_wsdl, 'wsdl' );
            
            if(!isset($_SERVER[ 'HTTP_HOST' ])) {
                $_SERVER[ 'HTTP_HOST' ] = 'localhost';
            }
            
            $header = '
            <AgentSession xmlns="https://entservices.totalegame.net">
                <SessionGUID>' . $this->_configuration['session_guid'] . '</SessionGUID>
                <IPAddress>' . $_SERVER[ 'HTTP_HOST' ] . '</IPAddress>
                <IsLengthenSession>1</IsLengthenSession>
            </AgentSession>
            ';
            $this->_client->setHeaders( $header );
        }
    }
}