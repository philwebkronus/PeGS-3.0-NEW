<?php
#Name: MicrogamingAPI.class.php
#Author: FTG
#Version: 1.0.0
#Copyright 2010 PhilWeb Corporation

//require_once('../sys/class/nusoap/nusoap.php');
//require_once('../sys/class/nusoap/class.wsdlcache.php');
//$_MicrogamingAPI = new MicrogamingAPI( $wsdlUrl, $loginName, $pinCode );

class MicrogamingAPI
{
    private $_loginName;
    private $_pinCode;
    private $_wsdlUrl;
    private $_soapClient;
    private $_isAuthenticated = 0;
    private $_sessionGUID;
    private $_xmlSoapHeader;
    
    public function __construct()
    {
        $argv = func_get_args();

        switch(func_num_args())
        {
            default:
            case 4: self::__construct1( $argv[0], $argv[1], $argv[2], $argv[3] ); break;
            case 5: self::__construct1( $argv[0], $argv[1], $argv[2], $argv[3], $argv[4] ); break;
            case 6: self::__construct3( $argv[0], $argv[1], $argv[2], $argv[3], $argv[4], $argv[5] ); break;
        }
    }
    
    function __construct1( $wsdlUrl, $loginName, $pinCode )
    {
        $this->_loginName = $loginName;
        $this->_pinCode = $pinCode;
        $this->_wsdlUrl = $wsdlUrl;

        $wsdl = new wsdl( $wsdlUrl, '', '', '', '', 5 );
      
        $this->_soapClient = new nusoap_client( $wsdl, 'wsdl' );
        
        $response = $this->_soapClient->call( 'IsAuthenticate', array('loginName' => $loginName, 'pinCode' => $pinCode) );
        
        if ( $response[ 'IsAuthenticateResult' ][ 'ErrorCode' ] == 0 )
        {
            $this->_sessionGUID = $response[ 'IsAuthenticateResult' ][ 'SessionGUID' ];
            $this->_isAuthenticated = 1;

            $this->_xmlSoapHeader = '
                <AgentSession xmlns="https://entservices.totalegame.net">
                    <SessionGUID>' . $response[ 'IsAuthenticateResult' ][ 'SessionGUID' ] . '</SessionGUID>
                    <IPAddress>' . $response[ 'IsAuthenticateResult' ][ 'IPAddress' ] . '</IPAddress>
                    <IsLengthenSession>1</IsLengthenSession>
                </AgentSession>
                ';

            $this->_soapClient->setHeaders($this->_xmlSoapHeader);
        }
    }
    
    function __construct2( $wsdlUrl, $loginName, $pinCode, $caching = FALSE )
    {
        $this->_loginName = $loginName;
        $this->_pinCode = $pinCode;
        $this->_wsdlUrl = $wsdlUrl;

        if ( $caching == TRUE )
        {
            $cache = new nusoap_wsdlcache( ROOT_DIR . 'sys/tmp/cache', 86400 );

            $wsdl = $cache->get( $wsdlUrl );

            if ( is_null( $wsdl ) )
            {
                $wsdl = new wsdl( $wsdlUrl, '', '', '', '', 5 );
                $cache->put( $wsdl );
            }
        }
        else
        {
            $wsdl = new wsdl( $wsdlUrl, '', '', '', '', 5 );
        }
      
        $this->_soapClient = new nusoap_client( $wsdl, 'wsdl' );
        
        $response = $this->_soapClient->call( 'IsAuthenticate', array('loginName' => $loginName, 'pinCode' => $pinCode) );
        
        if ( $response[ 'IsAuthenticateResult' ][ 'ErrorCode' ] == 0 )
        {
            $this->_sessionGUID = $response[ 'IsAuthenticateResult' ][ 'SessionGUID' ];
            $this->_isAuthenticated = 1;
            
            $this->_xmlSoapHeader = '
                <AgentSession xmlns="https://entservices.totalegame.net">
                    <SessionGUID>' . $response[ 'IsAuthenticateResult' ][ 'SessionGUID' ] . '</SessionGUID>
                    <IPAddress>' . $response[ 'IsAuthenticateResult' ][ 'IPAddress' ] . '</IPAddress>
                    <IsLengthenSession>1</IsLengthenSession>
                </AgentSession>
                ';

            $this->_soapClient->setHeaders($this->_xmlSoapHeader);
        }
    }
    
    function __construct3( $wsdlUrl, $loginName, $pinCode, $sessionGUID, $IPAddress, $caching = FALSE )
    {
        $this->_wsdlUrl = $wsdlUrl;

        if ( $caching == TRUE )
        {
            $cache = new nusoap_wsdlcache( ROOT_DIR . 'sys/tmp/cache', 86400 );

            $wsdl = $cache->get( $wsdlUrl );

            if ( is_null( $wsdl ) )
            {
                $wsdl = new wsdl( $wsdlUrl, '', '', '', '', 5 );
                $cache->put( $wsdl );
            }
        }
        else
        {
            $wsdl = new wsdl( $wsdlUrl, '', '', '', '', 5 );
        }

        $this->_soapClient = new nusoap_client( $wsdl, 'wsdl' );
        
        $this->_sessionGUID = $sessionGUID;
        $this->_isAuthenticated = 1;
        
        $this->_xmlSoapHeader = '
            <AgentSession xmlns="https://entservices.totalegame.net">
                <SessionGUID>' . $sessionGUID . '</SessionGUID>
                <IPAddress>' . $IPAddress . '</IPAddress>
                <IsLengthenSession>1</IsLengthenSession>
            </AgentSession>
            ';

        $this->_soapClient->setHeaders( $this->_xmlSoapHeader );
    }       
    
    public function GetSessionGUID()
    {
        return $this->_sessionGUID;
    }
    
    public function IsAuthenticated()
    {
        return $this->_isAuthenticated;
    }
    
    public function GetError()
    {
    	return $this->_soapClient->getError();
    }

    public function GetSoapClient()
    {
	return $this->_soapClient;
    }

    public function AddAccount($accountNumber,
                               $password,
                               $firstName,
                               $lastName,
                               $currency,
                               $isMobile,
                               $mobileNumber,
                               $isSendGame,
                               $bettingProfileId)
    {
        $response = $this->_soapClient->call( 'AddAccount', array('accountNumber' => $accountNumber,
                                                                'password' => $password,
                                                                'firstName' => $firstName,
                                                                'lastName' => $lastName,
                                                                'currency' => $currency,
                                                                'isMobile' => $isMobile,
                                                                'mobileNumber' => $mobileNumber,
                                                                'isSendGame' => $isSendGame,
                                                                'bettingProfileId' => $bettingProfileId) );
        
        return $response;
    }
    
    public function Deposit( $accountNumber, $amount, $currency = 9 )
    {
        $response = $this->_soapClient->call( 'Deposit', array('accountNumber' => $accountNumber,
                                                             'amount' => $amount,
                                                             'currency' => $currency) );
        
        return $response;
    }
    
    public function GetAccountBalance( $accountNumber )
    {
        $response = $this->_soapClient->call( 'GetAccountBalance', array('delimitedAccountNumbers' => $accountNumber) );

        return $response;
    }    
    
    public function Withdrawal( $accountNumber, $amount )
    {
        $response = $this->_soapClient->call( 'Withdrawal', array('accountNumber' => $accountNumber, 'amount' => $amount) );

        return $response;
    }
    
    //to check if agent still have balance
    public function GetMyBalance() 
    { 
        return $this->_soapClient->call( 'GetMyBalance' );    
    
    }
}

?>