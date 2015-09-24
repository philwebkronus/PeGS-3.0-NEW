<?php
#Name: RealtimeGamingCashierAPI.class.php
#Author: FTG
#Version: 2.0.0
#Copyright 2011 PhilWeb Corporation

//$_RealtimeGamingCashierAPI = new RealtimeGamingCashierAPI( $wsdlUrl, $certFilePath, $keyFilePath, $passPhrase );

class RealtimeGamingCashierAPI
{
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

    public function __construct()
    {
        $argv = func_get_args();

        switch ( func_num_args() )
        {
            default:
            case 4: self::__construct1( $argv[0], $argv[1], $argv[2], $argv[3] ); break;
            case 5: self::__construct2( $argv[0], $argv[1], $argv[2], $argv[3], $argv[4] ); break;
        }
    }
	
    public function __construct1( $url, $certFilePath = '', $keyFilePath = '', $passPhrase = '' )
    {
        $this->_url = $url;
        $this->_certFilePath = $certFilePath;
        $this->_keyFilePath = $keyFilePath;
        $this->_passPhrase = $passPhrase;
    }
	
    public function __construct2( $wsdlUrl, $certFilePath = '', $keyFilePath = '', $passPhrase = '', $caching = FALSE )
    {
        $this->_url = $url;
        $this->_certFilePath = $certFilePath;
        $this->_keyFilePath = $keyFilePath;
        $this->_passPhrase = $passPhrase;
        $this->_caching = $caching;        
    }

    public function GetError()
    {
    	return $this->_error;
    }
    
    public function DepositGeneric( $casinoID,
            $PID,
            $methodID,
            $amount,
            $tracking1,
            $tracking2,
            $tracking3,
            $tracking4,
            $sessionID,
            $skinID = 1,
            $userID = 0 )
    {
        $data = array( 'casinoID' => $casinoID,
            'PID' => $PID,
            'methodID' => $methodID,
            'amount' => $amount,
            'tracking1' => $tracking1,
            'tracking2' => $tracking2,
            'tracking3' => $tracking3,
            'tracking4' => $tracking4,
            'sessionID' => $sessionID,
            'userID' => $userID,
            'SkinID' => $skinID );
        
        $response = $this->SubmitRequest( $this->_url . '/DepositGeneric', http_build_query( $data ) );
        
        if ( $response[0] == 200 )
        {
            $this->_APIresponse = $this->XML2Array( $response[1] );

            $this->_APIresponse = array( 'DepositGenericResult' => $this->_APIresponse );
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }

        return $this->_APIresponse;
    }
    
    public function GetAccountBalance( $casinoID, $PID, $forMoney = 1 )
    {
        $this->_APIresponse = null;
        
        $data = array( 'casinoID' => $casinoID, 'PID' => $PID, 'forMoney' => $forMoney );

        $response = $this->SubmitRequest( $this->_url . '/GetAccountBalance', http_build_query( $data ) );
        
        if ( $response[0] == 200 )
        {
            $this->_APIresponse = $this->XML2Array( $response[1] );

            $this->_APIresponse = array( 'GetAccountBalanceResult' => $this->_APIresponse );
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }

        return $this->_APIresponse;
    }
    
    public function GetAccountInfoByPID( $casinoID, $PID )
    {
        $data = array('casinoID' => $casinoID, 'PID' => $PID );

        $response = $this->SubmitRequest( $this->_url . '/GetAccountInfoByPID', http_build_query( $data ) );
        
        if ( $response[0] == 200 )
        {
            $this->_APIresponse = $this->XML2Array( $response[1] );

            $this->_APIresponse = array( 'GetAccountInfoByPIDResult' => $this->_APIresponse );
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }

        return $this->_APIresponse;
    }
    
    public function GetPIDFromLogin( $login )
    {	
        $data = array( 'login' => $login );

        $response = $this->SubmitRequest( $this->_url . '/GetPIDFromLogin', http_build_query( $data ) );
        
        if ( $response[0] == 200 )
        {                        
            $this->_APIresponse = $this->XML2Array( $response[1] );
            #$this->_APIresponse = array( 'GetPIDFromLoginResult' => $this->_APIresponse[0] );
            if(count($this->_APIresponse) == 0)
            {
              $this->_APIresponse = array( 'GetPIDFromLoginResult' => $this->_APIresponse);
            }
            
            else{
              $this->_APIresponse = array( 'GetPIDFromLoginResult' => $this->_APIresponse[0]);    
            }
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }

        return $this->_APIresponse;
    }
    
