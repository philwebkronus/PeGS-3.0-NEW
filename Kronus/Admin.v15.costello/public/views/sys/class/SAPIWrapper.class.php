<?php

class SAPIWrapper {
    
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
    
    private $_error;
    
    public function endSession($terminalName, $url){
        $uri = $url."?TerminalName=".$terminalName."&CommandID=10";
        
        $result = $this->SubmitData($uri);
        if ($result[0] == 200) {
            return $result[1];
        }
        else {
            $this->_error = $result[0];
            
            return $this->_error;
        }
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
