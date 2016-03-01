<?php
/**
 * This is a base class for MG API Calls
 * @version MGCAPI
 * @author elperez
 * Created on : May 1, 2012
 */

Yii::import('application.modules.launchpad.components.casinoapi.AbstractCasino');
Yii::import('application.modules.launchpad.components.casinoapi.Array2XML');

class MGInjectorCapi extends AbstractCasino{
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
     * Actual response from webservice (xml format)
     * @var string 
     */
    private $_actual_response;
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
    
    /***
     * CAPI
     */
    /**
     * Error message
     * @var string
     */
    private $_error;

    /**
     * Holds the ticket ID for each request
     * @var string
     */
    private $_ticketID;
    
    private function __construct() {}
    
    /**
     * Get instance of MGInjector
     * @return MGInjector 
     */
    public static function app()
    {
        if(self::$_instance == null)
            self::$_instance = new MGInjectorCapi();
        return self::$_instance;
    }
    
    public function setTicketID( $ticketID )
    {
        $this->_ticketID = $ticketID;
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
     * Get account balance
     * @param string $account
     * @return string balance 
     */
    public function getBalance($account)
    {
        $this->_checkConfiguration();
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'GetBalance',
                'LayerName' => $this->_configuration['mgcapi_playername'],
                'ServerID' => $this->_configuration['mgcapi_serverid'] ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_configuration['mgcapi_username'],
                'Password' => $this->_configuration['mgcapi_password'],
                'TicketID' => $this->_ticketID,
                'ServerID' => $this->_configuration['mgcapi_serverid'] ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $account ) )
                )
            )
        );
        
        $response = $this->ProcessXMLRequest( $xmlData );
        $this->_actual_response = $response;
        if(!isset($response[ "Result" ][ "Returnset" ][ "Balance" ][ "@attributes" ][ "Value" ]))
            return false;
        $this->_status = true;
        return $response[ "Result" ][ "Returnset" ][ "Balance" ][ "@attributes" ][ "Value" ] / 100 ;
    }
    
    /**
     * formats money amount
     * @param float $money
     * @return int 
     */
    private function removeComma($money) {
        return str_replace(',', '', $money);
    }


    /**
     * For guest players
     * @param string $loginName
     * @param string $amount
     * @param string $eventID
     * @return array API Response 
     */
    protected function ChangeBalance( $loginName, $password, $amount, $transactionID, $eventID )
    {
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'ChangeBalance',
                'LayerName' => $this->_configuration['mgcapi_playername'],
                'ServerID' => $this->_configuration['mgcapi_serverid'] ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_configuration['mgcapi_username'],
                'Password' => $this->_configuration['mgcapi_password'],
                'TicketID' =>  $transactionID,
                'ServerID' => $this->_configuration['mgcapi_serverid'] ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) ),
                'Amount' => array( '@attributes' => array( 'Value' => $amount ) )
                )
            )
        );

        $response = $this->ProcessXMLRequest( $xmlData );
        
        if ( !$this->GetError() )
        {
            if ( is_array( $response ) )
            {
                if ( $response["Result"]["@attributes"]["Success"] == 1 )
                {
                    return array
                    (
                        'IsSucceed' => true,
                        'ErrorCode' => 0,
                        'ErrorMessage' => null,
                        'TransactionInfo' => array
                            (
                                'TransactionAmount' => abs($amount / 100),
                                'TransactionId' =>  $transactionID,
                                'TransactionStatus'=> 'true',
                                'Balance' => $response[ "Result" ][ "Returnset" ][ "Balance" ][ "@attributes" ][ "Value" ] / 100
                            )
                    );
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => "(" . $response[ "Result" ][ "Returnset" ][ "ErrorCode" ][ "@attributes" ][ "Value" ] . ") " . $response[ "Result" ][ "Returnset" ][ "Error" ][ "@attributes" ][ "Value" ] );
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: ' . $this->GetError() );
        }
        
    }
    
    /**
     * For real players, used in staging server of MG
     * @param type $loginName
     * @param type $password
     * @param type $amount
     * @param type $transactionID
     * @param type $eventID
     * @return type 
     */
    public function ChangeBalanceEvents( $loginName, $password, $amount, $transactionID, $eventID )
    {
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'ChangeBalanceEvents',
                'LayerName' => $this->_configuration['mgcapi_playername'],
                'ServerID' => $this->_configuration['mgcapi_serverid'] ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_configuration['mgcapi_username'],
                'Password' => $this->_configuration['mgcapi_password'],
                'TicketID' =>  $transactionID,
                'ServerID' => $this->_configuration['mgcapi_serverid'] ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) ),
                'Password' => array( '@attributes' => array( 'Value' => $password ) ),
                'Amount' => array( '@attributes' => array( 'Value' => $amount ) ),
                'TransactionID' => array( '@attributes' => array( 'Value' => $transactionID ) ),
                'EventID' => array( '@attributes' => array( 'Value' => $eventID ) )
                )
            )
        );

        $response = $this->ProcessXMLRequest( $xmlData );
        
        if ( !$this->GetError() )
        {
            if ( is_array( $response ) )
            {
                if ( $response["Result"]["@attributes"]["Success"] == 1 )
                {
                    return array
                    (
                        'IsSucceed' => true,
                        'ErrorCode' => 0,
                        'ErrorMessage' => null,
                        'TransactionInfo' => array
                            (
                                'TransactionAmount' => abs($amount / 100),
                                'TransactionId' =>  $transactionID,
                                'TransactionStatus'=> 'true',
                                'Balance' => $response[ "Result" ][ "Returnset" ][ "Balance" ][ "@attributes" ][ "Value" ] / 100
                            )
                    );
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => "(" . $response[ "Result" ][ "Returnset" ][ "ErrorCode" ][ "@attributes" ][ "Value" ] . ") " . $response[ "Result" ][ "Returnset" ][ "Error" ][ "@attributes" ][ "Value" ] );
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: ' . $this->GetError() );
        }
    }
    
    /**
     * For real players, used in live server of MG
     * @param type $loginName
     * @param type $password
     * @param type $amount
     * @param type $transactionID
     * @param type $eventID
     * @return type 
     */
    public function ChangeBalanceEx( $loginName, $password, $amount, $transactionID, $eventID )
    {
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'ChangeBalanceEx',
                'LayerName' => $this->_configuration['mgcapi_playername'],
                'ServerID' => $this->_configuration['mgcapi_serverid'] ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_configuration['mgcapi_username'],
                'Password' => $this->_configuration['mgcapi_password'],
                'TicketID' =>  $transactionID,
                'ServerID' => $this->_configuration['mgcapi_serverid'] ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) ),
                'Password' => array( '@attributes' => array( 'Value' => $password ) ),
                'Amount' => array( '@attributes' => array( 'Value' => $amount ) ),
                'TransactionID' => array( '@attributes' => array( 'Value' => $transactionID ) ),
                'EventID' => array( '@attributes' => array( 'Value' => $eventID ) )
                )
            )
        );

        $response = $this->ProcessXMLRequest( $xmlData );
        
        if ( !$this->GetError() )
        {
            if ( is_array( $response ) )
            {
                if ( $response["Result"]["@attributes"]["Success"] == 1 )
                {
                    return array
                    (
                        'IsSucceed' => true,
                        'ErrorCode' => 0,
                        'ErrorMessage' => null,
                        'TransactionInfo' => array
                            (
                                'TransactionAmount' => abs($amount / 100),
                                'TransactionId' =>  $transactionID,
                                'TransactionStatus'=> 'true',
                                'Balance' => $response[ "Result" ][ "Returnset" ][ "Balance" ][ "@attributes" ][ "Value" ] / 100
                            )
                    );
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => "(" . $response[ "Result" ][ "Returnset" ][ "ErrorCode" ][ "@attributes" ][ "Value" ] . ") " . $response[ "Result" ][ "Returnset" ][ "Error" ][ "@attributes" ][ "Value" ] );
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: ' . $this->GetError() );
        }
    }
    
    /**
     * Deposit
     * @param string $account
     * @param string|int $amount
     * @param string $tracking1 not required no purpose at the meantime
     * @param string $tracking2 not required no purpose at the meantime
     * @param string $tracking3 not required no purpose at the meantime
     * @param string $tracking4 not required no purpose at the meantime
     * @param string $terminal_pwd required terminal password
     * @param string ticket_id required nullable
     * @return string 'true' or 'false'
     */
    public function deposit($account, $amount, $lpTransactionID, $tracking1=null, $tracking2=null, 
                            $tracking3=null, $tracking4= '', $terminal_pwd = '')
    {
        $this->setTicketID( $lpTransactionID );

        // All balances in the system are stored as cent units - 1/100th of a credit unit.
        $amount = $this->removeComma($amount) * 100;
        $method = LPConfig::app()->params['mg_config']['mgcapi_trans_method'];
        $eventid = LPConfig::app()->params['mg_config']['mgcapi_event_id'][0];
        $response = $this->$method( $account, $terminal_pwd, $amount, $lpTransactionID, $eventid);
        $this->_actual_response = $response;
        if(!isset($response['IsSucceed']))
            return 'false';
        if(!isset($response['TransactionInfo']['TransactionStatus']))
             return $this->_status = false;
        $this->_transactionID = $response['TransactionInfo']['TransactionId'];
        return $this->_status = $response['TransactionInfo']['TransactionStatus'];
    }
    
    /**
     * Withdraw
     * @param string $account
     * @param string|amount $amount
     * @param string $tracking1 not required no purpose at the meantime
     * @param string $tracking2 not required no purpose at the meantime
     * @param string $tracking3 not required no purpose at the meantime
     * @param string $tracking4 not required no purpose at the meantime
     * @param string $terminal_pwd required terminal password
     * @param string ticket_id required nullable
     * @return string 'true' or 'false' 
     */
    public function withdraw($account, $amount, $lpTransactionID, $tracking1=null, $tracking2=null, 
                             $tracking3=null, $tracking4= '', $terminal_pwd= '') 
    {
        $this->setTicketID( $lpTransactionID );

        // All balances in the system are stored as cent units - 1/100th of a credit unit.
        $amount = -($this->removeComma($amount) * 100);
        $method = LPConfig::app()->params['mg_config']['mgcapi_trans_method'];
        $eventid = LPConfig::app()->params['mg_config']['mgcapi_event_id'][0];
        $response = $this->$method( $account, $terminal_pwd, $amount, $lpTransactionID, $eventid);
        
        $this->_actual_response = $response;
        if(!isset($response['IsSucceed']))
            return 'false';
        if(!isset($response['TransactionInfo']['TransactionStatus']))
            return $this->_status = false;
        $this->_transactionID = $response['TransactionInfo']['TransactionId'];
        return $this->_status = $response['TransactionInfo']['TransactionStatus'];
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
     * Use this if you want to determine if successfully deposit or withdraw
     * @return bool true or false status in withdraw or deposit 
     */
    public function getGenericStatus() 
    {
        return $this->_status;
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
     * @return string
     */
    public function GetError()
    {
        return $this->_error;
    }

    /**
     *
     * @return xml
     */
    public function GetXMLRequest()
    {
        return $this->_xmlRequest;
    }

    /**
     *
     * @return xml
     */
    public function GetXMLResponse()
    {
        return $this->_xmlResponse;
    }

    public function ProcessXMLRequest( $xmlData )
    {
        
        $object = null;
        
        $xml = Array2XML::createXML( 'PKT', $xmlData );

        $this->_xmlRequest = $xml->saveXML();

        $response = $this->PostXMLData( $this->_xmlRequest, $this->_configuration['service_api'] );

        if ( $response[0] == 200 )
        {
            $this->_xmlResponse = $response[1];

            if ( $this->_xmlResponse != null )
            {
                // XML to array
                $object = $this->XML2Array( $this->_xmlResponse );
            }
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }
        
        return $object;
    }
    
    private function XML2Array( $xmlString )
    {
        $xml = simplexml_load_string( $xmlString );

        $json = json_encode( $xml );

        return json_decode( $json, TRUE );
    }
    
    /**
     * Check if all configuration are all set
     * @return bool true or throw CException 
     */
    protected function _checkConfiguration() 
    {
        if(!isset($this->_configuration['service_api']))
            throw new CException('Please set "service_api" in configuration');
        
        if(!isset($this->_configuration['currency']))
            throw new CException('Please set "currency" in configuration');          
        
        if(!isset($this->_configuration['mgcapi_username']))
            throw new CException('Please set "mgcapi_username" in configuration');
        
        if(!isset($this->_configuration['mgcapi_password']))
            throw new CException('Please set "mgcapi_password" in configuration');
        
        if(!isset($this->_configuration['mgcapi_playername']))
            throw new CException('Please set "mgcapi_playername" in configuration');
        
        if(!isset($this->_configuration['mgcapi_serverid']))
            throw new CException('Please set "mgcapi_serverid" in configuration');
        
        return true;
    }
    
    /**
     * Post XML data to remote server
     *
     * @param xml $xmlData
     * @param string $URI
     * @return data
     */
    private function PostXMLData( $xmlData, $URI )
    {
        $curl = curl_init( $URI );

        curl_setopt( $curl, CURLOPT_FRESH_CONNECT, $this->_caching );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout );
        curl_setopt( $curl, CURLOPT_TIMEOUT, $this->_timeout );
        curl_setopt( $curl, CURLOPT_USERAGENT, $this->_userAgent );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_POST, TRUE );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: text/xml; charset=utf-8' ) );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $xmlData );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );

        $response = curl_exec( $curl );

        $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

        curl_close( $curl );

        return array( $http_status, $response );
    }
}