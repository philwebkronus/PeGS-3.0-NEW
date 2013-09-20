<?php

/**
 * Description of WsCommonAPIController
 * @datecreated 09/19/13
 * @author elperez
 */
class CommonController{
    
    CONST VOUCHER_STATUS_INACTIVE = 0;
    CONST VOUCHER_STATUS_ACTIVE = 1;
    CONST VOUCHER_STATUS_VOID = 2;
    CONST VOUCHER_STATUS_USED = 3;
    CONST VOUCHER_STATUS_REIMBURSED = 5;
    CONST VOUCHER_STATUS_CLAIMED = 4;    
    CONST VOUCHER_STATUS_EXPIRED = 6;
    CONST VOUCHER_STATUS_CANCELLED = 7; 
    
    CONST GENERATE_KAPI_SOURCE = 1;
    CONST GENERATE_EGM_SOURCE = 2;
    
    CONST VOUCHER_TYPE_TICKET = 1;
    CONST VOUCHER_TYPE_COUPON = 2;
    
    /**
     * @author elperez
     * @datecreated 09/16/2013
     * @purpose Get coupon status
     * @param int $AID cashier id
     * @param str $voucherCode coupon code
     * @param int $source cashier | kapi | egm
     * @param str $trackingid unique tracking info
     * @param str $voucherTypeID ticket | coupon
     * @return array
     */
    public function verifyCoupon($AID,$voucherCode,$source,$trackingid, $voucherTypeID){
        $_voucherBatchInfoModel = new VoucherBatchInfoModel();
        $_couponBatchModel = new CouponBatchModel();
        
        $amount = 0;
        $dateCreated = ""; 
        $loyaltyCreditable = 0;
                
        //set the active coupon batch id for coupon
        $batchID = $_voucherBatchInfoModel->getActiveBatchNo($voucherTypeID);
        
        //set the active coupon batch table
        $couponBatchTable = "couponbatch_".$batchID;
        
        //verify coupon date expiration
        $dateNow = date("Y-m-d H:i:s");
        $dateExpiry = $_voucherBatchInfoModel->getVoucherBatchInfo($voucherTypeID);
        
        //check if date is already expired        
        if($dateNow < $dateExpiry['ExpiryDate']){
            
            //call the active coupon batch table
            $couponBatchResult = $_couponBatchModel->getActiveCouponBatch($couponBatchTable, $voucherCode);
            $amount =  (float)$couponBatchResult['Amount'];
            $loyaltyCreditable = (int)$couponBatchResult['LoyaltyCreditable'];
            $couponStatus = $couponBatchResult['Status'];
            $dateCreated = (string)$couponBatchResult['DateCreated'];

            //check the status of coupon
            switch($couponStatus){
                //inactive
                case "0" :
                    $transMsg = "Inactive Coupon.";
                    $errorCode = 4;
                    break;
                //unused
                case "1" :
                    $errorCode = 0;
                    $transMsg = "Coupon is unclaimed.";
                    break;
                //used
                case "2" : 
                    $errorCode = 5;
                    $transMsg = "Coupon is already used";
                    break;
                default :
                    $errorCode = 6;
                    $transMsg = "Invalid coupon";
                    break;
            }
        } else {
            $transMsg = "Coupon was already expired.";
            $errorCode = 10;
            $couponStatus = 6;
        }
        
        
        $details = "VerifyVoucher : ".$transMsg;
        AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, (int)$couponStatus);
        return $this->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, 
                            $amount, $dateCreated, $loyaltyCreditable);
    }
    
    /**
     * @todo verification for ticket
     */
    public function verifyTicket(){
        
    }
    
    /**
     * 
     * @param str $voucherCode coupon / ticket type
     * @param int $aid account id
     * @param str $trackingID unique tracking info
     * @param int $terminalID egm / kronus terminal
     * @param int $source egm, kronus, kapi
     * @param int $voucherTypeID coupon / ticket type
     * @return array transaction result
     */
    public function useCoupon($voucherCode, $aid,$trackingID,
                            $terminalID, $source, $voucherTypeID){
        $_voucherBatchInfoModel = new VoucherBatchInfoModel();
        $_couponBatchModel = new CouponBatchModel();
        $_couponModel = new CouponModel();
        
         //set the active coupon batch id for coupon
        $batchID = $_voucherBatchInfoModel->getActiveBatchNo($voucherTypeID);
        
        //set the active coupon batch table
        $couponBatchTable = "couponbatch_".$batchID;
        
        //verify coupon date expiration
        $dateNow = date("Y-m-d H:i:s");
        $dateExpiry = $_voucherBatchInfoModel->getVoucherBatchInfo($voucherTypeID);
        
        //check if date is already expired        
        if($dateNow < $dateExpiry['ExpiryDate']){
            
            //call the active coupon batch table
            $couponBatchResult = $_couponBatchModel->getActiveCouponBatch($couponBatchTable, $voucherCode);
            $amount =  (float)$couponBatchResult['Amount'];
            $loyaltyCreditable = (int)$couponBatchResult['LoyaltyCreditable'];
            $couponStatus = $couponBatchResult['Status'];

            //check the status of coupon
            switch($couponStatus){
                //inactive
                case "0" :
                      $transMsg = "Inactive Coupon.";
                      $errorCode = 4;                  
                    break;
                //unused
                case "1" :
                       $isTrackingIdExists = $_couponModel->isTrackingIDExists($trackingID);

                       if($isTrackingIdExists == 0){
                            $isUsed = $_couponModel->insertCoupon($voucherTypeID, $trackingID, 
                                           $voucherCode, $batchID, $terminalID, $amount, 
                                           $aid, $source, $couponBatchTable,$loyaltyCreditable,
                                           $dateExpiry['ExpiryDate']);
                            
                            //check if transaction was successful
                            if($isUsed){
                                $transMsg = "Transaction approved.";
                                $errorCode = 0;
                            } else {
                                $transMsg = "Transaction denied.";
                                $errorCode = 8;
                            }
                       } else {
                           $transMsg = "Traacking ID was already used.";
                           $errorCode = 9;
                       }
                    break;
                //used
                case "2" : 
                    $transMsg = "Coupon is already used.";
                    $errorCode = 5;
                    break;
                default :
                    $transMsg = "Invalid coupon";
                    $errorCode = 6;
                    break;
            }
        } else {
            $transMsg = "Coupon was already expired.";
            $errorCode = 10;
            $couponStatus = 6;
        }
        
        $details = "UseVoucher : ".$transMsg;
        AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, $couponStatus);
        return $this->getUseRetMsg(2, $transMsg, $errorCode);
        
    }

    /**
     * @todo for ticket system
     */
    public function useTicket(){
        
    }
    
    /**
     * Get the return output of verify voucher API
     * @param type $voucherTypeID
     * @param int $errorCode
     * @param string $transMsg
     * @param str $voucherCode
     * @param float $amount
     * @param str $dateCreated
     * @param int $loyaltyCreditable
     * @return array response
     */
    public function getVerifyRetMsg($voucherTypeID, $errorCode, $transMsg, 
            $voucherCode = '', $amount = '', $dateCreated = '', $loyaltyCreditable = ''){
        switch($voucherTypeID){
            case 1 :
                //todo for ticket APi 
                break;
            case 2 :
                return array("CouponCode"=>$voucherCode,
                             "Amount"=>(float)$amount,
                             "DateCreated"=>$dateCreated,
                             "TransMsg"=>$transMsg,
                             "LoyaltyCreditable"=>(int)$loyaltyCreditable,
                             "ErrorCode"=>(int)$errorCode); 
                break;
            default : 
                 $errorCode = 7;
                 $transMsg = "Invalid voucher type";
                 return array("CouponCode"=>"",
                             "Amount"=> 0,
                             "DateCreated"=> "",
                             "TransMsg"=>$transMsg,
                             "ErrorCode"=>(int)$errorCode); 
                break;
        }
        
    }
    
    /**
     * Get the return output of use voucher API
     * @param type $voucherTypeID
     * @param string $transMsg
     * @param int $errorCode
     * @return type
     */
    public function getUseRetMsg($voucherTypeID, $transMsg, $errorCode){
        switch ($voucherTypeID){
            case 1 :
                //todo for ticket API
                break;
            case 2 :
                  return array("TransMsg"=>$transMsg,
                               "ErrorCode"=>$errorCode);
                break;
            default :
                 $errorCode = 7;
                 $transMsg = "Invalid voucher type";
                 return array("TransMsg"=>$transMsg,
                             "ErrorCode"=>(int)$errorCode); 
                
                break;
        }
    }
}

?>
