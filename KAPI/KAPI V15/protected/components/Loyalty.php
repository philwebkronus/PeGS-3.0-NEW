<?php

/**
 * Date Created 11 21, 11 2:40:05 PM <pre />
 * Description of Loyalty
 * @author Bryan Salazar
 */
class Loyalty {
    public function getCardInfo($card_number,$return_transfer=false) {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, Yii::app()->params['card_inquiry'] . '?SerialNumber=' . $card_number);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $return_transfer); 
        $result = curl_exec($ch);
        curl_close($ch);   
        return $result;
    }
        
    /**
     *
     * @param type $session_id
     * @param type $card_number
     * @param type $trans_date
     * @param type $amount
     * @param type $pos_account_no
     * @param type $service_id
     * @param type $terminal_login
     * @param type $provider_trans_id
     * @param type $return_transfer (if 1 or true it will return transfer on success, if not 1 or true it will display transfer on success
     * @return type 
     */
    public function loyaltyDeposit($session_id, $card_number, $trans_date,$amount, $pos_account_no, $service_id, $terminal_login, $provider_trans_id,$return_transfer=false) {
        //open connection
        $ch = curl_init();
        $url = $this->getUrlAddPoints($session_id, $card_number, $trans_date, $amount, $pos_account_no, $service_id, $terminal_login, $provider_trans_id);
//        MI_Logger::log('===DEPOSIT==='.$url, E_ERROR);
        curl_setopt($ch,CURLOPT_URL, $url);
        
        // display or not the return
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $return_transfer); 
        
        //execute post
        $result = curl_exec($ch);
        
        //close connection
        curl_close($ch);   
        return $result;
    }
    
    public function loyaltyReload($session_id,$trans_date,$amount,$pos_account_no,$service_id,$terminal_login,$provider_trans_id,$return_transfer=false) {
        $ch = curl_init();
        $url = $this->getUrlReloadPoints($session_id,$trans_date,$amount,$pos_account_no,$service_id,$terminal_login,$provider_trans_id);
//        MI_Logger::log('===RELOAD==='.$url, E_ERROR);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $return_transfer); 
        $result = curl_exec($ch);
        curl_close($ch);  
        return $result;
    }
    
    public function loyaltyWithdraw($session_id,$trans_date,$amount,$pos_account_no,$service_id,$terminal_login,$provider_trans_id,$return_transfer=false) {
        $ch = curl_init();
        $url = $this->getUrlWithdraw($session_id, $trans_date, $amount, $pos_account_no, $service_id, $terminal_login,$provider_trans_id);
//        MI_Logger::log('===WITHDRAW==='.$url, E_ERROR);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        $result = curl_exec($ch);
        curl_close($ch);  
        return $result;
    }
    
    // url for deposit
    public function getUrlAddPoints($session_id,$card_number,$trans_date,$amount,$pos_account_no,$service_id,$terminal_login,$provider_trans_id) {
        $old = trim($amount);
        $amount = explode('.', trim($amount));
        if(count($amount) == 2)
            $amount = $amount[0];
        else
            $amount = $old;        
   
        return Yii::app()->params['add_points'] . '?sessionid=' . $session_id . '&cardnumber=' . $card_number . '&transdate=' . $trans_date . 
                '&amount=' . $amount . '&posaccountno=' . $pos_account_no . '&serviceid=' . $service_id . '&terminallogin=' . $terminal_login . 
                '&providertransactionid=' . $provider_trans_id;
    }
    
    public function getUrlReloadPoints($session_id,$trans_date,$amount,$pos_account_no,$service_id,$terminal_login,$provider_trans_id) {
        $old = trim($amount);
        $amount = explode('.', trim($amount));
        if(count($amount) == 2)
            $amount = $amount[0];
        else
            $amount = $old;        
        
        return Yii::app()->params['add_points'] . '?sessionid=' . $session_id . '&transdate=' . $trans_date . '&amount=' . $amount . 
                '&posaccountno=' . $pos_account_no . '&serviceid=' . $service_id . '&terminallogin=' . $terminal_login . 
                '&providertransactionid=' . $provider_trans_id;
    }
    
    // url for deposit
    public function getUrlWithdraw($session_id,$trans_date,$amount,$pos_account_no,$service_id,$terminal_login,$provider_trans_id) {
        $old = trim($amount);
        $amount = explode('.', trim($amount));
        if(count($amount) == 2)
            $amount = $amount[0];
        else
            $amount = $old;
        return Yii::app()->params['withdraw'] . '?sessionid=' . $session_id . '&transdate=' . $trans_date . '&amount=' . $amount . 
                '&posaccountno=' . $pos_account_no . '&serviceid=' . $service_id . '&terminallogin=' . $terminal_login . 
                '&providertransactionid=' . $provider_trans_id;
    }    
}

