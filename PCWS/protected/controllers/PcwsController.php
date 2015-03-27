<?php

/**
 * Controller for ForceT Authentication webservice
 * @author gvjagolino,aqdepliyan,jefloresca,jdlachica,fdlsison
 */
class PcwsController extends Controller{
    
    public $_un; //username
    public $_dt; //accessdate
    public $_tkn; //token

    private function _authenticate(){
        $syscode = Yii::app()->params['SystemCode'];
        $onrequesttimeout = Yii::app()->params['onrequesttimeout'];
        $retval = 1;
        if(!empty($this->_un)){
            foreach ($syscode as $key => $value) {
                if($this->_un == $key){
                    if(!empty($this->_dt)){
                        $date1 = new DateTime($this->_dt);
                        $dt = $date1->format('YmdHis');
                        $processtkn = sha1($dt.$value);
                        if(!empty($this->_tkn)){
                            if($this->_tkn == $processtkn){
                                if($onrequesttimeout){
                                    $date2 = new DateTime(date('Y-m-d H:i:s'));
                                    $diff = $date2->diff($date1);
                                    $hours = $diff->h;
                                    $hours = $hours + ($diff->days*24);

                                    if($hours == 0){
                                        $retval = 0; //Authentication Successful
                                    } else { $retval = 2; } //Expired Request
                                } else { $retval = 0; } //Authentication Successful
                            } else { $retval = 1; } //Pass token parameters did not match token process by API.
                        } else { $retval = 3; } //Incomplete Data: Empty Token
                    } else { $retval = 3; } //Incomplete Data: Empty Date Time
                }
            }
        } else { $retval = 3; } //Incomplete Data: Empty System Username
        
        return $retval;
    }
    
