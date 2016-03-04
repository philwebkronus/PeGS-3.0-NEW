<?php

class LoyaltyUBWrapper{
          
   /**
     *
     * @param type $card_number
     * @param type $isReg
     * @param type $return_transfer (if 1 or true it will return transfer on success, if not 1 or true it will display transfer on success
     * @return type 
     */
    public function getCardInfo2($card_number, $connection, $return_transfer=false, $isReg = 0) {
        
        $card_number = urlencode(trim($card_number));
        $isReg = urlencode(trim($isReg));
        
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $connection . '?cardnumber=' . $card_number.'&isreg='.$isReg);
        curl_setopt( $ch, CURLOPT_FRESH_CONNECT, FALSE );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 500 );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'PEGS Station Manager' );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, $return_transfer );
        curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );
        
        $result = curl_exec( $ch );
        $http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );
        
        return $result;
    }
    
}
    

    
?>
