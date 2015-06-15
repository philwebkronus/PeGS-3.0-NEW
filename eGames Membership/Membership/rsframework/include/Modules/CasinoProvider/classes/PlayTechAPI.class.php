<?php
#Name: PlayTechAPI.class.php
#Author: FTG
#Version: 1.0.0
#Copyright 2010 PhilWeb Corporation

//$_PlayTechAPI = new PlayTechAPI( $URI, $casino, $secretKey, $responseType, $caching, $userAget, $connectionTimeout, $timeout );

/**
 * Convert XML into json response
 * @author modified by Edson Perez
 * date modified 12/13/12
 */
class PlayTechAPI
{
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

	/**
	 * Holds the web service end point 
	 * @var string
	 */	
	private $_URI;
	
	/**
	 * 
	 * @var string
	 */	
	private $_casino;
	
	/**
	 * 
	 * @var string
	 */	
	private $_secretKey;
	
	/**
	 * 
	 * @var string
	 */	
	private $_methodFileName;
	
	/**
	 * 
	 * @var string
	 */	
	private $_responseType = 'xml';
	
	/**
	 * 
	 * @var string
	 */	
	private $_queryString = '';
	
	/**
	 * 
	 * @var string
	 */	
	private $_fullUri;
        
        /**
         * Holds API Response
         * @var array 
         */
        private $_APIresponse;
		
	/**
		* Class constructor
		*
		* @param string $URI
		* @param string $casino
		* @param string $secretKey
		* @param string $responseType
		* @param string $caching
		* @param string $userAgent
		* @param string $connectionTimeout
		* @param string $timeout
		* @return void
		*/	
	public function __construct( $URI, $casino, $secretKey, $responseType = 'xml', $caching = FALSE, $userAgent = 'PEGS Station Manager', $connectionTimeout = 10, $timeout = 500 )
	{
		$this->_URI = $URI;
		$this->_casino = $casino;
		$this->_secretKey = $secretKey;
		$this->_responseType = $responseType;
		$this->_caching = $caching;
		$this->_userAgent = $userAgent;
		$this->_connectionTimeout = $connectionTimeout;
		$this->_timeout = $timeout;

		$this->InitQueryString();
		
		$this->_fullUri = $URI;
	}
	
	private function InitQueryString()
	{
		$this->_queryString = '';
		$this->_queryString = '?';
		$this->_queryString = $this->_queryString . 'responsetype=' . $this->_responseType;
		$this->_queryString = $this->_queryString . '&casino=' . $this->_casino;
		$this->_queryString = $this->_queryString . '&secretkey=' . $this->_secretKey;
	}
	
	public function GetError()
	{
		return $this->_error;
	}
	
	public function ChangePassword( $userName, $newpassword, $playerType = 'player' )
	{
		$_methodFileName = 'change_password.php';
		
		$this->InitQueryString();        
		$this->_queryString = $this->_queryString . '&username=' . $userName;
		$this->_queryString = $this->_queryString . '&newpassword=' . $newpassword;
		$this->_queryString = $this->_queryString . '&remotecreate=1';
		$this->_queryString = $this->_queryString . '&remoteip='; // revisit later
		$this->_queryString = $this->_queryString . '&playertype=' . $playerType;
		
		$this->_fullUri = $this->_URI;
		
		$this->_fullUri = $this->_fullUri . '/' . $_methodFileName . $this->_queryString;
		
		$response = $this->SubmitData( $this->_fullUri );	
                
                if($response[0] == 200){
                    
                    $this->_APIresponse = $this->XML2Array($response[1]);
                    
                } else {
                    
                    $this->_error = $response[0];
                }
                
                print_r($this->_APIresponse);exit;
                return $this->_APIresponse;
	}
	
	public function CheckTransaction( $externalTranId )
	{
		$_methodFileName = 'checktransaction.php';
		
		$this->InitQueryString();        
		$this->_queryString = $this->_queryString . '&externaltranid=' . $externalTranId;
		
		$this->_fullUri = $this->_URI;
		
		$this->_fullUri = $this->_fullUri . '/' . $_methodFileName . $this->_queryString;
		
		$response = $this->SubmitData( $this->_fullUri );	
                
                if($response[0] == 200){
                    
                    $this->_APIresponse = $this->XML2Array($response[1]);
                    
                } else {
                    
                    $this->_error = $response[0];
                }
                print_r($this->_APIresponse);exit;
                return $this->_APIresponse;	
	}	
	
