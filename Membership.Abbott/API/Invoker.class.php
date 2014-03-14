<?php

/*
 * @author : owliber
 * @date : 2013-04-23
 */

class Invoker
{
    public function Invoker()
    {
        
        
    }
    
    public function SendRequest( $url, $data )
    {
        $curl = curl_init( $url . '?' . $data );

        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_POST, FALSE );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );

        $response = curl_exec( $curl );

        $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

        curl_close( $curl );

        return array( $http_status, $response );
    }
}
?>
