<?php

class UserBasedTrans {
    
    public function start($terminal_id,$site_id,$trans_type,$service_id,$bcf,$initial_deposit,$acctid,
            $loyalty_card,  $paymentType, $stackerbatchid, $casinoUsername = '', $casinoPassword = '', $casinoHashedPassword = '', 
             $casinoServiceID = '', $mid = '', $userMode = '') {
        
        Yii::import('application.components.CasinoApiUB');
        
        $casinoApi = new CasinoApiUB();
        $terminalsModel = new TerminalsModel();
        $transSummaryModel = new TransactionSummaryModel();
        $transDetailsModel = new TransactionDetailsModel();
        $siteBalance = new SiteBalanceModel();
        $gamingSessions = new GamingSessionsModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $sitesModel = new SitesModel();
        
        $siteCode = $sitesModel->getSiteCode($site_id);
        
        if($terminalsModel->isPartnerAlreadyStarted($terminal_id, $siteCode)) {
            $message = $terminalsModel->terminal_code . ' terminal already started';
            Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>17);
        }
        
        $getBalance = $casinoApi->getBalanceUB($terminal_id, $site_id, 'D', 
                        $casinoServiceID, $acct_id = '', $casinoUsername, $casinoPassword);
        //check if get balance response is string
        if(is_string($getBalance)){
            $message = $getBalance;
            Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>26);
        }
        list($terminal_balance,$service_name,$terminalSessionsModel,
                $transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount) = $getBalance;
        
        $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);
        $is_egmsession_active = $gamingSessions->chkActiveEgmSession($terminal_id);

        //check if session is active
        if($is_terminal_active === false) {
            $message = 'Can\'t get status.';
            return array('TransMessage'=>$message,'ErrorCode'=>30);
        }
        
        if($is_terminal_active != 0) {
            $message = 'Terminal is already active.';
            Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>32);
        }
 
        if(!$is_egmsession_active){
            $message = 'Terminal has no active EGM session.';
            return array('TransMessage'=>$message,'ErrorCode'=>55);
        }
        
        if($terminal_balance != 0) {
            $message = 'Please inform customer service for manual redemption.';
            Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>33);
        }
        
        if(($bcf - $initial_deposit) < 0) {
            $message = 'BCF is not enough.';
            Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>35);
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
                $message = "Failed to insert record in servicetransactionref";
                Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>20);
            }
        } else {
            $transaction_id = '';
        }
        
        $udate = CasinoApiUB::udate('YmdHisu');
        
        //insert into terminalsessions
        $trans_summary_max_id = null;
        $is_terminal_exist = $terminalSessionsModel->insert2($terminal_id, $service_id, $initial_deposit, $trans_summary_max_id,
                $loyalty_card, $mid,$userMode, $casinoUsername, $casinoPassword, $casinoHashedPassword);
        
        if(!$is_terminal_exist){
            $message = 'Terminal has an existing session.';
            Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>36);
        }
        
        //insert into transaction request log
        $trans_req_log_last_id = $transReqLogsModel->insert2($udate, $initial_deposit, 'D', $terminal_id, $site_id, $service_id,
                1, $loyalty_card, $mid, $userMode, $stackerbatchid, '', '', $transaction_id);
 
        if(!$trans_req_log_last_id) {
            $message = 'There was a pending transaction for this user / terminal.';
            $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>38);
        }
        
        $tracking1 = $trans_req_log_last_id;
        $tracking2 = 'D';
        $tracking3 = $terminal_id;
        $tracking4 = $site_id;
        $event_id = Yii::app()->params['mgcapi_event_id'][0]; //Event ID for Deposit
        
        // check if casino's reply is busy, added 05/17/12
        if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
            $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
            $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $message = 'Can\'t connect to casino';
            Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>41);
        }
        
        //if PT, unfreeze its account
        if(strpos($service_name, 'PT') !== false) {
            $terminal_name = $casinoUsername;
            $changeStatusResult = $casinoApiHandler->ChangeAccountStatus($casinoUsername, 0);
            if(!$changeStatusResult['IsSucceed']){
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = "Info: Failed to unlock the terminal in Swinging Singapore.";
                Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>39);
            }
        }
        //if PT, unfreeze its account
        if(strpos($service_name, 'RTG2') !== false) {
            $terminal_name = $casinoUsername;
        }
        
       
        /************************* DEPOSIT ************************************/
        $resultdeposit = $casinoApiHandler->Deposit($terminal_name, $initial_deposit, 
            $tracking1, $tracking2, $tracking3, $tracking4, $casinoPassword, $event_id, $transaction_id);
        //check if Deposit API reply is null
        if(is_null($resultdeposit)){
            
            // check again if Casino Server is busy
            if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Can\'t connect to casino';
                Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>41);
            }
            
            //execute TransactionSearchInfo API Method
            $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($terminal_name, 
                               $tracking1 , $tracking2 , $tracking3, $tracking4, $transaction_id);
            
            //check if TransactionSearchInfo API is not successful
            if(isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false)
            {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Failed to start session.';
                                Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id." | ".
                                    "ErrorMessage: ".$transSearchInfo['ErrorMessage']);
                return array('TransMessage'=>$message,'ErrorCode'=>42);
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
                    //$initial_deposit = $transSearchInfo['TransactionInfo']['Balance'];
                    $transrefid = $transSearchInfo['TransactionInfo']['MG']['TransactionId'];
                    $apiresult = $transSearchInfo['TransactionInfo']['MG']['TransactionStatus'];
                }
                //PT / Vibrant Vegas
                elseif(isset($transSearchInfo['TransactionInfo']['PT']))
                {
                    //$initial_deposit = $transSearchInfo['TransactionInfo']['Balance'];
                    $transrefid = $transSearchInfo['TransactionInfo']['PT']['id'];
                    $apiresult = $transSearchInfo['TransactionInfo']['PT']['status'];
                }
            }
        } else {
            
            //check if TransactionSearchInfo API is not successful
            if(isset($resultdeposit['IsSucceed']) && $resultdeposit['IsSucceed'] == false) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Failed to start session.';
                Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id." | ".
                                    "ErrorMessage: ".$resultdeposit['ErrorMessage']);
                return array('TransMessage'=>$message,'ErrorCode'=>42);
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

            //this will return the transaction summary ID as well as transaction id
            list($trans_summary_id, $trans_details_id) = $commonTransactionsModel->startSessionTransaction($site_id, $terminal_id, 
                                    $initial_deposit, $acctid, $udate, 'D', $service_id, $transstatus, $loyalty_card, $mid, $paymentType, $stackerbatchid);
            
            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, $transstatus,$transrefid,$terminal_id);
            
            $newbal = $bcf - $initial_deposit;

            $siteBalance->updateBcf($newbal, $site_id, 'Start session'); //update bcf
            
            if(!$trans_summary_id)
            {
                $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
                $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Failed to insert records in transaction tables.';
                Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>43);
            }
            
            $transDate = $transDetailsModel->getTransactionDate($trans_details_id);
            
            $message = 'New player session started.The player initial playing balance is PhP ' . Utilities::toMoney($initial_deposit);
            
            return array('transStatus'=>1,'message'=>$message,'newbcf'=> Utilities::toMoney($newbal),'initial_deposit'=>Utilities::toMoney($initial_deposit),
                         'TransactionDate'=>$transDate,'terminal_name'=>$terminal_name,'trans_ref_id'=>$transrefid,'udate' => $udate,'trans_summary_id'=>$trans_summary_id,
                         'trans_details_id'=>$trans_details_id);
            
        } else {
            
            //if PT and failed in start session, freeze its account
            if(strpos($service_name, 'PT') !== false) {
                $changeStatusResult = $casinoApiHandler->ChangeAccountStatus($casinoUsername, 1);
                if(!$changeStatusResult['IsSucceed']){
                    $message = $changeStatusResult['ErrorMessage'];
                    Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                    return array('TransMessage'=>$message,'ErrorCode'=>29);
                }
            }
            
            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
            $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $message = 'Request denied. Please try again.';
            Utilities::errorLogger($message, "Start Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>28);
        }
        
    }
    
    
    public function reload($bcf,$amount, $paymentType,$stackerbatchid, $terminal_id,$site_id,$service_id,$acctid,
            $loyalty_card, $voucher_code = '',$trackingid = '', $mid = '', $userMode = '',
            $casinoUsername = '',$casinoPassword = '', $casinoServiceID = '', $stackerdetailID = '') {
        
        Yii::import('application.components.CasinoApiUB');
        
        $casinoApi = new CasinoApiUB();
        $terminalsModel = new TerminalsModel();
        $transSummaryModel = new TransactionSummaryModel();
        $transDetailsModel = new TransactionDetailsModel();
        $siteBalance = new SiteBalanceModel();
        $gamingSessions = new GamingSessionsModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $sitesModel = new SitesModel();
        $stackerDetailsModel = new StackerDetailsModel();
        $eWalletTransModel      = new EwalletTransModel();
       
        $getBalance = $casinoApi->getBalanceUB($terminal_id, $site_id, 'D', 
                        $casinoServiceID, $acct_id = '', $casinoUsername, $casinoPassword);
        
        if(is_string($getBalance)){
            $message = $getBalance;
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>26);
        }
        
        list($terminal_balance,$service_name,$terminalSessionsModel,
                $transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount) = $getBalance;
        
       
        $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);
        $is_egmsession_active = $gamingSessions->chkActiveEgmSession($terminal_id);
               
        if($is_terminal_active === false) {
            $message = 'Can\'t get status.';
            return array('TransMessage'=>$message,'ErrorCode'=>30);
        }
        
        if($is_terminal_active < 1) {
            $message = 'Terminal has no active session.';
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>32);
        }
        
        if(!$is_egmsession_active){
            $message = 'Terminal has no active EGM session.';
            return array('TransMessage'=>$message,'ErrorCode'=>55);
        }
        
        
        if(($bcf - $amount) < 0) {
            $message = 'BCF is not enough.';
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>35);
        }
        
        if($mgaccount != '') {
            $terminal_name = $mgaccount;
        } else {
            $terminal_name = $terminalsModel->getTerminalName($terminal_id);
        }
        
        
        //Get Last Transaction Summary ID
        $trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($terminal_id);
        if(!$trans_summary_id){
            $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $message = 'Reload Session Failed. Please check if the terminal
                            has a valid start session.';
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>22,'DateExpiry'=>'');
        }
        
        //get last transaction ID if service is MG
        if(strpos($service_name, 'MG') !== false) {
            $trans_origin_id = 0; //cashier origin Id
            $transaction_id = $terminalsModel->insertserviceTransRef($service_id, $trans_origin_id);
            if(!$transaction_id){
                $message = "Failed to insert record in servicetransactionref";
                Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>20);
            }
        } else {
            $transaction_id = '';
        }
        
        $udate = CasinoApiUB::udate('YmdHisu');
        
        
        //insert into transaction request log
        $trans_req_log_last_id = $transReqLogsModel->insert2($udate, $amount, 'R', $terminal_id, $site_id, $service_id,
                $paymentType, $loyalty_card, $mid, $userMode, $stackerbatchid, substr($trackingid, -30), $voucher_code, $transaction_id);
 
        if(!$trans_req_log_last_id) {
            $message = 'There was a pending transaction for this user / terminal.';
            $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>38);
        }
        
        $tracking1 = $trans_req_log_last_id;
        $tracking2 = 'R';
        $tracking3 = $terminal_id;
        $tracking4 = $site_id;
        $event_id = Yii::app()->params['mgcapi_event_id'][1]; //Event ID for Deposit
        
        // check if casino's reply is busy, added 05/17/12
        if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
            $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
            $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $message = 'Can\'t connect to casino';
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>41);
        }
        
        //if PT, unfreeze its account
        if(strpos($service_name, 'PT') !== false) {
            $terminal_name = $casinoUsername;
        }
        if(strpos($service_name, 'RTG2') !== false) {
            $terminal_name = $casinoUsername;
        }
        /************************* DEPOSIT ************************************/
        $resultdeposit = $casinoApiHandler->Deposit($terminal_name, $amount, 
            $tracking1, $tracking2, $tracking3, $tracking4, $casinoPassword, $event_id, $transaction_id);
        
        //check if Deposit API reply is null
        if(is_null($resultdeposit)){
            
            // check again if Casino Server is busy
            if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Can\'t connect to casino';
                Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>41);
            }
            
            //execute TransactionSearchInfo API Method
            $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($terminal_name, 
                               $tracking1 , $tracking2 , $tracking3, $tracking4, $transaction_id);
            
            //check if TransactionSearchInfo API is not successful
            if(isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false)
            {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Failed to start session.';
                Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id." | ".
                                    "ErrorMessage: ".$transSearchInfo['ErrorMessage']);
                return array('TransMessage'=>$message,'ErrorCode'=>42);
            }
            
            //Check TransactionSearchInfo API
            if(isset($transSearchInfo['TransactionInfo']))
            {
                //RTG / Magic Macau
                if(isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']))
                {
                    $amount = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['amount'];
                    $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                    $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                }
                //MG / Vibrant Vegas
                elseif(isset($transSearchInfo['TransactionInfo']['MG']))
                {
                    //$initial_deposit = $transSearchInfo['TransactionInfo']['Balance'];
                    $transrefid = $transSearchInfo['TransactionInfo']['MG']['TransactionId'];
                    $apiresult = $transSearchInfo['TransactionInfo']['MG']['TransactionStatus'];
                }
                //PT / Vibrant Vegas
                elseif(isset($transSearchInfo['TransactionInfo']['PT']))
                {
                    //$initial_deposit = $transSearchInfo['TransactionInfo']['Balance'];
                    $transrefid = $transSearchInfo['TransactionInfo']['PT']['id'];
                    $apiresult = $transSearchInfo['TransactionInfo']['PT']['status'];
                }
            }
        } else {
            
            //check if TransactionSearchInfo API is not successful
            if(isset($resultdeposit['IsSucceed']) && $resultdeposit['IsSucceed'] == false) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $message = 'Failed to reload session.';
                Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id." | ".
                                    "ErrorMessage: ".$resultdeposit['ErrorMessage']);
                return array('TransMessage'=>$message,'ErrorCode'=>42);
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
                    $apierrmsg = $resultdeposit['ErrorCode'];
                }
                //Rockin Reno
                else if(isset($resultdeposit['TransactionInfo']['PT'])) {
                    $transrefid = $resultdeposit['TransactionInfo']['PT']['TransactionId'];
                    $apiresult = $resultdeposit['TransactionInfo']['PT']['TransactionStatus'];
                    $apierrmsg = $resultdeposit['ErrorMessage'];
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
            
            $total_reload_balance = $terminal_balance + $amount;
            
            //this will return the transaction summary ID as well as transaction id
            list($trans_summary_id, $trans_details_id) = $commonTransactionsModel->reloadSessionTransaction($amount, $trans_summary_id, $udate, 
                    $site_id, $terminal_id, 'R', $paymentType, $service_id, $acctid, $transstatus, $terminal_balance, $total_reload_balance, $loyalty_card, $mid, $stackerbatchid);
            
            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, $transstatus,$transrefid,$terminal_id);
            
            $newbal = $bcf - $amount;

            $siteBalance->updateBcf($newbal, $site_id, 'Reload session'); //update bcf
            //Update Stacker Details
            $updateTransDetailsID = $stackerDetailsModel->updateTransactionDetailsID($trans_details_id, $stackerdetailID);

            if(!$trans_summary_id)
            {
                $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
                $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Failed to insert records in transaction tables.';
                Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>43);
            }
            
            $transDate = $transDetailsModel->getTransactionDate($trans_details_id);
            
            $message = 'The amount of PhP ' . Utilities::toMoney($amount) . ' is successfully loaded.';
            
            return array('transStatus'=>1,'TransMessage'=>$message,'newbcf'=> Utilities::toMoney($newbal),'initial_deposit'=>Utilities::toMoney($amount),
                         'udate'=>$udate,'terminal_name'=>$terminal_name,'trans_ref_id'=>$transrefid,'trans_summary_id'=>$trans_summary_id,
                         'trans_details_id'=>$trans_details_id,'TransactionDate'=>$transDate);
            
        } else {
            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
            $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $message = 'Request denied. Please try again.';
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>28);
        }
        
    }
    
    
    public function redeem($login_pwd, $terminal_id, $terminal_name, $stackerbatchid, $site_id, $bcf,$service_id, 
                           $amount, $paymentType, $acct_id, $loyalty_card, 
                           $mid = '', $userMode = '',$casinoUsername = '', $trackingID = '', $voucherTicketBarcode = '', 
                           $casinoPassword = '', $casinoServiceID = '') 
    {
        Yii::import('application.components.CasinoApiUB');
        Yii::import('application.components.VoucherManagement');
        Yii::import('application.components.VoucherTicketAPIWrapper');
        
        $casinoApi = new CasinoApiUB();
        $terminalsModel = new TerminalsModel();
        $gamingSessionsModel = new GamingSessionsModel();
        $transSummaryModel = new TransactionSummaryModel();
        $transDetailsModel = new TransactionDetailsModel();
        $siteBalance = new SiteBalanceModel();
        $terminalsessions = new TerminalSessionsModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $sitesModel = new SitesModel();
        $pendingTerminalTransactionCountModel = new PendingTerminalTransactionCountModel();
        $voucherTicket = new VoucherTicketAPIWrapper();
//        $spyder_enabled = $sitesModel->getSpyderStatus($site_id); 
//        
//        //call SAPI, lock launchpad terminal
//        $casinoApi->callSpyderAPI($commandId = 1, $terminal_id, $casinoUsername, $login_pwd, $service_id, $spyder_enabled);
        
        //call PT, freeze and force logout of session
        $casinoApi->_doCasinoRules($terminal_id, $service_id, $casinoUsername);
       
        $getBalance = $casinoApi->getBalanceUB($terminal_id, $site_id, 'W', 
                        $casinoServiceID, $acct_id, $casinoUsername, $casinoPassword);
        
        if(is_string($getBalance)){
            $message = $getBalance;
            Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>26);
        }
        
        list($terminal_balance,$service_name,$terminalSessionsModel,
                $transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount) = $getBalance;
        
        if ($redeemable_amount != $amount)
        {
            $message = 'Error in redeemable amount. ';
            Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>24,'DateExpiry'=>'', 'amount' => '');
        }
        //update last balance in terminal sessions
        $updateBalance = $terminalsessions->updateTerminalBalance($terminal_id, $redeemable_amount);
        $amount = $terminalsessions->getCurrentBalance($terminal_id, $service_id);
        
        $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);
        $is_egmsession_active = $gamingSessionsModel->chkActiveEgmSession($terminal_id);
        
        if($is_terminal_active === false) {
            $message = 'Can\'t get status.';
            return array('TransMessage'=>$message,'ErrorCode'=>30);
        }
        
        if($is_terminal_active < 1) {
            $message = 'Terminal has no active session.';
            Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>32);
        }
        
        if(!$is_egmsession_active){
            $message = 'Terminal has no active EGM session.';
            return array('TransMessage'=>$message,'ErrorCode'=>55);
        }
        
        if($mgaccount != '') {
            $terminal_name = $mgaccount;
        } else {
            $terminal_name = $terminalsModel->getTerminalName($terminal_id);
        }
        
        //revert player bet on hand regardless of the current bet, for PT only
        if(strpos($service_name, 'PT') !== false) {
            $result = $casinoApi->RevertBrokenGamesAPI($terminal_id, $service_id, $casinoUsername);
           
            if($result['RevertBrokenGamesReponse'][0] == false){
                //unfreeze PT account 
                $casinoApiHandler->ChangeAccountStatus($casinoUsername, 0);
                //unlock launchpad gaming terminal
//                $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $casinoUsername, $login_pwd, $service_id, $spyder_enabled);
                $message = 'Unable to revert bet on hand.';
                Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>32);
            }
        }
        
        //check if there was a pending game bet for RTG
        if(strpos($service_name, 'RTG2') !== false) {
            $PID = $casinoApiHandler->GetPIDLogin($casinoUsername);
            $pendingGames = $casinoApi->GetPendingGames($terminal_id, $service_id,$PID);    
        } else {
            $pendingGames = '';
        }
        
        //logout player