	public function ExternalDeposit( $userName, $password, $amount, $externalTranId, $currency = 'PHP', $comments = '' )
	{
		$_methodFileName = 'externaldeposit.php';
		
		$this->InitQueryString();
		$this->_queryString = $this->_queryString . '&username=' . $userName;
		$this->_queryString = $this->_queryString . '&password=' . $password;
		$this->_queryString = $this->_queryString . '&amount=' . $amount;
		$this->_queryString = $this->_queryString . '&currency=' . $currency;
		$this->_queryString = $this->_queryString . '&externaltranid=' . $externalTranId;
		$this->_queryString = $this->_queryString . '&comments=' . $comments;
		
		$this->_fullUri = $this->_URI;
		
		$this->_fullUri = $this->_fullUri . '/' . $_methodFileName . $this->_queryString;
                
		
		$response = $this->SubmitData( $this->_fullUri );	
                
                if($response[0] == 200){
                    
                    $this->_APIresponse = $this->XML2Array($response[1]);
                    
                } else {
                    
                    $this->_error = $response[0];
                }
                
                return $this->_APIresponse;
	}
	
	public function ExternalWithdraw( $userName, $password, $amount, $externalTranId, $currency = 'PHP', $comments = '' )
	{
		$_methodFileName = 'externalwithdraw.php';
		
		$this->InitQueryString();
		$this->_queryString = $this->_queryString . '&username=' . $userName;
		$this->_queryString = $this->_queryString . '&password=' . $password;
		$this->_queryString = $this->_queryString . '&amount=' . $amount;
		$this->_queryString = $this->_queryString . '&currency=' . $currency;
		$this->_queryString = $this->_queryString . '&externaltranid=' . $externalTranId;
		$this->_queryString = $this->_queryString . '&comments=' . $comments;
		
		$this->_fullUri = $this->_URI;
		
		$this->_fullUri = $this->_fullUri . '/' . $_methodFileName . $this->_queryString;
		
		$response = $this->SubmitData( $this->_fullUri );	
                
                if($response[0] == 200){
                    
                    $this->_APIresponse = $this->XML2Array($response[1]);
                    
                } else {
                    
                    $this->_error = $response[0];
                }
                
                return $this->_APIresponse;
	}
	
	public function FreezePlayer( $userName, $frozen = 0 )
	{
		$_methodFileName = 'freeze_player.php';
		
		$this->InitQueryString();        
		$this->_queryString = $this->_queryString . '&username=' . $userName;
		$this->_queryString = $this->_queryString . '&frozen=' . $frozen;
		
		$this->_fullUri = $this->_URI;
		
		$this->_fullUri = $this->_fullUri . '/' . $_methodFileName . $this->_queryString;
		
		$response = $this->SubmitData( $this->_fullUri );	
                
                if($response[0] == 200){
                    
                    $this->_APIresponse = $this->XML2Array($response[1]);
                    
                } else {
                    
                    $this->_error = $response[0];
                }
                
                return $this->_APIresponse;
	}
	
	public function GetBalance( $userName, $currency = 'PHP' )
	{
		$_methodFileName = 'get_balance.php';
		
		$this->InitQueryString();        
		$this->_queryString = $this->_queryString . '&username=' . $userName;
		$this->_queryString = $this->_queryString . '&currencycode=' . $currency;
		$this->_queryString = $this->_queryString . '&additional_fields=';
		
		$this->_fullUri = $this->_URI;
		
		$this->_fullUri = $this->_fullUri . '/' . $_methodFileName . $this->_queryString;
		
		$response = $this->SubmitData( $this->_fullUri );	
                
                if($response[0] == 200){
                    
                    $this->_APIresponse = $this->XML2Array($response[1]);
                    
                } else {
                    
                    $this->_error = $response[0];
                }
                
                return $this->_APIresponse;
	}
	
	public function GetPlayerInfo( $userName, $password )
	{
		$_methodFileName = 'get_playerinfo.php';
		
		$this->InitQueryString();        
		$this->_queryString = $this->_queryString . '&username=' . $userName;
		$this->_queryString = $this->_queryString . '&password=' . $password;
		
		$this->_fullUri = $this->_URI;
		
		$this->_fullUri = $this->_fullUri . '/' . $_methodFileName . $this->_queryString;
		
		$response = $this->SubmitData( $this->_fullUri );	
                
                if($response[0] == 200){
                    
                    $this->_APIresponse = $this->XML2Array($response[1]);
                    
                } else {
                    
                    $this->_error = $response[0];
                }
                
                return $this->_APIresponse;
	}
	
