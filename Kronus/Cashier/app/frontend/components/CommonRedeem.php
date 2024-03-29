<?php

/**
 * For Terminal Based Transaction 
 * Common redeem session for TerminalMonitoring. Stand-alone and Hotkey
 * Date Created 11 8, 11 9:17:55 AM <pre />
 * Date Modified May 6, 2013
 * @author Bryan Salazar
 * @author Edson Perez <elperez@philweb.com.ph>
 */
class CommonRedeem {
    
     /**
     * Redeem method for terminal based
     * @param str $login_pwd [Terminal Password (Hashed)]
     * @param int $terminal_id 
     * @param int $site_id
     * @param str $bcf
     * @param int $service_id
     * @param str $amount
     * @param int $paymentType [((1)Cash, (2)Voucher)]
     * @param int $acct_id [cashier id]
     * @param str $loyalty_card [membership card]
     * @param int $mid [membership id]
     * @param int $userMode [(0)Terminal, (1)User Based]
     * @return array result
     */
     public function redeem($login_pwd, $terminal_id,$site_id,$bcf,$service_id,$amount, $paymentType,$acct_id,
            $loyalty_card, $mid = '', $userMode = '') {
        
        Mirage::loadComponents('CasinoApi');
        Mirage::loadModels(array('TerminalsModel', 'EgmSessionsModel','CommonTransactionsModel',
                                 'PendingTerminalTransactionCountModel'));
        
        $casinoApi = new CasinoApi();
        $terminalsModel = new TerminalsModel();
        $egmSessionsModel = new EgmSessionsModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $pendingTerminalTransactionCountModel = new PendingTerminalTransactionCountModel();
                
        $terminalname = $terminalsModel->getTerminalName($terminal_id);
        
        //call SAPI, lock launchpad terminal
        $casinoApi->callSpyderAPI($commandId = 1, $terminal_id, $terminalname, $login_pwd, $service_id);
        
        //call PT, freeze and force logout of session
        $casinoApi->_doCasinoRules($terminal_id, $service_id, $terminalname);
        
        list($terminal_balance,$service_name,$terminalSessionsModel,
                $transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount,$currentbet) = $casinoApi->getBalance(
                        $terminal_id, $site_id,'W',$service_id,$acct_id,$login_pwd);
        
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
            $terminal_name = $terminalname;
        }

