<?php

/**
 * Description of WsCommonAPIController
 * @datecreated 09/19/13
 * @author elperez
 */
class CommonController {

    CONST VOUCHER_STATUS_INACTIVE = 0;
    CONST VOUCHER_STATUS_ACTIVE = 1;
    CONST VOUCHER_STATUS_VOID = 2;
    CONST VOUCHER_STATUS_USED = 3;
    CONST VOUCHER_STATUS_CLAIMED = 4;
    CONST VOUCHER_STATUS_REIMBURSED = 5;
    CONST VOUCHER_STATUS_EXPIRED = 6;
    CONST VOUCHER_STATUS_CANCELLED = 7;
    CONST GENERATE_KAPI_SOURCE = 1;
    CONST GENERATE_EGM_SOURCE = 2;
    CONST VOUCHER_TYPE_TICKET = 1;
    CONST VOUCHER_TYPE_COUPON = 2;
    CONST TRACKING_ID_STATUS_PENDING = 0;
    CONST TRACKING_ID_STATUS_SUCCESSFUL = 1;
    CONST TRACKING_ID_STATUS_FAILED = 2;
    CONST TRACKING_ID_STATUS_FULFILLMENT_APPROVED = 3;
    CONST TRACKING_ID_STATUS_FULFILLMENT_DENIED = 4;
    CONST ACCOUNTTYPE_ID_VIRTUAL_CASHIER = 15;

    /**
     * Obsolote Method
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
    public function x_verifyCoupon($AID, $voucherCode, $source, $trackingid, $voucherTypeID) {
        $_voucherBatchInfoModel = new VoucherBatchInfoModel();
        $_couponBatchModel = new CouponBatchModel();

        $amount = 0;
        $dateCreated = "";
        $loyaltyCreditable = 0;

        //set the active coupon batch id for coupon
        $batchID = $_voucherBatchInfoModel->getActiveBatchNo($voucherTypeID);

        //set the active coupon batch table
        $couponBatchTable = "couponbatch_" . $batchID;

        //verify coupon date expiration
        $dateNow = date("Y-m-d H:i:s");
        $dateExpiry = $_voucherBatchInfoModel->getVoucherBatchInfo($voucherTypeID);

        //check if date is already expired        
        if ($dateNow < $dateExpiry['ExpiryDate']) {

            //call the active coupon batch table
            $couponBatchResult = $_couponBatchModel->getActiveCouponBatch($couponBatchTable, $voucherCode);
            $amount = (float) $couponBatchResult['Amount'];
            $loyaltyCreditable = (int) $couponBatchResult['LoyaltyCreditable'];
            $couponStatus = $couponBatchResult['Status'];
            $dateCreated = (string) $couponBatchResult['DateCreated'];

            //check the status of coupon
            switch ($couponStatus) {
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


        $details = "VerifyVoucher : " . $transMsg;
        AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, (int) $couponStatus);
        return $this->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
    }

    /**
     * @todo verification for ticket
     */
    public function verifyTicket() {
        
    }