	public function KickPlayer( $userName )
	{
		$_methodFileName = 'freeze_player.php';
		
		$this->InitQueryString();        
		$this->_queryString = $this->_queryString . '&username=' . $userName;
		$this->_queryString = $this->_queryString . '&kickout=1';
		
		$this->_fullUri = $this->_URI;
		
		$this->_fullUri = $this->_fullUri . '/' . $_methodFileName . $this->_queryString;
		
		$response = $this->SubmitData( $this->_fullUri );	
                
                if($response[0] == 200){
                    
                    $this->_APIresponse = $this->XML2Array($response[1]);
                    
                } else {
                    
                    $this->_error = $response[0];
                }
                
                return $this->_APIresponse;
	}	
	
	public function NewPlayer( $userName, $password, $email, $firstName, $lastName, $birthDate, $address, $city, $countryCode, $phone, $zip, $viplevel, $currency = 'PHP', $skin = 'philweb' )
	{
		$_methodFileName = 'newplayer.php';
		
		$this->InitQueryString();
		$this->_queryString = $this->_queryString . '&username=' . $userName;
		$this->_queryString = $this->_queryString . '&password1=' . $password;
		$this->_queryString = $this->_queryString . '&password2=' . $password;
		$this->_queryString = $this->_queryString . '&email=' . $email;
		$this->_queryString = $this->_queryString . '&firstname=' . $firstName;
		$this->_queryString = $this->_queryString . '&lastname=' . $lastName;
		$this->_queryString = $this->_queryString . '&address=' . $address;
		$this->_queryString = $this->_queryString . '&birthdate=' . $birthDate;
		$this->_queryString = $this->_queryString . '&city=' . $city;
		$this->_queryString = $this->_queryString . '&countrycode=' . $countryCode;
		$this->_queryString = $this->_queryString . '&phone=' . $phone;
		$this->_queryString = $this->_queryString . '&zip=' . $zip;
		$this->_queryString = $this->_queryString . '&currency=' . $currency;		
		$this->_queryString = $this->_queryString . '&viplevel=' . $viplevel;
		$this->_queryString = $this->_queryString . '&skin=' . $skin;

		$this->_fullUri = $this->_URI;
		
		$this->_fullUri = $this->_fullUri . '/' . $_methodFileName . $this->_queryString;
                
                /*
		$response = $this->SubmitData( $this->_fullUri );	
                
                //Enable PT creation, 08/22/13
                if($response[0] == 200){
               
                    $this->_APIresponse = $this->XML2Array($response[1]);
                    
                } else {
                    
                    $this->_error = $response[0];
                }
                */
               
                //always pass OK, to continue transaction in membership, remove this
                //if PT will be deployed 06/25/13
                $this->_APIresponse = array('transaction'=>array('@attributes'=>array('result'=>'OK')));
                
                return $this->_APIresponse;	
	}	
		
	public function SetVIPLevel( $userName, $password, $vipLevel )
	{
		$_methodFileName = 'change_player.php';
		
		$this->InitQueryString();        
		$this->_queryString = $this->_queryString . '&sync_username=' . $userName;
		$this->_queryString = $this->_queryString . '&sync_password=' . $password;
		$this->_queryString = $this->_queryString . '&viplevel=' . $vipLevel;
		
		$this->_fullUri = $this->_URI;
		
		$this->_fullUri = $this->_fullUri . '/' . $_methodFileName . $this->_queryString;
		
		$response = $this->SubmitData( $this->_fullUri );	
                
                if($response[0] == 200){
                    
                    $this->_APIresponse = $this->XML2Array($response[1]);
                    
                } else {
                    
                    $this->_error = $response[0];
                }
                
                return $this->_APIresponse;
	}	
	
	private function SubmitData( $URI )
	{
		$curl = curl_init( $URI );

		curl_setopt( $curl, CURLOPT_FRESH_CONNECT, $this->_caching );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout );
		curl_setopt( $curl, CURLOPT_TIMEOUT, $this->_timeout );
		curl_setopt( $curl, CURLOPT_USERAGENT, $this->_userAgent );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $curl, CURLOPT_POST, FALSE );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );

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