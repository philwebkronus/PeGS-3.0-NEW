<?php

class LoyaltyAPIWrapper{

    /**
     *
     * @param type $card_number
     * @param type $isReg
     * @param type $return_transfer (if 1 or true it will return transfer on success, if not 1 or true it will display transfer on success
     * @return type 
     */
    public function getCardInfo($card_number, $isReg = 0) {

        $card_number = urlencode(trim($card_number));
        $isReg = urlencode(trim($isReg));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, Yii::app()->params['card_inquiry'] . '?cardnumber=' . $card_number . '&isreg=' . $isReg);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, FALSE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_USERAGENT, 'PEGS Station Manager');
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        $result = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return array($http_status, $result);

    }
}