    /**
     * Obsolete Function
     * @param str $voucherCode coupon / ticket type
     * @param int $aid account id
     * @param str $trackingID unique tracking info
     * @param int $terminalID egm / kronus terminal
     * @param int $source egm, kronus, kapi
     * @param int $voucherTypeID coupon / ticket type
     * @return array transaction result
     */
    public function x_useCoupon($voucherCode, $aid, $trackingID, $terminalID, $source, $voucherTypeID) {
        $_voucherBatchInfoModel = new VoucherBatchInfoModel();
        $_couponBatchModel = new CouponBatchModel();
        $_couponModel = new CouponModel();

        //set the active coupon batch id for coupon
        $batchID = $_voucherBatchInfoModel->getActiveBatchNo($voucherTypeID);

        //set the active coupon batch table
        $couponBatchTable = "couponbatch_" . $batchID;

        //verify coupon date expiration
        $dateNow = date("Y-m-d H:i:s");
        $dateExpiry = $_voucherBatchInfoModel->getVoucherBatchInfo($voucherTypeID);

        //check if date is already expired        
        if ($dateNow < $dateExpiry['ExpiryDate']) {

            //call the active coupon batch table
            $couponBatchResult = $_couponBatchModel->getActiveCouponBatch($couponBatchTable, $voucherCode);
            $amount = (float) $couponBatchResult['Amount'];
            $loyaltyCreditable = (int) $couponBatchResult['LoyaltyCreditable'];
            $couponStatus = $couponBatchResult['Status'];

            //check the status of coupon
            switch ($couponStatus) {
                //inactive
                case "0" :
                    $transMsg = "Inactive Coupon.";
                    $errorCode = 4;
                    break;
                //unused
                case "1" :
                    $isTrackingIdExists = $_couponModel->isTrackingIDExists($trackingID);

                    if ($isTrackingIdExists == 0) {
                        $isUsed = $_couponModel->insertCoupon($voucherTypeID, $trackingID, $voucherCode, $batchID, $terminalID, $amount, $aid, $source, $couponBatchTable, $loyaltyCreditable, $dateExpiry['ExpiryDate']);

                        //check if transaction was successful
                        if ($isUsed) {
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

        $details = "UseVoucher : " . $transMsg;
        AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, $couponStatus);
        return $this->getUseRetMsg(2, $transMsg, $errorCode);
    }

    /**
     * @todo for ticket system
     */
//    public function useTicket(){
//        
//    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/2013
     * @purpose Validate Coupon and Ticket
     * @param str $trackingID unique tracking info
     * @param str $terminalName name of terminal
     * @param str $voucherTicketBarcode ticket | coupon
     * @param int $source cashier | kapi | egm
     * @param int $aid cashier id
     * @return array
     */
    public function validateCouponTicket($trackingID, $terminalName, $voucherTicketBarcode, $source, $aid) {
        $_couponModel = new CouponModel();
        $_ticketModel = new TicketModel();
        return $_couponModel->getCouponDataByCode($voucherTicketBarcode);
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
    public function getVerifyRetMsg($voucherTypeID, $errorCode, $transMsg, $voucherCode = '', $amount = '', $dateCreated = '', $loyaltyCreditable = '', $expirationDate = '', $sequenceNo = '') {

        switch ($voucherTypeID) {
            //ticket
            case 1 :
                return array('Amount' => (float) $amount,
                    'VoucherTicketBarcode' => $voucherCode,
                    'DateCreated' => $dateCreated,
                    'VoucherTypeID' => (int) $voucherTypeID,
                    'LoyaltyCreditable' => (int) $loyaltyCreditable,
                    'ExpirationDate' => $expirationDate,
                    'SequenceNo' => $sequenceNo,
                    'TransactionMessage' => $transMsg,
                    'ErrorCode' => (int) $errorCode,
                );
                break;
            //coupon
            case 2 :
                return array("VoucherTicketBarcode" => $voucherCode,
                    "Amount" => (float) $amount,
                    "DateCreated" => $dateCreated,
                    "VoucherTypeID" => (int) $voucherTypeID,
                    "LoyaltyCreditable" => (int) $loyaltyCreditable,
                    "ExpirationDate" => $expirationDate,
                    "SequenceNo" => $sequenceNo,
                    "TransactionMessage" => $transMsg,
                    "ErrorCode" => (int) $errorCode,
                );
                break;
            default :
                return array("VoucherTicketBarcode" => "",
                    "Amount" => 0,
                    "DateCreated" => "",
                    "VoucherTypeID" => "",
                    "ExpirationDate" => "",
                    "TransactionMessage" => $transMsg,
                    "ErrorCode" => (int) $errorCode,
                );
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
    public function getUseRetMsg($voucherTypeID, $transMsg, $errorCode) {
        switch ($voucherTypeID) {
            case 1 :
                //todo for ticket API
                break;
            case 2 :
                return array("TransactionMessage" => $transMsg,
                    "ErrorCode" => $errorCode);
                break;
            default :
                $errorCode = 7;
                $transMsg = "Invalid voucher type";
                return array("TransactionMessage" => $transMsg,
                    "ErrorCode" => (int) $errorCode);

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
    public function getUseReturnMsg($voucherTypeID, $transMsg, $errorCode) {
        switch ($voucherTypeID) {
            case 1 :
                return array("TransactionMessage" => $transMsg,
                    "ErrorCode" => $errorCode);
                break;
            case 2 :
//                  return array("TransMsg"=>$transMsg,
//                               "ErrorCode"=>$errorCode);
                break;
            default :
                $errorCode = 7;
                $transMsg = "Invalid voucher type";
                return array("TransactionMessage" => $transMsg,
                    "ErrorCode" => (int) $errorCode);

                break;
        }
    }

    /**
     * @author elperez
     * @datecreated 10/24/2013
     * @purpose Get coupon status
     * @param int $AID cashier id
     * @param str $voucherCode coupon code
     * @param int $source cashier | kapi | egm
     * @param str $trackingid unique tracking info
     * @param str $voucherTypeID ticket | coupon
     * @return array
     */
    public function verifyCoupon($AID, $voucherCode, $source, $trackingid, $voucherTypeID) {
        $couponModel = new CouponModel();

        $amount = 0;
        $dateCreated = "";
        $loyaltyCreditable = 0;
        $couponStatus = "";

        //verify coupon date expiration
        $dateNow = date("Y-m-d H:i:s");

        $couponResults = $couponModel->chkCouponAvailable($voucherCode);

        if (is_array($couponResults)) {
            $amount = (float) $couponResults['Amount'];
            $loyaltyCreditable = (int) $couponResults['IsCreditable'];
            $couponStatus = $couponResults['Status'];
            $fromDateExpiry = (string) $couponResults['ValidFromDate'];
            $toDateExpiry = (string) $couponResults['ValidToDate'];
            $dateCreated = (string) $couponResults['DateCreated'];

            //check the status of coupon
            //0-Inactive, 1-Active, 2-Deactivated, 3-Used, 4-Cancelled, 5-Reimbursed
            switch ($couponStatus) {
                //inactive
                case "0" :
                    $transMsg = "Inactive Coupon.";
                    $errorCode = 4;
                    break;
                //unused
                case "1" :
                    //check if date is already expired
                    if ($dateNow >= $fromDateExpiry && $dateNow <= $toDateExpiry) {
                        $errorCode = 0;
                        $transMsg = "Coupon is unclaimed.";
                    } else {
                        $transMsg = "Coupon was already expired.";
                        $errorCode = 1;
                    }

                    break;
                //deactivated
                case "2":
                    $errorCode = 12;
                    $transMsg = "Coupon is deactivated.";
                    break;
                //used
                case "3" :
                    $errorCode = 5;
                    $transMsg = "Coupon is already used";
                    break;
                //cancelled
                case "4":
                    $errorCode = 13;
                    $transMsg = "Coupon is cancelled";
                    break;
                //reimbursed
                case "5":
                    $errorCode = 14;
                    $transMsg = "Coupon is already reimbursed";
                    break;
                default :
                    $errorCode = 6;
                    $transMsg = "Invalid coupon";
                    break;
            }
        } else {
            $transMsg = "Coupon is invalid.";
            $errorCode = 11;
        }

        $details = "VerifyVoucher : " . $transMsg;
        AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, (int) $couponStatus);
        return $this->getVerifyRetMsg(2, $errorCode, $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
    }

    /**
     * @param str $voucherCode coupon / ticket type
     * @param int $aid account id
     * @param str $trackingID unique tracking info
     * @param int $terminalID egm / kronus terminal
     * @param int $source egm, kronus, kapi
     * @param int $voucherTypeID coupon / ticket type
     * @param int $mid membership ID
     * @param int $siteID site ID 
     * @return array transaction result
     */
    public function useCoupon($voucherCode, $aid, $trackingID, $terminalID, $source, $voucherTypeID, $mid, $siteID) {
        $couponModel = new CouponModel();
        $couponStatus = "";

        $couponResults = $couponModel->chkCouponAvailable($voucherCode);

        if (is_array($couponResults)) {
            $amount = (float) $couponResults['Amount'];
            $loyaltyCreditable = (int) $couponResults['IsCreditable'];
            $couponStatus = $couponResults['Status'];
            $fromDateExpiry = (string) $couponResults['ValidFromDate'];
            $toDateExpiry = (string) $couponResults['ValidToDate'];

            //verify coupon date expiration
            $dateNow = date("Y-m-d H:i:s");

            //check the status of coupon
            switch ($couponStatus) {
                //inactive
                case "0" :
                    $transMsg = "Inactive Coupon.";
                    $errorCode = 4;
                    break;
                //unused
                case "1" :
                    //check if date is already expired
                    if ($dateNow >= $fromDateExpiry && $dateNow <= $toDateExpiry) {

                        $isTrackingIdExists = $couponModel->isTrackingIDExists($trackingID);

                        if ($isTrackingIdExists == 0) {
                            $isUpdated = $couponModel->usedCoupon($siteID, $terminalID, $mid, $aid, $trackingID, $voucherCode);

                            //check if transaction was successful
                            if ($isUpdated) {
                                $transMsg = "Transaction approved.";
                                $errorCode = 0;
                            } else {
                                $transMsg = "Transaction denied.";
                                $errorCode = 8;
                            }
                        } else {
                            $transMsg = "Tracking ID was already used.";
                            $errorCode = 9;
                        }
                    } else {
                        $transMsg = "Coupon was already expired.";
                        $errorCode = 1;
                    }
                    break;
                //used
                case "3" :
                    $transMsg = "Coupon is already used.";
                    $errorCode = 5;
                    break;
                //cancelled
                case "4":
                    $errorCode = 13;
                    $transMsg = "Coupon is cancelled";
                    break;
                //reimbursed
                case "5":
                    $errorCode = 14;
                    $transMsg = "Coupon is already reimbursed";
                    break;
                default :
                    $transMsg = "Invalid coupon";
                    $errorCode = 6;
                    break;
            }
        } else {
            $transMsg = "Coupon is invalid.";
            $errorCode = 11;
        }

        $details = "UseVoucher : " . $transMsg;
        AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, $couponStatus);
        return $this->getUseRetMsg(2, $transMsg, $errorCode);
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $vtcode
     * @param float $amount
     * @param str $datecreated
     * @param int $vouchertypeid
     * @param int $loyaltycreditable
     * @param str $transMsg
     * @param str $errCode
     * @return array
     */
    public static function addTicketResponse($amount, $vtcode, $datecreated, $expirationdate, $sequenceNo, $transMsg, $errCode) {
        return CJSON::encode(array('AddTicket' => (array('Amount' => $amount,
                'VoucherTicketBarcode' => $vtcode,
                'DateTime' => $datecreated,
                'ExpirationDate' => $expirationdate,
                'SequenceNo' => $sequenceNo,
                'TransactionMessage' => $transMsg,
                'ErrorCode' => (int) $errCode))));
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $vtcode
     * @param float $amount
     * @param str $datecreated
     * @param int $vouchertypeid
     * @param int $loyaltycreditable
     * @param str $transMsg
     * @param str $errCode
     * @return array
     */
    public static function verifyTicketResponse($vouchercode, $amount, $datecreated, $vouchertypeid, $loyaltycreditable, $expirationDate, $transMsg, $errCode) {
        return CJSON::encode(array('VerifyTicket' => (array('VoucherTicketBarcode' => $vouchercode,
                'Amount' => $amount,
                'DateCreated' => $datecreated,
                'VoucherTypeID' => $vouchertypeid,
                'LoyaltyCreditable' => $loyaltycreditable, 
                'ExpirationDate' => $expirationDate, 
                'TransactionMessage' => $transMsg,
                'ErrorCode' => (int) $errCode))));
    }

    /**
     * @author gvjagolino
     * @datecreated 10/27/13
     * @param str $voucherCode
     * @param str $trackingID
     * @param str $terminalID
     * @param int $source
     * @param int $voucherTypeID
     * @param int $mid
     * @param int $siteID
     * @return array
     */
    public function useTicket($voucherCode, $aid, $trackingID, $terminalID, $source, $voucherTypeID, $mid, $siteID, $terminalName, $amount = "") {
        $ticketModel = new TicketModel();
        $ticketStatus = "";

        $ticketResults = $ticketModel->chkTicketAvailable($voucherCode);
        
        if (is_array($ticketResults)) {
            $amount = (float) $ticketResults['Amount'];
            $loyaltyCreditable = (int) $ticketResults['IsCreditable'];
            $ticketStatus = $ticketResults['Status'];
            $fromDateExpiry = (string) $ticketResults['ValidFromDate'];
            $toDateExpiry = (string) $ticketResults['ValidToDate'];
            $ticketsiteid = (int) $ticketResults['SiteID'];

            //verify coupon date expiration
            $dateNow = date("Y-m-d H:i:s");

            if ($ticketsiteid == $siteID) {

                //check if date is already expired
                if ($dateNow >= $fromDateExpiry && $dateNow <= $toDateExpiry) {
                    
                    //check the status of coupon
                    switch ($ticketStatus) {
                        //inactive
                        case "0" :
                            $transMsg = "Inactive Ticket.";
                            $errorCode = 30;
                            break;
                        //unused
                        case "1" || "2":
                            $isTrackingIdExists = $ticketModel->isTrackingIDExists($trackingID);

                            if ($isTrackingIdExists == 0) {
                                $isUpdated = $ticketModel->usedTicket($siteID, $terminalID, $mid, $aid, $trackingID, $voucherCode, $ticketStatus);
                                //check if transaction was successful
                                if ($isUpdated) {
                                    $transMsg = "Transaction Approved.";
                                    $errorCode = 0;
                                } else {
                                    $transMsg = "Transaction denied.";
                                    $errorCode = 31;
                                }
                            } else {
                                $transMsg = "Tracking ID was already used.";
                                $errorCode = 32;
                            }
                            break;
                        //used
                        case "3" :
                            $transMsg = "Ticket is already used.";
                            $errorCode = 3;
                            break;
                        //cancelled
                        case "4":
                            $errorCode = 13;
                            $transMsg = "Ticket is cancelled";
                            break;
                        //reimbursed
                        case "5":
                            $errorCode = 14;
                            $transMsg = "Ticket is already reimbursed";
                            break;
                        default :
                            $transMsg = "Invalid ticket";
                            $errorCode = 6;
                            break;
                    }
                } else {
                    $transMsg = "Ticket was already expired.";
                    $errorCode = 33;
                }
            } else {
                $transMsg = "Ticket cannot be used in this site.";
                $errorCode = 34;
            }
        } else {
            $transMsg = "Ticket is invalid.";
            $errorCode = 11;
        }

        $details = "UseVoucher : " . $transMsg;
        AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, $ticketStatus);
        return $this->getUseReturnMsg(1, $transMsg, $errorCode);
    }

}

?>
