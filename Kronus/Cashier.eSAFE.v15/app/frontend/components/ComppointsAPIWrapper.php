<?php

/**
 * Date Created 03 11, 2013 2:30:05 PM <pre />
 * Description of LoyaltyWrapper
 * @author aqdepliyan
 */
class ComppointsAPIWrapper {
    
 
    /**
     *
     * @param type $card_number
     * @param type $transdate
     * @param type $payment_type
     * @param type $transtype
     * @param type $amount
     * @param type $site_id
     * @param type $eWallletTransID
     * @param type $terminal_login
     * @param type $iscreditable
     * @param type $vouchercode
     * @param type $service_id
     * @param type $return_transfer (if 1 or true it will return transfer on success, if not 1 or true it will display transfer on success
     * @return type 
     */
     /**
     *
     * @param type $card_number
     * @param type $transdate
     * @param type $payment_type
     * @param type $transtype
     * @param type $amount
     * @param type $site_id
     * @param type $eWallletTransID
     * @param type $terminal_login
     * @param type $iscreditable
     * @param type $vouchercode
     * @param type $service_id
     * @param type $return_transfer (if 1 or true it will return transfer on success, if not 1 or true it will display transfer on success
     * @return type 
     */
    public function processPoints($card_number, $transdate, $payment_type, $transtype, $amount, $site_id, $eWallletTransID, $terminal_login, $iscreditable, $vouchercode, $service_id, $return_transfer = false) {

        $card_number = urlencode(trim($card_number));
        $eWallletTransID = urlencode(trim($eWallletTransID));
        $transdate = urlencode(trim($transdate));
        $transtype = urlencode(trim($transtype));
        $payment_type = urlencode(trim($payment_type));
        $amount = urlencode(trim($amount));
        $site_id = urlencode(trim($site_id));
        $service_id = urlencode(trim($service_id));
        $terminal_login = urlencode(trim($terminal_login));
        $iscreditable = urlencode(trim($iscreditable));
        $vouchercode = urlencode(trim($vouchercode));
        
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, Mirage::app()->param['process_points'] . '?cardnumber=' . $card_number . '&transactionid=' . $eWallletTransID . '&transdate=' . $transdate .
                '&transtype=' . $transtype . '&paymenttype=' . $payment_type . '&amount=' . $amount . '&siteid=' . $site_id .
                '&serviceid=' . $service_id . '&terminallogin=' . $terminal_login . '&iscreditable=' . $iscreditable .
                '&vouchercode=' . $vouchercode);
        
        curl_setopt( $ch, CURLOPT_FRESH_CONNECT, FALSE );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 500 );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'PCWS' );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, $return_transfer );
        curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );
        $result = curl_exec( $ch );
        $http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );
        
        $isSuccessful = json_decode($result);
        
        
        if($isSuccessful->AddPoints->StatusCode == 1){
            return true;
        } else {
            return false;
        }
    }
}

