<?php

/**
 * User/Account Based Transaction
 * Common redeem session for TerminalMonitoring. Stand-alone and Hotkey
 * Date Created 11 8, 11 9:17:55 AM <pre />
 * Date Modified May 6, 2013
 * @author Bryan Salazar
 * @author Edson Perez <elperez@philweb.com.ph>
 */
class CommonUBRedeem {
    
    /**
     * @param int $terminal_id
     * @param int $site_id
     * @param [int,string] $bcf
     * @param int $service_id
     * @param int $amount
     * @param int $acct_id
     * @return array 
     */
    public function redeem($terminal_id,$site_id,$bcf,$service_id,$amount, $paymentType,$acct_id,
            $loyalty_card, $mid = '', $userMode = '',$casinoUsername = '',
            $casinoPassword = '', $casinoServiceID = '') {
        Mirage::loadComponents('CasinoApi');
        Mirage::loadModels(array('TerminalsModel', 'CommonTransactionsModel',
                                 'PendingUserTransactionCountModel'));
        
        $casinoApi = new CasinoApi();
        $terminalsModel = new TerminalsModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $pendingUserTransCountModel = new PendingUserTransactionCountModel();
        
        $casinoApi->_doCasinoRules($terminal_id, $service_id, $casinoUsername);
        
        list($terminal_balance,$service_name,$terminalSessionsModel,
                $transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount,$currentbet) = $casinoApi->getBalanceUB($terminal_id, $site_id, 'W', 
                        $casinoServiceID, $acct_id, $casinoUsername, $casinoPassword);
        
        if($redeemable_amount > 0){
            $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);
        
            if($is_terminal_active === false) {
                $message = 'Error: Can\'t get status.';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                CasinoApi::throwError($message);
            }

            if($is_terminal_active < 1) {
                $message = 'Error: Terminal has no active session.';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                CasinoApi::throwError($message);
            }
            
            if($mgaccount != '') {
                $terminal_name = $mgaccount;
            } else {
                $terminal_name = $terminalsModel->getTerminalName($terminal_id);
            }
            
            if($currentbet > 0){
                $result = $casinoApi->RevertBrokenGamesAPI($terminal_id, $service_id, $casinoUsername);
                if($result['RevertBrokenGamesReponse'][0] == false){
                    CasinoApi::throwError("Unable to revert bet on hand.");
                }
            }
            
            //Get Last Transaction Summary ID
            $trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($terminal_id);
            if(!$trans_summary_id){
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Redeem Session Failed. Please check if the terminal
                            has a valid start session.';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                CasinoApi::throwError($message);
            }
            
            //get last transaction ID if service is MG
            if(strpos($service_name, 'MG') !== false) {
                $trans_origin_id = 0; //cashier origin Id
                $transaction_id = $terminalsModel->insertserviceTransRef($service_id, $trans_origin_id);
                if(!$transaction_id){
                    $message = "Error: Failed to insert record in servicetransactionref";
                    logger($message);
                    CasinoApi::throwError($message);
                }
            } else {
                $transaction_id = '';
            }

            $udate = CasinoApi::udate('YmdHisu');

            //insert into transaction request log
            $trans_req_log_last_id = $transReqLogsModel->insert($udate, $amount, 'W', $paymentType,
                    $terminal_id, $site_id, $service_id,$loyalty_card, $mid, $userMode);
            
            
            if(!$trans_req_log_last_id) {
                $pendingUserTransCountModel->updatePendingUserCount($loyalty_card);
                $message = 'There was a pending transaction for this user / terminal.';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                CasinoApi::throwError($message);
            }

            if(toMoney($amount) != toMoney(toInt($redeemable_amount))) {
                $transReqLogsModel->update($trans_req_log_last_id, false, 2,null,$terminal_id);
                $message = 'Error: Redeemable amount is not equal.';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                CasinoApi::throwError($message);
            }
            
            $tracking1 = $trans_req_log_last_id;
            $tracking2 = 'W';
            $tracking3 = $terminal_id;
            $tracking4 = $site_id;   
            $event_id = Mirage::app()->param['mgcapi_event_id'][2]; //Event ID for Withdraw
            
            // check if casino's reply is busy, added 05/17/12
            if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $message = 'Can\'t connect to casino';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                CasinoApi::throwError($message);
            }
            
            
            /************************ WITHDRAW ************************************/
            $resultwithdraw = $casinoApiHandler->Withdraw($casinoUsername, $amount, 
                $tracking1, $tracking2, $tracking3, $tracking4, $casinoPassword, $event_id, $transaction_id);   
        
