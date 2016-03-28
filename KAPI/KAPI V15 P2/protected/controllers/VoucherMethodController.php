<?php

/**
 * Controller for Voucher Method 
 * This integrates Kronus and VMS Webservice 
 * @date 10/12/12
 * @author elperez
 */
class VoucherMethodController extends CommonController{
    
    /**
     * Deposit Transaction - this calls the verify and claim voucher web methods
     * @param string $token
     * @param int $terminalID
     * @param string $transDetails
     * @param string $trackingId
     * @return array
     */
    public function depositTrans($token, $terminalID, $transDetails, $trackingId){
        Yii::import('application.components.CommonStartSession');
        Yii::import('application.components.Loyalty');
        Yii::import('application.components.VoucherManagement');
        
        $commonStartSession = new CommonStartSession();
        $sitesModel = new SitesModel();
        $gamingMachineModel = new GamingMachineModel();
        $gamingRequestLogsModel = new GamingRequestLogs();
        $loyalty = new Loyalty();
        $voucherManagement = new VoucherManagement();
        
        $loyaltyCard = '';
        $rdetails = explode(";", $transDetails);
        
        //check if transaction details parameter are complete
        if(count($rdetails) == 3 || count($rdetails) == 2)
        {
            $serviceID = $rdetails[0]; 
            $voucherCode = $rdetails[1];
            
            //Check if loyalty barcode is passed
            if(isset($rdetails[2]))
                $loyaltyCard = $rdetails[2];
            
            //verify if service id is not numeric
            if(!is_numeric($serviceID)){
                $this->status = 2;
                $message = 'Invalid Service ID';
                Utilities::log($message);
                return array('TransStatus'=>2,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                             'TransMessage'=>$message,'ErrorCode'=>35);
                Yii::app()->end();
            }

            $rgaming = $gamingMachineModel->getMachineDetails($terminalID);
        
            $this->site_id = $rgaming['POSAccountNo'];
            $this->acc_id = $rgaming['CreatedByAID'];
            
            $verifyVoucherResult = $voucherManagement->verifyVoucher($voucherCode, $this->acc_id);
            
            //verify if vms API has no error/reachable
            if(is_string($verifyVoucherResult)){
                $this->status = 2;
                $message = $verifyVoucherResult;
                Utilities::log($message);
                return array('TransStatus'=>2,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                             'TransMessage'=>$message,'ErrorCode'=>57);
                Yii::app()->end();
            }
            
            //check if voucher is not yet claimed
            if(isset($verifyVoucherResult['VerifyVoucher']['ErrorCode']) && $verifyVoucherResult['VerifyVoucher']['ErrorCode'] == 0)
            {
                if(isset($verifyVoucherResult['VerifyVoucher']['Amount']) && $verifyVoucherResult['VerifyVoucher']['Amount'] != '')
                {
                    $amount = $verifyVoucherResult['VerifyVoucher']['Amount'];
                    
                    //Is Site BCF greater than amount to be deposit
                    if(Utilities::toInt($this->getSiteBalance()) < $amount) {
                        $this->status = 2;
                        $message = 'Not enough BCF';
                        Utilities::log($message);
                        return array('TransStatus'=>2,'TransAmount'=>'',
                                     'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                                     'TransMessage'=>$message,'ErrorCode'=>41);
                        Yii::app()->end();
                    }

                    $isverified = $this->verifyDenomination(DENOMINATION_TYPE::INITIAL_DEPOSIT, 
                                              $terminalID, $amount, $trackingId);

                    //Verify amount satisfies with site denomiation set
                    if(is_bool($isverified)){

                        $trans_id = $gamingRequestLogsModel->insertGamingRequestLogs($trackingId, 
                                            $amount, $this->status, 'D', $this->site_id,
                                            $terminalID, $serviceID);

                        //is logs successfully recorded in Transaction Logs
                        if($trans_id){

                            $is_loyalty = false;        

                            //Verify is loyalty barcode was not empty
                            if($loyaltyCard == '') {
                                $loyaltyCard = $gamingMachineModel->getDummyLoyalty($terminalID);
                            }
                            
                            $result = $loyalty->getCardInfo($loyaltyCard,1);
                            $obj_result = json_decode($result);

                            if($obj_result->CardInfo->CardNumber == null) {
                                $this->status = 2;
                                $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null);
                                $message = 'Can\'t get card info';
                                Utilities::log($message);
                                return array('TransStatus'=>2,'TransAmount'=>(float)$amount,
                                            'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                                            'TransMessage'=>$message,'ErrorCode'=>29);

                                Yii::app()->end();
                            }

                            //if playername and if SiteGroup is BGI
                            elseif($obj_result->CardInfo->PlayerName == null){
                                $this->status = 2; 
                                $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null);
                                $message = 'Loyalty Card profile is not updated.';
                                Utilities::log($message);
                                return array('TransStatus'=>2,'TransAmount'=>(float)$amount,
                                            'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                                            'TransMessage'=>$message,'ErrorCode'=>51);
                                Yii::app()->end();
                            }

                            //if player status is deactivated and Site Group is BGI
                            elseif($obj_result->CardInfo->Status != 'A')
                            {
                                $this->status = 2;
                                $message = 'Card is inactive or deactivated';              
                                $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null);
                                Utilities::log($message);
                                return array('TransStatus'=>2,'TransAmount'=>(float)$amount,
                                            'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                                            'TransMessage'=>$message,'ErrorCode'=>30);
                                Yii::app()->end();
                            }

                            else {
                                $is_loyalty = true;
                            }

                            $result = $commonStartSession->start($terminalID, $this->site_id, 'D', $serviceID,
                                                                 Utilities::toInt($this->getSiteBalance()), 
                                                                 $amount, $this->acc_id);

                            $pos_account_no = $sitesModel->getPosAccountNo($this->site_id);

                            //Is cashier transaction successful
                            if(isset($result['trans_summary_id'])){
                                
                                $this->status = $result['transStatus'];

                                //check if loyaty is set and insert records in Loyalty Desposit Webservice
                                if($is_loyalty) {
                                    $loyalty->loyaltyDeposit($result['trans_summary_id'], $loyaltyCard, $result['udate'], $amount, 
                                            $pos_account_no, Yii::app()->params['loyalty_service'], $result['terminal_name'], $result['trans_ref_id'], 1);
                                }
                                
                                $useVoucherResult = $voucherManagement->useVoucher($this->acc_id, $trackingId, $voucherCode, $terminalID);
                                
                                $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, $result['udate'], 
                                                                    $result['trans_details_id']);
                                //check if claim VMS has error 500
                                if(!is_string($useVoucherResult)){
                                    
                                    //Check if voucher has successfully claimed
                                    if(isset($useVoucherResult['UseVoucher']['ErrorCode']) && 
                                             $useVoucherResult['UseVoucher']['ErrorCode'] == 0)
                                    {
                                        return array('TransStatus'=>1,'TransAmount'=>Utilities::toDecimal($amount),
                                                     'TransDate'=>$result['TransactionDate'],'TrackingID'=>$trackingId,
                                                     'TransID'=>$result['trans_details_id'],'TransMessage'=>'Transaction Successfull',
                                                     'ErrorCode'=>0);
                                    }
                                    else
                                    {
                                        $message = "VMS: ".$useVoucherResult['UseVoucher']['TransMsg'];
                                        Utilities::log($message);
                                        return array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
                                                     'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                                                     'TransMessage'=>$message,'ErrorCode'=>52);
                                    }
                                } else {
                                    $message = "VMS: ".$useVoucherResult;
                                    Utilities::log($message);
                                    return array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
                                                 'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                                                 'TransMessage'=>$message,'ErrorCode'=>57);
                                }
                            }
                            else
                            {
                                $this->status = 2;
                                
                                $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null);
                                
                                return array_merge(array('TransStatus'=>2,
                                                         'TransAmount'=>Utilities::toDecimal($amount),
                                                         'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>''),
                                                   $result);
                            }
                        }
                        else{
                            $message = 'Tracking ID must be unique.';
                            Utilities::log($message);
                            return array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
                                         'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                                         'TransMessage'=>$message,'ErrorCode'=>32);
                        }
                    }
                    else
                        return $isverified;
                }
                else
                {
                    $message = 'Amount is not set';
                    Utilities::log($message);
                    return array('TransStatus'=>2,'TransAmount'=>'',
                                 'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                                 'TransMessage'=>$message,'ErrorCode'=>33);
                }
            }
            else
            {
                $message = "VMS: ".$verifyVoucherResult['VerifyVoucher']['TransMsg'];
                Utilities::log($message);
                return array('TransStatus'=>2,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                             'TransMessage'=>$message,'ErrorCode'=>52);
            }
        }
        else
        {
            $message = 'Transaction Details has invalid parameters';
            Utilities::log($message);
            return array('TransStatus'=>2,'TransAmount'=>'',
                         'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                         'TransMessage'=>$message,'ErrorCode'=>33);
        }
    }
    
    /**
     * Reload transaction
     * This calls the verify and claim voucher api
     * @param str $token
     * @param int $terminalID
     * @param str $transDetails
     * @param str $trackingId
     * @return array 
     */
    public function reloadTrans($token, $terminalID, $transDetails, $trackingId) {
        
        Yii::import('application.components.CommonReload');
        Yii::import('application.components.Loyalty');
        Yii::import('application.components.VoucherManagement');
        
        $commonReload = new CommonReload();
        $loyalty = new Loyalty();
        $sitesModel = new SitesModel();
        $gamingMachineModel = new GamingMachineModel();
        $gamingRequestLogsModel = new GamingRequestLogs();
        $voucherManagement = new VoucherManagement();
        $terminalSessionsModel = new TerminalSessionsModel();
         
        $rdetails = explode(";", $transDetails);
        
        //check if voucher code is set
        if(count($rdetails) == 1)
        {
            $voucherCode = $rdetails[0];
            $rgaming = $gamingMachineModel->getMachineDetails($terminalID);
        
            $this->site_id = $rgaming['POSAccountNo'];
            $this->acc_id = $rgaming['CreatedByAID'];
            
            $verifyVoucherResult = $voucherManagement->verifyVoucher($voucherCode, $this->acc_id, 
                                        $token, Yii::app()->params['vms_source']);
            
            //verify if vms API has no error/reachable
            if(is_string($verifyVoucherResult)){
                $this->status = 2;
                $message = $verifyVoucherResult;
                Utilities::log($message);
                return array('TransStatus'=>2,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                             'TransMessage'=>$message,'ErrorCode'=>57);
                Yii::app()->end();
            }
            
            //check if voucher is not yet claimed
            if(isset($verifyVoucherResult['VerifyVoucher']['ErrorCode']) && $verifyVoucherResult['VerifyVoucher']['ErrorCode'] == 0)
            {
                //check if amount is not blank 
                if(isset($verifyVoucherResult['VerifyVoucher']['Amount']) && $verifyVoucherResult['VerifyVoucher']['Amount'] != '')
                {
                    $amount = $verifyVoucherResult['VerifyVoucher']['Amount'];
                    
                    //Verify Site Denomination
                    $isverified = $this->verifyDenomination(DENOMINATION_TYPE::RELOAD, $terminalID,
                                              $amount, $trackingId);
                    
                    //Check if amount satisfies the min | max denominations
                    if(is_bool($isverified)){
                        
                        $serviceID = $terminalSessionsModel->getServiceId($terminalID);

                        $trans_id = $gamingRequestLogsModel->insertGamingRequestLogs($trackingId, 
                                        $amount, $this->status, 'R', $this->site_id, $terminalID,
                                        $serviceID);
                        
                        //Check trackingID is unique
                        if($trans_id){

                            $result = $commonReload->reload(Utilities::toInt($this->getSiteBalance()), 
                                                Utilities::toInt($amount),$terminalID, $this->site_id, 
                                                $this->acc_id, $serviceID);
                            
                            $pos_account_no = $sitesModel->getPosAccountNo($this->site_id);   

                            $this->status = $result['transStatus'];

                            if(isset($result['trans_summary_id']))
                            {
                                $loyalty->loyaltyReload($result['trans_summary_id'], $result['udate'], $amount, 
                                                    $pos_account_no, Yii::app()->params['loyalty_service'], $result['terminal_name'], 
                                                    $result['trans_ref_id'], true);
                                
                                $useVoucherResult = $voucherManagement->useVoucher($this->acc_id, $trackingId, 
                                                                            $voucherCode, $terminalID, $token, 
                                                                            Yii::app()->params['vms_source']);
                                
                                $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, $result['udate'], 
                                                                            $result['trans_details_id']);
                                if(!is_string($useVoucherResult)){
                                    
                                    //check if voucher is successfully claimed
                                    if(isset($useVoucherResult['UseVoucher']['ErrorCode']) && 
                                             $useVoucherResult['UseVoucher']['ErrorCode'] == 0)
                                    {
                                        return array('TransStatus'=>1,'TransAmount'=>Utilities::toDecimal($amount),
                                                     'TransDate'=>$result['TransactionDate'],'TrackingID'=>$trackingId,
                                                     'TransID'=>$result['trans_details_id'],'TransMessage'=>'Transaction Successfull',
                                                     'ErrorCode'=>0);
                                    }
                                    else
                                    {
                                        $message = "VMS: ".$useVoucherResult['UseVoucher']['TransMsg'];
                                        Utilities::log($message);
                                        return array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
                                                     'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                                                     'TransMessage'=>$message,'ErrorCode'=>52);
                                    }
                                } else {
                                    $message = "VMS: ".$useVoucherResult;
                                    Utilities::log($message);
                                    return array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
                                                 'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                                                 'TransMessage'=>$message,'ErrorCode'=>57);
                                }
                            }
                            else{
                                $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null);
                                
                                return array_merge(array('TransStatus'=>2,
                                                         'TransAmount'=>Utilities::toDecimal($amount),
                                                         'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>''),
                                                   $result);
                            }
                        }
                        else
                        {
                            $message = 'Tracking ID must be unique.';
                            Utilities::log($message);
                            return array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
                                         'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                                         'TransMessage'=>$message,'ErrorCode'=>32);
                        }
                    }
                    else
                        return $isverified;
                }
                else
                {
                    $message = 'Amount is not set';
                    Utilities::log($message);
                    return array('TransStatus'=>2,'TransAmount'=>'',
                                 'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                                 'TransMessage'=>$message,'ErrorCode'=>33);
                }
            }
            else
            {
                $message = "VMS: ".$verifyVoucherResult['VerifyVoucher']['TransMsg'];
                Utilities::log($message);
                return array('TransStatus'=>2,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                             'TransMessage'=>$message,'ErrorCode'=>52);
            }
        }
        else
        {
            $message = 'Transaction Details has invalid parameters';
            Utilities::log($message);
            return array('TransStatus'=>2,'TransAmount'=>'',
                         'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                         'TransMessage'=>$message,'ErrorCode'=>33);
        }
    }
}
?>