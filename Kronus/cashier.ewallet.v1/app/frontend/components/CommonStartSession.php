<?php

/**
 * For Terminal Based Transaction
 * Common start session for TerminalMonitoring. Stand-alone and Hotkey
 * Core transaction process for deposit
 * Date Created 11 7, 11 2:00:24 PM <pre />
 * Date Modified May 06, 2013 for PEGS 3.0 Project
 * @date 03/14/13 - supports Terminal Based
 * @author Bryan Salazar, elperez
 */
class CommonStartSession {
    
    /**
     * @param int $terminal_id
     * @param int $site_id
     * @param string $trans_type
     * @param int $service_id
     * @param int $bcf
     * @param int $initial_deposit
     * @param int $acctid
     * @return array 
     */
    public function start($terminal_id,$site_id, $trans_type, $paymentType, $service_id, $bcf, 
                          $initial_deposit, $acctid, $loyalty_card, $voucher_code = '', 
                          $trackingid = '',  $casinoUsername = '', $casinoPassword = '', $casinoHashedPassword = '',
                          $casinoServiceID = '', $mid = '', $userMode = '', $traceNumber='', $referenceNumber='') {
        
        Mirage::loadComponents(array('CasinoApi','PCWSAPI.class'));
        Mirage::loadModels(array('TerminalsModel','EgmSessionsModel','SiteBalanceModel','CommonTransactionsModel',
                                 'PendingTerminalTransactionCountModel','BankTransactionLogsModel'));
        
        $casinoApi = new CasinoApi();
        $terminalsModel = new TerminalsModel();
        $egmSessionsModel = new EgmSessionsModel();
        $siteBalance = new SiteBalanceModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $pendingTerminalTransactionCountModel = new PendingTerminalTransactionCountModel();
        $pcwsapi = new PCWSAPI();
        $bankTransactionLogs = new BankTransactionLogsModel();
         
        if($terminalsModel->isPartnerAlreadyStarted($terminal_id)) {
            $message = 'Error: '. $terminalsModel->terminal_code . ' terminal already started';
            logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            CasinoApi::throwError($message);
        }
        
        list($terminal_balance,$service_name,$terminalSessionsModel,
                $transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount) = $casinoApi->getBalance($terminal_id, $site_id,'D',$service_id);
        
        $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);
        
        if($is_terminal_active === false) {
            $message = 'Error: Can\'t get status.';
            logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            CasinoApi::throwError($message);
        }
        
        if($is_terminal_active != 0) {
            $message = 'Error: Terminal is already active.';
            logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            CasinoApi::throwError($message);
        }
        
        if($terminal_balance != 0) {
            $message = 'Error: Please inform customer service for manual redemption.';
            logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            CasinoApi::throwError($message);
        }
         
        if(($bcf - $initial_deposit) < 0) {
            $message = 'Error: BCF is not enough.';
            logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            CasinoApi::throwError($message);
        }
        
