<?php

/**
 * Date Created 11 9, 11 2:41:32 PM <pre />
 * Description of CommonReload
 * @author Bryan Salazar
 */
class CommonReload {
    
    /**
     * @param int $bcf
     * @param int $amount
     * @param int $terminal_id
     * @param int $site_id
     * @param int $acctid
     * @param int $service_id
     * @return array 
     */
    public function reload($bcf,$amount,$terminal_id,$site_id,$acctid, $service_id) {
        Yii::import('application.components.CasinoApi');       
        
        $casinoApi = new CasinoApi();
        $terminalsModel = new TerminalsModel();
        $transSummaryModel = new TransactionSummaryModel();
        $transDetailsModel = new TransactionDetailsModel();
        $siteBalance = new SiteBalanceModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        $terminalSessionsModel = new TerminalSessionsModel();
        
//        $service_id = $terminalSessionsModel->getServiceId($terminal_id);
        
        if(($bcf - $amount) < 0) {
            $message = 'Not enough BCF';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>'Not enough BCF','ErrorCode'=>41);
        }
        
        $is_terminal_active = $terminalSessionsModel->isSessionActive($terminal_id);
        
        if($is_terminal_active === false) {
            $message = 'Can\'t get status.';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>18,'DateExpiry'=>'');
        }
        
        if($is_terminal_active < 1) {
            $message = 'Terminal has no active session.';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>43,'DateExpiry'=>'');
        }  
        
        $getBalance = $casinoApi->getBalance($terminal_id, $site_id,'D',$service_id);
        
        if(is_string($getBalance)){
            $message = $getBalance;
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>14,'DateExpiry'=>'');
        }
        
        list($terminal_balance,$service_name,$terminalSessionsModel,
                $transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount) = $getBalance;
        
        $total_terminal_balance = $terminal_balance + $amount;
        
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
                return array('TransMessage'=>$message,'ErrorCode'=>22,'DateExpiry'=>'');
            }
        } else {
            $transaction_id = '';
        }
        
        //get terminal password 
        $terminal_pwd_res = $terminalsModel->getTerminalPassword($terminal_id, $service_id);
        $terminal_pwd = $terminal_pwd_res['ServicePassword'];
        
        $udate = CasinoApi::udate('YmdHisu');
        
        //insert into transaction request log
        $trans_req_log_last_id = $transReqLogsModel->insert($udate, $amount, 'R', $terminal_id, $site_id, $service_id);
        
        if(!$trans_req_log_last_id) {
            $message = 'Failed to insert in transactionrequestlogs';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>24,'DateExpiry'=>'');
        }
        
        $tracking1 = $trans_req_log_last_id;
        $tracking2 = 'R';
        $tracking3 = $terminal_id;
        $tracking4 = $site_id;
        $event_id = Yii::app()->params['mgcapi_event_id'][1]; //Event ID for Reload
        
        // check if casino's reply is busy, added 05/17/12
        if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
            $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
            $message = 'Can\'t connect to casino';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>25,'DateExpiry'=>'');
        }
        
        /************************* RELOAD *************************************/
        $resultdeposit = $casinoApiHandler->Deposit($terminal_name, $amount, 
            $tracking1, $tracking2, $tracking3, $tracking4, $terminal_pwd, $event_id, $transaction_id);
        
        //check if Deposit API reply is null
        if(is_null($resultdeposit)){
            
            // check again if Casino Server is busy
            if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $message = 'Can\'t connect to casino';
                Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>25,'DateExpiry'=>'');
            }
            
            //execute TransactionSearchInfo API Method
            $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($terminal_name, 
                               $tracking1 , $tracking2 , $tracking3, $tracking4, $transaction_id);
            
            //check if TransactionSearchInfo API is not successful
            if(isset($transSearchInfo['IsSucceed']) && $transSearchInfo['IsSucceed'] == false)
            {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $message = 'Failed to reload session.';
                Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id.' ErrorMessage='.$transSearchInfo['ErrorMessage']);
                return array('TransMessage'=>$message,'ErrorCode'=>44,'DateExpiry'=>'');
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
                elseif(isset($transSearchInfo['TransactionInfo']))
                {
                    //$amount = abs($transSearchInfo['TransactionInfo']['Balance']);
                    $transrefid = $transSearchInfo['TransactionInfo']['TransactionId'];
                    $apiresult = $transSearchInfo['TransactionInfo']['TransactionStatus'];
                }
            }
        } else {
            
            //check if TransactionSearchInfo API is not successful
            if(isset($resultdeposit['IsSucceed']) && $resultdeposit['IsSucceed'] == false) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $message = 'Failed to reload session.';
                Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id.' ErrorMessage='.$resultdeposit['ErrorMessage']);
                return array('TransMessage'=>$message,'ErrorCode'=>44,'DateExpiry'=>'');
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
                else{
                    $transrefid = $resultdeposit['TransactionInfo']['TransactionId'];
                    $apiresult = $resultdeposit['TransactionInfo']['TransactionStatus'];
                    $apierrmsg = $resultdeposit['ErrorCode'];
                }
            }
        }
        
        if($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true') {
            $transstatus = '1';
        } else {
            $transstatus = '2';
        }
        
        
        //if Deposit / TransactionSearchInfo API status is approved
        if ($apiresult == 'true' || $apiresult == 'TRANSACTIONSTATUS_APPROVED'){
            
            $trans_summary_id = $transSummaryModel->getLastTransSummaryId($terminal_id, $site_id);
            
            $trans_summary = $transSummaryModel->getTransactionSummaryDetail($site_id, $terminal_id);
            
            $trans_summary_id = $trans_summary['TransactionsSummaryID'];
            
            $total_reload_balance = $trans_summary['Reload'] + $amount;
            
            $trans_details_id = $commonTransactionsModel->reloadTransaction($amount, $trans_summary_id, 
                            $udate, $site_id, $terminal_id, 'R', $service_id, 
                            $acctid, $transstatus, $total_reload_balance, $total_terminal_balance);
            
            if(!$trans_details_id) {
                $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
                $message = 'Failed insert records in transaction tables';
                Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>27,'DateExpiry'=>'');
            }
            
            $transDate = $transDetailsModel->getTransactionDate($trans_details_id);
            
            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, $transstatus,$transrefid,$terminal_id);
            
            $newbal = $bcf - $amount;
            
            $siteBalance->updateBcf($newbal, $site_id, 'Reload session');
            
            $message = 'The amount of PhP ' . Utilities::toMoney($amount) . ' is successfully loaded.';
            
            $new_terminal_balance = Utilities::toInt($terminal_balance) + Utilities::toInt($amount);
            
            return array('transStatus'=>1,'message'=>$message,'newbcf'=> Utilities::toMoney($newbal),
                         'reload_amount'=> Utilities::toMoney($amount),'terminal_balance'=>Utilities::toMoney($new_terminal_balance),
                         'trans_summary_id'=>$trans_summary_id,'udate'=>$udate,'trans_ref_id'=>$transrefid,
                         'trans_details_id'=>$trans_details_id,'TransactionDate'=>$transDate,'terminal_name'=>$terminal_name);
            
        } else {
            $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
            $message = 'Request denied. Please try again.';
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>28,'DateExpiry'=>'');
        }
    }
}
