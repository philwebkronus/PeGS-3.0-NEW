<?php
#Name: MicrogamingAPI.class.php
#Author: FTG
#Version: 1.0.0
#Copyright 2010 PhilWeb Corporation

//require_once('libs/nusoap/nusoap.php');
require_once('nusoap/class.wsdlcache.php');
//$_MicrogamingAPI = new MicrogamingAPI($wsdlUrl, $loginName, $pinCode);

class MicrogamingAPI
{
    private $_loginName;
    private $_pinCode;
    private $_wsdlUrl;
    private $_soapClient;
    private $_lastErrorMessage;
    private $_lastException;
    private $_isAuthenticated = 0;
    private $_sessionGUID;
    private $xmlSoapHeader;
    
    public function __construct()
    {
        $argv = func_get_args();

        switch(func_num_args())
        {
            default:
            case 1: self::__construct1($argv[0], $argv[1], $argv[2]); break;
            case 5: self::__construct2($argv[0], $argv[1], $argv[2], $argv[3], $argv[4]); break;
        }
    }
    
    function __construct1($wsdlUrl, $loginName, $pinCode)
    {
        $this->_loginName = $loginName;
        $this->_pinCode = $pinCode;
        $this->_wsdlUrl = $wsdlUrl;

	$cache = new nusoap_wsdlcache($_SERVER['DOCUMENT_ROOT'] . '/tmp', 86400);

        $wsdl = $cache->get($wsdlUrl);

        if (is_null($wsdl))
        {
                $wsdl = new wsdl($wsdlUrl, '', '', '', '', 5);
                $cache->put($wsdl);
        }
      
        $this->_soapClient = new nusoap_client($wsdl, 'wsdl');
        
        $result = $this->_soapClient->call('IsAuthenticate', array('loginName' => $loginName, 'pinCode' => $pinCode));
        
        if ($result["IsAuthenticateResult"]["ErrorCode"] == 0)
        {
            $this->_sessionGUID = $result["IsAuthenticateResult"]["SessionGUID"];            
            $this->_isAuthenticated = 1;
            
            $xmlSoapHeader = '
                <AgentSession xmlns="https://entservices.totalegame.net">
                    <SessionGUID>' . $result["IsAuthenticateResult"]["SessionGUID"] . '</SessionGUID>
                    <IPAddress>' . $result["IsAuthenticateResult"]["IPAddress"] . '</IPAddress>
                    <IsLengthenSession>1</IsLengthenSession>
                </AgentSession>
                ';

            $this->_soapClient->setHeaders($xmlSoapHeader);
        }
    }
    
    function __construct2($wsdlUrl, $loginName, $pinCode, $sessionGUID, $IPAddress)
    {
        $this->_wsdlUrl = $wsdlUrl;

	$cache = new nusoap_wsdlcache($_SERVER['DOCUMENT_ROOT']. '/tmp', 86400);

	$wsdl = $cache->get($wsdlUrl);

	if (is_null($wsdl))
	{
		$wsdl = new wsdl($wsdlUrl, '', '', '', '', 5);
		$cache->put($wsdl);
	}

        $this->_soapClient = new nusoap_client($wsdl, 'wsdl');
        
        $this->_sessionGUID = $sessionGUID;
        $this->_isAuthenticated = 1;
        
        $xmlSoapHeader = '
            <AgentSession xmlns="https://entservices.totalegame.net">
                <SessionGUID>' . $sessionGUID . '</SessionGUID>
                <IPAddress>' . $IPAddress . '</IPAddress>
                <IsLengthenSession>1</IsLengthenSession>
            </AgentSession>
            ';

        $this->_soapClient->setHeaders($xmlSoapHeader);        
    }
    
    public function GetLastException()
    {
        return $this->_lastException;
    }
    
    public function GetLastErrorMessage()
    {
        return $this->_lastErrorMessage;
    }
    
    public function GetSessionGUID()
    {
        return $this->_sessionGUID;
    }
    
    public function IsAuthenticated()
    {
        return $this->_isAuthenticated;
    }

    public function GetSoapClient()
    {
	return $this->_soapClient;
    }

    public function AddAccount($accountNumber, $password, $firstName, $lastName, $currency = 9, $isMobile, $mobileNumber, $isSendGame, $bettingProfileId)
    {
        $result = $this->_soapClient->call('AddAccount', array('accountNumber' => $accountNumber,
                                                                'password' => $password,
                                                                'firstName' => $firstName,
                                                                'lastName' => $lastName,
                                                                'currency' => $currency,
                                                                'isMobile' => $isMobile,
                                                                'mobileNumber' => $mobileNumber,
                                                                'isSendGame' => $isSendGame,
                                                                'bettingProfileId' => $bettingProfileId));
        
        return $result;
    }
    
    public function Deposit($accountNumber, $amount, $currency = 9)
    {
        $result = $this->_soapClient->call('Deposit', array('accountNumber' => $accountNumber, 'amount' => $amount, 'currency' => $currency));
        
        return $result;
    }
    
    public function GetAccountBalance($accountNumber)
    {
        $result = $this->_soapClient->call('GetAccountBalance', array('delimitedAccountNumbers' => $accountNumber));

        return $result;
    }    
    
    public function Withdrawal($accountNumber, $amount)
    {
        $result = $this->_soapClient->call('Withdrawal', array('accountNumber' => $accountNumber, 'amount' => $amount));

        return $result;
    } 
}

?>
