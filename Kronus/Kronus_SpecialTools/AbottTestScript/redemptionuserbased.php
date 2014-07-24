<?php

include 'components/SpyderController.php';
include 'CasinoAPI/RealtimeGamingCashierAPI2.class.php';

class redemptionuserbased{
    
    function redeem($redemptionmodel, $login_pwd, $terminal_id, $site_id, $bcf, $service_id, $amount, $paymentType, $acct_id, $loyalty_card, $terminalcode, $mid = '', $userMode = '',$casinoUsername = '', $casinoPassword = '', $casinoServiceID = ''){
        
        $timechecker = array();
        
        $spyder = $redemptionmodel->getSpyderStatus($site_id);
        
        if(isset($spyder)){
            $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
            $datetime = array('GetSiteSpyderStatus'=>$date);
            array_push($timechecker, $datetime);
        }
        
        $spyderstatus = $spyder['Spyder'];
        
        if($spyderstatus == 1){
            
            $spyderReqLogID = $redemptionmodel->insertSpyderRequest($terminalcode, 1);
            
            $terminal = substr($terminalcode, strlen("ICSA-")); //removes the "icsa-
            $computerName = str_replace("VIP", '', $terminal);

            $spyder = new SpyderController();
            
            $spyderresult = $spyder->runAction($computerName, 1, $casinoUsername, $login_pwd, 1, $spyderReqLogID, $service_id);
            
            if(isset($spyderresult)){
                $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
                $datetime = array('CallSpyderAPI'=>$date);
                array_push($timechecker, $datetime);
            }
            
            if($spyderresult[0] == 200 ){
                if($spyderresult[1] == "1"){
                      //update spyder request logs as successful
                      $status = 1;
                      $redemptionmodel->updateSpyderRequest($status, $spyderReqLogID);
                      

                } else {
                      //update spyder request logs as failed
                      $status = 2;
                      $redemptionmodel->updateSpyderRequest($status, $spyderReqLogID);
                }
            } else {
                //update spyder request logs as failed
               $status = 2;
               $redemptionmodel->updateSpyderRequest($status, $spyderReqLogID);
            }
            
        }
        
        $issessionactive = $redemptionmodel->isSessionActive($terminal_id);
        
        if(isset($issessionactive)){
            $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
            $datetime = array('CheckidsessionisActive'=>$date);
            array_push($timechecker, $datetime);
          
        }
        
        $cashierUrl = 'https://125.5.1.18/ABBOTTRUVMANSYWPLMXI/processor/ProcessorAPI/Cashier2.asmx';
        $certFilepath = '/var/www/AbottAPITest/19/cert.pem';
        $keyFilePath = '/var/www/AbottAPITest/19/key.pem';

        $cashierAPI = new RealtimeGamingCashierAPI($cashierUrl, $certFilepath, $keyFilePath, '');
        
        $PIDresult = $cashierAPI->GetPIDFromLogin($casinoUsername);
        
        if ( $PIDresult[ 'GetPIDFromLoginResult' ] != null )
        {
            $pidresult = array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'PID' => $PIDresult[ 'GetPIDFromLoginResult' ] );
        }
        else
        {
            $pidresult = array( 'IsSucceed' => false, 'ErrorCode' => 30, 'ErrorMessage' => 'Response malformed' );
        }
        
        if(isset($pidresult)){
            $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
            $datetime = array('RTGAPIGetPIDFromLogin'=>$date);
            array_push($timechecker, $datetime);
          
        }
        
        $PID = $pidresult['PID'];
        
        $GetAccountBalanceResult = $cashierAPI->GetAccountBalance(1, $PID);
        