//        if(strpos($service_name, 'RTG2') !== false) {
//            $PID = $casinoApiHandler->GetPIDLogin($casinoUsername);
//            $casinoApi->LogoutPlayer($terminal_id, $service_id, $PID);    
//        }

        //Display message
        if(is_array($pendingGames) && $pendingGames['IsSucceed'] == true){
            $message = "Info: There was a pending game bet. ";
            //unlock launchpad gaming terminal
//            $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminal_name, $login_pwd, $service_id, $spyder_enabled);
            Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>32);
        }
        
        //Get Last Transaction Summary ID
        $trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($terminal_id);
        if(!$trans_summary_id){
            $gamingSessionsModel->deleteGamingSessions($terminal_id, $stackerbatchid);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $message = 'Reload Session Failed. Please check if the terminal
                            has a valid start session.';
            Utilities::errorLogger($message, "Redeem Session", 
                                   "TerminalID:".$terminal_id." | ".
                                   "MID: ".$mid." | ".
                                   "SiteID: ".$site_id." | ".
                                   "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>22,'DateExpiry'=>'');
        }
        
        //get last transaction ID if service is MG
        if(strpos($service_name, 'MG') !== false) {
            $trans_origin_id = 0; //cashier origin Id
            $transaction_id = $terminalsModel->insertserviceTransRef($service_id, $trans_origin_id);
            if(!$transaction_id){
                $message = "Failed to insert record in servicetransactionref";
                Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>20);
            }
        } else {
            $transaction_id = '';
        }
        
        $udate = CasinoApiUB::udate('YmdHisu');
        
        //insert into transaction request log
        $trans_req_log_last_id = $transReqLogsModel->insert2($udate, $amount, 'W', $terminal_id, $site_id, $service_id,
                $paymentType, $loyalty_card, $mid, $userMode, $stackerbatchid, '', '', $transaction_id);
        if(!$trans_req_log_last_id) {
            $pendingTerminalTransactionCountModel->updatePendingTerminalCount($terminal_id);
            $message = 'There was a pending transaction for this user / terminal.';
            Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>24,'DateExpiry'=>'', 'amount' => '');
        }
        
        if(Utilities::toMoney($amount) != Utilities::toMoney(Utilities::toInt($redeemable_amount))) {
            $transReqLogsModel->update($trans_req_log_last_id, false, 2,null,$terminal_id);
            $message = 'Redeemable amount is not equal.';
            Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>24,'DateExpiry'=>'');
        }
        
        //check if redeemable amount is greater than 0, else skip on calling Withdraw
        //API method
        if($redeemable_amount > 0){
        
                $tracking1 = $trans_req_log_last_id;
                $tracking2 = 'W';
                $tracking3 = $terminal_id;
                $tracking4 = $site_id;
                $event_id = Yii::app()->params['mgcapi_event_id'][2]; //Event ID for Withdraw


                // check if casino's reply is busy, added 05/17/12
                if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
                    $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                    $message = 'Can\'t connect to casino';
                    Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                    return array('TransMessage'=>$message,'ErrorCode'=>41,'DateExpiry'=>'');
                }

                /************************* WITHDRAW ************************************/
                $resultwithdraw = $casinoApiHandler->Withdraw($casinoUsername, $amount, $tracking1, $tracking2, 
                              $tracking3, $tracking4,  $casinoPassword, $event_id, $transaction_id);
                //check if Deposit API reply is null
                if(is_null($resultwithdraw)){

                    // check again if Casino Server is busy
                    if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
                        $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                        $message = 'Can\'t connect to casino';
                        Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                        //unlock launchpad gaming terminal
//                        $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminal_name, $login_pwd, $service_id, $spyder_enabled);
                        return array('TransMessage'=>$message,'ErrorCode'=>41);
                    }

                    //execute TransactionSearchInfo API Method
                    $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($terminal_name, 
                                       $tracking1 , $tracking2 , $tracking3, $tracking4, $transaction_id);

                    //check if TransactionSearchInfo API is not successful
                    if(isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false)
                    {
                        $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                        $gamingSessionsModel->deleteGamingSessions($terminal_id, $stackerbatchid);
                        $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                        $message = 'Failed to start session.';
                        Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id." | ".
                                    "ErrorMessage: ".$transSearchInfo['ErrorMessage']);
