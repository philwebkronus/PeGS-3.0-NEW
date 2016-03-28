<?php
Yii::import('application.components.CasinoApi');

/**
 * This class was for cash methods of (D, R, W)
 * @date 10/04/12
 * @author elperez
 */
class CashMethodController extends CommonController{
    
    /**
     * Deposit Transaction through cash method
     * @param int $terminalID
     * @param str $transDetails
     * @param str $trackingId
     * @return array result 
     */
    public function depositTrans($terminalID, $transDetails, $trackingId){
        Yii::import('application.components.CommonStartSession');
        Yii::import('application.components.Loyalty');
        
        $commonStartSession = new CommonStartSession();
        $sitesModel = new SitesModel();
        $gamingMachineModel = new GamingMachineModel();
        $gamingRequestLogsModel = new GamingRequestLogs();
        $loyalty = new Loyalty();
        
        $loyaltyCard = '';
        $rdetails = explode(";", $transDetails);
        
        //check if transaction details parameter are complete
        if(count($rdetails) == 3 || count($rdetails) == 2)
        {
            $serviceID = $rdetails[0]; 
            $amount = $rdetails[1];
            
            //Check if loyalty barcode is passed
            if(isset($rdetails[2]))
                $loyaltyCard = $rdetails[2];
            
            //verify if service id is not numeric
            if(!is_numeric($serviceID)){
                $this->status = 2;
                return array('TransStatus'=>2,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                             'TransMessage'=>'Invalid Service ID','ErrorCode'=>35);
                Yii::app()->end();
            }

            //verify if amount is not numeric
            if(!is_numeric($amount)){
                $this->status = 2;
                return array('TransStatus'=>2,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                             'TransMessage'=>'Invalid Amount','ErrorCode'=>34);
                Yii::app()->end();
            }
            
            $rgaming = $gamingMachineModel->getMachineDetails($terminalID);
        
            $this->site_id = $rgaming['POSAccountNo'];
            $this->acc_id = $rgaming['CreatedByAID'];
            
            //Is Site BCF greater than amount to be deposit
            if(Utilities::toInt($this->getSiteBalance()) < $amount) {
                $this->status = 2;
                return array('TransStatus'=>2,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                             'TransMessage'=>'Not enough BCF','ErrorCode'=>41);
                Yii::app()->end();
            }
            
            $isverified = $this->verifyDenomination(DENOMINATION_TYPE::INITIAL_DEPOSIT, 
                                      $terminalID, $amount, $trackingId);

            //Verify amount if satisfies with site denomination (min | max)
            if(is_bool($isverified)){
                
                $trans_id = $gamingRequestLogsModel->insertGamingRequestLogs($trackingId, 
                                $amount, $this->status, 'D', $this->site_id, $terminalID,
                                $serviceID);

                //check if tracking ID is unique
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
                        return array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
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
                        return array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
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
                        return array('TransStatus'=>2,'TransAmount'=>Utilities::toDecimal($amount),
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
                        
                        $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, $result['udate'], 
                                                                    $result['trans_details_id']);

                        return array('TransStatus'=>1,'TransAmount'=>Utilities::toDecimal($amount),
                                     'TransDate'=>$result['TransactionDate'],'TrackingID'=>$trackingId,
                                     'TransID'=>$result['trans_details_id'],'TransMessage'=>'Transaction Successfull',
                                     'ErrorCode'=>0);
                    }
                    else
                    {
                        $this->status = 2;
                        
                        $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null);
                        
                        return array_merge(array('TransStatus'=>2,
                                                           'TransAmount'=>Utilities::toDecimal($amount),
                                                           'TransDate'=>'','TrackingID'=>$trackingId,
                                                           'TransID'=>''), $result);
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
            $message = 'Transaction Details has invalid parameters';
            Utilities::log($message);
            return array('TransStatus'=>2,'TransAmount'=>'',
                         'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                         'TransMessage'=>$message,'ErrorCode'=>33);
        }
    }
    
    /**
     * Reload Transaction through cash method
     * @param int $terminalID
     * @param str $transDetails
     * @param str $trackingId
     * @return array
     */
    public function reloadTrans($terminalID, $transDetails, $trackingId){
        
        Yii::import('application.components.CommonReload');
        Yii::import('application.components.Loyalty'); 
        
        $commonReload = new CommonReload();
        $sitesModel = new SitesModel();
        $loyalty = new Loyalty();
        $sitesModel = new SitesModel();
        $gamingMachineModel = new GamingMachineModel();
        $gamingRequestLogsModel = new GamingRequestLogs();
        $terminalSessionsModel = new TerminalSessionsModel();
        
        $loyaltyCard = '';
        $rdetails = explode(";", $transDetails);
        if(count($rdetails) == 1)
        {
            $amount = $rdetails[0];

            //validate if amount is numerics
            if(!is_numeric($amount)){
                $this->status = 2;
                $message = 'Invalid Amount';
                return array('TransStatus'=>2,'TransAmount'=>'',
                             'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                             'TransMessage'=>$message,'ErrorCode'=>34);
                Yii::app()->end();
            }
            
            $rgaming = $gamingMachineModel->getMachineDetails($terminalID);
        
            $this->site_id = $rgaming['POSAccountNo'];
            $this->acc_id = $rgaming['CreatedByAID'];
            
            $isverified = $this->verifyDenomination(DENOMINATION_TYPE::RELOAD, 
                                      $terminalID, $amount, $trackingId);
            
            //check if amount satisfies with min | max denomination
            if(is_bool($isverified)){
                
                $serviceID = $terminalSessionsModel->getServiceId($terminalID);
                
                $trans_id = $gamingRequestLogsModel->insertGamingRequestLogs($trackingId, 
                                $amount, $this->status, 'R', $this->site_id, $terminalID,
                                $serviceID);

                //check if tracking id was unique
                if($trans_id){
                    
                    $result = $commonReload->reload(Utilities::toInt($this->getSiteBalance()), Utilities::toInt($amount),
                                        $terminalID, $this->site_id, $this->acc_id, $serviceID);
                
                    $pos_account_no = $sitesModel->getPosAccountNo($this->site_id);   

                    if(isset($result['trans_summary_id']))
                    {
                        $this->status = $result['transStatus'];
                        
                        $loyalty->loyaltyReload($result['trans_summary_id'], $result['udate'], $amount, 
                                            $pos_account_no, Yii::app()->params['loyalty_service'], $result['terminal_name'], 
                                            $result['trans_ref_id'], true);
                        
                        $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, $result['udate'], 
                                                                    $result['trans_details_id']);
                        
                        return array('TransStatus'=>1,'TransAmount'=>Utilities::toDecimal($amount),
                                 'TransDate'=>$result['TransactionDate'],'TransID'=>$result['trans_details_id'],'TrackingID'=>$trackingId,
                                 'TransMessage'=>'Transaction Successfull','ErrorCode'=>0);
                    }
                    else
                    {
                        $this->status = 2;
                        
                        $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null);
                        
                        return array_merge (array('TransStatus'=>2,
                                                  'TransAmount'=>Utilities::toDecimal($amount),
                                                  'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>''),
                                            $result);
                    }
                }
                else
                {
                    $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null);
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
        else{
            $message = 'Transaction Details has invalid parameters';
            Utilities::log($message);
            return array('TransStatus'=>2,'TransAmount'=>'',
                         'TransDate'=>'','TrackingID'=>$trackingId,'TransID'=>'',
                         'TransMessage'=>$message,'ErrorCode'=>33);
        }
    }
}
?>
