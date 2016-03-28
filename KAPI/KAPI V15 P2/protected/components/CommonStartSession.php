<?php

/**
 * Date Created 11 7, 11 2:00:24 PM <pre />
 * Date Modified 10/12/12
 * StartSession for EGM
 * @author Bryan Salazar
 * @author Edson Perez
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
    public function start($terminal_id,$site_id,$trans_type,$service_id,$bcf,$initial_deposit,$acctid) {
        Yii::import('application.components.CasinoApi');
        
        $casinoApi = new CasinoApi();
        $terminalsModel = new TerminalsModel();
        $transSummaryModel = new TransactionSummaryModel();
        $transDetailsModel = new TransactionDetailsModel();
        $siteBalance = new SiteBalanceModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $sitesModel = new SitesModel();
        
        $siteCode = $sitesModel->getSiteCode($site_id);
        
        if($terminalsModel->isPartnerAlreadyStarted($terminal_id, $siteCode)) {
            $message = $terminalsModel->terminal_code . ' terminal already started';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>17);
        }
        
        $getBalance = $casinoApi->getBalance($terminal_id, $site_id,'D',$service_id);
        
        if(is_string($getBalance)){
            $message = $getBalance;
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>14);
        }
        
        list($terminal_balance,$service_name,$terminalSessionsModel,
                $transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount) = $getBalance;
        
        $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);
        
        if($is_terminal_active === false) {
            $message = 'Can\'t get status.';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>18);
        }
        
        if($is_terminal_active != 0) {
            $message = 'Terminal is already active.';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>19);
        }
        
        if($terminal_balance != 0) {
            $message = 'Please inform customer service for manual redemption.';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>20);
        }
         
        if(($bcf - $initial_deposit) < 0) {
            $message = 'BCF is not enough.';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>21);
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
                Utilities::log($message);
                return array('TransMessage'=>$message,'ErrorCode'=>22);
            }
        } else {
            $transaction_id = '';
        }
        
        //get terminal password 
        $terminal_pwd_res = $terminalsModel->getTerminalPassword($terminal_id, $service_id);
        $terminal_pwd = $terminal_pwd_res['ServicePassword'];

        $udate = CasinoApi::udate('YmdHisu');
        
        //insert into terminalsessions
        $trans_summary_max_id = null;
        $is_terminal_exist = $terminalSessionsModel->insert($terminal_id, $service_id, $initial_deposit, $trans_summary_max_id);
        
        if(!$is_terminal_exist){
            $message = 'Terminal has an existing session.';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>23);
        }
        
        //insert into transaction request log
        $trans_req_log_last_id = $transReqLogsModel->insert($udate, $initial_deposit, 'D', $terminal_id, $site_id, $service_id);
 
        if(!$trans_req_log_last_id) {
            $message = 'Failed to insert in transactionrequestlogs';
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>24);
        }
        
        $tracking1 = $trans_req_log_last_id;
        $tracking2 = 'D';
        $tracking3 = $terminal_id;
        $tracking4 = $site_id;
        $event_id = Yii::app()->params['mgcapi_event_id'][0]; //Event ID for Deposit
        
        // check if casino's reply is busy, added 05/17/12
        if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
            $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $message = 'Can\'t connect to casino';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>25);
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
                $message = 'Can\'t connect to casino';
                Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>25);
            }
            
            //execute TransactionSearchInfo API Method
            $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($terminal_name, 
                               $tracking1 , $tracking2 , $tracking3, $tracking4, $transaction_id);
            
            //check if TransactionSearchInfo API is not successful
            if(isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false)
            {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Failed to start session.';
                Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id. ' ErrorMessage='.$transSearchInfo['ErrorMessage']);
                return array('TransMessage'=>$message,'ErrorCode'=>26);
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
                elseif(isset($transSearchInfo['TransactionInfo']))
                {
                    $initial_deposit = $transSearchInfo['TransactionInfo']['Balance'];
                    $transrefid = $transSearchInfo['TransactionInfo']['TransactionId'];
                    $apiresult = $transSearchInfo['TransactionInfo']['TransactionStatus'];
                }
            }
        } else {
            
            //check if TransactionSearchInfo API is not successful
            if(isset($resultdeposit['IsSucceed']) && $resultdeposit['IsSucceed'] == false) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Failed to start session.';
                Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id. 'ErrorMessage = '.$resultdeposit['ErrorMessage']);
                return array('TransMessage'=>$message,'ErrorCode'=>26);
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
                else if(isset($resultdeposit['TransactionInfo'])) {
                    $transrefid = $resultdeposit['TransactionInfo']['TransactionId'];
                    $apiresult = $resultdeposit['TransactionInfo']['TransactionStatus'];
                    $apierrmsg = $resultdeposit['ErrorMessage'];
                }
            }
        }
        
        if($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true') {
            $transstatus = '1';
        } else {
            $transstatus = '2';
        }
        
        
        //if Deposit / TransactionSearchInfo API status is approved
        if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED'){

            //this will return the transaction summary ID as well as transaction id
            list($trans_summary_id, $trans_details_id) = $commonTransactionsModel->startTransaction($site_id, $terminal_id, 
                                    $initial_deposit, $acctid, $udate, 'D', $service_id, $transstatus);
            if(!$trans_summary_id)
            {
                $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
                $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
                $message = 'Failed to insert records in transaction tables.';
                Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>27);
            }
            
            $transDate = $transDetailsModel->getTransactionDate($trans_details_id);
            
            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, $transstatus,$transrefid,$terminal_id);
            
            $newbal = $bcf - $initial_deposit;
            $siteBalance->updateBcf($newbal, $site_id, 'Start session'); //update bcf
            $message = 'New player session started.The player initial playing balance is PhP ' . Utilities::toMoney($initial_deposit);
            
            return array('transStatus'=>1,'message'=>$message,'newbcf'=> Utilities::toMoney($newbal),'initial_deposit'=>Utilities::toMoney($initial_deposit),
                         'udate'=>$udate,'terminal_name'=>$terminal_name,'trans_ref_id'=>$transrefid,'trans_summary_id'=>$trans_summary_id,
                         'trans_details_id'=>$trans_details_id,'TransactionDate'=>$transDate);
            
        } else {
            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
            $terminalSessionsModel->deleteTerminalSessionById($terminal_id);
            $message = 'Request denied. Please try again.';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>28);
        }
    }
}