        if($mgaccount != '') {
            $terminal_name = $mgaccount;
        } else {
            $terminal_name = $terminalsModel->getTerminalName($terminal_id);
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
        
        //get terminal password 
        $terminal_pwd_res = $terminalsModel->getTerminalPassword($terminal_id, $service_id);
        $terminal_pwd = $terminal_pwd_res['ServicePassword'];

        $udate = CasinoApi::udate('YmdHisu');
        
        //check terminal type if Genesis = 1
        $terminaltype = $terminalsModel->checkTerminalType($terminal_id);
        
        if($terminaltype == 1){
            //insert egm session
            $egmsessionsresult = $egmSessionsModel->insert($mid, $terminal_id, $service_id, $_SESSION['accID']);

            if(!$egmsessionsresult){
                $message = 'Error: The terminal has an ongoing terminal deposit session.';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                CasinoApi::throwError($message);
            }
        }
        
        $checkegmsession = $egmSessionsModel->checkEgmSession($service_id, $mid);
        
        if(!empty($checkegmsession)){
                $message = 'Error: User has an ongoing EGM deposit session.';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                CasinoApi::throwError($message);
        }
        
        //insert into terminalsessions, throw error if there is existing session 
        //this terminal / user
        $trans_summary_max_id = null;
        $is_terminal_exist = $terminalSessionsModel->insert($terminal_id, $service_id, 
                               $initial_deposit, $trans_summary_max_id, $loyalty_card, $mid,
                               $userMode, $casinoUsername, $casinoPassword, $casinoHashedPassword);
        
        if(!$is_terminal_exist){
            $message = 'Error: Terminal / User has an existing session.';
            logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            CasinoApi::throwError($message);
        }
        
        //insert into transaction request log
        $bankTransactionStatus = null;
        $trans_req_log_last_id = $transReqLogsModel->insert($udate, $initial_deposit, 'D', $paymentType,
            $terminal_id, $site_id, $service_id,$loyalty_card, $mid, $userMode, 
            $trackingid, $voucher_code, $transaction_id);
        
        if(!$trans_req_log_last_id) {
            $pendingTerminalTransactionCountModel->updatePendingTerminalCount($terminal_id);
            $message = 'There was a pending transaction for this user / terminal.';
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $egmSessionsModel->deleteEgmSessionById($terminal_id);
            logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            CasinoApi::throwError($message);
        }
        
        if($traceNumber!='' && $referenceNumber!=''){
            if($trans_req_log_last_id){
                $bankTransactionStatus = $bankTransactionLogs->insertBankTransaction($trans_req_log_last_id, $traceNumber, $referenceNumber, $paymentType);
            }
        }
        
        if($bankTransactionStatus===false){
            $message = 'Bank Transaction Failed.';
            $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $egmSessionsModel->deleteEgmSessionById($terminal_id);
            logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            CasinoApi::throwError($message);
        }
        
        
        $tracking1 = $trans_req_log_last_id;
        $tracking2 = 'D';
        $tracking3 = $terminal_id;
        $tracking4 = $site_id;
        $event_id = Mirage::app()->param['mgcapi_event_id'][0]; //Event ID for Deposit
        
        // check if casino's reply is busy, added 05/17/12
        if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
            $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $egmSessionsModel->deleteEgmSessionById($terminal_id);
            $message = 'Can\'t connect to casino';
            logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            CasinoApi::throwError($message);
        }
        
        //if PT, unfreeze its account
        if(strpos($service_name, 'PT') !== false) {
            $changeStatusResult = $casinoApiHandler->ChangeAccountStatus($terminal_name, 0);
            if(!$changeStatusResult['IsSucceed']){
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $egmSessionsModel->deleteEgmSessionById($terminal_id);
                $message = "Info: Failed to unlock the terminal in Swinging Singapore.";
                logger($message);
                CasinoApi::throwError($message);
            }
        }
        
        /************************* DEPOSIT ************************************/
        $resultdeposit = $casinoApiHandler->Deposit($terminal_name, $initial_deposit, 
            $tracking1, $tracking2, $tracking3, $tracking4, $terminal_pwd, $event_id, $transaction_id);
           
        //check if Deposit API reply is null
        if(is_null($resultdeposit)){
            
            // check again if Casino Server is busy
            if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $egmSessionsModel->deleteEgmSessionById($terminal_id);
                $message = 'Can\'t connect to casino';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                CasinoApi::throwError($message);
            }
            
            //execute TransactionSearchInfo API Method
            $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($terminal_name, 
                               $tracking1 , $tracking2 , $tracking3, $tracking4, $transaction_id);
            