        //revert player bet on hand regardless of the current bet, for PT only
        if(strpos($service_name, 'PT') !== false) {
            $result = $casinoApi->RevertBrokenGamesAPI($terminal_id, $service_id, $terminal_name);
            if($result['RevertBrokenGamesReponse'][0] == false){
                //unfreeze PT account 
                $casinoApiHandler->ChangeAccountStatus($terminal_name, 0);
                //unlock launchpad gaming terminal
                $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminal_name, $login_pwd, $service_id);
                CasinoApi::throwError("Unable to revert bet on hand.");
            }
        }
        
        //check if there was a pending game bet for RTG
        if(strpos($service_name, 'RTG') !== false) {
            $PID = $casinoApiHandler->GetPIDLogin($terminal_name);
            $pendingGames = $casinoApi->GetPendingGames($terminal_id, $service_id,$PID);    
        } else {
            $pendingGames = '';
        }

        //Display message
        if(is_array($pendingGames) && $pendingGames['IsSucceed'] == true){
            $message = "Info: There was a pending game bet on  ";
            logger($message.$pendingGames['PendingGames']['GetPendingGamesByPIDResult']['Gamename'].'.' . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            $message = "Info: There was a pending game bet. ";
            //unlock launchpad gaming terminal
            $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminalname, $login_pwd, $service_id);
            CasinoApi::throwError($message);   
        }


        //Get Last Transaction Summary ID
        $trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($terminal_id);
        if(!$trans_summary_id){
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $egmSessionsModel->deleteEgmSessionById($terminal_id);
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
                $message = "Error: Failed to insert record in transaction table [0001].";
                logger($message);
                CasinoApi::throwError($message);
            }
        } else {
            $transaction_id = '';
        }

        $terminal_pwd_res = $terminalsModel->getTerminalPassword($terminal_id, $service_id);
        $terminal_pwd = $terminal_pwd_res['ServicePassword'];

        $udate = CasinoApi::udate('YmdHisu');

        //insert into transaction request log
        $trans_req_log_last_id = $transReqLogsModel->insert($udate, $amount, 'W', $paymentType,
                $terminal_id, $site_id, $service_id,$loyalty_card, $mid, $userMode, $transaction_id);


        if(!$trans_req_log_last_id) {
            $pendingTerminalTransactionCountModel->updatePendingTerminalCount($terminal_id);
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
        
        //check if redeemable amount is greater than 0, else skip on calling Withdraw
        //API method
        if($redeemable_amount > 0){
            
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
                //unlock launchpad gaming terminal
                $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminalname, $login_pwd, $service_id);
                CasinoApi::throwError($message);
            }

            
           /************************ WITHDRAW ************************************/
           $resultwithdraw = $casinoApiHandler->Withdraw($terminal_name, $amount, 
                    $tracking1, $tracking2, $tracking3, $tracking4, $terminal_pwd, $event_id, $transaction_id);   
               
                //check if Withdraw API reply is null
                if(is_null($resultwithdraw)){

                    // check again if Casino Server is busy
                    if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
                        $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                        $message = 'Can\'t connect to casino';
                        logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                        //unlock launchpad gaming terminal
                        $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminalname, $login_pwd, $service_id);
                        CasinoApi::throwError($message);
                    }

                    //execute TransactionSearchInfo API Method
                    $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($terminal_name, 
                                       $tracking1 , $tracking2 , $tracking3, $tracking4, $transaction_id);

                    //check if TransactionSearchInfo API is not successful
                    if(isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false)
                    {
                        $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                        $message = 'Error: Request denied. Please try again.';
                        logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id.
                               ' ErrorMessage='.$transSearchInfo['ErrorMessage']);
                        //unlock launchpad gaming terminal
                        $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminalname, $login_pwd, $service_id);
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
                            logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id.
                                   ' ErrorMessage='.$resultwithdraw['ErrorMessage']);
                            //unlock launchpad gaming terminal
                            $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminalname, $login_pwd, $service_id);
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

                    $isredeemed = $commonTransactionsModel->redeemTransaction($amount, $trans_summary_id, $udate, 
                                        $site_id, $terminal_id, 'W', $paymentType,$service_id, $acct_id, $transstatus,
                                        $loyalty_card, $mid);
                    
                    //check terminal type if Genesis = 1
                    $terminalType = $terminalsModel->checkTerminalType($terminal_id);
                    
                    if($terminalType == 1){
                        $egmSessionsModel->deleteEgmSessionById($terminal_id);
                    }
                    
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
                    //unlock launchpad gaming terminal
                    $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminalname, $login_pwd, $service_id);
                    CasinoApi::throwError($message);
                } 

        } else {
            
            $isredeemed = $commonTransactionsModel->redeemTransaction($amount, $trans_summary_id, $udate, 
                                        $site_id, $terminal_id, 'W', $paymentType,$service_id, $acct_id, 1,
                                        $loyalty_card, $mid);
            //check terminal type if Genesis = 1
            $terminalType = $terminalsModel->checkTerminalType($terminal_id);
                    
            if($terminalType == 1){
                $egmSessionsModel->deleteEgmSessionById($terminal_id);
            }
            
            
            $transReqLogsModel->updateTransReqLogDueZeroBal($terminal_id, $site_id, 'W', $trans_req_log_last_id);
                        
            if(!$isredeemed){
                $message = 'Error: Failed update records in transaction tables';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                CasinoApi::throwError($message);
            }
                    
            return array('message'=>'Info: Session has been ended.',
                        'trans_summary_id'=>$trans_summary_id,'udate'=>$udate,'amount'=>$amount,'terminal_login'=>$terminal_name,
                        'terminal_name'=>$terminal_name,'trans_details_id'=>$isredeemed);
        }    
    }
}