//                        $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminal_name, $login_pwd, $service_id, $spyder_enabled);
                        return array('TransMessage'=>$message,'ErrorCode'=>42);
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
                        $message = 'Request denied. Please try again.';
                        Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
//                        $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminal_name, $login_pwd, $service_id, $spyder_enabled);
                        return array('TransMessage'=>$message,'ErrorCode'=>26);
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

                if($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved') {
                    $transstatus = '1';
                } else {
                    $transstatus = '2';
                }
                
                //if Deposit / TransactionSearchInfo API status is approved
                if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved'){

                    //this will return if the transaction is successful
                    $isredeemed = $commonTransactionsModel->redeemSessionTransaction($amount, $trans_summary_id, $udate, $site_id, 
                            $terminal_id, 'W', $paymentType, $service_id, $acct_id, 1, $loyalty_card, $mid, $stackerbatchid);

                    //check if terminaltype is Genesis
                    $terminalType = $terminalsModel->checkTerminalType($terminal_id);
                    /*********************INSERT TICKET***********************/
                    $source = Yii::app()->params['voucher_source'];
                    //get reg terminal name
                    $reg_terminal = trim(str_replace("VIP", "", $terminal_name));
                    $regTerminal  = trim(str_replace(Yii::app()->params['SitePrefix'], "", $reg_terminal));
                  
                    $getvoucher = $voucherTicket->addTicket($regTerminal, $amount, $acct_id, $source, $loyalty_card, 1, $stackerbatchid, $voucherTicketBarcode, $trackingID);
                    if (isset($getvoucher['AddTicket']['ErrorCode']) && $getvoucher['AddTicket']['ErrorCode'] == 0) {
                        //success transaction
                        $ticketCode         = $getvoucher['AddTicket']['VoucherTicketBarcode'];
                        $ticketExpiration   = $getvoucher['AddTicket']['ExpirationDate'];

                    } else {
                        $ticketCode         = "";
                        $ticketExpiration   = "";

                        if (isset($getvoucher['AddTicket']['ErrorCode']) && $getvoucher['AddTicket']['ErrorCode'] != 0) {
                            $message = $getvoucher['AddTicket']['TransactionMessage'];
                            $errorcode = $getvoucher['AddTicket']['ErrorCode'];
                        } else {
                            if (isset($getvoucher['AddTicket']['TransactionMessage'])) {
                                $message = $getvoucher['AddTicket']['TransactionMessage'];
                                $errorcode = "";
                            } else {
                                $message = "Can't connect to VMS Server";
                                $errorcode = "";
                            }
                        }
                        //Log To Error Logger
                        Utilities::errorLogger($message, "Redeem Session", 
                                                                "TerminalID:".$terminal_id." | ".
                                                                "MID: ".$mid." | ".
                                                                "SiteID: ".$site_id." | ".
                                                                "CasinoID: ".$service_id. " | ".
                                                                "GeneratedTicketCode: ".$voucherTicketBarcode);
                    }
                    /****************************************************/
                    if($terminalType == 1){
                        $gamingSessionsModel->deleteGamingSessions($terminal_id, $stackerbatchid);
                    }
            
                    $transReqLogsModel->update($trans_req_log_last_id, $apiresult, $transstatus,$transrefid,$terminal_id);


                    if(!$isredeemed){
                        $message = 'Failed update records in transaction tables';
                        Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                        return array('TransMessage'=>$message,'ErrorCode'=>27);
                    }
                    $transDate = $transDetailsModel->getTransactionDate($isredeemed);

                    $message = 'You have successfully redeemed the amount of PhP ' . Utilities::toMoney($amount);

                    return array('TransMessage'=>$message,'transStatus'=>1,
                        'trans_summary_id'=>$trans_summary_id,'udate'=>$udate,'amount'=>$amount,'terminal_login'=>$terminal_name,
                        'trans_ref_id'=>$transrefid,'terminal_name'=>$terminal_name,'trans_details_id'=>$isredeemed,
                        'TransactionDate'=>$transDate, 'VoucherTicketBarcode' => $ticketCode, 'ExpirationDate' => $ticketExpiration);

                } else {

                    $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
                    $message = 'Requests denied. Please try again.';
                    Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                    return array('TransMessage'=>$message,'ErrorCode'=>28);
                }
        
        } else {
            
            $isredeemed = $commonTransactionsModel->redeemSessionTransaction($amount, $trans_summary_id, $udate, $site_id, 
                            $terminal_id, 'W', $paymentType, $service_id, $acct_id, 1, $loyalty_card, $mid, $stackerbatchid);
            
            //check if terminaltype is Genesis
            $terminalType = $terminalsModel->checkTerminalType($terminal_id);
                    
            if($terminalType == 1){
                $gamingSessionsModel->deleteGamingSessions($terminal_id, $stackerbatchid);
            }
            
            $transReqLogsModel->updateTransReqLogDueZeroBal($terminal_id, $site_id, 'W', $trans_req_log_last_id);
                        
            if(!$isredeemed){
                $message = 'Failed update records in transaction tables';
                Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            }
            
            $transDate = $transDetailsModel->getTransactionDate($isredeemed);
                    
            return array('TransMessage'=>'Info: Session has been ended.','transStatus'=>1, 'amount' => $amount, 
                        'trans_summary_id'=>$trans_summary_id,'udate'=>$udate,'amount'=>$amount,'terminal_login'=>$terminal_name,
                        'terminal_name'=>$terminal_name,'trans_details_id'=>$isredeemed, 'TransactionDate'=>$transDate);
        }
        
    }
    /**
     * Core Controller of eSAFE Reload Genesis 
     * @param type $bcf
     * @param type $amount
     * @param type $paymentType
     * @param type $stackerbatchid
     * @param type $terminal_id
     * @param type $site_id
     * @param type $service_id
     * @param type $acctid
     * @param type $loyalty_card
     * @param type $voucher_code
     * @param type $trackingid
     * @param type $mid
     * @param type $userMode
     * @param type $casinoUsername
     * @param type $casinoPassword
     * @param type $casinoServiceID
     * @param type $stackerdetailID
     * @return type
     * @date Sept. 28, 2015
     * created by: mge
     */
    public function eSAFEReload($bcf,$amount, $paymentType,$stackerbatchid, $terminal_id,$site_id,$service_id,$acctid,
            $loyalty_card, $voucher_code = '',$trackingid = '', $mid = '', $userMode = '',
            $casinoUsername = '',$casinoPassword = '', $casinoServiceID = '', $stackerdetailID = '') {
        
        Yii::import('application.components.CasinoApiUB');
        
        $casinoApi                  = new CasinoApiUB();
        $terminalsModel             = new TerminalsModel();
        $transSummaryModel          = new TransactionSummaryModel();
        $transDetailsModel          = new TransactionDetailsModel();
        $siteBalance                = new SiteBalanceModel();
        $gamingSessions             = new GamingSessionsModel();
        $commonTransactionsModel    = new CommonTransactionsModel();
        $sitesModel                 = new SitesModel();
        $stackerDetailsModel        = new StackerDetailsModel();
        $eWalletTransModel          = new EwalletTransModel();
        //get casino balance
        $getBalance = $casinoApi->getBalanceUB($terminal_id, $site_id, 'D', 
                        $casinoServiceID, $acct_id = '', $casinoUsername, $casinoPassword);
        
        if(is_string($getBalance)){
            $message = $getBalance;
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>26);
        }
        
        list($terminal_balance,$service_name,$terminalSessionsModel,
                $transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount) = $getBalance;
        
       
        $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id); //check active terminal session
        $is_egmsession_active = $gamingSessions->chkActiveEgmSession($terminal_id); //check active egm session
               
        if($is_terminal_active === false) {
            $message = 'Can\'t get status.';
            return array('TransMessage'=>$message,'ErrorCode'=>30);
        }
        
        if($is_terminal_active < 1) {
            $message = 'Terminal has no active session.';
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>32);
        }
        
        if(!$is_egmsession_active){
            $message = 'Terminal has no active EGM session.';
            return array('TransMessage'=>$message,'ErrorCode'=>55);
        }
        
        //check bcf
        if(($bcf - $amount) < 0) {
            $message = 'BCF is not enough.';
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>35);
        }
        
        if($mgaccount != '') {
            $terminal_name = $mgaccount;
        } else {
            $terminal_name = $terminalsModel->getTerminalName($terminal_id);
        }
        
        
        //Get Last Transaction Summary ID
        $trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($terminal_id);
        if(!$trans_summary_id){
            $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $message = 'Reload Session Failed. Please check if the terminal
                            has a valid start session.';
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>22,'DateExpiry'=>'');
        }
        
        //get last transaction ID if service is MG
        if(strpos($service_name, 'MG') !== false) {
            $trans_origin_id = 0; //cashier origin Id
            $transaction_id = $terminalsModel->insertserviceTransRef($service_id, $trans_origin_id);
            if(!$transaction_id){
                $message = "Failed to insert record in servicetransactionref";
                Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>20);
            }
        } else {
            $transaction_id = '';
        }
        
        $udate = CasinoApiUB::udate('YmdHisu');
        
        
        //insert into transaction request log
