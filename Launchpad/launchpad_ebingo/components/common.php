<?php

/**
 * Description of common
 *
 * @author 
 */

require_once( 'checkhost.php' );

class common
{
    // http://petewarden.typepad.com/searchbrowser/2008/06/how-to-post-an.html
    static function curl_post_async( $url, $params )
    {
        foreach ( $params as $key => &$val )
        {
            if ( is_array( $val ) ) $val = implode( ',', $val );
                $post_params[] = $key . '='. urlencode( $val );
        }

        $post_string = implode( '&', $post_params );

        $parts = parse_url( $url );
        $fp = fsockopen( $parts[ 'host' ], isset( $parts[ 'port' ]) ? $parts[ 'port' ]:80, $errno, $errstr, 30 );

        $out = "POST " . $parts[ 'path' ] . " HTTP/1.1\r\n";
        $out.= "Host: " . $parts[ 'host' ] . "\r\n";
        $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out.= "Content-Length: " . strlen( $post_string ) . "\r\n";
        $out.= "Connection: Close\r\n\r\n";

        if ( isset( $post_string ) ) $out .= $post_string;

        fwrite( $fp, $out );
        fclose( $fp );
    }

    // network related

    static function getUserHostAddress()
    {
        if ( isset( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) )
        {
            return $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
        }
        else
        {
            return $_SERVER[ 'REMOTE_ADDR' ];
        }
    }

    static function isHostReachable( $url, $port )
    {
        $urlArray = parse_url( $url );

        $host = $urlArray[ 'host' ];

        $ch = new CheckHost( $host, $port, 'fsockopen' );

        return $ch->Check();
    }

    // security related

    
}

?>