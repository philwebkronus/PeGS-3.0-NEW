<?php

/**
 * @author owliber
 * @date Oct 2, 2012
 * @filename VoucherAPI.php
 * 
 */

class VoucherAPI extends CFormModel
{
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
     * 
     * @param string $voucherCode
     * @return string
     */
    public function getVoucherInfo($voucherCode)
    {
        $query = "SELECT VoucherCode,TerminalID,Amount,DateCreated,DateExpiry,TrackingID
                  FROM vouchers WHERE voucherCode=:voucherCode";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":voucherCode", $voucherCode); 
        $result = $sql->queryRow();
        
        return $result;
    }
    
    /**
     * 
     * @param string $trackingID
     * @return string
     */
    public function getVoucherTrackingInfo($trackingID)
    {
        $query = "SELECT VoucherCode,TerminalID,Amount,DateCreated,DateExpiry
                  FROM vouchers WHERE TrackingID=:trackingID";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":trackingID", $trackingID);
        $result = $sql->queryRow();
        
        return $result;
    }
    
    /**
     * Check if voucher is already existing
     * @param int $voucherCode
     * @return boolean
     */
    public function VoucherCodeIsUnique($voucherCode)
    {
        $query = "SELECT VoucherCode FROM vouchers
                  WHERE VoucherCode =:vouchercode";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":vouchercode", $voucherCode);
        $result = $sql->queryAll();
        
        if(count($result) <= 0)
            return true;
        else
            return false;
    }
    
    /**
     * 
     * @param string $voucherCode
     * @return boolean
     */
    public function verifyVoucherExist($voucherCode)
    {
        $query = "SELECT * FROM vouchers WHERE voucherCode=:voucherCode";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":voucherCode", $voucherCode);
        $result = $sql->queryAll();
        