//        $trans_req_log_last_id = $transReqLogsModel->insert2($udate, $amount, 'R', $terminal_id, $site_id, $service_id,
//                $paymentType, $loyalty_card, $mid, $userMode, $stackerbatchid, substr($trackingid, -30), $voucher_code, $transaction_id);
        $ewallet_trans_id = $eWalletTransModel->insertEWalletTrans($site_id, $mid, $loyalty_card, 
                                                              $amount, $terminal_balance, $casinoServiceID, $userMode, 
                                                              $paymentType, 'D', $stackerbatchid, $terminal_id, $acctid, 
                                                              $trans_summary_id, $stackerdetailID);
 
        if(!$ewallet_trans_id) {
            $message = 'There was a pending transaction for this user / terminal.';
            $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>38);
        }
        $tracking1 = $ewallet_trans_id;
        $tracking2 = 'R';
        $tracking3 = $terminal_id;
        $tracking4 = $site_id;
        $event_id = Yii::app()->params['mgcapi_event_id'][1]; //Event ID for Deposit
        
        // check if casino's reply is busy, added 05/17/12
        if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
            $eWalletTransModel->updateEwalletTrans($ewallet_trans_id, $stackerdetailID, 2, "", "", $acctid);
            $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $message = 'Can\'t connect to casino';
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>41);
        }
        
        //if PT, unfreeze its account
        if(strpos($service_name, 'PT') !== false) {
            $terminal_name = $casinoUsername;
        }
        if(strpos($service_name, 'RTG2') !== false) {
            $terminal_name = $casinoUsername;
        }
        $locatorName = null;
        if ($service_id == 20) {
            //get siteclassificationID
            $siteClass = $sitesModel->getSiteClassificationByTerminal($terminal_id);
            if ($siteClass == 1) 
              $locatorName = Yii::app()->params['skinNamePlatinum'];
            else 
              $locatorName = Yii::app()->params['skinNameNonPlatinum'];   
        }

        /************************* DEPOSIT ************************************/
        $resultdeposit = $casinoApiHandler->Deposit($terminal_name, $amount, 
            $tracking1, $tracking2, $tracking3, $tracking4, $casinoPassword, 
            $event_id, $transaction_id, $locatorName);
        
        //check if Deposit API reply is null
        if(is_null($resultdeposit)){
            
            // check again if Casino Server is busy
            if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
                $eWalletTransModel->updateEwalletTrans($ewallet_trans_id, $stackerdetailID, 2, "", "", $acctid);
                $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Can\'t connect to casino';
                Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>41);
            }
            
            //execute TransactionSearchInfo API Method
            $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($terminal_name, 
                               $tracking1 , $tracking2 , $tracking3, $tracking4, $transaction_id);
            
            //check if TransactionSearchInfo API is not successful
            if(isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false)
            {
                $eWalletTransModel->updateEwalletTrans($ewallet_trans_id, $stackerdetailID, 2, "", "", $acctid);
                $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Failed to start session.';
                Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id." | ".
                                    "ErrorMessage: ".$transSearchInfo['ErrorMessage']);
                return array('TransMessage'=>$message,'ErrorCode'=>42);
            }
            
            //Check TransactionSearchInfo API
            if(isset($transSearchInfo['TransactionInfo']))
            {
                //RTG / Magic Macau
                if(isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']))
                {
                    $amount = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['amount'];
                    $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                    $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                }
                //MG / Vibrant Vegas
                elseif(isset($transSearchInfo['TransactionInfo']['MG']))
                {
                    //$initial_deposit = $transSearchInfo['TransactionInfo']['Balance'];
                    $transrefid = $transSearchInfo['TransactionInfo']['MG']['TransactionId'];
                    $apiresult = $transSearchInfo['TransactionInfo']['MG']['TransactionStatus'];
                }
                //PT / Vibrant Vegas
                elseif(isset($transSearchInfo['TransactionInfo']['PT']))
                {
                    //$initial_deposit = $transSearchInfo['TransactionInfo']['Balance'];
                    $transrefid = $transSearchInfo['TransactionInfo']['PT']['id'];
                    $apiresult = $transSearchInfo['TransactionInfo']['PT']['status'];
                }
            }
        } else {
            
            //check if TransactionSearchInfo API is not successful
            if(isset($resultdeposit['IsSucceed']) && $resultdeposit['IsSucceed'] == false) {
                $eWalletTransModel->updateEwalletTrans($ewallet_trans_id, $stackerdetailID, 2, "", "", $acctid);
                $message = 'Failed to reload session.';
                Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id." | ".
                                    "ErrorMessage: ".$resultdeposit['ErrorMessage']);
                return array('TransMessage'=>$message,'ErrorCode'=>42);
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
                    $apierrmsg = $resultdeposit['ErrorCode'];
                }
                //Rockin Reno
                else if(isset($resultdeposit['TransactionInfo']['PT'])) {
                    $transrefid = $resultdeposit['TransactionInfo']['PT']['TransactionId'];
                    $apiresult = $resultdeposit['TransactionInfo']['PT']['TransactionStatus'];
                    $apierrmsg = $resultdeposit['ErrorMessage'];
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
            
            $total_reload_balance = $terminal_balance + $amount;
            
            //this will return the transaction summary ID as well as transaction id
//            list($trans_summary_id, $trans_details_id) = $commonTransactionsModel->reloadSessionTransaction($amount, $trans_summary_id, $udate, 
//                    $site_id, $terminal_id, 'R', $paymentType, $service_id, $acctid, $transstatus, $terminal_balance, $total_reload_balance, $loyalty_card, $mid, $stackerbatchid);
//            
//            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, $transstatus,$transrefid,$terminal_id);
            $ewallet_result = $eWalletTransModel->updateEwalletTrans($ewallet_trans_id, $stackerdetailID, $transstatus, $transrefid, $apiresult, $acctid, 
                                                   $service_id, $total_reload_balance, $amount, $trans_summary_id, $transaction_id, $mid, $stackerbatchid);
            
            $newbal = $bcf - $amount;

            $siteBalance->updateBcf($newbal, $site_id, 'Reload session'); //update bcf

            if($ewallet_result['TransCode'] != 0)
            {
                $eWalletTransModel->updateEwalletTrans($ewallet_trans_id, $stackerdetailID, 2, "", "", $acctid);
                $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Failed to insert records in transaction tables.';
                Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>43);
            }
            
            $transDate = $eWalletTransModel->getTransactionDate($ewallet_trans_id);
            
            $message = 'The amount of PhP ' . Utilities::toMoney($amount) . ' is successfully loaded.';
            
            return array('transStatus'=>1,'TransMessage'=>$message,'newbcf'=> Utilities::toMoney($newbal),'initial_deposit'=>Utilities::toMoney($amount),
                         'udate'=>$udate,'terminal_name'=>$terminal_name,'trans_ref_id'=>$transrefid,'trans_summary_id'=>$trans_summary_id,
                         'ewallet_trans_id'=>$ewallet_trans_id,'TransactionDate'=>$transDate);
            
        } else {
            $eWalletTransModel->updateEwalletTrans($ewallet_trans_id, $stackerdetailID, 2, "", "", $acctid);
            $gamingSessions->deleteGamingSessions($terminal_id, $stackerbatchid);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $message = 'Request denied. Please try again.';
            Utilities::errorLogger($message, "Reload Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>28);
        }
        
    }
    public function eSAFERedemption($login_pwd, $terminal_id, $terminal_name, $stackerbatchid, $site_id, $bcf,$service_id, 
                           $amount, $paymentType, $acct_id, $loyalty_card, 
                           $mid, $userMode,$casinoUsername, $trackingID, $voucherTicketBarcode, 
                           $casinoPassword, $partialAmount) 
    {
        Yii::import('application.components.CasinoApiUB');
        Yii::import('application.components.VoucherManagement');
        Yii::import('application.components.VoucherTicketAPIWrapper');
        Yii::import('application.components.PcwsWrapper');
        
        $casinoApi = new CasinoApiUB();
        $terminalsModel = new TerminalsModel();
        $gamingSessionsModel = new GamingSessionsModel();
        $transSummaryModel = new TransactionSummaryModel();
        $transDetailsModel = new TransactionDetailsModel();
        $siteBalance = new SiteBalanceModel();
        $terminalsessions = new TerminalSessionsModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $sitesModel = new SitesModel();
        $pendingTerminalTransactionCountModel = new PendingTerminalTransactionCountModel();
        $voucherTicket = new VoucherTicketAPIWrapper();
        $ewalletTransModel = new EwalletTransModel();
        $stackerSummaryModel = new StackerSummaryModel();
        $pcwsWrapper = new PcwsWrapper("gkapi", "439941");
//        $spyder_enabled = $sitesModel->getSpyderStatus($site_id); 
//        
//        //call SAPI, lock launchpad terminal
//        $casinoApi->callSpyderAPI($commandId = 1, $terminal_id, $casinoUsername, $login_pwd, $service_id, $spyder_enabled);
        
        //call PT, freeze and force logout of session
        $casinoApi->_doCasinoRules($terminal_id, $service_id, $casinoUsername);
       
        $getBalance = $casinoApi->getBalanceUB($terminal_id, $site_id, 'W', 
                        $service_id, $acct_id, $casinoUsername, $casinoPassword);
        
        if(is_string($getBalance)){
            $message = $getBalance;
            Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>26);
        }
        
        list($terminal_balance,$service_name,$terminalSessionsModel,
                $transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount) = $getBalance;
        
        if ($redeemable_amount < $partialAmount)
        {
            $message = "Please enter an amount than is less than or equal to your balance.";
            $this->_sendResponse(200, CommonController::redeemSessionResponse(2, '', '', '', '', '', '', '', $message, 0, 42));

            exit;
        }
        //update last balance in terminal sessions
        $updateBalance = $terminalsessions->updateTerminalBalance($terminal_id, $redeemable_amount);
        $amount = $terminalsessions->getCurrentBalance($terminal_id, $service_id);
        
        $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);
        $is_egmsession_active = $gamingSessionsModel->chkActiveEgmSession($terminal_id);
        
        if($is_terminal_active === false) {
            $message = 'Can\'t get status.';
            return array('TransMessage'=>$message,'ErrorCode'=>30);
        }
        
        if($is_terminal_active < 1) {
            $message = 'Terminal has no active session.';
            Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>32);
        }
        
        if(!$is_egmsession_active){
            $message = 'Terminal has no active EGM session.';
            return array('TransMessage'=>$message,'ErrorCode'=>55);
        }
        
        if($mgaccount != '') {
            $terminal_name = $mgaccount;
        } else {
            $terminal_name = $terminalsModel->getTerminalName($terminal_id);
        }
        
        //revert player bet on hand regardless of the current bet, for PT only
        if(strpos($service_name, 'PT') !== false) {
            $result = $casinoApi->RevertBrokenGamesAPI($terminal_id, $service_id, $casinoUsername);
           
            if($result['RevertBrokenGamesReponse'][0] == false){
                //unfreeze PT account 
                $casinoApiHandler->ChangeAccountStatus($casinoUsername, 0);
                //unlock launchpad gaming terminal
//                $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $casinoUsername, $login_pwd, $service_id, $spyder_enabled);
                $message = 'Unable to revert bet on hand.';
                Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>32);
            }
        }
        
        //check if there was a pending game bet for RTG
        if(strpos($service_name, 'RTG2') !== false) {
            $PID = $casinoApiHandler->GetPIDLogin($casinoUsername);
            $pendingGames = $casinoApi->GetPendingGames($terminal_id, $service_id,$PID);    
        } else {
            $pendingGames = '';
        }
        
        //logout player
