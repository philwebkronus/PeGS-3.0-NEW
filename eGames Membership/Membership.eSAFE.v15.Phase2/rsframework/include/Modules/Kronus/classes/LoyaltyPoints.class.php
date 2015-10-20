<?php

/**
 * @DateCreated October 12, 2015 <pre />
 * @Description For Points Integration
 * @author javida
 */
class LoyaltyPoints extends BaseEntity {
    
    public function processPoints($card_number, $transdate, $transtype, $amount, $site_id, $iscreditable, $vouchercode = '', $service_id, $return_transfer = false) {

        $card_number = urlencode(trim($card_number));
        $transid = 0;
        $transdate = urlencode(trim($transdate));
        $transtype = urlencode(trim($transtype));
        $payment_type = 1;
        $amount = urlencode(trim($amount));
        $site_id = urlencode(trim($site_id));
        $service_id = App::getParam('serviceid');
        $terminal_login = 0;
        $iscreditable = urlencode(trim($iscreditable));
        $vouchercode = urlencode(trim($vouchercode));
        
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, App::getParam('processpoints') . '?cardnumber=' . $card_number . '&transactionid=' . $transid . '&transdate=' . $transdate .
                '&transtype=' . $transtype . '&paymenttype=' . $payment_type. '&amount=' . $amount . '&siteid=' . $site_id .
                '&serviceid='  . $service_id  . '&terminallogin=' . $terminal_login . '&iscreditable=' . $iscreditable .
                '&vouchercode=' . $vouchercode);
       
        
        curl_setopt( $ch, CURLOPT_FRESH_CONNECT, FALSE );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 500 );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'PEGS Station Manager' );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        //curl_setopt( $ch, CURLOPT_POST, TRUE);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );
        $result = curl_exec( $ch );
        $http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );
        var_dump($result);
       $isSuccessful = json_decode($result, true);
       
        if ($isSuccessful['AddPoints']['StatusCode']==1) {
            return true;
        } else {
            return false;
        }
    }
}