        if(count($result) > 0) //Voucher exist
        {
            return true;
        }else
            return false;
    }
        
    /**
     * 
     * @param string $voucherCode
     * @return boolean
     */
    public function verifyVoucherExpiry($voucherCode)
    {
        $query = "SELECT DateExpiry FROM vouchers
                  WHERE VoucherCode =:voucherCode";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":voucherCode", $voucherCode);
        $result = $sql->queryRow();
        
        $currentDate = date('Y-m-d');
        $expiryDate = $result["DateExpiry"];
        
        if($currentDate >= $expiryDate)
            $this->updateVoucherStatus($voucherCode, self::VOUCHER_STATUS_EXPIRED);

    }
        
    /**
     * 
     * @param string $voucherCode
     * @return type
     */
    public function verifyVoucherStatus($voucherCode)
    {
        $query = "SELECT `Status` FROM vouchers
                  WHERE VoucherCode =:voucherCode";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":voucherCode", $voucherCode);
        $result = $sql->queryRow();
        
        return $result["Status"];
        
    }
    /**
     * 
     * @param int $AID
     * @param string $voucherCode
     * @return boolean
     */
    public function checkVoucher($AID,$voucherCode)
    {
        /**
         *  Check if voucher is generated on the same 
         *  site for redemption and siteaccount AID is active
         */
        
        $query = "SELECT *
                    FROM
                      vouchers v
                    INNER JOIN terminals t
                    ON v.TerminalID = t.TerminalID
                    INNER JOIN sites s
                    ON s.SiteID = t.SiteID
                    INNER JOIN siteaccounts sa
                    ON s.SiteID = sa.SiteID
                    WHERE
                      sa.AID =:AID
                    AND v.VoucherCode =:voucherCode";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":voucherCode", $voucherCode);
        $sql->bindValue(":AID", $AID);
        
        $result = $sql->queryAll();
        
        //Check if voucher type return true if type is COUPON
        if($this->checkVoucherType($voucherCode) == self::VOUCHER_TYPE_COUPON)
        {
            return true;
        }
        else
        {
            if(count($result)>0)
            {
                return true;
            }else 
                return false;
        }
        
        
    }
    
    /**
     * 
     * @param int $voucherCode
     * @return int VoucherTypeID
     */
    public function checkVoucherType($voucherCode)
    {
        /**
         *  Check if voucher type is coupon or ticket
         */
        
        $query = "SELECT VoucherTypeID
                    FROM
                      vouchers
                    WHERE
                        VoucherCode =:voucherCode";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":voucherCode", $voucherCode);
        
        $result = $sql->queryRow();
        
        return $result['VoucherTypeID'];
        
    }
    
    
    public function updateVoucherStatus($voucherCode,$status)
    {
        $query = "UPDATE vouchers
                  SET Status =:status
                  WHERE VoucherCode =:voucherCode";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":status", $status);
        $sql->bindParam(":voucherCode", $voucherCode);
        $sql->execute();        
    }
    
    /**
     * 
     * @param string $trackingID
     * @return boolean
     */
    public function checkTrackingID($trackingID)
    {
        $query = "SELECT * FROM vouchers WHERE TrackingID =:trackingID";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":trackingID", $trackingID);
        $result = $sql->queryAll();
        
        if(count($result) > 0)
        {
            return true;
        }else
            return false;
    }
    
    /**
     * 
     * @param int $AID
     * @param string $trackingid
     * @return boolean
     */
    public function verifyTrackingID($AID,$trackingid,$source)
    {        
        /**
         * WITHDRAWAL CHECKING
         * Generation checking if connection was disrupted
         * and there is no returned value. System will use
         * verify API to check if trackingid was generated
         */

        $query = "SELECT *
                    FROM
                      vouchers v
                    INNER JOIN terminals t
                    ON v.TerminalID = t.TerminalID
                    INNER JOIN sites s
                    ON s.SiteID = t.SiteID
                    INNER JOIN siteaccounts sa
                    ON s.SiteID = sa.SiteID
                    WHERE
                      sa.AID =:AID
                    AND v.TrackingID =:trackingid";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValues(array(
                ":trackingid"=>$trackingid,
                ":AID"=>$AID,
            ));
                
        $result = $sql->queryAll();
        
        if(count($result)>0)
        {
            $row = $result[0];
            $amount = $row["Amount"];
            $datecreated = $row["DateCreated"];
            $voucherCode = $row['VoucherCode'];
            
            $details = 'Verify tracking ID';
            AuditLog::logAPITransactions(7, $source, $details, $AID, $trackingid, 1);
                    
            return array("VoucherCode"=>$voucherCode,
                         "Amount"=> floatval($amount),
                         "DateCreated"=>$datecreated,
                         "TransMsg"=>"Verification successful",
                         "ErrorCode"=>intval(0));            
        }
        else
        {
            $details = 'Tracking ID '.$trackingid.' does not exist.';
            AuditLog::logAPITransactions(7, $source, $details, $AID, $trackingid, 2);
            
            return array("VoucherCode"=>"",
                         "Amount"=>"",
                         "DateCreated"=>"",
                         "TransMsg"=>"TrackingID does not exist.",
                         "ErrorCode"=>intval(1)); 
        }
                   
    }
    
    /**
     * 
     * @param type $AID
     * @param type $voucherCode
     * @return boolean
     */
    public function verifyVoucher($AID,$voucherCode,$source,$trackingid)
    {
        
        /**
         * DEPOSIT/RELOAD CHECKING
         * Verify voucher if ACTIVE/VOID or NOT
         * If ACTIVE return result as Unclaimed
         * If VOID and parameter ALLOW_VOID_TICKET set to TRUE
         * then accept VOID TICKET on DEPOSIT or RELOAD
         */
        $query = "SELECT *
                    FROM
                      vouchers v
                    INNER JOIN terminals t
                    ON v.TerminalID = t.TerminalID
                    INNER JOIN sites s
                    ON s.SiteID = t.SiteID
                    INNER JOIN siteaccounts sa
                    ON s.SiteID = sa.SiteID
                    WHERE
                      sa.AID =:AID
                    AND v.VoucherCode =:vouchercode";

        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValues(array(
                ":vouchercode"=>$voucherCode,
                ":AID"=>$AID,
            ));
      
                
        $result = $sql->queryAll();
                
        if( count($result) > 0 )
        {
            $row = $result[0];
            $amount = $row['Amount'];
            $datecreated = $row['DateCreated'];
            $vouchertype = $row['VoucherTypeID'];
            $loyaltycredit = $row['LoyaltyCreditable'];
            
            //Check and update voucher expiry
            $this->verifyVoucherExpiry($voucherCode);
               
            switch($this->verifyVoucherStatus($voucherCode))
            {
                
                case self::VOUCHER_STATUS_ACTIVE:
                    
                    $details = 'Unclaimed voucher '.$voucherCode;
                    AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, 1);
            
                    return array("VoucherCode"=>$voucherCode,
                                 "VoucherTypeID"=>intval($vouchertype),
                                 "Amount"=>floatval($amount),
                                 "DateCreated"=>$datecreated,
                                 "LoyaltyCreditable"=>intval($loyaltycredit),
                                 "TransMsg"=>"Voucher is unclaimed",
                                 "ErrorCode"=>intval(0));  
                    break;
                case self::VOUCHER_STATUS_VOID:
                    if(Utilities::getParameters('ALLOW_VOID_TICKETS'))
                    {
                        $details = 'Unclaimed voucher '.$voucherCode;
                        AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, 1);
                        
                        return array("VoucherCode"=>$voucherCode,
                                     "VoucherTypeID"=>intval($vouchertype),
                                     "Amount"=>floatval($amount),
                                     "DateCreated"=>$datecreated,
                                     "LoyaltyCreditable"=>intval($loyaltycredit),
                                     "TransMsg"=>"Voucher is unclaimed",
                                     "ErrorCode"=>intval(0));
                    }
                    else
                    {
                        $details = 'Void voucher '.$voucherCode;
                        AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, 1);
                    
                        return array("VoucherCode"=>$voucherCode,
                                     "VoucherTypeID"=>intval($vouchertype),
                                     "Amount"=>floatval($amount),
                                     "DateCreated"=>$datecreated,
                                     "LoyaltyCreditable"=>intval($loyaltycredit),
                                     "TransMsg"=>"Voucher is void",
                                     "ErrorCode"=>intval(1));  
                    }
                    break;
                
                case self::VOUCHER_STATUS_INACTIVE:
                    
                    $details = 'Inactive voucher '.$voucherCode;
                    AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, 1);
                        
                    return array("VoucherCode"=>$voucherCode,
                                 "VoucherTypeID"=>intval($vouchertype),
                                 "Amount"=>floatval($amount),
                                 "DateCreated"=>$datecreated,
                                 "LoyaltyCreditable"=>intval($loyaltycredit),
                                 "TransMsg"=>"Voucher is not activated",
                                 "ErrorCode"=>intval(2));  
                    break;
                
                case self::VOUCHER_STATUS_USED:
                    
                    $details = 'Used voucher '.$voucherCode;
                    AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, 1);
                        
                    return array("VoucherCode"=>$voucherCode,
                                 "VoucherTypeID"=>intval($vouchertype),
                                 "Amount"=>floatval($amount),
                                 "DateCreated"=>$datecreated,
                                 "LoyaltyCreditable"=>intval($loyaltycredit),
                                 "TransMsg"=>"Voucher is already used",
                                 "ErrorCode"=>intval(3));  
                    break;
                
                case self::VOUCHER_STATUS_CLAIMED:
                    
                    $details = 'Claimed voucher '.$voucherCode;
                    AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, 1);
                        
                    return array("VoucherCode"=>$voucherCode,
                                 "VoucherTypeID"=>intval($vouchertype),
                                 "Amount"=>floatval($amount),
                                 "DateCreated"=>$datecreated,
                                 "LoyaltyCreditable"=>intval($loyaltycredit),
                                 "TransMsg"=>"Voucher is already claimed",
                                 "ErrorCode"=>intval(4));  
                    break;
                
                case self::VOUCHER_STATUS_REIMBURSED:
                    
                    $details = 'Reimbursed voucher '.$voucherCode;
                    AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, 1);
                        
                    return array("VoucherCode"=>$voucherCode,
                                 "VoucherTypeID"=>intval($vouchertype),
                                 "Amount"=>floatval($amount),
                                 "DateCreated"=>$datecreated,
                                 "LoyaltyCreditable"=>intval($loyaltycredit),
                                 "TransMsg"=>"Voucher is already reimbursed",
                                 "ErrorCode"=>intval(5));  
                    break;
                          
                case self::VOUCHER_STATUS_EXPIRED:
                    
                    $details = 'Expired voucher '.$voucherCode;
                    AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, 1);
                        
                    return array("VoucherCode"=>$voucherCode,
                                 "VoucherTypeID"=>intval($vouchertype),
                                    "Amount"=>floatval($amount),
                                    "DateCreated"=>$datecreated,
                                    "LoyaltyCreditable"=>intval($loyaltycredit),
                                    "TransMsg"=>"Voucher is already expired",
                                    "ErrorCode"=>intval(6));
                    
                    break;
                
                case self::VOUCHER_STATUS_CANCELLED:
                    
                    $details = 'Cancelled voucher '.$voucherCode;
                    AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, 1);
                        
                    return array("VoucherCode"=>$voucherCode,
                                 "VoucherTypeID"=>intval($vouchertype),
                                 "Amount"=>floatval($amount),
                                 "DateCreated"=>$datecreated,
                                 "LoyaltyCreditable"=>intval($loyaltycredit),
                                 "TransMsg"=>"Voucher is cancelled",
                                 "ErrorCode"=>intval(7));  
                    break;
            }
            
        }
                
        if(!$this->verifyVoucherExist($voucherCode))
        {
            $details = 'Voucher '.$voucherCode.' does not exist.';
            AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, 2);
                        
            return array("VoucherCode"=>"",
                         "VoucherTypeID"=>"",
                         "Amount"=>floatval(0),
                         "DateCreated"=>"",
                         "LoyaltyCreditable"=>"",
                         "TransMsg"=>"Voucher does not exist",
                         "ErrorCode"=>intval(8)); //Voucher not exist
            
        }else
            
            $details = 'Other site';
            AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, 2);
            
             return array("VoucherCode"=>$voucherCode,                 
                         "VoucherTypeID"=>"",
                         "Amount"=>floatval(0),
                         "DateCreated"=>"",
                         "LoyaltyCreditable"=>"",
                         "TransMsg"=>"Other site.",
                         "ErrorCode"=>intval(9)); //Generated and claimed from other site
        
                   
    }
           
    /**
     * KAPI will update voucher status into USED once
     * DEPOSIT or RELOAD is successful
     * 
     * @param integer $AID
     * @param string $voucherCode
     * @param integer $status
     * @return string
     */
    public function useVoucher($terminalID,$AID,$voucherCode,$trackingID,$source)
    {
        
        $conn = Yii::app()->db;
        
        $trx = $conn->beginTransaction();
        
        if($this->checkVoucherType($voucherCode) == self::VOUCHER_TYPE_TICKET)
        {
            $status = $this->verifyVoucherStatus($voucherCode) == self::VOUCHER_STATUS_ACTIVE ? 'active' : 'void';
            
            $query = "UPDATE vouchers 
                        SET DateUsed = now_usec(),
                            Status =:status,
                            ProcessedByAID =:AID
                        WHERE VoucherCode =:voucherCode
                          AND Status =:currentstatus";
            
            $sql = $conn->createCommand($query);
            $sql->bindValue(":voucherCode", $voucherCode);
            $sql->bindValue(":AID", $AID);
            $sql->bindValue(":status", self::VOUCHER_STATUS_USED);
            
            if($status == 'active')
                $sql->bindValue(":currentstatus", self::VOUCHER_STATUS_ACTIVE);
            else
                $sql->bindValue(":currentstatus", self::VOUCHER_STATUS_VOID);
        }
        else
        {
            /**
             * If Voucher Type is COUPON, update the TerminalID and DateUsed
             */
            $query = "UPDATE vouchers 
                        SET DateUsed = now_usec(),
                            TerminalID =:terminalid,
                            Status =:status,
                            ProcessedByAID =:AID
                        WHERE VoucherCode =:voucherCode
                          AND Status =:activestatus";
            
            $sql = $conn->createCommand($query);
            $sql->bindValue(":terminalid", $terminalID);
            $sql->bindValue(":voucherCode", $voucherCode);
            $sql->bindValue(":AID", $AID);
            $sql->bindValue(":status", self::VOUCHER_STATUS_USED);
            $sql->bindValue(":activestatus", self::VOUCHER_STATUS_ACTIVE);
        }
        
        //Check and update voucher expiry
        $this->verifyVoucherExpiry($voucherCode);
        
        //Check if voucher code exist and generated from the same site
        switch($this->checkVoucher($AID,$voucherCode))
        {
            case true:

                switch($this->verifyVoucherStatus($voucherCode))
                {
                    case self::VOUCHER_STATUS_ACTIVE:

                        //Execute query
                        $sql->execute();

                         try
                         {                              
                             //Log to audit trail
                             $details = 'Use voucher '.$voucherCode;
                             AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, 1);
                
                             $trx->commit();

                             return array("TransMsg"=>"Transaction is approved",
                                          "ErrorCode"=>intval(0));
                         }
                         catch(Exception $e)
                         {
                             $trx->rollback();

                             //Log to audit trail
                             $details = 'Failed to use voucher '.$voucherCode;
                             AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, 2);
                             
                             $trx->commit();
                             
                             return array("TransMsg"=>$e->getMessage(),
                                          "ErrorCode"=>intval(1)); 
                         }

                        break;
                    
                    case self::VOUCHER_STATUS_VOID:
                        //Check variable ALLOW_VOID_TICKETS if set to TRUE
                        if(Utilities::getParameters('ALLOW_VOID_TICKETS')=='true')
                        {
                            //Execute query
                            $sql->execute();

                             try
                             {                              
                                 //Log to audit trail
                                 $details = 'Use voucher '.$voucherCode;
                                 AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, 1);

                                 $trx->commit();

                                 return array("TransMsg"=>"Transaction is approved",
                                              "ErrorCode"=>intval(0));
                             }
                             catch(Exception $e)
                             {
                                 $trx->rollback();

                                 $details = 'Failed to use voucher '.$voucherCode;
                                 AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, 2);
                                 
                                 $trx->commit();
                                 
                                 return array("TransMsg"=>$e->getMessage(),
                                              "ErrorCode"=>intval(1)); 
                             }
                        }
                        else
                        {
                            $details = 'Voucher '.$voucherCode. ' is void';
                            AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, 2);
                                 
                            $trx->commit();
                            
                            return array("TransMsg"=>"Voucher is void",
                                         "ErrorCode"=>intval(2));
                            
                        }
                        
                        break;

                    case self::VOUCHER_STATUS_INACTIVE:

                        $details = 'Voucher '.$voucherCode. ' is not activated';
                        AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, 2);
                           
                        $trx->commit();
                        
                        return array("TransMsg"=>"Voucher is not activated",
                                     "ErrorCode"=>intval(3));
                        break;

                    case self::VOUCHER_STATUS_USED:

                        $details = 'Voucher '.$voucherCode. ' is already used';
                        AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, 2);
                        
                        $trx->commit();
                        
                        return array("TransMsg"=>"Voucher is already used",
                                     "ErrorCode"=>intval(4));
                        break;

                    case self::VOUCHER_STATUS_CLAIMED:

                        $details = 'Voucher '.$voucherCode. ' is already claimed';
                        AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, 2);
                        
                        $trx->commit();
                        
                        return array("TransMsg"=>"Voucher is already claimed",
                                     "ErrorCode"=>intval(5));
                        break;

                    case self::VOUCHER_STATUS_REIMBURSED:

                        $details = 'Voucher '.$voucherCode. ' is already reimbursed';
                        AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, 2);
                        
                        $trx->commit();
                        
                        return array("TransMsg"=>"Voucher is already reimbursed",
                                     "ErrorCode"=>intval(6));
                        break;
                    
                    case self::VOUCHER_STATUS_EXPIRED:
                        
                        $details = 'Voucher '.$voucherCode. ' is already expired';
                        AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, 2);
                        
                        $trx->commit();
                        
                        return array("TransMsg"=>"Voucher is already expired",
                                         "ErrorCode"=>intval(7));
                        
                        break;

                    case self::VOUCHER_STATUS_CANCELLED:

                        $details = 'Voucher '.$voucherCode. ' is already cancelled';
                        AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, 2);
                        
                        $trx->commit();
                        
                        return array("TransMsg"=>"Voucher is already cancelled",
                                     "ErrorCode"=>intval(8));
                        break;

                }

                break;

            case false:

                $details = 'Voucher '.$voucherCode. ' does not exist';
                AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, 2);
                       
                $trx->commit();
                
                return array("TransMsg"=>"Voucher does not exist",
                             "ErrorCode"=>intval(9));

                break;

            default:

                $details = 'Voucher '.$voucherCode. ' does not exist';
                AuditLog::logAPITransactions(2, $source, $details, $voucherCode, $trackingID, 2);
                
                $trx->commit();
                
                return array("TransMsg"=>"Transaction denied",
                             "ErrorCode"=>intval(10));

                break;

        }
                          
    }
    
    /**
     * 
     * @param string $voucherCode
     * @param int $terminalCode
     * @param double $amount
     * @return string
     */
    public function generateVoucher($trackingID, $terminalID, $AID, $amount, $source)
    {
        $conn = Yii::app()->db;
        
        //Always check if tracking ID is already existing, otherwise generate else return records
        if($this->checkTrackingID($trackingID))
        {
            $row = $this->getVoucherTrackingInfo($trackingID);

            //Log to audit trail
            $details = 'Generate voucher '.$row["VoucherCode"];
            AuditLog::logAPITransactions(3, $source, $details, $row["VoucherCode"], $trackingID, 1);
            
            return array("GenerateVoucher"=>array("VoucherCode"=>$row["VoucherCode"],
                                                  "TerminalID"=>intval($row["TerminalID"]),
                                                  "Amount"=>floatval($row["Amount"]),
                                                  "DateCreated"=>$row["DateCreated"],
                                                  "DateExpiry"=>$row["DateExpiry"],    
                                                  "TransMsg"=>"Tracking ID is already existing.",
                                                  "ErrorCode"=>intval(1)
            ));
        }
        else
        {
            $trx = $conn->beginTransaction();
            
            //Generate a 18 random numeric combinations
            $voucherCode = CodeGenerator::generateCode(18,1);
            
            /**
             * Get and set the voucher type for generated
             * tickets from sites.
             */
            $voucherType = self::VOUCHER_TYPE_TICKET;
            
            /**
             * Check if voucherType is loyalty creditable or not
             */
            
            $loyaltyCredit = Utilities::loyaltyCreditable($voucherType);

            //Get duration in days
            $duration = Utilities::getParameters('EXPIRY_DAYS');
            

            //Set Expiry date
            $dateExpiry = date('Y-m-d H:i:s', strtotime('+'.$duration.' DAYS'));

            /**
             * Set status to Void but claimable or convertible to cash if lower
             * than the minimum allowed denomination, else set voucher status
             * to Active.
             */

            $status = $source == self::GENERATE_KAPI_SOURCE ? self::VOUCHER_STATUS_ACTIVE : self::VOUCHER_STATUS_VOID;

            //Query to insert into vouchers table
            $query = "INSERT INTO vouchers (VoucherTypeID, TrackingID, VoucherCode, TerminalID, Amount, CreatedByAID, DateCreated, DateExpiry, Source, LoyaltyCreditable, Status)
                     VALUES (:voucherType, :trackingID, :voucherCode, :terminalID, :amount, :AID, now_usec(), :dateExpiry, :source, :loyaltyCredit, :status)";

            $sql = $conn->createCommand($query);
            $sql->bindValue(":voucherType", $voucherType);
            $sql->bindValue(":dateExpiry", $dateExpiry);
            $sql->bindValue(":trackingID", $trackingID);
            $sql->bindValue(":voucherCode", $voucherCode[0]);
            $sql->bindValue(":terminalID", $terminalID);
            $sql->bindValue(":AID", $AID);
            $sql->bindValue(":amount", $amount);
            $sql->bindValue(":source", $source);
            $sql->bindValue(":loyaltyCredit", $loyaltyCredit);
            $sql->bindValue(":status", $status);

            $result = $sql->execute();

            //Affected rows
            if($result == 1)
            {

                try
                {   
                    //Log to audit trail
                    $details = 'Generate voucher '.$voucherCode[0];
                    AuditLog::logAPITransactions(3, $source, $details, $voucherCode[0], $trackingID, 1);

                    $trx->commit();

                    $row = $this->getVoucherInfo($voucherCode[0]);

                    return array("GenerateVoucher"=>array("VoucherCode"=>$row["VoucherCode"],
                                                          "TerminalID"=>intval($row["TerminalID"]),
                                                          "Amount"=>floatval($row["Amount"]),
                                                          "DateCreated"=>$row["DateCreated"],
                                                          "DateExpiry"=>$row["DateExpiry"],
                                                          "TransMsg"=>"Transaction successful",
                                                          "ErrorCode"=>intval(0)
                    ));
                }
                catch(Exception $e)
                {
                    $trx->rollBack();

                    //Log to audit trail
                    $details = 'Generation failed from terminal '.$terminalID;
                    AuditLog::logAPITransactions(3, $source, $details, $terminalID, $trackingID, 2);

                    $trx->commit();
                    
                    return array("GenerateVoucher"=>array("VoucherCode"=>"",
                                                          "TerminalID"=>intval($terminalID),
                                                          "Amount"=>floatval($amount),
                                                          "DateCreated"=>"",
                                                          "DateExpiry"=>"",
                                                          "TransMsg"=>$e->getMessage(),
                                                          "ErrorCode"=>intval(2)
                    ));
                }//End try


            }
            else
            {
                
                 //Log to audit trail
                 $details = 'Generation failed from terminal '.$terminalID;
                 AuditLog::logAPITransactions(3, $source, $details, $terminalID, $trackingID, 2);

                 $trx->commit();
                 
                 return array("GenerateVoucher"=>array("VoucherCode"=>"",
                                                        "TerminalID"=>intval($terminalID),
                                                        "Amount"=>floatval($amount),
                                                        "DateCreated"=>"",
                                                        "DateExpiry"=>"",
                                                        "TransMsg"=>"Transaction failed",
                                                        "ErrorCode"=>intval(3)
                 ));

            }//End if result
            
        } //End outer else statement
   
    }
    
    public function getStackerSessionID($terminalID)
    {
        $query = "SELECT a.EGMStackerSessionID
                    FROM
                      egmstackersessions a
                    INNER JOIN egmmachineinfo b
                    ON a.EGMMachineInfoID = b.EGMMachineInfoId_PK
                    WHERE
                      (b.TerminalID =:terminalid
                      OR b.TerminalIDVIP =:terminalid)
                      AND a.IsEnded = 0";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":terminalid", $terminalID);
        $result = $sql->queryRow();
        return $result['EGMStackerSessionID'];
    }
    
    /**
     * 
     * @param int $cashInType
     * @param int $voucherCode
     * @param double $amount
     * @param int $terminalID
     * @param int $transactionType
     */
    public function logStackerCashIn($cashType,$amount,$terminalID,$transType,$trackingID,$voucherCode=null)
    {
        $conn = Yii::app()->db;
        
        $trx  = $conn->beginTransaction();
        
        $stackersessionID = $this->getStackerSessionID($terminalID);
        
        if(!empty($stackersessionID))
        {
            $query = "INSERT INTO egmstackerentries (EGMStackerSessionID,CashType,TrackingID,VoucherCode,Amount,TerminalID,TransactionType,TransactionDate)
                    VALUES (:stackersessionID, :cashType, :trackingID, :voucherCode, :amount, :terminalID, :transType, now_usec())";
            $sql = $conn->createCommand($query);
            $sql->bindValues(array(
                ':stackersessionID'=>$stackersessionID,
                ':cashType'=>$cashType,
                ':trackingID'=>$trackingID,
                ':voucherCode'=>$voucherCode,
                ':amount'=>$amount,
                ':terminalID'=>$terminalID,
                ':transType'=>$transType
            ));


            //Verify tracking id
            if(!$this->checkStackerTrackingID($trackingID,$voucherCode))
            {
                //Log to audit trail
                $details = 'New stacker entry';                  
                AuditLog::logAPITransactions(4, self::GENERATE_EGM_SOURCE, $details, $terminalID, $trackingID, 1);

                $result = $sql->execute();

                //Successful insert
                if($result == 1)
                {
                    //$machineid = Stacker::getMachineIDByTerminal($terminalID);

                    if($cashType == 3)//Cash
                    {
                        $query2 = "UPDATE egmstackersessions
                                    SET
                                      CashAmount = `CashAmount` + :cashamount, 
                                      TotalAmount = `TotalAmount` + :totalamount, 
                                      Quantity = `Quantity` + 1
                                    WHERE
                                      EGMStackerSessionID =:stackersessionID
                                      AND IsEnded = 0;";

                        $sql2 = $conn->createCommand($query2);
                        $sql2->bindValues(array(
                            ':stackersessionID'=>$stackersessionID,
                            ':cashamount'=>$amount,
                            ':totalamount'=>$amount
                        ));


                    }
                    else
                    {
                        $query2 = "UPDATE egmstackersessions
                                    SET
                                      TotalAmount = `TotalAmount` + :totalamount, 
                                      Quantity = `Quantity` + 1
                                    WHERE
                                      EGMStackerSessionID =:stackersessionID
                                      AND IsEnded = 0;";

                        $sql2 = $conn->createCommand($query2);
                        $sql2->bindValues(array(
                             ':stackersessionID'=>$stackersessionID,
                            ':totalamount'=>$amount
                        ));
                    }

                    //Log to audit trail
                    $details = 'Update stacker sessionID '.$stackersessionID;                               
                    AuditLog::logAPITransactions(4, self::GENERATE_EGM_SOURCE, $details, $terminalID, $trackingID, 1);

                    $sql2->execute();

                    try
                    {

                       $trx->commit();

                       return array("LogStacker"=>array("TransMsg"=>"Transaction successful",
                                                        "ErrorCode"=>intval(0)
                       ));
                    }
                    catch(Exception $e)
                    {
                       $trx->rollback();

                       //Log to audit trail
                        $details = 'Stacker entry log failed. '.$e->getMessage();                               
                        AuditLog::logAPITransactions(4, self::GENERATE_EGM_SOURCE, $details, $terminalID, $trackingID, 2);


                       return array("LogStacker"=>array("TransMsg"=>"Transaction failed",
                                                        "ErrorCode"=>intval(1)
                       ));
                    }
                }

            }
            else
            {
                   //Log to audit trail
                   $details = 'Duplicate voucher '.$voucherCode;                               
                   AuditLog::logAPITransactions(4, self::GENERATE_EGM_SOURCE, $details, $voucherCode, $trackingID, 2);

                   return array("LogStacker"=>array("TransMsg"=>"Duplicate voucher code",
                                                    "ErrorCode"=>intval(2)
                   ));
            }
        }
        else
        {
               //Log to audit trail
               $details = 'No active stacker session for terminalID '.$terminalID;        
               AuditLog::logAPITransactions(4, self::GENERATE_EGM_SOURCE, $details, $terminalID, $trackingID, 2);

               return array("LogStacker"=>array("TransMsg"=>"No active stacker session",
                                                "ErrorCode"=>intval(3)
               ));
        }
        
        
        
        
        
    }
    
    /**
     * 
     * @param string $trackingid
     * @param int $vouchercode
     * @return boolean
     */
    public function checkStackerTrackingID($trackingid,$vouchercode)
    {
        $query = "SELECT * FROM egmstackerentries WHERE TrackingID =:trackingid OR VoucherCode =:vouchercode";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":trackingid", $trackingid);
        $sql->bindValue(":vouchercode", $vouchercode);
        $result = $sql->queryAll();
     
        if(count($result) > 0)
            return true;
        else
            return false;
    }
    
    /**
     * 
     * @param string $trackingid
     * @return string
     */
    public function verifyStackerCashIn($trackingid)
    {
        $query = "SELECT * FROM egmstackerentries WHERE TrackingID =:trackingid";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":trackingid", $trackingid);
        $result = $sql->queryAll();
     
        if(count($result) > 0)
            return array("VerifyStacker"=>array("TransMsg"=>"Transaction successful",
                                                "ErrorCode"=>intval(0)
            ));
        else
            return array("VerifyStacker"=>array("TransMsg"=>"TrackingID does not exist.",
                                                "ErrorCode"=>intval(1)
            ));
    }
    
    public function getMachineID($machineid)
    {
        $query = "SELECT EGMMachineInfoId_PK FROM egmmachineinfo
                  WHERE Machine_Id =:machineid";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":machineid", $machineid);
        
        $result = $sql->queryRow();
        
        return $result['EGMMachineInfoId_PK'];
    }
    
    public function logStackerSession($machineid,$date,$action)
    {
        $conn = Yii::app()->db;
        
        $trx = $conn->beginTransaction();
        
        /** 
         * EGM Stacker is inserted
         */
        if($action == 1)
        {
            $details = 'New stacker session';
            $query = "INSERT INTO egmstackersessions (EGMMachineInfoID, DateStarted)
                      VALUES (:machineid, :date)";
            
        }
        else //EGM Stacker is removed
        {
            $details = 'Machine ID '.$machineid.' has ended.';
            $query = "UPDATE egmstackersessions 
                        SET DateEnded =:date,
                            IsEnded = 1
                        WHERE EGMMachineInfoID =:machineid
                            AND IsEnded = 0";
            
        }
        
        $sql = $conn->createCommand($query);
        $sql->bindValues(array(
                ':machineid'=>$machineid,
                ':date'=>$date
            ));
        
        $result = $sql->execute();
            
        if($result == 1)
        {
            
            AuditLog::logAPITransactions(6, self::GENERATE_EGM_SOURCE, $details, $machineid, null, 1);
            
            $trx->commit();

            return array("StackerSession"=>array("TransMsg"=>"Transaction successful.",
                                                 "ErrorCode"=>intval(0)
            ));
        }
        else
        {
            $details = 'Failed stacker session';
            AuditLog::logAPITransactions(6, self::GENERATE_EGM_SOURCE, $details, $machineid, null, 2);
            
            $trx->rollback();

            return array("StackerSession"=>array("TransMsg"=>"Transaction failed.",
                                                 "ErrorCode"=>intval(1)
            ));
        }
    }
    
}
?>