//        if(strpos($service_name, 'RTG2') !== false) {
//            $PID = $casinoApiHandler->GetPIDLogin($casinoUsername);
//            $casinoApi->LogoutPlayer($terminal_id, $service_id, $PID);    
//        }

        //Display message
        if(is_array($pendingGames) && $pendingGames['IsSucceed'] == true){
            $message = "Info: There was a pending game bet. ";
            //unlock launchpad gaming terminal
//            $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminal_name, $login_pwd, $service_id, $spyder_enabled);
            Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>32);
        }
        
        //Get Last Transaction Summary ID
        $trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($terminal_id);
        if(!$trans_summary_id){
            $gamingSessionsModel->deleteGamingSessions($terminal_id, $stackerbatchid);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $message = 'Reload Session Failed. Please check if the terminal
                            has a valid start session.';
            Utilities::errorLogger($message, "Redeem Session", 
                                   "TerminalID:".$terminal_id." | ".
                                   "MID: ".$mid." | ".
                                   "SiteID: ".$site_id." | ".
                                   "CasinoID: ".$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>22,'DateExpiry'=>'');
        }
        
        //get last transaction ID if service is MG
        if(strpos($service_name, 'MG') !== false) {
            $trans_origin_id = 0; //cashier origin Id
            $transaction_id = $terminalsModel->insertserviceTransRef($service_id, $trans_origin_id);
            if(!$transaction_id){
                $message = "Failed to insert record in servicetransactionref";
                Utilities::errorLogger($message, "Redeem Session", 
                                    "TerminalID:".$terminal_id." | ".
                                    "MID: ".$mid." | ".
                                    "SiteID: ".$site_id." | ".
                                    "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>20);
            }
        } else {
            $transaction_id = '';
        }
        
        $udate = CasinoApiUB::udate('YmdHisu');
                
        //check if redeemable amount is greater than 0, else skip on calling Withdraw
        //API method
        if($redeemable_amount > 0 && $partialAmount > 0){
            //insert into transaction request log
            //$trans_req_log_last_id = $transReqLogsModel->insert2($udate, $amount, 'W', $terminal_id, $site_id, $service_id,
            //$paymentType, $loyalty_card, $mid, $userMode, $stackerbatchid, '', '', $transaction_id);
            $ewallet_trans_id = $ewalletTransModel->insertEWalletTrans($site_id, $mid, $loyalty_card, $partialAmount, $terminal_balance, $service_id, $userMode, 
                    $paymentType, 'W', $stackerbatchid, $terminal_id, $acct_id, $trans_summary_id);

            if(!$ewallet_trans_id) {
                $pendingTerminalTransactionCountModel->updatePendingTerminalCount($terminal_id);
                $message = 'There was a pending transaction for this user / terminal.';
                Utilities::errorLogger($message, "Redeem Session", 
                                        "TerminalID:".$terminal_id." | ".
                                        "MID: ".$mid." | ".
                                        "SiteID: ".$site_id." | ".
                                        "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>24,'DateExpiry'=>'', 'amount' => '');
            }

            $tracking1 = $ewallet_trans_id;
            $tracking2 = 'W';
            $tracking3 = $terminal_id;
            $tracking4 = $site_id;
            $event_id = Yii::app()->params['mgcapi_event_id'][2]; //Event ID for Withdraw


            // check if casino's reply is busy, added 05/17/12
            if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
                $ewalletTransModel->updateEwalletTrans($ewallet_trans_id, "", 2, "", "", $acct_id);
                $message = 'Can\'t connect to casino';
                Utilities::errorLogger($message, "Redeem Session", 
                                "TerminalID:".$terminal_id." | ".
                                "MID: ".$mid." | ".
                                "SiteID: ".$site_id." | ".
                                "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>41,'DateExpiry'=>'');
            }
            $locatorName = null;
            if ($service_id == 20) {
                //get siteclassificationID
                $siteClass = $sitesModel->getSiteClassificationByTerminal($terminal_id);
                if ($siteClass == 1) 
                  $locatorName = Yii::app()->params['skinNamePlatinum'];
                else 
                  $locatorName = Yii::app()->params['skinNameNonPlatinum'];   
            }
            /************************* WITHDRAW ************************************/
            $resultwithdraw = $casinoApiHandler->Withdraw($casinoUsername, $partialAmount, $tracking1, $tracking2, 
                          $tracking3, $tracking4,  $casinoPassword, $event_id, $transaction_id, $locatorName);
            //check if Deposit API reply is null
            if(is_null($resultwithdraw)){

                // check again if Casino Server is busy
                if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
                    $ewalletTransModel->updateEwalletTrans($ewallet_trans_id, "", 2, "", "", $acct_id);
                    $message = 'Can\'t connect to casino';
                    Utilities::errorLogger($message, "Redeem Session", 
                                "TerminalID:".$terminal_id." | ".
                                "MID: ".$mid." | ".
                                "SiteID: ".$site_id." | ".
                                "CasinoID: ".$service_id);
                    //unlock launchpad gaming terminal
//                        $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminal_name, $login_pwd, $service_id, $spyder_enabled);
                    return array('TransMessage'=>$message,'ErrorCode'=>41);
                }

                //execute TransactionSearchInfo API Method
                $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($terminal_name, 
                                   $tracking1 , $tracking2 , $tracking3, $tracking4, $transaction_id);

                //check if TransactionSearchInfo API is not successful
                if(isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false)
                {
                    $ewalletTransModel->updateEwalletTrans($ewallet_trans_id, "", 2, "", "", $acct_id);
                    $gamingSessionsModel->deleteGamingSessions($terminal_id, $stackerbatchid);
                    $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                    $message = 'Failed to start session.';
                    Utilities::errorLogger($message, "Redeem Session", 
                                "TerminalID:".$terminal_id." | ".
                                "MID: ".$mid." | ".
                                "SiteID: ".$site_id." | ".
                                "CasinoID: ".$service_id." | ".
                                "ErrorMessage: ".$transSearchInfo['ErrorMessage']);
//                        $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminal_name, $login_pwd, $service_id, $spyder_enabled);
                    return array('TransMessage'=>$message,'ErrorCode'=>42);
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
                    $ewalletTransModel->updateEwalletTrans($ewallet_trans_id, "", 2, "", "", $acct_id);
                    $message = 'Request denied. Please try again.';
                    Utilities::errorLogger($message, "Redeem Session", 
                                "TerminalID:".$terminal_id." | ".
                                "MID: ".$mid." | ".
                                "SiteID: ".$site_id." | ".
                                "CasinoID: ".$service_id);
//                        $casinoApi->callSpyderAPI($commandId = 0, $terminal_id, $terminal_name, $login_pwd, $service_id, $spyder_enabled);
                    return array('TransMessage'=>$message,'ErrorCode'=>26);
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

            if($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved') {
                $transstatus = '1';
            } else {
                $transstatus = '2';
            }

            //if Deposit / TransactionSearchInfo API status is approved
            if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved'){

                $current_balance = $pcwsWrapper->getBalance(Yii::app()->params['get_balance'], $loyalty_card, $service_id);
                if (is_string($current_balance)) {
                    $balance = $current_balance['GetBalance']['TransactionMessage'];
                }
                else {
                    $balance = $current_balance['GetBalance']['PlayableBalance'];
                }
                //this will return if the transaction is successful
                $isredeemed = $ewalletTransModel->updateEwalletTrans($ewallet_trans_id, "", $transstatus, $transrefid, $apiresult, $acct_id, $service_id, 
                                               $balance, $partialAmount, $trans_summary_id, $transaction_id, $mid, $stackerbatchid, $voucherTicketBarcode);

                //check if terminaltype is Genesis
                $terminalType = $terminalsModel->checkTerminalType($terminal_id);
                /*********************INSERT TICKET***********************/
                $source = Yii::app()->params['voucher_source'];
                //get reg terminal name
                $reg_terminal = trim(str_replace("VIP", "", $terminal_name));
                $regTerminal  = trim(str_replace(Yii::app()->params['SitePrefix'], "", $reg_terminal));

                $getvoucher = $voucherTicket->addTicket($regTerminal, $partialAmount, $acct_id, $source, $loyalty_card, 1, $stackerbatchid, $voucherTicketBarcode, $trackingID);
                if (isset($getvoucher['AddTicket']['ErrorCode']) && $getvoucher['AddTicket']['ErrorCode'] == 0) {
                    //success transaction
                    $ticketCode         = $getvoucher['AddTicket']['VoucherTicketBarcode'];
                    $ticketExpiration   = $getvoucher['AddTicket']['ExpirationDate'];

                } else {
                    $ticketCode         = "";
                    $ticketExpiration   = "";

                    if (isset($getvoucher['AddTicket']['ErrorCode']) && $getvoucher['AddTicket']['ErrorCode'] != 0) {
                        $message = $getvoucher['AddTicket']['TransactionMessage'];
                        $errorcode = $getvoucher['AddTicket']['ErrorCode'];
                    } else {
                        if (isset($getvoucher['AddTicket']['TransactionMessage'])) {
                            $message = $getvoucher['AddTicket']['TransactionMessage'];
                            $errorcode = "";
                        } else {
                            $message = "Can't connect to VMS Server";
                            $errorcode = "";
                        }
                    }
                    //Log To Error Logger
                    Utilities::errorLogger($message, "Redeem Session", 
                                                            "TerminalID:".$terminal_id." | ".
                                                            "MID: ".$mid." | ".
                                                            "SiteID: ".$site_id." | ".
                                                            "CasinoID: ".$service_id. " | ".
                                                            "GeneratedTicketCode: ".$voucherTicketBarcode);
                }
                /****************************************************/
                if($isredeemed['TransCode'] != 0){
                    $ewalletTransModel->updateEwalletTrans($ewallet_trans_id, "", 2, "", "", $acct_id);
                    $message = 'Failed update records in transaction tables';
                    Utilities::errorLogger($message, "Redeem Session", 
                                "TerminalID:".$terminal_id." | ".
                                "MID: ".$mid." | ".
                                "SiteID: ".$site_id." | ".
                                "CasinoID: ".$service_id);
                    return array('TransMessage'=>$message,'ErrorCode'=>27);
                }
                $transDate = $ewalletTransModel->getTransactionDate($ewallet_trans_id);

                //force logout gen
                $pcwsWrapper->forceLogoutGen(Yii::app()->params['force_logout_gen'], $casinoUsername, $service_id);

                $message = 'You have successfully redeemed the amount of PhP ' . Utilities::toMoney($partialAmount);
                $partialAmount = Utilities::toMoney($partialAmount);
                return array('TransMessage'=>$message,'transStatus'=>1,
                    'trans_summary_id'=>$trans_summary_id,'udate'=>$udate,'amount'=>$partialAmount,'terminal_login'=>$terminal_name,
                    'trans_ref_id'=>$transrefid,'terminal_name'=>$terminal_name,'ewallet_trans_id'=>$ewallet_trans_id,
                    'TransactionDate'=>$transDate, 'VoucherTicketBarcode' => $ticketCode, 'ExpirationDate' => $ticketExpiration);

            } else {

                $ewalletTransModel->updateEwalletTrans($ewallet_trans_id, "", 2, "", "", $acct_id);
                $message = 'Requests denied. Please try again.';
                Utilities::errorLogger($message, "Redeem Session", 
                                "TerminalID:".$terminal_id." | ".
                                "MID: ".$mid." | ".
                                "SiteID: ".$site_id." | ".
                                "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>28);
            }
        
        } else {
            $current_balance = $pcwsWrapper->getBalance(Yii::app()->params['get_balance'], $loyalty_card, $service_id);
            if (is_string($current_balance)) {
                $balance = $current_balance['GetBalance']['TransactionMessage'];
            }
            else {
                $balance = $current_balance['GetBalance']['PlayableBalance'];
            }
            $updateZeroAmt = $stackerSummaryModel->updateESAFETransWithZero($stackerbatchid, $acct_id, $balance, $mid, $service_id);
            if($updateZeroAmt['TransCode'] != 0){
                $message = 'Failed update records in transaction tables';
                Utilities::errorLogger($message, "Redeem Session", 
                            "TerminalID:".$terminal_id." | ".
                            "MID: ".$mid." | ".
                            "SiteID: ".$site_id." | ".
                            "CasinoID: ".$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>27);
            }
            $transDate = date('Y-m-d H:i:s');
            //force logout gen
            $pcwsWrapper->forceLogoutGen(Yii::app()->params['force_logout_gen'], $casinoUsername, $service_id);
            
            return array('TransMessage'=>'Info: Session has been ended.','transStatus'=>1, 'amount' => $partialAmount, 
                        'trans_summary_id'=>$trans_summary_id,'udate'=>$udate,'terminal_login'=>$terminal_name,
                        'terminal_name'=>$terminal_name,'ewallet_trans_id'=>'', 'TransactionDate'=>$transDate);
        }
        
    }
}
?>
