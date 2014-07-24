<?php

/**
 * Spyder API Wrapper to call lock | unlock function of GTC Terminal
 * @author its-edson
 * @date May 3, 2013
 */

class SpyderController {
    
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
     * Call lock | unlock sapi api method
     * @param str $terminalName
     * @param int $commandId
     * @param str $username
     * @param str $password
     * @param int $type
     */
    public function runAction($terminalName, $commandId, $username, $password, $type, $spyderReqLogID, $casinoId){
        
        $queryString =  '?';
        $queryString = $queryString. 'TerminalName='.$terminalName;
        $queryString = $queryString. '&CommandID='.$commandId;
        $queryString = $queryString. '&UserName='.$username;
        $queryString = $queryString. '&Password='.$password;
        $queryString = $queryString. '&Type='.$type;
        $queryString = $queryString. '&CasinoID='.$casinoId;
        
        $sapi = 'http://192.168.28.62/sapi/index.php'.$queryString;
        
        return $response = $this->SubmitData($sapi);
        
    }

    private function SubmitData( $uri )
    {
            $curl = curl_init( $uri );

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
    
}

?>