        if ( $GetAccountBalanceResult[ 'GetAccountBalanceResult' ][ 'balance' ] )
        {
            $balance = (float)$GetAccountBalanceResult[ 'GetAccountBalanceResult' ][ 'balance' ];
            $bonusBalance = (float)$GetAccountBalanceResult[ 'GetAccountBalanceResult' ][ 'bonusBalance' ];

            $redeemable = $balance - $bonusBalance;

            $redeemable_amount = array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'BalanceInfo' => array( 'Balance' => $balance, 'BonusBalance' => $bonusBalance, 'Redeemable' => $redeemable ) );
        }
        else
        {
            $redeemable_amount =  array( 'IsSucceed' => false, 'ErrorCode' => 10, 'ErrorMessage' => 'Response malformed' );
        }
        
        if(isset($redeemable_amount)){
              $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
              $datetime = array('RTGAPIGetAccountBalance'=>$date);
              array_push($timechecker, $datetime);
            
        }
        
        $redeemable_amount = $redeemable_amount['BalanceInfo']['Redeemable'];
        
        $trans_summary_id = $redemptionmodel->getLastSessSummaryID($terminal_id);
        
        $udate = $this->udate('YmdHisu');
        
        $trans_req_log_last_id = $redemptionmodel->insertTransactionRequestLogs($udate, $amount, 'W', $paymentType,
                $terminal_id, $site_id, $service_id,$loyalty_card, $mid, $userMode, '');
        
        if(isset($trans_req_log_last_id)){
              $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
              $datetime = array('InsertinTransactionRequestLogs'=>$date);
              array_push($timechecker, $datetime);
            
        }
        
        if($redeemable_amount > 0){
            
            $tracking1 = $trans_req_log_last_id;
            $tracking2 = 'W';
            $tracking3 = $terminal_id;
            $tracking4 = $site_id;   
            $event_id = '10003'; //Event ID for Withdraw
            $methodID = 502;
            
            
            /************************ WITHDRAW ************************************/
            $hashedPassword = sha1( $casinoPassword );
            $sessionID = $cashierAPI->Login(1, $PID, $hashedPassword, 1, $_SERVER[ 'HTTP_HOST' ]);
            
            if(isset($sessionID)){
                $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
                $datetime = array('RTGAPILogin'=>$date);
                array_push($timechecker, $datetime);
                
            }
            
            $sessionID = $sessionID['LoginResult'];
            
            $resultwithdraw = $cashierAPI->WithdrawGeneric(1, $PID, $methodID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionID);
            
            if ( is_array( $resultwithdraw ) )
            {
                $resultwithdraw = array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $resultwithdraw );
            }
            else
            {
                $resultwithdraw = array( 'IsSucceed' => false, 'ErrorCode' => 60, 'ErrorMessage' => 'Response malformed' );
            }
            
            if(isset($resultwithdraw)){
                $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
                $datetime = array('RTGAPIWithdrawGeneric'=>$date);
                array_push($timechecker, $datetime);
                
            }
                
            //check Withdraw API Result
            if (isset($resultwithdraw['TransactionInfo'])) {
                //RTG / Magic Macau
                if(isset($resultwithdraw['TransactionInfo']['WithdrawGenericResult'])) {
                    $transrefid = $resultwithdraw['TransactionInfo']['WithdrawGenericResult']['transactionID'];
                    $apiresult = $resultwithdraw['TransactionInfo']['WithdrawGenericResult']['transactionStatus'];
                } 
                //MG / Vibrant Vegas
                if(isset($resultwithdraw['TransactionInfo']['MG'])) {
                    $transrefid = $resultwithdraw['TransactionInfo']['MG']['TransactionId'];
                    $apiresult = $resultwithdraw['TransactionInfo']['MG']['TransactionStatus'];
                }
                //PT / Rocking Reno
                if(isset($resultwithdraw['TransactionInfo']['PT'])) {
                    $transrefid = $resultwithdraw['TransactionInfo']['PT']['TransactionId'];
                    $apiresult = $resultwithdraw['TransactionInfo']['PT']['TransactionStatus'];
                }
            }
            
            
            if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved') {
                $transstatus = '1';
            } else {
                $transstatus = '2';
            }

            //if Withdraw / TransactionSearchInfo API status is approved
            if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved'){
                                
                $isredeemed = $redemptionmodel->redeemTransaction($amount, $trans_summary_id, $udate, 
                                    $site_id, $terminal_id, 'W', $paymentType,$service_id, $acct_id, $transstatus,
                                    $loyalty_card, $mid);
                
                  if(isset($isredeemed)){
                    $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
                    $datetime = array('InsertinTransactionalTables'=>$date);
                    array_push($timechecker, $datetime);

                  }

                $updatetranslogs = $redemptionmodel->updateTransLogs($trans_req_log_last_id, $apiresult, $transstatus,$transrefid,$terminal_id);
                
                if(isset($updatetranslogs)){
                    $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
                    $datetime = array('UpdateTransLogs'=>$date);
                    array_push($timechecker, $datetime);

                }
                
                if(!$isredeemed){                    
                    $message = 'Error: Failed update records in transaction tables';
                }

                $confirm = array('message'=>'You have successfully redeemed the amount of PhP ' . $amount);
                
                array_push($timechecker, $confirm);
                
                $confirm2 = array('trans_details_id'=>$isredeemed);
                
                array_push($timechecker, $confirm2);
                
                
                $udate =  $this->udate('Y-m-d H:i:s.u');
       
    
                $loyaltyrequestlogsID = $redemptionmodel->insertLoyaltyReqLogs($mid, 'W', $terminal_id, $amount, $isredeemed,1,1);

                $card_number = urlencode(trim($loyalty_card));
                $transid = urlencode(trim($isredeemed));
                $transdate = urlencode(trim($udate));
                $transtype = urlencode(trim('W'));
                $payment_type = urlencode(trim('1'));
                $amount = urlencode(trim($amount));
                $site_id = urlencode(trim($site_id));
                $service_id = urlencode(trim($casinoServiceID));
                $terminal_login = urlencode(trim($terminalcode));
                $iscreditable  = urlencode(trim('1'));
                $vouchercode  = urlencode(trim(''));

                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL, 'http://172.16.102.174/membership.rewards/API/addpoints.php' . '?cardnumber=' . $card_number.'&transactionid='.$transid.'&transdate='.$transdate.
                                                                                                                        '&transtype='.$transtype.'&paymenttype='.$payment_type.'&amount='.$amount.'&siteid='.$site_id.
                                                                                                                        '&serviceid='.$service_id.'&terminallogin='.$terminal_login.'&iscreditable='.$iscreditable.
                                                                                                                        '&vouchercode='.$vouchercode);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                $results = curl_exec($ch);
                curl_close($ch);   

                $isSuccessfuls = json_decode($results);

                if($isSuccessfuls->AddPoints->StatusCode == 1){
                    $isSuccessful = true;
                } else {
                    $isSuccessful = false;
                }


                if($isSuccessful){
                    $redemptionmodel->updateLoyaltyRequestLogs($loyaltyrequestlogsID,1);

                    $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
                    $datetime3 = array('LoyaltyProcessPoints'=>$date);

                    array_push($timechecker, $datetime3);

                } else {
                    $redemptionmodel->updateLoyaltyRequestLogs($loyaltyrequestlogsID,2);
                } 
                
                return $timechecker;
                
            } else {
                $redemptionmodel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
                $message = 'Error: Request denied. Please try again.';
                
            }
        } else {
                        
            $isredeemed = $redemptionmodel->redeemTransaction($amount, $trans_summary_id, $udate, 
                                        $site_id, $terminal_id, 'W', $paymentType,$service_id, $acct_id, 1,
                                        $loyalty_card, $mid);
            
            if(isset($isredeemed)){
                        $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8);
                        $datetime = array('InsertinTransactionalTables'=>$date);
                        array_push($timechecker, $datetime);
            }
                  
            
            $updatetranslogs = $redemptionmodel->updateTransReqLogDueZeroBal($terminal_id, $site_id, 'W', $trans_req_log_last_id);
            
            if(isset($updatetranslogs)){
                        $date = date("Y-m-d H:i:s") . substr((string)microtime(), 1, 8); 
                        $datetime = array('UpdateTransLogs'=>$date);
                        array_push($timechecker, $datetime);
            }
                    
            $confirm = array('message'=>'Info: Session has been ended.');
            
            array_push($timechecker, $confirm);

            return $timechecker;
        }
        
    }
    
    
    
    public function callSpyderAPI($commandId, $terminalcode, $login_uname, $login_pwd,
                                     $service_id)
    {    
            $spyderRequestLogsModel = new SpyderRequestLogsModel();
            $asynchronousRequest = new AsynchronousRequest();

            $spyder_req_id = $spyderRequestLogsModel->insert($terminalName, $commandId);

            $terminal = substr($terminalcode, strlen("ICSA-")); //removes the "icsa-
            $computerName = str_replace("VIP", '', $terminal);

            $params = array('r'=>'spyder/run','TerminalName'=>$computerName,'CommandID'=>$commandId,
                            'UserName'=>$login_uname,'Password'=>$login_pwd,'Type'=> Mirage::app()->param['SAPI_Type'],
                            'SpyderReqID'=>$spyder_req_id,'CasinoID'=>$service_id);
                        
            $asynchronousRequest->curl_request_async(Mirage::app()->param['Asynchronous_URI'], $params);
        
    }
    
    function udate($format, $utimestamp = null) {
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
}
?>
