<?php

/**
 * Date Created 11 8, 11 9:17:55 AM <pre />
 * Description of CommonRedeem
 * @author Bryan Salazar
 */
class CommonRedeem {
    
    /**
     * @param int $terminal_id
     * @param int $site_id
     * @param [int,string] $bcf
     * @param int $service_id
     * @param int $amount
     * @param int $acct_id
     * @return array 
     */
    public function redeem($terminal_id,$site_id,$bcf,$service_id,$amount,$acct_id) {
        
        Yii::import('application.components.CasinoApi');
        
        $casinoApi = new CasinoApi();
        $terminalsModel = new TerminalsModel();
        $transactionSummaryModel = new TransactionSummaryModel();
        $transactionDetailsModel = new TransactionDetailsModel();
        $siteBalanceModel = new SiteBalanceModel();
        $commonTransactionsModel = new CommonTransactionsModel();
        
        $getBalance = $casinoApi->getBalance($terminal_id, $site_id,'W',$service_id,$acct_id);
        
        if(is_string($getBalance)){
            $message = $getBalance;
            Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
            return array('TransMessage'=>$message,'ErrorCode'=>14,'DateExpiry'=>'');
        }
        
        list($terminal_balance,$service_name,$terminalSessionsModel,
                $transReqLogsModel,$redeemable_amount,$casinoApiHandler,$mgaccount) = $getBalance;
        
        if($redeemable_amount > 0){
            
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

            $requeststatus = $transReqLogsModel->getTransReqLogStatus($site_id, $terminal_id);

            if($requeststatus == 0) {
                $message = 'Terminal already has a pending transaction request.';
                Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>45,'DateExpiry'=>'');
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
                    return array('TransMessage'=>$message,'ErrorCode'=>22,'DateExpiry'=>'');
                }
            } else {
                $transaction_id = '';
            }

            $terminal_pwd_res = $terminalsModel->getTerminalPassword($terminal_id, $service_id);
            $terminal_pwd = $terminal_pwd_res['ServicePassword'];

            $udate = CasinoApi::udate('YmdHisu');

            //insert into transaction request log
            $trans_req_log_last_id = $transReqLogsModel->insert($udate, $amount, 'W', $terminal_id, $site_id, $service_id);

            if(!$trans_req_log_last_id) {
                $message = 'Failed to insert transactionrequestlogs table';
                Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>24,'DateExpiry'=>'');
            }

            if(Utilities::toMoney($amount) != Utilities::toMoney(Utilities::toInt($redeemable_amount))) {
                $transReqLogsModel->update($trans_req_log_last_id, false, 2,null,$terminal_id);
                $message = 'Redeemable amount is not equal.';
                Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>46,'DateExpiry'=>'');
            }
            
            $tracking1 = $trans_req_log_last_id;
            $tracking2 = 'W';
            $tracking3 = $terminal_id;
            $tracking4 = $site_id;   
            $event_id = Yii::app()->params['mgcapi_event_id'][2]; //Event ID for Withdraw
            
            // check if casino's reply is busy, added 05/17/12
            if (!(bool)$casinoApiHandler->IsAPIServerOK()) {
                $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                $message = 'Can\'t connect to casino';
                Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>25,'DateExpiry'=>'');
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
                    $message = 'Request denied. Please try again.';
                    Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id.' ErrorMessage='.$transSearchInfo['ErrorMessage']);
                    return array('TransMessage'=>$message,'ErrorCode'=>25,'DateExpiry'=>'');
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
                    elseif(isset($transSearchInfo['TransactionInfo']))
                    {
                        //$amount = abs($transSearchInfo['TransactionInfo']['Balance']); //returns 0 value
                        $transrefid = $transSearchInfo['TransactionInfo']['TransactionId'];
                        $apiresult = $transSearchInfo['TransactionInfo']['TransactionStatus'];
                    }
                }
            } else {
                
                //check if TransactionSearchInfo API is not successful
                if(isset($resultwithdraw['IsSucceed']) && $resultwithdraw['IsSucceed'] == false) {
                    $transReqLogsModel->update($trans_req_log_last_id, 'false', 2,null,$terminal_id);
                    $message = 'Request denied. Please try again.';
                    Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id.' ErrorMessage='.$resultwithdraw['ErrorMessage']);
                    return array('TransMessage'=>$message,'ErrorCode'=>28,'DateExpiry'=>'');
                }
                
                //check Withdraw API Result
                if (isset($resultwithdraw['TransactionInfo'])) {
                    if(isset($resultwithdraw['TransactionInfo']['WithdrawGenericResult'])) {
                        $transrefid = $resultwithdraw['TransactionInfo']['WithdrawGenericResult']['transactionID'];
                        $apiresult = $resultwithdraw['TransactionInfo']['WithdrawGenericResult']['transactionStatus'];
                    } else {
                        $transrefid = $resultwithdraw['TransactionInfo']['TransactionId'];
                        $apiresult = $resultwithdraw['TransactionInfo']['TransactionStatus'];
                    }
                }
            }
            
            if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true') {
                $transstatus = '1';
            } else {
                $transstatus = '2';
            }

            //if Withdraw / TransactionSearchInfo API status is approved
            if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED'){
                
                $trans_summary_id = $transactionSummaryModel->getLastTransSummaryId($terminal_id, $site_id);

                $trans_details_id = $commonTransactionsModel->redeemTransaction($amount, $trans_summary_id, $udate, 
                                    $site_id, $terminal_id, 'W', $service_id, $acct_id, $transstatus);

                if(!$trans_details_id){
                    $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
                    $message = 'Failed update records in transaction tables';
                    Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                    return array('TransMessage'=>$message,'ErrorCode'=>50,'DateExpiry'=>'');
                }

                $transDate = $transactionDetailsModel->getTransactionDate($trans_details_id);
                
                $transReqLogsModel->update($trans_req_log_last_id, $apiresult, $transstatus,$transrefid,$terminal_id);

                return array('transStatus'=>1,'message'=>'You have successfully redeemed the amount of PhP '.Utilities::toMoney($amount),
                    'trans_summary_id'=>$trans_summary_id,'udate'=>$udate,'amount'=>$amount,'terminal_login'=>$terminal_name,
                    'trans_ref_id'=>$transrefid,'terminal_name'=>$terminal_name,'voucher_code'=>$trans_details_id,
                    'TransactionDate'=>$transDate);
            } else {
                $transReqLogsModel->update($trans_req_log_last_id, $apiresult, 2,null,$terminal_id);
                $message = 'Request denied. Please try again.';
                Utilities::log($message . ' TerminalID='.$terminal_id . ' ServiceID='.$service_id);
                return array('TransMessage'=>$message,'ErrorCode'=>28,'DateExpiry'=>'');
            }
        }else{
            $message = 'Info: Session has been ended.';
            return array('TransMessage'=>$message,'ErrorCode'=>0,'DateExpiry'=>'');
        }    
    }
}