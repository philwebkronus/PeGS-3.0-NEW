<?php
#Name: PlayTechRevertBrokenGamesAPI.class.php
#Author: WEBiTS
#Version: 1.0.0
#Copyright 2013 PhilWeb Corporation

class PlayTechRevertBrokenGamesAPI
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

	public function __construct( $url, $certFilePath = '', $keyFilePath = '', $passPhrase = '', $caching = FALSE )
	{
			$this->_url = $url;
			$this->_certFilePath = $certFilePath;
			$this->_keyFilePath = $keyFilePath;
			$this->_passPhrase = $passPhrase;
	}

	public function DoRevertBrokenGames( $playerUsername, $playerMode = 'real', $revertMode = 'cancel' )
	{
                        ///brokengames?mode=revert';
                            $isSucceed = FALSE;
                            $fullUri = $this->_url . '/brokengames?mode=' . $revertMode;
                            
                            $httpHeader = array( 'Content-Type: text/plain; charset=utf-8', 'X-Player-Mode: ' . $playerMode, 'X-Player-Username: ' . $playerUsername );

                            $response = $this->SubmitData( $fullUri, $httpHeader );
                            
                            print_r("Request: ".$fullUri);
                            print_r("Response: ");
                            print_r($response);
                            exit;
                            if ( $response[0] == 200 )
                            {
                                    $isSucceed = TRUE;
                            }

                            return array( $isSucceed, $response[1] );
	}
    
    
    public function DoRevertBrokenGames2( $playerUsername, $playerMode = 'real', $revertMode = 'cancel' )
	{
                        ///brokengames?mode=revert';
                            $isSucceed = FALSE;
                            $fullUri = $this->_url . '/brokengames?mode=' . $revertMode;
                            
                            $httpHeader = array( 'Content-Type: text/plain; charset=utf-8', 'X-Player-Mode: ' . $playerMode, 'X-Player-Username: ' . $playerUsername );

                            $response = $this->SubmitData( $fullUri, $httpHeader );
                            
                            if ( $response['HTTP_STATUS'] == 200 )
                            {
                                    $isSucceed = TRUE;
                            }

                            return array( $isSucceed, $response[1] );
	}

	private function SubmitData( $URI, $httpHeader = '' )
	{
		$curl = curl_init( $URI );

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
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'DELETE' );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $httpHeader );
                
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );

		$response = curl_exec( $curl );

		$http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

                $total_time = curl_getinfo($curl, CURLINFO_TOTAL_TIME);
                
		curl_close( $curl );

		return array("HTTP_STATUS"=>$http_status, "API_RESPONSE"=>$response, "EXECUTION_TIME"=>$total_time );
	}
}

?>