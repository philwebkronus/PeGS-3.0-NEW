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

    public function __construct()
    {
        $argv = func_get_args();

        switch ( func_num_args() )
        {
            default:
            case 4: self::__construct1( $argv[0], $argv[1], $argv[2] ); break;
        }
    }
	
    public function __construct1( $url, $certFilePath, $passPhrase = '' )
    {
        $this->_url = $url;
        $this->_cert_keyFilePath = $certFilePath;
        $this->_passPhrase = $passPhrase;
    }
    
    public function getError()
    {
    	return $this->_error;
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
    public function createTerminalAccount($login, $password, $aid, $country, 
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
        
        $response = $this->SubmitRequest($this->_url, $data, $method);

        if (is_object($response) )
        {
            $this->_APIresponse = $this->XML2Array( $response );
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
    public function changePlayerPassword($login, $oldpassword, $newpassword)
    {
        $data = array('Login'=>$login,'OldPassword'=>$oldpassword,'NewPassword'=>$newpassword);
        
        $method = 'ChangePassword';
        
        $response = $this->submitRequest($this->_url, $data, $method);
        
        if (is_object($response) )
        {
            $this->_APIresponse = $this->XML2Array( $response );
        }
        else
        {
            $this->_error = "Bad request. Check if API configurations are correct.";
        }
        
        return $this->_APIresponse;
    }
    
    /**
     * Change player clasification (0-New Player, 1-High Roller)
     * @param str $pid
     * @param int $playerClassID
     * @param int $userID
     * @return object | array api response
     */
    public function changePlayerClasification($pid, $playerClassID, $userID){
        
        $data = array('PID'=>$pid,'playerClassID'=>$playerClassID,'UserID'=>$userID);
        
        $method = 'ChangePlayerClass';
        
        $response = $this->submitRequest($this->_url, $data, $method);
        
        if (is_object($response) )
        {
            $this->_APIresponse = $this->XML2Array( $response );
        }
        else
        {
            $this->_error = "Bad request. Check if API configurations are correct.";
        }
        
        return $this->_APIresponse;
    }
    
    /**
     * Get assigned player clasification
     * @param str $pid
     * @return object | array api response
     */
    public function getPlayerClasification($pid){
        $data = array('PID'=>$pid);
        
        $method = 'GetPlayerClass';
        
        $response = $this->submitRequest($this->_url, $data, $method);
        
        if (is_object($response) )
        {
            $this->_APIresponse = $this->XML2Array( $response );
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
    private function submitRequest( $url, $data, $method )
    {
        header( 'Content-Type: text/plain' );

        $soapArr = array(
                'trace' => true,
                'exceptions' => true,
                'local_cert' => $this->_cert_keyFilePath, //certificate folder
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
    private function XML2Array( $xmlString )
    {
        $json = json_encode( $xmlString );

        return json_decode( $json, TRUE );
    }
}

?>