            //check if Withdraw API reply is null
            if(is_null($resultwithdraw)){

                // check again if Casino Server is busy
                if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
                    $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                    $message = 'Can\'t connect to casino';
                    logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                    CasinoApi::throwError($message);
                }
                
                //execute TransactionSearchInfo API Method
                $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($casinoUsername, 
                                   $tracking1 , $tracking2 , $tracking3, $tracking4, $transaction_id);
                
                //check if TransactionSearchInfo API is not successful
                if(isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false)
                {
                    $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                    $message = 'Error: Request denied. Please try again.';
                    logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id.' ErrorMessage='.$transSearchInfo['ErrorMessage']);
                    CasinoApi::throwError($message);
                }

                //Check TransactionSearchInfo API
                if(isset($transSearchInfo['TransactionInfo']))
                {
                    //RTG / Magic Macau
                    if(isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']))
                    {
                        $amount = abs($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['amount']);
                        $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                        $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                    }
                    //MG / Vibrant Vegas
                    elseif(isset($transSearchInfo['TransactionInfo']['MG']))
                    {
                        //$amount = abs($transSearchInfo['TransactionInfo']['Balance']); //returns 0 value
                        $transrefid = $transSearchInfo['TransactionInfo']['MG']['TransactionId'];
                        $apiresult = $transSearchInfo['TransactionInfo']['MG']['TransactionStatus'];
                    }
                    //PT / PlayTech
                    if(isset($transSearchInfo['TransactionInfo']['PT'])){
                        $transrefid = $transSearchInfo['TransactionInfo']['PT']['id'];
                        $apiresult = $transSearchInfo['TransactionInfo']['PT']['status'];
                    }
                }
            } else {
                //check if TransactionSearchInfo API is not successful
                if(isset($resultwithdraw['IsSucceed']) && $resultwithdraw['IsSucceed'] == false) {
                    $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
                    $message = 'Error: Request denied. Please try again.';
                    logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id.' ErrorMessage='.$resultwithdraw['ErrorMessage']);
                    CasinoApi::throwError($message);
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
            }
            
            if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved') {
                $transstatus = '1';
            } else {
                $transstatus = '2';
            }

            //if Withdraw / TransactionSearchInfo API status is approved
            if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved'){
                
                //$trans_summary_id = $transactionSummaryModel->getLastTransSummaryId($terminal_id, $site_id);
                
                $isredeemed = $commonTransactionsModel->redeemTransaction($amount, $trans_summary_id, $udate, 
                                    $site_id, $terminal_id, 'W', $paymentType,$service_id, $acct_id, $transstatus,
                                    $loyalty_card, $mid);

                $transReqLogsModel->update($trans_req_log_last_id, $apiresult, $transstatus,$transrefid,$terminal_id);
                
                if(!$isredeemed){                    
                    $message = 'Error: Failed update records in transaction tables';
                    logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                    CasinoApi::throwError($message);
                }

                return array('message'=>'You have successfully redeemed the amount of PhP ' . toMoney($amount),
                    'trans_summary_id'=>$trans_summary_id,'udate'=>$udate,'amount'=>$amount,'terminal_login'=>$terminal_name,
                    'trans_ref_id'=>$transrefid,'terminal_name'=>$terminal_name,'trans_details_id'=>$isredeemed);
            } else {
                $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
                $message = 'Error: Request denied. Please try again.';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                CasinoApi::throwError($message);
            }
        } else {
            
            $this->_doCasinoRules($service_name, $casinoUsername);
            
            return array('message'=>'Info: Session has been ended.',
                         'amount'=>$redeemable_amount);
        }    
    }
    
    /**
     * Additional casino rules before session ending
     * @param str $service_name
     * @param str $terminal_name 
     */
    private function _doCasinoRules($casinoApiHandler, $service_name, $terminal_name){
        
        //if PT, freeze and force logout its account
        if(strpos($service_name, 'PT') !== false) {

            $kickPlayerResult = $casinoApiHandler->KickPlayer($terminal_name);
             
            $changeStatusResult = $casinoApiHandler->ChangeAccountStatus($terminal_name, 1);

            if(!$changeStatusResult['IsSucceed']){
                $message = "PT : Failed to lock the terminal.";
                logger($message);
                CasinoApi::throwError($message);
            }

            if(!$kickPlayerResult['IsSucceed']){
                $message = "PT : Failed to end gaming session";
                logger($message);
                CasinoApi::throwError($message);
            }
        }
    }
}