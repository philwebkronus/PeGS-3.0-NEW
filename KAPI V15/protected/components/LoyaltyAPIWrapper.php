<?php

/**
 * Date Created 03 11, 2013 2:30:05 PM <pre />
 * Description of LoyaltyWrapper
 * @author aqdepliyan
 */
class LoyaltyAPIWrapper {
    
      /**
     *
     * @param type $card_number
     * @param type $isReg
     * @param type $return_transfer (if 1 or true it will return transfer on success, if not 1 or true it will display transfer on success
     * @return type 
     */
    public function getCardInfo($card_number, $return_transfer=false, $isReg = 0) {
        $card_number = urlencode(trim($card_number));
        $isReg = urlencode(trim($isReg));
        
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, Yii::app()->params['mem_card_inquiry'] . '?cardnumber=' . $card_number.'&isreg='.$isReg);
        curl_setopt( $ch, CURLOPT_FRESH_CONNECT, FALSE );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 500 );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'PEGS Station Manager' );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, $return_transfer );
        curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );

        $result = curl_exec( $ch );
        $http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );
        
        return $result;
    }
    
    /**
     *
     * @param type $oldnumber (old loyalty card) 
     * @param type $newnumber (membershipcard either existing or newly registered)
     * @param type $aid (account id)
     * @param type $return_transfer (if 1 or true it will return transfer on success, if not 1 or true it will display transfer on success
     * @return type 
     */
    public function transferPoints($oldnumber,$newnumber,$aid,$return_transfer=false) {
        
        $oldnumber = urlencode(trim($oldnumber));
        $newnumber = urlencode(trim($newnumber));
        $aid = urlencode(trim($aid));
        
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, Yii::app()->params['mem_transfer_points'] . '?oldnumber=' . $oldnumber.'&newnumber='.$newnumber.'&aid='.$aid);
        curl_setopt( $ch, CURLOPT_FRESH_CONNECT, FALSE );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 500 );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'PEGS Station Manager' );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, $return_transfer );
        curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );

        $result = curl_exec( $ch );
        $http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );
        
        return $result;
    }

    /**
     *
     * @param type $card_number
     * @param type $transdate
     * @param type $payment_type
     * @param type $transtype
     * @param type $amount
     * @param type $site_id
     * @param type $transid
     * @param type $terminal_login
     * @param type $iscreditable
     * @param type $vouchercode
     * @param type $service_id
     * @param type $return_transfer (if 1 or true it will return transfer on success, if not 1 or true it will display transfer on success
     * @return type 
     */
    public function processPoints($card_number, $transdate, $payment_type, $transtype, $amount,
                                                                $site_id, $transid, $terminal_login, $iscreditable, $vouchercode='', 
                                                                $service_id = 7,$return_transfer=false) 
    {
        
        $card_number = urlencode(trim($card_number));
        $transid = urlencode(trim($transid));
        $transdate = urlencode(trim($transdate));
        $transtype = urlencode(trim($transtype));
        $payment_type = urlencode(trim($payment_type));
        $amount = urlencode(trim($amount));
        $site_id = urlencode(trim($site_id));
        $service_id = urlencode(trim($service_id));
        $terminal_login = urlencode(trim($terminal_login));
        $iscreditable  = urlencode(trim($iscreditable));
        $vouchercode  = urlencode(trim($vouchercode));
        
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, Yii::app()->params['mem_process_points'] . '?cardnumber=' . $card_number.'&transactionid='.$transid.'&transdate='.$transdate.
                                                                                                                '&transtype='.$transtype.'&paymenttype='.$payment_type.'&amount='.$amount.'&siteid='.$site_id.
                                                                                                                '&serviceid='.$service_id.'&terminallogin='.$terminal_login.'&iscreditable='.$iscreditable.
                                                                                                                '&vouchercode='.$vouchercode);
       
        curl_setopt( $ch, CURLOPT_FRESH_CONNECT, FALSE );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 500 );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'PEGS Station Manager' );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );
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
