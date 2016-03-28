<?php

/**
 * Class library to call various apis that have fire and forget function
 * This aims to avoid slowness on main process if api host was intermittent or unreachable
 * @author elperez
 * @date May 03, 2013
 * @link http://w-shadow.com/blog/2007/10/16/how-to-run-a-php-script-in-the-background/
 * Using an asynchronous HTTP request
 */
class AsynchronousRequest {
    
    /**
     * Call curl as POST Request and asynchronously
     * @param str $url
     * @param array $params
     * @param str $type GET | POST
     */
    public function curl_request_async($url, $params, $type='GET')
    {
        foreach ($params as $key => &$val) {
          if (is_array($val)) $val = implode(',', $val);
          $post_params[] = $key.'='.urlencode($val);
        }
        $post_string = implode('&', $post_params);

        $parts=parse_url($url);

        try{
            $fp = fsockopen($parts['host'],
                isset($parts['port'])?$parts['port']:80,
                $errno, $errstr, 30);

            // Data goes in the path for a GET request
            if('GET' == $type) $parts['path'] .= '?'.$post_string;

            $out = "$type ".$parts['path']." HTTP/1.1\r\n";
            $out.= "Host: ".$parts['host']."\r\n";
            $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out.= "Content-Length: ".strlen($post_string)."\r\n";
            $out.= "Connection: Close\r\n\r\n";
            // Data goes in the request body for a POST request
            if ('POST' == $type && isset($post_string)) $out.= $post_string;

            fwrite($fp, $out);
            fclose($fp);
        }catch(Exception $e){
            $this->throwError("Unable to call spyder api");
            logger($message . ' URL='.$url . ' Parameters='.$post_string);
        }
    }
    
    
    public function sapiconnect($params){
        $return_transfer = 1;
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_FRESH_CONNECT, 0 );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 1 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt($ch,CURLOPT_URL, Mirage::app()->param['SAPI_URI'] . '?'.$params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $return_transfer);
        curl_setopt( $ch, CURLOPT_POST, FALSE );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );
        curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );
        curl_exec($ch);
        curl_close($ch);  
    }


    /**
     * Description: end the program and send a message with a header of 404
     */
    public function throwError($message) {
        header('HTTP/1.0 404 Not Found');
            echo $message;
            Mirage::app()->end();
    }
}

?>
