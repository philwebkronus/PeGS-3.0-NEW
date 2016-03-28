<?php
/**
 * @author elperez
 * @createdon April 26, 2012
 * @purpose Base Class for calling of Player API of RTG w/o nusoap
 */
class RealtimeGamingPlayerAPI
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
    
    public function getError()
    {
    	return $this->_error;
    }
    
    public function createTerminalAccount($login, $password, $aid, $country, 
            $casinoID, $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, 
            $city, $state, $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, 
            $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID)
    {
        $data = array('login' => $login,
                      'pw' => $password,
                      'aid' => $aid,
                      'country' => $country,
                      'casinoID' => $casinoID,
                      'fname' => $fname,
                      'lname' => $lname,
                      'email' => $email,
                      'dayphone' => $dayphone,
                      'evephone' => $evephone,
                      'addr1' => $addr1,
                      'addr2' => $addr2,
                      'city' => $city,
                      'state' => $state,
                      'zip' => $zip,
                      'ip' => $ip,
                      'mac' => $mac,
                      'userID' => $userID,
                      'downloadID' => $downloadID,
                      'birthdate' => $birthdate,
                      'clientID' => $clientID,
                      'putInAffPID' => $putInAffPID,
                      'calledFromCasino' => $calledFromCasino,
                      'hashedPassword' => $hashedPassword,
                      'agentID' => $agentID,
                      'currentPosition' => $currentPosition,
                      'thirdPartyPID' => $thirdPartyPID);
        $response = $this->submitRequest($this->_url . '/createNewPlayerFull', http_build_query($data));
        
        if ( $response[0] == 200 )
        {
            $this->_APIresponse = $this->XML2Array( $response[1] );

            $this->_APIresponse = array('createNewPlayerFullResult' => $this->_APIresponse );
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }
        return $this->_APIresponse;
    }
    
    public function changePlayerPassword($casinoID, $login, $oldpassword, $newpassword)
    {
        $data = array('casinoID'=>$casinoID,
                      'login'=>$login,
                      'oldpw'=>$oldpassword,
                      'newpw'=>$newpassword);
        $response = $this->submitRequest($this->_url . '/changePlayerPW', http_build_query($data));
            
        if ( $response[0] == 200 )
        {
            $this->_APIresponse = $this->XML2Array( $response[1] );

            $this->_APIresponse = array('changePlayerPWResult' => $this->_APIresponse );
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }
        return $this->_APIresponse;
    }

    private function submitRequest( $url, $data )
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
    
    /**
     * Creates an casino account to RTG
     * @param type $login
     * @param type $password
     * @param type $aid
     * @param type $country
     * @param type $casinoID obsolete
     * @param type $fname
     * @param type $lname
     * @param type $email
     * @param type $dayphone
     * @param type $evephone
     * @param type $addr1
     * @param type $addr2
     * @param type $city
     * @param type $state
     * @param type $zip
     * @param type $ip
     * @param type $mac
     * @param type $userID obsolete
     * @param type $downloadID
     * @param type $birthdate
     * @param type $clientID
     * @param type $putInAffPID obsolete
     * @param type $calledFromCasino obsolete
     * @param type $hashedPassword
     * @param type $agentID
     * @param type $currentPosition
     * @param type $thirdPartyPID
     * @param type $playerClass
     * @return ojtect | array api response
     */
    public function createTerminalAccountUB($login, $password, $aid, $country, 
            $casinoID, $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, 
            $city, $state, $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, 
            $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, 
            $thirdPartyPID, $playerClass)
    {
        $data = array('Player'=>array(
                              'Contact'=>array(
                                     'CountryID'=>$country,
                                     'EMail'=>$email,
                                     'FirstName'=>$fname,
                                     'LastName'=>$lname,
                                     'DayPhone'=>$dayphone,
                                     'EvePhone'=>$evephone,
                                     'Address1'=>$addr1,
                                     'Address2'=>$addr2,
                                     'City'=>$city,
                                     'StateID'=>$state,
                                     'ZipCode'=>$zip,
                                ),
                              'Login'=>$login,
                              'Password'=>$password),
                        'ThirdPartyDataSync'=>true,
                        'UserID'=>0,
                        'MapToAffID'=>false,
                        'CalledFromCasino'=>0,
                        'IP'=>$ip,
                        'MACAddress'=>$mac,
                        'DownloadID'=>$downloadID,
                        'BirthDate'=>$birthdate,
                        'ClientID'=>$clientID,
                        'HashedPassword'=>$hashedPassword,
                        'AgentID'=>$agentID,
                        'CurrentPosition'=>$currentPosition,
                        'ThirdpartyPID'=>$thirdPartyPID,
                        'Class'=>$playerClass);
        
        $method = 'CreatePlayer';
        
        $response = $this->SubmitRequestUB($this->_url, $data, $method);
        
        if (is_object($response) )
        {
            $this->_APIresponse = $this->XML2ArrayUB( $response );
        }
        else
        {
            $this->_error = "Bad request. Check if API configurations are correct.";
        }
        
        return $this->_APIresponse;
    }
    
    /**
     * Change account current password by new password
     * @param str $login
     * @param str $oldpassword
     * @param str $newpassword
     * @return object | array
     */
    public function changePlayerPasswordUB($login, $oldpassword, $newpassword)
    {
        $data = array('Login'=>$login,'OldPassword'=>$oldpassword,'NewPassword'=>$newpassword);
        
        $method = 'ChangePassword';
        
        $response = $this->submitRequestUB($this->_url, $data, $method);
        
        if (is_object($response) )
        {
            $this->_APIresponse = $this->XML2ArrayUB( $response );
        }
        else
        {
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
    private function submitRequestUB( $url, $data, $method )
    {
        header( 'Content-Type: text/plain' );

        $soapArr = array(
                'trace' => true,
                'exceptions' => true,
                'local_cert' => $this->_certFilePath, //certificate folder
                'passphrase' => ''
        );
        
        $response = array();
        try{
            $client = new SoapClient( $url, $soapArr );
            
            $response = $client->$method($data);
            
        } catch (Exception $e){
            $this->_error = "Bad request. Check if API configurations are correct";
        }
        
        return $response;
    }

    /**
     * Formats a XML string to convert into an array
     * @param type $xmlString
     * @return type
     */
    private function XML2ArrayUB( $xmlString )
    {
        $json = json_encode( $xmlString );

        return json_decode( $json, TRUE );
    }
}

?>