    public function actionDeposit(){
        Yii::import('application.components.CasinoController');

        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));
          
        $paramval = CJSON::encode($request);
        $message = "[Deposit] Input: ".$paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $isconnvalid = $this->_authenticate();

        if($isconnvalid == 0){
                $serviceid = trim(trim($request['ServiceID']));
                $cardnumber = trim(trim($request['CardNumber']));
                $amount = trim(trim($request['Amount']));
                $paymenttype = trim(trim($request['PaymentType']));
                $siteid = trim(trim($request['SiteID']));
                $aid = trim(trim($request['AID']));
                $tracenumber = trim(trim($request['TraceNumber']));
                $referencenumber = trim(trim($request['ReferenceNumber']));

                if(isset($serviceid) && $serviceid !== '' && isset($cardnumber) && $cardnumber !== '' && isset($amount) && $amount !== '' && isset($paymenttype) && $paymenttype !== '' && isset($siteid) && $siteid !== '' && isset($aid) && $aid !== ''){
                    if($tracenumber == '' && $referencenumber == ''){
                        $bankid = null;
                        $approvalcode = null;
                    }
                    
                    $membercards = new MemberCardsModel();
                    $memberservices = new MemberServicesModel();
                    $ewallet = new EwallettransModel();
                    $terminalsessions = new TerminalSessionsModel();
                    $casinocontroller = new CasinoController();
                    $transactionsummarymodel = new TransactionSummaryModel();
                    $sitebalancemodel = new SiteBalanceModel();
                    $terminalsmodel = new TerminalsModel();
                    
                    $mid = $membercards->getMID($cardnumber);

                   if(is_array($mid)){

                       $mid = $mid['MID'];

                       $terminalid = $terminalsessions->checkSessionIDwithcard($cardnumber, $serviceid);
                       if(is_array($terminalid)){
                            $terminalid = $terminalid['TerminalID'];
                            
                            $siteID = $terminalsmodel->getSiteID($terminalid);
                            
                            if($siteID['SiteID'] != $siteid){
                                $transMsg = 'Error: Deposit Failed, Player has an existing session on a different site.';
                                $errCode = 24;
                                
                                $data =  CommonController::deposit($transMsg, $errCode);
                                $this->_sendResponse(200, $data);
                                exit;
                            }
                       }
                       else{
                            $terminalid = $siteid;
                       }

                       $casinocredentials = $memberservices->getCasinoCredentialsAbbott($mid);

                        if(!empty($casinocredentials) || !is_null($casinocredentials)){
                            $serviceUsername = $casinocredentials['ServiceUsername'];
                            $servicePassword = $casinocredentials['ServicePassword'];

                            $bcf = $sitebalancemodel->getSiteBalance($siteid);
                            $bcf = $bcf['Balance'];
                            
                            if(($bcf - $amount) < 0) {
                                $errCode = 7;
                                $transMsg = 'Error: BCF is not enough.';
                                
                                $data =  CommonController::deposit($transMsg, $errCode);
                                $this->_sendResponse(200, $data);
                                exit;
                            }
                            
                            $balance = $casinocontroller->GetBalance($serviceUsername);
                            
                            if(is_array($balance)){
                                $playablebalance = $balance['balance'];
                            
                                $transsumid = $terminalsessions->getTransSummaryID($mid, $serviceid);

                                if(is_array($transsumid)){
                                    $transsumid = $transsumid['TransactionSummaryID'];
                                }
                                else{
                                    $transsumid = null;
                                }

                                $tracking1 = $ewallet->insertEwallet($cardnumber, $siteid, $mid, $amount, $playablebalance, 'D', $serviceid, 1, $paymenttype, $aid, $transsumid, $terminalid, $tracenumber, $referencenumber);
                                $tracking2 = 'D';
                                $tracking3 = $terminalid;
                                $tracking4 = $siteid;
                                
                                if(!$tracking1){
                                    $errCode = 11;
                                    $transMsg = 'Deposit Failed, There was a pending transaction for this card.';

                                    $data = CommonController::deposit($transMsg, $errCode);
                                    $this->_sendResponse(200, $data);
                                    exit;
                                }

                                $resultdeposit = $casinocontroller->Deposit($serviceUsername, $servicePassword, 1, $amount, $tracking1, $tracking2, $tracking3, $tracking4);

                                if(is_null($resultdeposit)){
                                    $transSearchInfo = $casinocontroller->TransactionSerachInfo($serviceUsername, $tracking1, $tracking2, $tracking3, $tracking4);

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
                                }
                                else{
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

                                if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved'){

                                    $balance = $casinocontroller->GetBalance($serviceUsername);
                                    $playablebalance = $balance['balance'];
                                    
                                    if(!is_null($transsumid)){
                                        $totalamount = $transactionsummarymodel->getTotalReload($transsumid);
                                        if(is_array($totalamount)){
                                            $totalamount = $totalamount['WalletReloads'] + $amount;
                                        }
                                        else{
                                            $totalamount = $amount;
                                        }
                                        $isupdatedtranssum = $transactionsummarymodel->updateTransSummary($totalamount, $transsumid);
                                        
                                        if(!$isupdatedtranssum){
                                            $errCode =9;
                                            $transMsg = 'Failed to update Transaction Summary';

                                            $data = CommonController::deposit($transMsg, $errCode);
                                            $this->_sendResponse(200, $data);
                                            exit;
                                        }
                                    }

                                    $isupdatedewallet = $ewallet->updateEwallet($playablebalance, $transrefid, $apiresult, $aid, $transstatus, $tracking1);

                                if($isupdatedewallet){
                                    if($transstatus == 1){
                                        $errCode = 0;
                                        $transMsg = 'e-wallet loading successful';
                                        $newbal = $bcf - $amount;
                                        
                                        $sitebalancemodel->updateBcf($newbal, $siteid, 'load');
                                        
                                        $memberservices->UpdateBalances($playablebalance, "load-$tracking1", $mid, $serviceid);
                                    }
                                    else{
                                        $errCode = 8;
                                        $transMsg = 'Deposit Transaction Failed';
                                    }
                                }
                                else{
                                    $errCode = 10;
                                    $transMsg = 'Failed to update in transaction Table';
                                }

                                $data = CommonController::deposit($transMsg, $errCode);

                            } else {
                                $errCode = 8;
                                $tobalance = '0.00';
                                $ewallet->updateEwallet($tobalance, null, $apiresult, $aid, $transstatus, $tracking1);

                                $transMsg = 'Deposit Transaction Failed';

                                $data = CommonController::deposit($transMsg, 2);
                            }
                            }
                            else{
                                $transMsg = $balance; 
                                $errCode = 8;

                                $data = CommonController::deposit($transMsg, $errCode);
                            }

                        }
                        else{
                            $transMsg = 'Card does not have existing casino account'; 
                            $errCode = 6;

                            $data = CommonController::deposit($transMsg, $errCode);
                        }

                   }
                   else{
                        $transMsg = 'Card not found'; 
                        $errCode = 5;

                        $data = CommonController::deposit($transMsg, $errCode);
                   }
                }
                else{
                    $transMsg = 'All fields are required'; 
                    $errCode = 4;

                    $data = CommonController::deposit($transMsg, $errCode);
                }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.'; 
                    break;
                case 2:
                    $transMsg = 'Request time out.'; 
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.'; 
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }
        
        $message = "[Deposit] Token: ".$this->_tkn.", Output: ".$data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
        
        $this->_sendResponse(200, $data);
    }
    
    
    public function actionGetbalance(){
        Yii::import('application.components.CasinoController');
        
        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));
        
        $paramval = CJSON::encode($request);
        $message = "[GetBalance] Input: ".$paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $isconnvalid = $this->_authenticate();

        if($isconnvalid == 0){
                $cardnumber = trim(trim($request['CardNumber']));

                if($cardnumber !== ''){
                    $membercards = new MemberCardsModel();
                    $memberservices = new MemberServicesModel();

                    $mid = $membercards->getMID($cardnumber);

                    if(!empty($mid) || !is_null($mid)){
                        $mid = $mid['MID'];

                        $casinocredentials = $memberservices->getCasinoCredentialsAbbott($mid);

                        if(!empty($casinocredentials) || !is_null($casinocredentials)){
                            $serviceUsername = $casinocredentials['ServiceUsername'];

                            $casinocontroller = new CasinoController();

                            $balance = $casinocontroller->GetBalance($serviceUsername);

                            if(is_array($balance) && !empty($balance)){
                                $playablebalance = $balance['balance'];
                                $bonusbalance = $balance['bonusBalance'];
                                $compBalance = $balance['compBalance'];
                                $playthroughbal = $balance['playthroughBalance'];
                                $withdrawablebal = $playablebalance - $bonusbalance;
                                $transMsg = 'Success'; 
                                $errCode = 0;

                                $data = CommonController::getbalance($playablebalance, $bonusbalance, $compBalance, $playthroughbal, $withdrawablebal, $transMsg, $errCode);
                            }
                            else{
                                $redeemablebalance = '';
                            $bonusbalance = '';
                            $compBalance = '';
                            $playthroughbal = '';
                            $withdrawablebal = '';
                            $transMsg = 'Cant get balance'; 
                            $errCode = 8;

                                $data = CommonController::getbalance($redeemablebalance, $bonusbalance, $compBalance, $playthroughbal, $withdrawablebal, $transMsg, $errCode);
                            }

                        }
                        else{
                            $redeemablebalance = '';
                            $bonusbalance = '';
                            $compBalance = '';
                            $playthroughbal = '';
                            $withdrawablebal = '';
                            $transMsg = 'Card does not have existing casino account'; 
                            $errCode = 6;

                            $data = CommonController::getbalance($redeemablebalance, $bonusbalance, $compBalance, $playthroughbal, $withdrawablebal, $transMsg, $errCode);
                        }

                    }
                    else{
                        $redeemablebalance = '';
                        $bonusbalance = '';
                        $compBalance = '';
                        $playthroughbal = '';
                        $withdrawablebal = '';
                        $transMsg = 'Card not found'; 
                        $errCode = 5;

                        $data = CommonController::getbalance($redeemablebalance, $bonusbalance, $compBalance, $playthroughbal, $withdrawablebal, $transMsg, $errCode);
                    }
                }
                else{
                    $redeemablebalance = '';
                    $bonusbalance = '';
                    $compBalance = '';
                    $playthroughbal = '';
                    $withdrawablebal = '';
                    $transMsg = 'All fields are required'; 
                    $errCode = 4;

                    $data = CommonController::getbalance($redeemablebalance, $bonusbalance, $compBalance, $playthroughbal, $withdrawablebal, $transMsg, $errCode);
                }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.'; 
                    break;
                case 2:
                    $transMsg = 'Request time out.'; 
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.'; 
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }
        $message = "[GetBalance] Token: ".$this->_tkn.", Output: ".$data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
        
        $this->_sendResponse(200, $data);
    }
    
    
    public function actionWithdraw(){
        Yii::import('application.components.CasinoController');
        
        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));
          
        $paramval = CJSON::encode($request);
        $message = "[Withdraw] Input: ".$paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);
                
        $isconnvalid = $this->_authenticate();

        if($isconnvalid == 0){
                $serviceid = trim(trim($request['ServiceID']));
                $cardnumber = trim(trim($request['CardNumber']));
                $amount = trim(trim($request['Amount']));
                $siteid = trim(trim($request['SiteID']));
                $aid = trim(trim($request['AID']));

                if(isset($serviceid) && $serviceid !== '' && isset($cardnumber) && $cardnumber !== '' && isset($amount) && $amount !== '' && isset($siteid) && $siteid !== '' && isset($aid) && $aid !== ''){

                    $membercards = new MemberCardsModel();
                    $memberservices = new MemberServicesModel();
                    $ewallet = new EwallettransModel();
                    $terminalsessions = new TerminalSessionsModel();
                    $casinocontroller = new CasinoController();
                    $members = new MembersModel();

                    $mid = $membercards->getMID($cardnumber);

                   if(is_array($mid)){

                       $mid = $mid['MID'];
                       
                       $checkpinloginattempts = $members->checkPINLoginAttempts($mid);
                       
                       if($checkpinloginattempts['PINLoginAttemps'] >  Yii::app()->params['maxPinAttempts']){
                            $transMsg = 'Withdraw Failed, PIN is locked.'; 
                            $errCode = 13;

                            $data = CommonController::withdraw($transMsg, $errCode);
                            $this->_sendResponse(200, $data);
                            exit;
                       }

                       $terminalid = $terminalsessions->checkSessionIDwithcard($cardnumber, $serviceid);
                       if(is_array($terminalid)){
                            $terminalid = $terminalid['TerminalID'];
                       }
                       else{
                            $terminalid = null;
                       }
                       
                       $casinocredentials = $memberservices->getCasinoCredentialsAbbott($mid);

                        if(!empty($casinocredentials) || !is_null($casinocredentials)){
                            $serviceUsername = $casinocredentials['ServiceUsername'];
                            $servicePassword = $casinocredentials['ServicePassword'];

                            $balance = $casinocontroller->GetBalance($serviceUsername);
                            if(is_array($balance)){
                                $playablebalance = $balance['balance'];
                                
                                if($amount > $playablebalance){
                                    $transMsg = 'Input amount is greater than existing balance.'; 
                                    $errCode = 13;

                                    $data = CommonController::withdraw($transMsg, $errCode);
                                    $this->_sendResponse(200, $data);
                                    exit;
                                }

                                $tracking1 = $ewallet->insertEwallet($cardnumber, $siteid, $mid, $amount, $playablebalance, 'W', $serviceid, 1, 1, $aid, null, $terminalid, null, null);
                                $tracking2 = 'W';
                                $tracking3 = $siteid;
                                $tracking4 = $siteid;
                                
                                if(!$tracking1){
                                    $errCode = 11;
                                    $transMsg = 'Withdraw Failed, There was a pending transaction for this card.';

                                    $data = CommonController::withdraw($transMsg, $errCode);
                                    $this->_sendResponse(200, $data);
                                    exit;
                                }
                                
                                $resultwithdraw = $casinocontroller->Withdraw($serviceUsername, $servicePassword, 1, $amount, $tracking1, $tracking2, $tracking3, $tracking4);

                                if(is_null($resultwithdraw)){
                                    $transSearchInfo = $casinocontroller->TransactionSerachInfo($serviceUsername, $tracking1, $tracking2, $tracking3, $tracking4);

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
                                }
                                else{
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

                                if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved'){

                                    $balance = $casinocontroller->GetBalance($serviceUsername);
                                    $playablebalance = $balance['balance'];

                                $ewallet->updateEwallet($playablebalance, $transrefid, $apiresult, $aid, $transstatus, $tracking1);
                                $errCode = 0;
                                $transMsg = 'e-wallet withdraw successful';

                                $memberservices->UpdateBalances($playablebalance, "withdraw-$tracking1", $mid, $serviceid);

                                $data = CommonController::withdraw($transMsg, $errCode);

                            } else {
                                $errCode = 8;
                                $tobalance = '0.00';
                                $ewallet->updateEwallet($tobalance, null, $apiresult, $aid, $transstatus, $tracking1);

                                $transMsg = 'Withdraw Transaction Failed';

                                $data = CommonController::withdraw($transMsg, $errCode);
                            }
                            }
                            else{
                                $transMsg = $balance; 
                                $errCode = 8;

                                $data = CommonController::withdraw($transMsg, $errCode);
                            }

                        }
                        else{
                            $transMsg = 'All fields are required'; 
                            $errCode = 4;

                            $data = CommonController::withdraw($transMsg, $errCode);
                        }

                   }
                   else{
                        $transMsg = 'Card not found'; 
                        $errCode = 5;

                        $data = CommonController::withdraw($transMsg, $errCode);
                   }
                }
                else{
                    $transMsg = 'All fields are required'; 
                    $errCode = 4;

                    $data = CommonController::withdraw($transMsg, $errCode);
                }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.'; 
                    break;
                case 2:
                    $transMsg = 'Request time out.'; 
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.'; 
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }
        
        $message = "[Withdraw] Token: ".$this->_tkn.", Output: ".$data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
        
        $this->_sendResponse(200, $data);
    }
    
    public function actionGetCompPoints(){
        Yii::import('application.components.CasinoController');
        
        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));
        
        $paramval = CJSON::encode($request);
        $message = "[GetCompPoints] Input: ".$paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);
        
        $isconnvalid = $this->_authenticate();

        if($isconnvalid == 0){
                $compBalance = '';
                $cardnumber = trim(trim($request['CardNumber']));

                if(isset($cardnumber) && $cardnumber !== ''){
                    $membercards = new MemberCardsModel();
                    $memberservices = new MemberServicesModel();

                    $mid = $membercards->getMID($cardnumber);
                    if($mid){
                        $mid = $mid['MID'];

                        $casinocredentials = $memberservices->getCasinoCredentialsAbbott($mid);

                        if(!empty($casinocredentials) || !is_null($casinocredentials)){
                            $serviceUsername = $casinocredentials['ServiceUsername'];

                            $casinocontroller = new CasinoController();

                            $balance = $casinocontroller->GetBalance($serviceUsername);

                            if(is_array($balance) && !empty($balance)){    
                                $compBalance = $balance['compBalance'];
                                $transMsg = 'Success'; 
                                $errCode = 0;

                                $data = CommonController::getcomppoints($transMsg, $errCode, $compBalance); 
                            }
                            else{      
                                $transMsg = 'Cant get balance'; 
                                $errCode = 8;

                                $data = CommonController::getcomppoints($transMsg, $errCode, $compBalance);
                            }
                        }
                        else {
                            $transMsg = 'Card does not have existing casino account'; 
                            $errCode = 6;

                            $data = CommonController::getcomppoints($transMsg, $errCode, $compBalance);
                        }
                    }
                    else{
                        $transMsg = 'Card not found'; 
                        $errCode = 5;

                        $data = CommonController::getcomppoints($transMsg, $errCode, $compBalance);
                    }
                }
                else{
                    $transMsg = 'All fields are required'; 
                    $errCode = 4;

                    $data = CommonController::getcomppoints($transMsg, $errCode, $compBalance);
                }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.'; 
                    break;
                case 2:
                    $transMsg = 'Request time out.'; 
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.'; 
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }
        
        $message = "[GetCompPoints] Token: ".$this->_tkn.", Output: ".$data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
        
        $this->_sendResponse(200, $data);
    }
    
    public function actionAddCompPoints(){
        Yii::import('application.components.CasinoController');
        
        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));
        
        $paramval = CJSON::encode($request);
        $message = "[AddCompPoints] Input: ".$paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);
        
        $isconnvalid = $this->_authenticate();

        if($isconnvalid == 0){
                $cardnumber = trim(trim($request['CardNumber']));
                $amount = trim(trim($request['Amount']));
                $siteID = trim(trim($request['SiteID']));
                $serviceID = trim(trim($request['ServiceID']));


                if(isset($cardnumber) && $cardnumber !== '' && isset($amount) && $amount !== '' && isset($siteID) && $siteID !== '' && isset($serviceID) && $serviceID !== ''){
                    $membercards = new MemberCardsModel();
                    $memberservices = new MemberServicesModel();
                    $comppointslogs = new CompPointsLogsModel();
                    $terminalsessions = new TerminalSessionsModel();
                    $services = new ServicesModel();
                    
                    empty($siteID) ? $siteID = NULL:$siteID=$siteID;

                    $mid = $membercards->getMID($cardnumber);
                    if($mid){
                            $mid = $mid['MID'];
                            
                            $userMode = $services->getUserMode($serviceID);
                            //check if terminal-based (UserMode = 0)
                            if($userMode['UserMode'] == 0) {
                            
                                $terminalID = $terminalsessions->checkSessionIDwithcard($cardnumber, $serviceID);
                                $terminalID = $terminalID['TerminalID'];

                                $casinocredentials = $memberservices->getCasinoCredentialsAbbott($mid);

                                if(!empty($casinocredentials) || !is_null($casinocredentials)){
                                    $serviceUsername = $casinocredentials['ServiceUsername'];

                                    $casinocontroller = new CasinoController();

                                    $result = $casinocontroller->AddToCurrentBalance($serviceUsername, $amount);

                                    if(is_array($result) && !empty($result)){    
                                        $resultMsg = $result['success'];

                                        if($resultMsg == 1) {
                                            $transMsg = 'Success'; 
                                            $errCode = 0;
                                            $comppointslogs->logEvent($mid, $cardnumber, $terminalID, $siteID, $serviceID, $amount, 'D');
                                        }
                                        else {
                                            $transMsg = 'Failure'; 
                                            $errCode = 8;
                                        }

                                        $data = CommonController::addcomppoints($transMsg, $errCode); 
                                    }
                                    else{      
                                        $transMsg = 'Failed to process comp points'; 
                                        $errCode = 8;

                                        $data = CommonController::addcomppoints($transMsg, $errCode);
                                    }
                                }
                                else {
                                    $transMsg = 'Card does not have existing casino account'; 
                                    $errCode = 6;

                                    $data = CommonController::addcomppoints($transMsg, $errCode);
                                }
                            }
                            else {
                                $transMsg = 'Invalid User Mode'; 
                                $errCode = 12;
                                
                                $data = CommonController::addcomppoints($transMsg, $errCode); 
                            }
                    }
                    else{
                        $transMsg = 'Card not found'; 
                        $errCode = 5;

                        $data = CommonController::addcomppoints($transMsg, $errCode);
                    }     
                }
                else{
                    $transMsg = 'All fields are required'; 
                    $errCode = 4;

                    $data = CommonController::addcomppoints($transMsg, $errCode);
                }   
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.'; 
                    break;
                case 2:
                    $transMsg = 'Request time out.'; 
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.'; 
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }
        
        $message = "[AddCompPoints] Token: ".$this->_tkn.", Output: ".$data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
        
        $this->_sendResponse(200, $data);
    }
    
    public function actionDeductCompPoints(){
        Yii::import('application.components.CasinoController');
        
        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));
        
        $paramval = CJSON::encode($request);
        $message = "[DeductCompPoints] Input: ".$paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);
        
        $isconnvalid = $this->_authenticate();

        if($isconnvalid == 0){
            $cardnumber = trim(trim($request['CardNumber']));
            $amount = trim(trim($request['Amount']));
            $siteid = trim(trim($request['SiteID']));

            if(isset($cardnumber) && $cardnumber !== '' && isset($amount) && $amount !== ''){
                empty($siteid) ? $siteid = NULL:$siteid=$siteid;
                $membercards = new MemberCardsModel();
                $memberservices = new MemberServicesModel();
                $comppointslogs = new CompPointsLogsModel();
                
                $mid = $membercards->getMID($cardnumber);
                if($mid){
                    $mid = $mid['MID'];
                    $casinocredentials = $memberservices->getCasinoCredentialsAbbott($mid);
                    if(!empty($casinocredentials) || !is_null($casinocredentials)){
                        $serviceusername = $casinocredentials['ServiceUsername'];
                        
                        $casinocontroller = new CasinoController();
                        $result = $casinocontroller->GetBalance($serviceusername);

                        if((float)$result['compBalance'] > 0 && (float)$result['compBalance'] >= $amount){
                            $result = $casinocontroller->DeductToCurrentBalance($serviceusername, $amount);
                            
                            if(is_array($result) &&  !empty($result)){
                                $resultMsg = $result['success'];

                                    if($resultMsg == 1) {
                                        $transMsg = 'Success'; 
                                        $errCode = 0;
                                        $terminalid = NULL;
                                        $serviceid = NULL;
                                        $comppointslogs->logEvent($mid, $cardnumber, $terminalid, $siteid, $serviceid, $amount, 'W');
                                    } else {
                                        $transMsg = 'Failed to process comp points.'; 
                                        $errCode = 8;
                                    }

                                    $data = CommonController::deductcomppoints($transMsg, $errCode); 
                            }
                        } else {
                            $transMsg = 'Account does not have sufficient comp points.'; 
                            $errCode = 13;

                            $data = CommonController::deductcomppoints($transMsg, $errCode);
                        }
                    } else {
                        $transMsg = 'Card does not have existing casino account'; 
                        $errCode = 6;

                        $data = CommonController::deductcomppoints($transMsg, $errCode);
                    }   
                } else {
                    $transMsg = 'Card not found'; 
                    $errCode = 5;

                    $data = CommonController::deductcomppoints($transMsg, $errCode);
                }   
            } else {
                $transMsg = 'Incomplete data'; 
                $errCode = 4;

                $data = CommonController::deductcomppoints($transMsg, $errCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.'; 
                    break;
                case 2:
                    $transMsg = 'Request time out.'; 
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.'; 
                    break;
            }
            $errCode = 1;
            $data = CommonController::authenticate($transMsg, $errCode);
        }
        
        $message = "[DeductCompPoints] Token: ".$this->_tkn.", Output: ".$data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
        
        $this->_sendResponse(200, $data);
    }
    
     public function actionCheckpin()
    {
        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));
        
        $paramval = CJSON::encode($request);
        $message = "[CheckPin] Input: ".$paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);
        
        $isconnvalid = $this->_authenticate();

        if($isconnvalid == 0){
                $cardnumber = trim(trim($request['CardNumber']));
                $pin = trim(trim($request['PIN']));
                $membercards = new MemberCardsModel();
                $status = $membercards->getCardStatus($cardnumber);
                //Check if Card is Active
               
                    if(isset($cardnumber) && $cardnumber != "" && isset($pin) && $pin != "")
                    {
                        // Check IF PIN is Numeric and not more than 6 digits
                        if(is_numeric($pin) && strlen($pin) <= 6)
                        {
                            
                            switch ($status['Status'])
                            {
                                case 0:
                                    $transMsg = 'Card is Invalid.'; 
                                    $errCode = 4;
                                    $data = CommonController::checkPin($transMsg, $errCode);
                                    break;
                                
                                case 1:
                                    // Get MID by Card Number
                                    $MID = $membercards->getMID($cardnumber);
                                    // Get PIN by MID
                                    $members = new MembersModel();
                                    $memberpin = $members->getPIN2($MID['MID']);
                                    
                                    $pinlogattempts = $members->checkPINLoginAttempts($MID['MID']);
                                    
                                    if((int)$pinlogattempts['PINLoginAttemps'] >=  Yii::app()->params['maxPinAttempts']){
                                        $transMsg = 'PIN is locked.'; 
                                        $errCode = 25;
                                        $data = CommonController::checkPin($transMsg, $errCode);  
                                        $members->incrementLoginAttempts($MID['MID']);
                                    }else{
                                        if(sha1($pin) == $memberpin['PIN'])
                                        {
                                            $transMsg = 'Transaction successful. PIN and UB Card is valid.'; 
                                            $errCode = 0;
                                            $data = CommonController::checkPin($transMsg, $errCode);
                                        }
                                        else
                                        {
                                            $members->incrementLoginAttempts($MID['MID']);
                                            $transMsg = 'Mismatch Card Number and PIN Code'; 
                                            $errCode = 14;
                                            $data = CommonController::checkPin($transMsg, $errCode);
                                        }
                                    }
                                    break;
                                    
                                case 2:
                                    $transMsg = 'Cheking of PIN is not allowed for Deactivated Card.'; 
                                    $errCode = 4;
                                    $data = CommonController::checkPin($transMsg, $errCode);
                                    break;
                                
                                case 5:
                                    $transMsg = 'Cheking of PIN is not allowed for Active Temporary Card.'; 
                                    $errCode = 4;
                                    $data = CommonController::checkPin($transMsg, $errCode);
                                    break;
                                
                                case 7:
                                    $transMsg = 'Cheking of PIN is not allowed for New Migrated Card.'; 
                                    $errCode = 4;
                                    $data = CommonController::checkPin($transMsg, $errCode);
                                    break;
                                
                                case 8:
                                    $transMsg = 'Cheking of PIN is not allowed for Temporary Migrated Card.'; 
                                    $errCode = 4;
                                    $data = CommonController::checkPin($transMsg, $errCode);
                                    break;
                                
                                case 9:
                                    $transMsg = 'Cheking of PIN is not allowed for Banned Card.'; 
                                    $errCode = 4;
                                    $data = CommonController::checkPin($transMsg, $errCode);
                                    break;
                                
                                default:
                                    $transMsg = 'Invalid Card.'; 
                                    $errCode = 4;
                                    $data = CommonController::checkPin($transMsg, $errCode);
                            }
                           
                           
                        }
                        else
                        {
                            $transMsg = 'PIN must be numeric and not greater than 6 digits.'; 
                            $errCode = 9;
                            $data = CommonController::checkPin($transMsg, $errCode);
                        }

                    }
                    else
                    {
                        $transMsg = 'All fields are required.'; 
                        $errCode = 4;
                        $data = CommonController::checkPin($transMsg, $errCode);
                    }
                
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.'; 
                    break;
                case 2:
                    $transMsg = 'Request time out.'; 
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.'; 
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }
        
        $message = "[CheckPin] Token: ".$this->_tkn.", Output: ".$data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
        
        $this->_sendResponse(200, $data);
    }
    
    public function actionChangepin()
    {
        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));
        
        $data = '';
        $paramval = CJSON::encode($request);
        $message = "[ChangePin] Input: ".$paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);

        $isconnvalid = $this->_authenticate();
         
        if($isconnvalid == 0){
                $actionCode = trim(trim($request['ActionCode']));
                $cardNumber = trim(trim($request['CardNumber']));
                
                
                if(isset($actionCode) && $actionCode != "")
                {
                    if(isset($cardNumber) && $cardNumber != "")
                    {
                        // Get MID by Card Number
                        $membercards = new MemberCardsModel();
                        $MID = $membercards->getMID($cardNumber);
                        // Get PIN by MID
                        $members = new MembersModel();
                        $status = $membercards->getCardStatus($cardNumber);
                        // Check if Card is Active
                        
                        switch ($status['Status'])
                        {
                            case 0:
                                $transMsg = 'Card is Invalid.'; 
                                $errCode = 4;
                                $data = CommonController::changePin($transMsg, $errCode);
                                break;

                            case 1:
                                // Reset PIN
                                if($actionCode == 0)
                                {
                                        if($MID['MID'] == "" || $MID['MID'] > 0)
                                        {
                                            $default = "1234";
                                            $reset = $members->updatePINReset($MID['MID'], $default);
                                            if($reset == 1)
                                            {
                                                //Pin reset success
                                                $transMsg = 'PIN reset successful.'; 
                                                $errCode = 0;
                                                $data =  CommonController::changePin($transMsg, $errCode);
                                            }
                                            else
                                            {
                                                // Pin reset failed
                                                $transMsg = 'PIN reset failed.'; 
                                                $errCode = 15;
                                                $data = CommonController::changePin($transMsg, $errCode);
                                            }
                                        }
                                        else
                                        {
                                            // Pin reset failed
                                            $transMsg = 'Invalid Card Number.'; 
                                            $errCode = 4;
                                            $data = CommonController::changePin($transMsg, $errCode);
                                        }
                                }
                                else
                                {
                                    // Change PIN
                                    $currentPin = trim(trim($request['CurrentPin']));
                                    $newPin = trim(trim($request['NewPin']));
                                    if(isset($currentPin) && $currentPin != "" && isset($newPin) && $newPin != "")
                                    {
                                        if(is_numeric($currentPin) && strlen($currentPin) <= 6 && is_numeric($newPin) && strlen($newPin) <= 6)
                                        {
                                            // Change PIN
                                            // Validate current PIN
                                            $memberpin = $members->getPIN2($MID['MID']);
                                            if(sha1($currentPin) == $memberpin["PIN"])
                                            {
                                                // Check if New PIN and Current PIN is the same
                                                if(sha1($currentPin) == sha1($newPin))
                                                {
                                                   $transMsg = 'New PIN and Current PIN is the same.'; 
                                                   $errCode = 16;
                                                   $data = CommonController::changePin($transMsg, $errCode); 
                                                }
                                                else
                                                {
                                                    // Change PIN
                                                    $changePIN = $members->updatePIN($MID['MID'], $newPin);
                                                    if($changePIN == 1)
                                                    {
                                                        $transMsg = 'PIN successfully changed.'; 
                                                        $errCode = 0;
                                                        $data = CommonController::changePin($transMsg, $errCode); 
                                                    }
                                                    else
                                                    {
                                                        $transMsg = 'Change PIN failed.'; 
                                                        $errCode = 15;
                                                        $data = CommonController::changePin($transMsg, $errCode); 
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                $transMsg = 'Mismatch Card Number and PIN Code'; 
                                                $errCode = 14;
                                                $data = CommonController::changePin($transMsg, $errCode);
                                            }
                                        }
                                        else
                                        {
                                            $transMsg = 'PIN must be numeric and not greater than 6 digits.'; 
                                            $errCode = 9;
                                            $data = CommonController::changePin($transMsg, $errCode);
                                        }
                                    }
                                    else
                                    {
                                        $transMsg = 'Please enter Current PIN and New PIN.'; 
                                        $errCode = 4;
                                        $data = CommonController::changePin($transMsg, $errCode);
                                    }
                                }
                                break;

                            case 2:
                                $transMsg = 'Cheking of PIN is not allowed for Deactivated Card.'; 
                                $errCode = 4;
                                $data = CommonController::changePin($transMsg, $errCode);
                                break;

                            case 5:
                                $transMsg = 'Cheking of PIN is not allowed for Active Temporary Card.'; 
                                $errCode = 4;
                                $data = CommonController::changePin($transMsg, $errCode);
                                break;

                            case 7:
                                $transMsg = 'Cheking of PIN is not allowed for New Migrated Card.'; 
                                $errCode = 4;
                                $data = CommonController::changePin($transMsg, $errCode);
                                break;

                            case 8:
                                $transMsg = 'Cheking of PIN is not allowed for Temporary Migrated Card.'; 
                                $errCode = 4;
                                $data = CommonController::changePin($transMsg, $errCode);
                                break;

                            case 9:
                                $transMsg = 'Cheking of PIN is not allowed for Banned Card.'; 
                                $errCode = 4;
                                $data = CommonController::changePin($transMsg, $errCode);
                                break;

                            default:
                                $transMsg = 'Invalid Card.'; 
                                $errCode = 4;
                                $data = CommonController::changePin($transMsg, $errCode);
                        }
                    }
                    else
                    {
                        $transMsg = 'Please enter Cardnumber.'; 
                        $errCode = 4;
                        $data = CommonController::changePin($transMsg, $errCode);
                    }
                    
                    
                }
                else{
                    $transMsg = 'All fields are required.'; 
                    $errCode = 4;
                    $data = CommonController::changePin($transMsg, $errCode);
                }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.'; 
                    break;
                case 2:
                    $transMsg = 'Request time out.'; 
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.'; 
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }
        
        $message = "[ChangePin] Token: ".$this->_tkn.", Output: ".$data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
        $this->_sendResponse(200, $data);
        
    }
    
    
    private function _readJsonRequest() {

        //read the post input (use this technique if you have no post variable name):
        $post = file_get_contents("php://input");

        //decode json post input as php array:
        $data = CJSON::decode($post, true);

        return $data;
    }
    
    /**
     *
     * @param type $status
     * @param string $body
     * @param type $content_type 
     * @link http://www.yiiframework.com/wiki/175/how-to-create-a-rest-api
     */
    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html') {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);
       
        // and the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if ($body != '') {
            // send the body
            echo $body;
        }
        // we need to create the body if none is passed
        else {
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch ($status) {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            // servers don't always have a signature turned on 
            // (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templated in a real-world solution
            $body = '
                    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
                    <html>
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                        <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
                    </head>
                    <body>
                        <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
                        <p>' . $message . '</p>
                        <hr />
                        <address>' . $signature . '</address>
                    </body>
                    </html>';

            echo $body;
        }
        Yii::app()->end();
    }

    /**
     * HTTP Status Code Message
     * @param string $status
     * @return bool
     */
    private function _getStatusCodeMessage($status) {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            200 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }
    
    
    /*
     * @author Jeremiah D. Lachica
     * @date January 29, 2015
     * @param string TerminalCode
     * @param int ServiceID
     * @param string CardNumber
     * @return JSON
     */
    
    
    public function actionUnlock(){
        $data = $this->_readJsonRequest();
        
        $transactionMessage = array(
            null=>'',
            0=>'Transaction successful',
            4=>'All fields are required',
            5=>'Card not found',
            6=>'Card does not have existing casino account',
            8=>'Can\'t get balance',
            12=>'Invalid User Mode',
            17=>'Card/Terminal has an existing active session',
            18=>'Failed to start session', 
            21=>'Terminal has no active session',
            22=>'Cannot retrieve VIP Level',
        );
        
        $eCode = null;
        $transMessage = null;
        
        $paramval = CJSON::encode($data);
        $message = "[Unlock] Input: ".$paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);
        
        $systemUsername = trim($data['SystemUsername']);
        $this->_un = trim($systemUsername);
        $this->_dt = trim(trim($data['AccessDate']));
        $this->_tkn = trim(trim($data['Token']));

        $isconnvalid = $this->_authenticate();

        if($isconnvalid == 0){
       
            if(isset($data['TerminalCode']) && isset($data['ServiceID'])
                && isset($data['CardNumber']) && isset($data['SystemUsername'])
                && isset($data['AccessDate']) && isset($data['AccessDate'])){
            
                $terminalCode = trim($data['TerminalCode']);
                $serviceID = trim($data['ServiceID']);
                $cardNumber = trim($data['CardNumber']);
            
                $validate = new BatchDataValidationHelper();
                $terminalsModel = new TerminalsModel();
                $terminalSessionsModel = new TerminalSessionsModel();
                $servicesModel = new ServicesModel();
                $memberCardsModel = new MemberCardsModel();
                $memberServicesModel = new MemberServicesModel();
                $eWalletModel = new CommonEWalletTransactionsModel();
                $membersModel = new MembersModel();
                
                
                if($validate->isAllNotEmpty(array($terminalCode, $serviceID, $cardNumber, $systemUsername))){
                    if(ctype_alnum($cardNumber)){
                        $mid = Utilities::fetchFirstValue($memberCardsModel->getMID($cardNumber));
                        if($mid){
                            $isVIP = Utilities::fetchFirstValue($membersModel->getIsVIPByMID($mid));
                            if($isVIP!==false){
                                if(ctype_alnum($terminalCode)){
                                    
                                    $terminalCode .= $isVIP==1?'VIP':'';
                                    $terminalID = Utilities::fetchFirstValue($terminalsModel->getTerminalID($terminalCode));
                                    if($terminalID){
                                        if(is_numeric($serviceID)){

                                            $siteID = Utilities::fetchFirstValue($terminalsModel->getSiteID($terminalID));
                                            if($siteID){
                                                
                                                if(!Utilities::fetchFirstValue($terminalSessionsModel->isTerminalHasActiveUBSession($terminalID))){

                                                    $userMode = Utilities::fetchFirstValue($servicesModel->getUserMode($serviceID));
                                                    if($userMode){
                                                        if(!Utilities::fetchFirstValue($terminalSessionsModel->isCardHasActiveUBSession($cardNumber))){

                                                            $credentials = $memberServicesModel->getCasinoCredentialsCostelloAbbott($mid);
                                                            if( isset($credentials['ServiceUsername'])
                                                                && isset($credentials['ServicePassword'])
                                                                && isset($credentials['HashedServicePassword'])){

                                                                $serviceUsername = $credentials['ServiceUsername'];
                                                                $servicePassword = $credentials['ServicePassword'];
                                                                $hashedServicePassword = $credentials['HashedServicePassword'];

                                                                $balance = $this->retrieveBalance($serviceUsername);


                                                                if($balance){

                                                                    $transactionReferenceID = Utilities::generateUDate('YmdHisu');
                                                                    $amount = '0';
                                                                    $transactionType='D';

                                                                    $trackingID = '';
                                                                    $voucherCode='';
                                                                    $paymentType=1;
                                                                    $serviceTransactionID='';
                                                                    $deposit='0';
                                                                    
                                                                    $accountsModel = new AccountsModel();
                                                                    $AID = Utilities::fetchFirstValue($accountsModel->getAIDBySiteID($siteID));
                                                                    if($AID){

                                                                        $transactionResult = $eWalletModel->insert($mid, $terminalID, $serviceID, $cardNumber, $userMode, $serviceUsername, $servicePassword, $hashedServicePassword, $transactionReferenceID, $amount, $transactionType, $siteID, $trackingID, $voucherCode, $paymentType, $serviceTransactionID, $deposit, $AID, $balance);

                                                                        if($transactionResult){
                                                                            $eCode=0;
                                                                        }else{
                                                                            $eCode=18;//Failed to start session
                                                                        }
                                                                    }else{
                                                                        $eCode=4;
                                                                    }

                                                                }else{
                                                                    $eCode=8;//Can't get balance
                                                                }
                                                            }else{
                                                                $eCode=6;//Card does not have existing casino account
                                                            }
                                                        }else{
                                                            $eCode=17;//Card has an existing active session
                                                        }
                                                    }else{
                                                        $eCode=12;//Invalid Usercode
                                                    }
                                                }else{
                                                    $eCode=17;// Card/Terminal has an existing active session
                                                }
                                            }else{
                                                $eCode=4;//Incomplete Data: No SiteID
                                            }   

                                        }else{
                                            $eCode = 4;//Invalid data
                                        }
                                    }else{
                                        $eCode=22; //Invalid data
                                    }
                                }else{
                                    $eCode = 4;//Invalid data
                                }
                            }else{
                                $eCode=22;
                            }
                        }else{
                            $eCode=5;//Card not found
                        }
                    }else{
                        $eCode=4;//Invalid data
                    }
                }else{
                      $eCode = 4;//Incomplete data
                }
                $transMessage = $transactionMessage[$eCode];
                $data = CommonController::unlock($transMessage, $eCode);

            }else{
                $eCode = 4; //Invalid data
                $transMessage = $transactionMessage[$eCode];
                $data = CommonController::unlock($transMessage, $eCode);
            }
        
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.'; 
                    break;
                case 2:
                    $transMsg = 'Request time out.'; 
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.'; 
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }
        
        $message = "[Unlock] Token: ".$this->_tkn.", Output: ".$data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
        
        $this->_sendResponse(200, $data);
    }
    
   
       
    
    /*
     * @author Jeremiah D. Lachica
     * @date February 02, 2015
     * @param string TerminalCode
     * @param int ServiceID
     * @param string CardNumber
     * @return JSON
     */
    public function actionForceLogout(){
        $data = $this->_readJsonRequest();
        
        $transactionMessage = array(
            null=>'',
            0=>'Transaction successful (all transaction) / valid',
            4=>'Incomplete data / Invalid data',
            23=>'Card does not have an existing session',
            8=>'Can\'t get balance',
            19=>'Failed to end session',
            22=>'Player must be an ewallet account.'
        );
        
        $eCode = null;
        $transMessage = null;
        
        $paramval = CJSON::encode($data);
        $message = "[ForceLogout] Input: ".$paramval;
        CLoggerModified::log($message, CLoggerModified::REQUEST);
        
        $systemUsername = trim($data['SystemUsername']);
        $this->_un = trim($systemUsername);
        $this->_dt = trim(trim($data['AccessDate']));
        $this->_tkn = trim(trim($data['Token']));


        $isconnvalid = $this->_authenticate();

        if($isconnvalid == 0){
        
            if(isset($data['Login']) && isset($data['SystemUsername'])
                && isset($data['AccessDate']) && isset($data['AccessDate'])){

                $serviceUsername = trim($data['Login']);
                
                $validate = new BatchDataValidationHelper();
                $terminalsModel = new TerminalsModel();
                $terminalSessionsModel = new TerminalSessionsModel();
               
                $eWalletModel = new CommonEWalletTransactionsModel();
                $casinocontroller = new CasinoController();
                $accountsModel = new AccountsModel();
                $membersModel = new MembersModel();
                
                if(!$validate->isNullOrEmpty($serviceUsername)){
                    
                    if(ctype_alnum($serviceUsername)){
                        $casinoDetails = $terminalSessionsModel->getCasinoDetailsByUBServiceLogin($serviceUsername);
                        if(!empty($casinoDetails)
                        && isset($casinoDetails['TerminalID'])
                        && isset($casinoDetails['ServiceID'])
                        && isset($casinoDetails['LoyaltyCardNumber'])
                        && isset($casinoDetails['MID'])
                        && isset($casinoDetails['TransactionSummaryID'])
                        && isset($casinoDetails['UserMode'])){
                            
                            $terminalID = $casinoDetails['TerminalID'];
                            $siteID = Utilities::fetchFirstValue($terminalsModel->getSiteID($terminalID));
                            
                            if($siteID){
                                $AID = Utilities::fetchFirstValue($accountsModel->getAIDBySiteID($siteID));
                                if($AID){
                                    
                                    $balances = $casinocontroller->GetBalance($serviceUsername);

                                    if(is_array($balances) && !empty($balances)){
                                        $balance = $balances['balance'];
                                        $serviceID = $casinoDetails['ServiceID'];
                                        $cardNumber = $casinoDetails['LoyaltyCardNumber'];
                                        $mid = $casinoDetails['MID'];
                                        $transactionSummaryID = $casinoDetails['TransactionSummaryID'];

                                        $transactionReferenceID = Utilities::generateUDate('YmdHisu');

                                        $transactionType='W';

                                        $trackingID = '';
                                        $voucherCode='';
                                        $paymentType=1;
                                        $serviceTransactionID='';
                                        $withdrawal=0;
                                        $amount = 0;
                                        $userMode = $casinoDetails['UserMode'];

                                        $isWallet = Utilities::fetchFirstValue($membersModel->getIsWalletByMID($mid));
                                        if($isWallet==1){
                                            $transactionResult = $eWalletModel->forceLogout($mid, $terminalID, $serviceID, $cardNumber, $userMode, $transactionReferenceID, $amount, $transactionType, $siteID, $trackingID, $voucherCode, $paymentType, $serviceTransactionID, $AID, $transactionSummaryID, $withdrawal, $balance);

                                            if($transactionResult){
                                                $eCode=0;
                                                $casinocontroller->logout($serviceUsername);
                                            }else{
                                                $eCode=19;//Failed to end session
                                            }
                                        }else{
                                            $eCode = 22;//Player must be an ewallet account.
                                        }
                                    }else{
                                        $eCode = 8;// Can't get balance.
                                    }
                                }else{
                                    $eCode = 4;//Invalid data
                                }
                            }else{
                                $eCode = 4;//Invalid data
                            }
                        }else{
                            $eCode=23;//Card does not have existing casino account
                        }
                    }else{
                        $eCode=4;//Invalid data
                    }
                }else{
                    $eCode = 4; //Invalid data
                }
                
                $transMessage = $transactionMessage[$eCode];
                $data = CommonController::forceLogout($transMessage, $eCode);
            }else{
                $eCode = 4; //Incomplete data
                $transMessage = $transactionMessage[$eCode];
                $data = CommonController::forceLogout($transMessage, $eCode);
            }
        
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.'; 
                    break;
                case 2:
                    $transMsg = 'Request time out.'; 
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.'; 
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }
        
        $message = "[ForceLogout] Token: ".$this->_tkn.", Output: ".$data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
        
        $this->_sendResponse(200, $data);
    }
    
    private function retrieveBalance($serviceUsername){
        $casinocontroller = new CasinoController();
        $balance = null;
        $numberOfAttempts = 3;
        $attempts=0;

        while($attempts<$numberOfAttempts){
            $balances = $casinocontroller->GetBalance($serviceUsername); 
            if(is_array($balances) && !empty($balances)){
                $balance = $balances['balance'];
                $attempts = 5;
            }else{
                $attempts++;
            }
        }
        return $balance;
    }
    
    public function actionUpdateTerminalState(){
        $request = $this->_readJsonRequest();
        $this->_un = trim(trim($request['SystemUsername']));
        $this->_dt = trim(trim($request['AccessDate']));
        $this->_tkn = trim(trim($request['Token']));
        
        $params = preg_replace('/\s+/', ' ', print_r($request,true));
        $message = "[UpdateTerminalState] Input: ".$params;
        CLoggerModified::log($message, CLoggerModified::REQUEST);
        
        $isconnvalid = $this->_authenticate();

        if($isconnvalid == 0){
            $terminals = new TerminalsModel();
            $terminalsessions = new TerminalSessionsModel();
            $terminalname = trim(trim($request['TerminalName']));
            $serviceid = trim(trim($request['ServiceID']));
            $cardnumber = trim(trim($request['CardNumber']));
            
            if(!empty($terminalname) && !empty($serviceid) && !empty($cardnumber)){
                $terminalid = Utilities::fetchFirstValue($terminals->getTerminalID($terminalname));
                
                if(!empty($terminalid)){
                    $isvalid = Utilities::fetchFirstValue($terminalsessions->checkSessionValidity($terminalid, $serviceid, $cardnumber));

                    if($isvalid){
                        $result = $terminalsessions->updateTerminalState($terminalid, $serviceid, $cardnumber);
                        if($result){
                            $eCode = 0; 
                            $transMessage = "Terminal has been successfully locked.";
                            $data = CommonController::updateterminalstate($transMessage, $eCode);
                        } else {
                            $eCode = 20; 
                            $transMessage = "Failed to lock terminal.";
                            $data = CommonController::updateterminalstate($transMessage, $eCode);
                        }
                    } else {
                        $eCode = 21; 
                        $transMessage = "Failed to lock. Terminal has no valid session.";
                        $data = CommonController::updateterminalstate($transMessage, $eCode);
                    }
                } else {
                    $eCode = 4; 
                    $transMessage = "Invalid terminal name.";
                    $data = CommonController::updateterminalstate($transMessage, $eCode);
                }
            } else {
                $eCode = 4; 
                $transMessage = "All fields are required.";
                $data = CommonController::updateterminalstate($transMessage, $eCode);
            }
        } else {
            switch ($isconnvalid) {
                case 1:
                    $transMsg = 'Unauthorized Access! System does not have access right.'; 
                    break;
                case 2:
                    $transMsg = 'Request time out.'; 
                    break;
                case 3:
                    $transMsg = 'Incomplete/Invalid Request Data.'; 
                    break;
            }
            $errCode = $isconnvalid;
            $data = CommonController::authenticate($transMsg, $errCode);
        }
        
        $message = "[UpdateTerminalState] Token: ".$this->_tkn.", Output: ".$data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
        
        $this->_sendResponse(200, $data);
    }
    
}
?>