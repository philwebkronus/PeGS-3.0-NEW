<?php
Yii::import('application.components.CasinoApi');

/**
 * Description: Minimum and Maximum denomination will depend if initial deposit or reload.
 *  This class will act as enum
 */
class DENOMINATION_TYPE {
    const INITIAL_DEPOSIT = 1;
    const RELOAD = 2;
}

/**
 * Common methods used for EGM
 * @date 10/12/12
 * @author elperez
 */
class CommonController {
    
    CONST PARAM_MIN_TICKET_TO_PRINT_AMT = 14;
    
    /**
    * @var int site id
    */
    protected $site_id = null;
    
    /**
     * @var int account id
     */
    protected $acc_id = null;
    
    /**
     * @var int status 
     */
    protected $status = 0; //pending
    
    /**
     * Withdraw Transaction
     * This was integrated with GenerateVoucher API
     * @param string $token
     * @param int $terminalID
     * @param string $trackingId
     * @return array
     */
    public function withrawTrans($token, $terminalID, $trackingId){
        Yii::import('application.components.CommonRedeem');
        Yii::import('application.components.Loyalty');
        Yii::import('application.components.VoucherManagement');
        
        $commonRedeem = new CommonRedeem();
        $loyalty = new Loyalty();
        $voucherMgmt = new VoucherManagement();
        
        $terminalSessionsModel = new TerminalSessionsModel();
        $sitesModel = new SitesModel();
        $gamingMachineModel = new GamingMachineModel();
        $gamingRequestLogsModel = new GamingRequestLogs();
        
        $bcf = $this->getSiteBalance();
        
        $serviceID = $terminalSessionsModel->getServiceId($terminalID);
        
        //Check if terminal session has already been ended
        if($serviceID)
        {
            $amount = $terminalSessionsModel->getCurrentBalance($terminalID, $serviceID);
            
            if(!isset($amount)){
                $message = 'Session has been ended';
                Utilities::log($message);
                return array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
                             'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                             'TransMessage'=>$message,'ErrorCode'=>16,'DateExpiry'=>'');
            }
            
            $rgaming = $gamingMachineModel->getMachineDetails($terminalID);

            $this->site_id = $rgaming['POSAccountNo'];
            $this->acc_id = $rgaming['CreatedByAID'];

            $trans_id = $gamingRequestLogsModel->insertGamingRequestLogs($trackingId, 
                                $amount, $this->status, 'W', $this->site_id, $terminalID,
                                $serviceID);

            //check if tracking id is unique
            if($trans_id){

                $result = $commonRedeem->redeem($terminalID, $this->site_id, $bcf, 
                                                $serviceID, $amount,  $this->acc_id);

                $pos_account_no = $sitesModel->getPosAccountNo($this->site_id);

                //check if successfull reloaded in casino
                if(isset($result['trans_summary_id']))
                {
                    $this->status = $result['transStatus'];
                    
                    $loyalty->loyaltyWithdraw($result['trans_summary_id'], $result['udate'], $result['amount'], 
                        $pos_account_no, Yii::app()->params['loyalty_service'], $result['terminal_login'], true);

                    $vmsGenerate = $voucherMgmt->generateVoucher($trackingId, $terminalID, $result['amount'], $this->acc_id, 
                                                                Yii::app()->params['vms_source'], $token);

                    //verify if vms API has no error/reachable
                    if(!is_string($vmsGenerate)){
                        
                        //check if voucher was successfully generated
                        if(isset($vmsGenerate['GenerateVoucher']['VoucherCode']) && $vmsGenerate['GenerateVoucher']['VoucherCode'] != ''){

                            $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, $result['udate'], 
                                                                    $vmsGenerate['GenerateVoucher']['VoucherCode'], 
                                                                    $vmsGenerate['GenerateVoucher']['DateExpiry']);

                            return array('TransStatus'=>1,'TransAmount'=>Utilities::toDecimal($amount),
                                         'TransDate'=>$result['TransactionDate'],'TrackingID'=>$trackingId,
                                         'TransID'=>$vmsGenerate['GenerateVoucher']['VoucherCode'],
                                         'TransMessage'=>'Transaction Successfull','ErrorCode'=>0,
                                         'DateExpiry'=>$vmsGenerate['GenerateVoucher']['DateExpiry']);
                        } else {
                            
                            $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, $result['trans_ref_id'], 
                                                                    $voucherCode = null);

                            $vmsVerify = $voucherMgmt->verifyVoucher($vouchercode = '', $this->acc_id, 
                                                                    $token, Yii::app()->params['vms_source'], 
                                                                    $trackingId);

                            //Verify if voucher did successfull generated
                            if(isset($vmsVerify['VerifyVoucher']['VoucherCode']) && $vmsVerify['VerifyVoucher']['VoucherCode'] != ''){
                               return array('TransStatus'=>1,'TransAmount'=>Utilities::toDecimal($amount),
                                            'TransDate'=>$result['TransactionDate'],'TrackingID'=>$trackingId,
                                            'TransID'=>$vmsVerify['VerifyVoucher']['VoucherCode'],
                                            'TransMessage'=>'Transaction Successfull','ErrorCode'=>0,
                                            'DateExpiry'=>'');
                            } else {
                                return array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
                                             'TransDate'=>$result['TransactionDate'],'TrackingID'=>$trackingId,'TransID'=>'',
                                             'TransMessage'=>$vmsVerify['TransMsg'],'ErrorCode'=>52,'DateExpiry'=>'');
                            }
                            
                        }
                    } else {
                        $message = "VMS: ".$vmsGenerate;
                        Utilities::log($message);
                        return array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
                                     'TransDate'=>$result['TransactionDate'],'TrackingID'=>$trackingId,'TransID'=>'',
                                     'TransMessage'=>$message,'ErrorCode'=>57,'DateExpiry'=>'');
                    }
                    
                } else {
                    $this->status = 2;
                    
                    $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status,  null,  null);

                    return array_merge (array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
                                              'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>''),
                                        $result);
                }
            } else {
                 $message = 'Tracking ID must be unique.';
                 Utilities::log($message);
                 return array('TransStatus'=>2,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                             'TransMessage'=>$message,'ErrorCode'=>32,'DateExpiry'=>'');
            }
        }
        else
        {
            $message = 'Session has been ended';
            Utilities::log($message);
            return array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
                         'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                         'TransMessage'=>$message,'ErrorCode'=>16,'DateExpiry'=>'');
        }
    }
    
    /**
     * Description: get balance base on site id
     * @return string money formatted value
     */
    public function getSiteBalance() {
        $sitebalanceModel = new SiteBalanceModel();
        $site_balance = $sitebalanceModel->getSiteBalance($this->site_id);
        return Utilities::toMoney($site_balance['Balance']);
    }
    
        
    public function getSiteBalanceBySiteID($SiteID) {
        $sitebalanceModel = new SiteBalanceModel();
        $site_balance = $sitebalanceModel->getSiteBalance($SiteID);
        return Utilities::toMoney($site_balance['Balance']);
    }
    
    /**
     * Checks if amount was satisfied in min | max denomination
     * @param string $denomination_type
     * @param int $terminalID
     * @param string $amount
     * @param string $trackingId
     * @return bool | array
     */
    protected function verifyDenomination($denomination_type, $terminalID, 
                                          $amount, $trackingId){

        $terminalsModel = new TerminalsModel();
        $siteDenominationModel = new SiteDenominationModel();
        
        $terminal_data = $terminalsModel->getDataByTerminalId($terminalID);
        $is_vip = $terminal_data['isVIP'];

        $denomination = $siteDenominationModel->getDenominationPerSiteAndType($this->site_id, $denomination_type, $is_vip);

        $minDenom = SiteDenominationModel::$min;
        $maxDenom = SiteDenominationModel::$max;
        $divisible = 100;
        
        //commented on : 11/15/12 : disable checking of amount divisibility
//        if(ceil($amount) % $divisible != 0) {
//            $this->status = 2;
//            $message = 'Amount should be divisible by '.number_format($divisible, 2);
//            Utilities::log($message);
//            return array('TransStatus'=>2,'TransAmount'=>'',
//                         'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
//                         'TransMessage'=>$message,'ErrorCode'=>40,'DateExpiry'=>'');
//            Yii::app()->end();
//        }
        
        if(isset($maxDenom) && $amount > $maxDenom) {
            $this->status = 2;
            $message = 'Amount should be less than or equal to '.number_format($maxDenom,2);
            Utilities::log($message);
            return array('TransStatus'=>2,'TransAmount'=>'',
                         'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                         'TransMessage'=>$message,'ErrorCode'=>38);
            Yii::app()->end();
        }

        // check minimum denomination type for deposit only
        if($denomination_type == DENOMINATION_TYPE::INITIAL_DEPOSIT){
            
            if(isset($minDenom) && $amount < $minDenom) {
                $this->status = 2;
                $message = 'Amount should be greater than or equal to '.number_format($minDenom,2);
                Utilities::log($message);
                return array('TransStatus'=>2,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                             'TransMessage'=>$message,'ErrorCode'=>39);
            }
        }
        else
        {
            if($amount <= 0) {
                $this->status = 2;
                $message = 'Amount should be greater than 0';
                Utilities::log($message);
                return array('TransStatus'=>72,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                             'TransMessage'=>$message,'ErrorCode'=>39);
            }
        }
        
        return true;
    }
    
    /**
     * Sends a json response
     * @author JunJun S. Hernandez
     * @param int $TerminalID
     * @param int $ServiceID
     * @return float
     */
    public function getPlayingBalanceByID($TerminalID, $ServiceID) {
        Yii::import('application.components.CasinoApi2');
        $casinoApi = new CasinoApi2();
        $getBalance = $casinoApi->getBalance($TerminalID, $ServiceID);
        if(isset($getBalance['BalanceInfo']['Balance'])){
            $getBalance = $getBalance['BalanceInfo']['Balance'];
        } else {
            $getBalance = 0;
        }
        return Utilities::toMoney($getBalance);
    }
    
    /**
     * Sends a json response
     * @author JunJun S. Hernandez
     * @param int $terminal_id
     * @param int $service_id
     * @param string $cardnumber
     * @param boolean $return_transfer
     * @return float
     */
    public function getPlayingBalanceUserBased($terminal_id, $service_id, $cardnumber, $return_transfer, $user_mode) {
//        Yii::import('application.components.CasinoApi2');
        Yii::import('application.components.CasinoApiUB');
        $casinoApi = new CasinoApiUB;
        $getBalance = $casinoApi->getBalanceUserBased($terminal_id, $service_id, $cardnumber, $return_transfer, $user_mode);
        if(isset($getBalance['BalanceInfo']['Balance'])){
            $getBalance = $getBalance['BalanceInfo']['Balance'];
        } else {
            $getBalance = 0;
        }
        return Utilities::toMoney($getBalance);
    }
    
    /**
     * Sends a json response
     * @author JunJun S. Hernandez
     * @param string $cardnumber
     * @param boolean $return_transfer
     * @return json array
     */
    public function getCardInfo($cardnumber, $return_transfer){
        Yii::import('application.components.LoyaltyAPIWrapper');
        $loyaltyAPIWrapper = new LoyaltyAPIWrapper();
        $cardInfo = $loyaltyAPIWrapper->getCardInfo($cardnumber);
        return $cardInfo;
    }
    
    /**
     * Sends a json response
     * @author JunJun S. Hernandez
     * @param int $isPlaying
     * @param float $playingBalance
     * @param int $playerMode
     * @param int $currentCasino
     * @param array $mappedCasinos
     * @param str $minmaxAmount
     * @param int $sessionMode
     * @param str $membershipCardNo
     * @param str $siteCode
     * @param str $startDateTime
     * @param str $transMsg
     * @param int $errCode
     * @return json response
     */
    public static function getTerminalInfoResponse($isStarted, $isPlaying, $playingBalance, $playerMode, $currentCasino,
            $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime,
            $stackerBatchID, $stackerAmount, $transMsg,$errCode,$siteName){
        if(!empty($stackerBatchID)) {
            $stackerBatchID = $stackerBatchID;
        } else {
            $stackerBatchID = "";
        }
        if(Yii::app()->params['ParamDB'] == 1) {
            $_refParamModel = new RefParametersModel();
            $param_id = self::PARAM_MIN_TICKET_TO_PRINT_AMT;
            $paramValue = $_refParamModel->getParamValueById($param_id);
            if(!empty($paramValue) || $paramValue != '') {
                $minTicketToPrintAmt = $paramValue;
            } else {
                $minTicketToPrintAmt = 0;
            }
        } else {
            $minTicketToPrintAmt = Yii::app()->params['MinTicketToPrintAmount'];
        }
        return CJSON::encode(array('GetTerminalInfo'=>(array('IsStarted'=>(int)$isStarted,'IsPlaying'=>(int)$isPlaying,'PlayingBalance'=>$playingBalance,
                                   'PlayerMode'=>(int)$playerMode,'CurrentCasino'=>(int)$currentCasino,'MappedCasinos'=>$mappedCasinos,
                                   'MinMaxAmount'=>$minmaxAmount,'SessionMode'=>(int)$sessionMode,'MembershipCardNumber'=>$membershipCardNo,'SiteName'=>$siteName,
                                   'SiteCode'=>$siteCode,'StartDateTime'=>$startDateTime,'StackerBatchID'=>$stackerBatchID, 'TotalStackerAmount' => $stackerAmount, 
                                   'MaxRedeemableAmount' => (float)Yii::app()->params['MaxRedeemableAmount'], 'MinTicketToPrintAmount' => (float)$minTicketToPrintAmt,
                                   'TransactionMessage'=>$transMsg,'ErrorCode'=>(int)$errCode))));
    }
    
    /**
     * Sends a json response
     * @param Decimal $amount
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public static function getPlayingBalanceResponse($amount, $transMsg, $errCode){
        return CJSON::encode(array('GetPlayingBalance'=>(array('Amount'=>$amount, 'TransactionMessage'=>$transMsg,'ErrorCode'=>(int)$errCode))));
    }
    
    /**
     * Sends a json response
     * @param Decimal $amount
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public static function getMembershipInfo($status, $nickname, $gender, $classification, $mappedcasinos, $transMsg, $errCode){
        return CJSON::encode(array('GetMembershipInfo'=>(array('Status'=>$status, 'Nickname'=>$nickname, 'Gender'=>$gender, 'PlayerClassification'=>$classification, 'MappedCasinos'=>$mappedcasinos, 'TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
    /**
     * Sends a json response
     * @param Decimal $amount
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public static function checkTransaction($status, $datetime, $transactionid, $amount, $voucherticketbarcode, $expirationdate, $transMsg, $errCode){
        return CJSON::encode(array('CheckTransaction'=>(array('Status'=>$status, 'DateTime'=>$datetime, 'TransactionID'=>$transactionid, 'Amount'=>$amount, 'VoucherTicketBarcode'=>$voucherticketbarcode, 'ExpirationDate'=>$expirationdate, 'TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
    /**
     * Sends a json response
     * @param Decimal $amount
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public static function getLoginInfo($login, $hashedpassword, $plainpassword, $transMsg, $errCode){
        return CJSON::encode(array('GetLoginInfo'=>(array('Login'=>$login, 'HashedPassword'=>$hashedpassword, 'PlainPassword'=>$plainpassword, 'TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
    
    /**
     * Sends a json response
     * @param int $isstarted
     * @param String $datecreted
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public static function creteEgmSessionResponse($isstarted, $datecreted, $transMsg, $errCode){
        return CJSON::encode(array('CreateEgmSession'=>(array('IsStarted'=>$isstarted, 'DateCreated'=>$datecreted, 'TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
    
    public static function startSessionResponse($status, $DateTime, $trackingID, $transMsg, $errCode){
        
        return CJSON::encode(array('StartSession'=>(array('Status'=>(int)$status,
                                               'DateTime'=>$DateTime,
                                               'TransactionID'=>$trackingID,
                                               'TransactionMessage'=>$transMsg,
                                               'ErrorCode'=>(int)$errCode))));
        
    }
    
    
    public static function reloadSessionResponse($status, $DateTime, $trackingID, $transMsg, $errCode){
        
        return CJSON::encode(array('ReloadSession'=>(array('Status'=>(int)$status,
                                               'DateTime'=>$DateTime,
                                               'TransactionID'=>$trackingID,
                                               'TransactionMessage'=>$transMsg,
                                               'ErrorCode'=>(int)$errCode))));
        
    }
    
    
    public static function redeemSessionResponse($status,$amount, $VoucherTicketBarcode, $DateTime, $ExpirationDate, 
            $SiteCode, $membershipcardno, $trackingID, $transMsg, $isPrintTicket, $errCode){
        
        return CJSON::encode(array('RedeemSession'=>(array('Status'=>(int)$status,
                                               'Amount'=>(float)$amount,
                                               'VoucherTicketBarcode'=>$VoucherTicketBarcode,
                                               'DateTime'=>$DateTime,
                                               'ExpirationDate'=>$ExpirationDate,
                                               'SiteCode'=>$SiteCode,
                                               'MembershipCardNumber'=>$membershipcardno,
                                               'TransactionID'=>$trackingID,
                                               'TransactionMessage'=>$transMsg,
                                               'IsPrintTicket'=>(int)$isPrintTicket,
                                               'ErrorCode'=>(int)$errCode))));
        
    }
    /**
     * Added by Mark Kenneth Esguerra
     * @date June 5, 2014
     */
    public static function removeEgmSessionResponse($transMsg, $errCode){
        return CJSON::encode(array('RemoveEgmSession'=>(array('TransactionMessage'=>$transMsg, 
                                                              'ErrorCode'=>(int)$errCode))));
    }
}

?>