    /*
    ** @Description: Get the Skin ID for RTG V15
    ** @Author: aqdepliyan
    ** @Parameters: locatorName (Skin Name)
    */
    public function GetSkinID( $locatorName )
    {
        $data = array('locatorName' => $locatorName );

        $response = $this->SubmitRequest( $this->_url . '/GetSkinID', http_build_query( $data ) );
        
        if ( $response[0] == 200 )
        {
            $this->_APIresponse = $this->XML2Array( $response[1] );

            $this->_APIresponse = array( 'GetSkinIDResult' => $this->_APIresponse[0] );
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }

        return $this->_APIresponse;
    }
    
    public function Login( $casinoID, $PID, $hashedPassword, $forMoney, $IP, $skinID = 1 )
    {
        $this->_APIresponse = null;
        
        $data = array( 'casinoID' => $casinoID,
            'PID' => $PID,
            'hashedPassword' => $hashedPassword,
            'forMoney' => $forMoney,
            'IP' => $IP,
            'skinID' => $skinID );

        $response = $this->SubmitRequest( $this->_url . '/Login', http_build_query( $data ) );

        if ( $response[0] == 200 )
        {
            $this->_APIresponse = $this->XML2Array( $response[1] );

            $this->_APIresponse = array( 'LoginResult' => $this->_APIresponse[0] );
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }

        return $this->_APIresponse;
    }
    
    public function WithdrawGeneric( $casinoID,
            $PID,
            $methodID,
            $amount,
            $tracking1,
            $tracking2,
            $tracking3,
            $tracking4,
            $sessionID,
            $skinID = 1, 
            $userID = 0 )
    {        
        $data = array( 'casinoID' => $casinoID,
            'PID' => $PID,
            'methodID' => $methodID,
            'amount' => $amount,
            'tracking1' => $tracking1,
            'tracking2' => $tracking2,
            'tracking3' => $tracking3,
            'tracking4' => $tracking4,
            'sessionID' => $sessionID,
            'userID' => $userID,
            'skinID' => $skinID );

        $response = $this->SubmitRequest( $this->_url . '/WithdrawGeneric', http_build_query( $data ) );

        if ( $response[0] == 200 )
        {
            $this->_APIresponse = $this->XML2Array( $response[1] );

            $this->_APIresponse = array( 'WithdrawGenericResult' => $this->_APIresponse );
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }

        return $this->_APIresponse;
    }    
    
    public function TrackingInfoTransactionSearch( $PID, $tracking1, $tracking2 = '', $tracking3 = '', $tracking4 = '' )
    {
        $data = array( 'pid' => $PID,
            'tracking1' => $tracking1,
            'tracking2' => $tracking2,
            'tracking3' => $tracking3,
            'tracking4' => $tracking4 );

        $response = $this->SubmitRequest( $this->_url . '/TrackingInfoTransactionSearch', http_build_query( $data ) );

        if ( $response[0] == 200 )
        {
            $this->_APIresponse = $this->XML2Array( $response[1] );

            $this->_APIresponse = array( 'TrackingInfoTransactionSearchResult' => $this->_APIresponse );
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }

        return $this->_APIresponse;
    }
    
    public function AdjustComps($casinoID, $PID, $amount,$comment = 0) {
        $comvalue = $comment == 0 ? 'AddPoints':'DeductPoints';
        $data = array('casinoID' => $casinoID,
            'PID' => $PID,
            'amount' => $amount,
            'Comment' => $comvalue);

        $response = $this->SubmitRequest($this->_url . '/AdjustComps', http_build_query( $data ) );

        if ( $response[0] == 200 )
        {
            $this->_APIresponse = $this->XML2Array( $response[1] );

            $this->_APIresponse = array( 'AdjustCompsResult' => $this->_APIresponse );

        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }

        return $this->_APIresponse;
    }

    private function SubmitRequest( $url, $data )
    {
        $curl = curl_init( $url . '?' . $data );

        curl_setopt( $curl, CURLOPT_FRESH_CONNECT, $this->_caching );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout );
        curl_setopt( $curl, CURLOPT_TIMEOUT, $this->_timeout );
        curl_setopt( $curl, CURLOPT_USERAGENT, $this->_userAgent );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_SSLCERTTYPE, 'PEM' );
        curl_setopt( $curl, CURLOPT_SSLCERT, $this->_certFilePath );
        curl_setopt( $curl, CURLOPT_SSLKEYTYPE, 'PEM' );
        curl_setopt( $curl, CURLOPT_SSLKEY, $this->_keyFilePath );
        curl_setopt( $curl, CURLOPT_SSLKEYPASSWD, $this->_passPhrase );
        curl_setopt( $curl, CURLOPT_POST, FALSE );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_SSLVERSION, 3 ); 

        $response = curl_exec( $curl );

        $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

        curl_close( $curl );

        return array( $http_status, $response );
    }

    private function XML2Array( $xmlString )
    {
        $xml = simplexml_load_string( $xmlString );

        $json = json_encode( $xml );

        return json_decode( $json, TRUE );
    }
}

?>