            //check if TransactionSearchInfo API is not successful
            if(isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false)
            {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $egmSessionsModel->deleteEgmSessionById($terminal_id);
                $message = 'Error: Failed to start session.';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id. ' ErrorMessage='.$transSearchInfo['ErrorMessage']);
                CasinoApi::throwError($message);
            }
            
            //Check TransactionSearchInfo API
            if(isset($transSearchInfo['TransactionInfo']))
            {
                //RTG / Magic Macau
                if(isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']))
                {
                    $initial_deposit = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['amount'];
                    $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                    $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                }
                //MG / Vibrant Vegas
                elseif(isset($transSearchInfo['TransactionInfo']['MG']))
                {
                    //$initial_deposit = $transSearchInfo['TransactionInfo']['MG']['Balance'];
                    $transrefid = $transSearchInfo['TransactionInfo']['MG']['TransactionId'];
                    $apiresult = $transSearchInfo['TransactionInfo']['MG']['TransactionStatus'];
                }
                //PT / PlayTech
                elseif(isset($transSearchInfo['TransactionInfo']['PT'])){
                    //$initial_deposit = $transSearchInfo['TransactionInfo']['PT']['']; //need to ask if reported amount will be passed from PT
                    $transrefid = $transSearchInfo['TransactionInfo']['PT']['id'];
                    $apiresult = $transSearchInfo['TransactionInfo']['PT']['status'];
                }
            }
        } else {
            
            //check if TransactionSearchInfo API is not successful
            if(isset($resultdeposit['IsSucceed']) && $resultdeposit['IsSucceed'] == false) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $egmSessionsModel->deleteEgmSessionById($terminal_id);
                $message = 'Error: Failed to start session.';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id. 'ErrorMessage = '.$resultdeposit['ErrorMessage']);
                CasinoApi::throwError($message);
            }

            //check Deposit API Result
            if(isset($resultdeposit['TransactionInfo'])){
                //RTG / Magic Macau
                if(isset($resultdeposit['TransactionInfo']['DepositGenericResult'])) {
                    $transrefid = $resultdeposit['TransactionInfo']['DepositGenericResult']['transactionID'];
                    $apiresult = $resultdeposit['TransactionInfo']['DepositGenericResult']['transactionStatus'];
                    $apierrmsg = $resultdeposit['TransactionInfo']['DepositGenericResult']['errorMsg'];
                } 
                //MG / Vibrant Vegas
                else if(isset($resultdeposit['TransactionInfo']['MG'])) {
                    $transrefid = $resultdeposit['TransactionInfo']['MG']['TransactionId'];
                    $apiresult = $resultdeposit['TransactionInfo']['MG']['TransactionStatus'];
                    $apierrmsg = $resultdeposit['ErrorMessage'];
                }
                //Rockin Reno
                else if(isset($resultdeposit['TransactionInfo']['PT'])) {
                    $transrefid = $resultdeposit['TransactionInfo']['PT']['TransactionId'];
                    $apiresult = $resultdeposit['TransactionInfo']['PT']['TransactionStatus'];
                    $apierrmsg = $resultdeposit['TransactionInfo']['PT']['TransactionStatus'];
                }
            }
        }
        
        if($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved') {
            $transstatus = '1';
        } else {
            $transstatus = '2';
        }
        
        //if Deposit / TransactionSearchInfo API status is approved
        if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved'){
 
            //this will return the transaction summary ID
            $trans_summary_id = $commonTransactionsModel->startTransaction($site_id, $terminal_id, 
                                    $initial_deposit, $acctid, $udate, 'D', $paymentType, $service_id, $transstatus,$loyalty_card, $mid);
            
            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, $transstatus,$transrefid,$terminal_id);
            
            $newbal = $bcf - $initial_deposit;
            $siteBalance->updateBcf($newbal, $site_id, 'Start session'); //update bcf
            
            if(!$trans_summary_id)
            {
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $egmSessionsModel->deleteEgmSessionById($terminal_id);
                $message = 'Error: Failed to insert records in transaction tables.';
                logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                CasinoApi::throwError($message);
            }
            
            if(strpos($service_name, 'MG') !== false) {
                $systemusername = $systemusername = Mirage::app()->param['pcwssysusername'];
                $pcwsapi->AddCompPoints($systemusername, $loyalty_card, $site_id, $service_id, number_format($initial_deposit,-2));
            }
            
            $message = 'New player session started.The player initial playing balance is PhP ' . toMoney($initial_deposit);
            
            return array('message'=>$message,'newbcf'=> toMoney($newbal),'initial_deposit'=>toMoney($initial_deposit),
                'udate'=>$udate,'terminal_name'=>$terminal_name,'trans_ref_id'=>$transrefid,'trans_summary_id'=>$trans_summary_id["trans_summary_max_id"],
                'trans_details_id' => $trans_summary_id["transdetails_max_id"]);
        } else {
            
            //if PT and failed in start session, freeze its account
            if(strpos($service_name, 'PT') !== false) {
                $changeStatusResult = $casinoApiHandler->ChangeAccountStatus($terminal_name, 1);
                if(!$changeStatusResult['IsSucceed']){
                    $message = $changeStatusResult['ErrorMessage'];
                    logger($message);
                    CasinoApi::throwError($message);
                }
            }
            
            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $egmSessionsModel->deleteEgmSessionById($terminal_id);
            $message = 'Error: Request denied. Please try again.';
            logger($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            CasinoApi::throwError($message);
        }
    }
}
