<?php

/**
 * @author owliber
 * @date Nov 6, 2012
 * @filename VoucherGeneration.php
 * 
 */

class VoucherGeneration extends CFormModel
{
    //Define global value for inactive, or newly generated vouchers
    CONST VOUCHER_STATUS_INACTIVE = 0;
    CONST VOUCHER_STATUS_ACTIVE = 1;
    
    CONST VOUCHER_TYPE_TICKET = 1;
    CONST VOUCHER_TYPE_COUPON = 2;
    
    CONST SOURCE_VMS = 4;
    
    /**
     * 
     * @return string
     */
    public function getAllGeneratedVoucherBatch()
    {
        $query = "SELECT VoucherBatchID
                ,   BatchNumber
                ,   Quantity
                ,   Amount
                ,   Amount * Quantity AS `Total`
                ,   DateGenerated
                ,   DateExpiry 
                FROM voucherbatch";
        $sql = Yii::app()->db->createCommand($query);
        $result = $sql->queryAll();
        
        return $result;
    }
    
    /**
     * 
     * @param int $batchno
     * @return int
     */
    public function activateVoucherBatch($batchno)
    {
        $conn = Yii::app()->db;
        
        $trx = $conn->beginTransaction();
        
        $activestatus = self::VOUCHER_STATUS_ACTIVE;
        $status = self::VOUCHER_STATUS_INACTIVE;
        
        $query = "UPDATE vouchers 
                  SET Status =:activestatus
                  WHERE BatchNumber =:batchno
                    AND Status =:status";
        
        $sql = $conn->createCommand($query);
        $sql->bindValues(array(":activestatus"=>$activestatus,
                                ":status"=>$status,
                                ":batchno"=>$batchno));
        $result = $sql->execute();
        
        if($result > 0)
        {
            try
            {
                $trx->commit();
                return 1;
            }
            catch(Exception $e)
            {
                $trx->rollback();
                return 0;
            }
        }
        
    }
    
    /**
     * 
     * @param int $batchno
     * @return int
     */
    public function deActivateVoucherBatch($batchno)
    {
        $conn = Yii::app()->db;
        
        $trx = $conn->beginTransaction();
        
        $activestatus = self::VOUCHER_STATUS_ACTIVE;
        $inactivestatus = self::VOUCHER_STATUS_INACTIVE;
        
        $query = "UPDATE vouchers 
                  SET Status =:inactivestatus
                  WHERE BatchNumber =:batchno
                    AND Status =:activestatus";
        
        $sql = $conn->createCommand($query);
        $sql->bindValues(array(":activestatus"=>$activestatus,
                                ":inactivestatus"=>$inactivestatus,
                                ":batchno"=>$batchno));
        $result = $sql->execute();
        
        if($result > 0)
        {
            try
            {
                $trx->commit();
                return 1;
            }
            catch(Exception $e)
            {
                $trx->rollback();
                return 0;
            }
        }
        
    }
    
    /**
     * 
     * @param int $batchno
     * @return string
     */
    public function getAllGeneratedVoucherBatchByBatchNo($batchno)
    {
        $query = "SELECT VoucherBatchID
                ,   BatchNumber
                ,   Quantity
                ,   Amount
                ,   Amount * Quantity AS `Total`
                ,   DateGenerated
                ,   DateExpiry 
                FROM voucherbatch
                WHERE BatchNumber =:batchno";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":batchno", $batchno);
        
        $result = $sql->queryRow();
        
        return $result;
    }
    
    /**
     * 
     * @param int $batchno
     * @return string
     */
    public function exportVoucherBatchByBatchNo($batchno)
    {
        $query = "SELECT VoucherCode
                ,   Amount
                ,   DateCreated
                ,   DateExpiry
                FROM vouchers
                WHERE BatchNumber =:batchno";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":batchno", $batchno);
        
        $result = $sql->queryAll();
        
        return $result;
    }
        
    /**
     * 
     * @param int $batchnumber
     * @return int
     */
    public static function getBatchStatus($batchnumber)
    {
        $query = "SELECT DISTINCT(Status) AS `Status` 
                  FROM vouchers
                  WHERE BatchNumber =:batchnumber";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":batchnumber", $batchnumber);
        $result = $sql->queryRow();
        
        return $result['Status'];
        
    }
    
    /**
     * 
     * @param int $batchno
     * @return string
     */
    public function getAllGeneratedVouchers($batchno = null)
    {
        if(isset($batchno))
        {
            $query = "SELECT VoucherID
                    , BatchNumber
                    , VoucherCode
                    , Amount
                    , DateCreated
                    , DateExpiry
                    , CASE Status
                      WHEN 0 THEN
                        'Inactive'
                      WHEN 1 THEN
                        'Active'
                      WHEN 3 THEN
                        'Used'
                      WHEN 5 THEN
                        'Reimbursed'
                      WHEN 6 THEN
                        'Expired'
                      WHEN 7 THEN
                        'Cancelled'
                      END `Status`
               FROM
                 vouchers
               WHERE
                 BatchNumber =:batchno";
            $sql = Yii::app()->db->createCommand($query);
            $sql->bindParam(":batchno", $batchno);
            
        }
        else
        {
            $query = "SELECT VoucherID
                    , BatchNumber
                    , VoucherCode
                    , Amount                    
                    , SUM(Amount) AS `Total`
                    , DateCreated
                    , DateExpiry
                    , CASE Status
                      WHEN 1 THEN
                        'Active'
                      WHEN 2 THEN
                        'Void'
                      WHEN 3 THEN
                        'Used'
                      WHEN 4 THEN
                        'Claimed'
                      WHEN 6 THEN
                        'Expired'
                      WHEN 7 THEN
                        'Cancelled'
                      END `Status`
               FROM
                 vouchers
               WHERE
                 BatchNumber IS NOT NULL";
            $sql = Yii::app()->db->createCommand($query);
            
        }
        
        
        return $sql->queryAll();
    }
    
    /**
     * 
     * @param int $voucherType
     * @param string $dateFrom
     * @param string $dateTo
     * @param in $status
     * @return string
     */
    public function getAllGeneratedVouchersByStatus($dateFrom,$dateTo,$status)
    {
        $query = "SELECT VoucherID
                    , BatchNumber
                    , VoucherCode
                    , Amount
                    , DateCreated
                    , DateExpiry
                    , CASE Status
                      WHEN 0 THEN
                        'Inactive'
                      WHEN 1 THEN
                        'Active'
                      WHEN 2 THEN
                        'Void'
                      WHEN 3 THEN
                        'Used'
                      WHEN 4 THEN
                        'Claimed'
                      WHEN 5 THEN
                        'Reimbursed'
                      WHEN 6 THEN
                        'Expired'
                      WHEN 7 THEN
                        'Cancelled'
                      END `Status`
               FROM
                 vouchers
               WHERE DateCreated >=:datefrom
               AND DateCreated <:dateto
               AND Status =:status
               AND VoucherTypeID =:vouchertype";
        
            $sql = Yii::app()->db->createCommand($query);
            $sql->bindValues(array(":vouchertype"=>self::VOUCHER_TYPE_COUPON,
                                    ":datefrom"=>$dateFrom,
                                    ":dateto"=>$dateTo,
                                    ":status"=>$status,
                ));
            
            $result = $sql->queryAll();
            
            return $result;
    }
    
    /**
     * 
     * @param string $dateFrom
     * @param string $dateTo
     * @return string
     */
    public function getAllGeneratedVouchersByRange($dateFrom,$dateTo)
    {
        $query = "SELECT VoucherID
                    , BatchNumber
                    , VoucherCode
                    , Amount
                    , DateCreated
                    , DateExpiry
                    , CASE Status
                        WHEN 0 THEN
                          'Inactive'
                        WHEN 1 THEN
                          'Active'
                        WHEN 2 THEN
                          'Void'
                        WHEN 3 THEN
                          'Used'
                        WHEN 4 THEN
                          'Claimed'
                        WHEN 5 THEN
                          'Reimbursed'
                        WHEN 6 THEN
                          'Expired'
                        WHEN 7 THEN
                          'Cancelled'
                      END `Status`
               FROM
                 vouchers
               WHERE DateCreated >=:datefrom
               AND DateCreated <:dateto
               AND VoucherTypeID =:vouchertype";
        
            $sql = Yii::app()->db->createCommand($query);
            $sql->bindValues(array(":vouchertype"=>self::VOUCHER_TYPE_COUPON,
                                    ":datefrom"=>$dateFrom,
                                    ":dateto"=>$dateTo,
                ));
            
            $result = $sql->queryAll();
            
            return $result;
    }
    
    /**
     * Get and return the last batch number
     * @return int
     */
    public function getLastBatchNo()
    {
        $query = "SELECT max(BatchNumber) AS BatchNumber FROM voucherbatch";
        $sql = Yii::app()->db->createCommand($query);
        $result = $sql->queryRow();
        
        if($result['BatchNumber'] > 0)
            return $result['BatchNumber'] + 1;
        else
            return 1;
    }
    
    /**
     * Check if voucher code is already existing
     * @param int $voucherCode
     * @return boolean
     */
    public function VoucherCodeIsUnique($voucherCode)
    {
        $query = "SELECT * FROM vouchers
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
     * @param int $terminalCode
     * @param double $amount
     * @return string
     */
    public function generateVoucherBatch($quantity, $amount, $validity, $AID)
    {
        $conn = Yii::app()->db;
        
        $trx = $conn->beginTransaction();

        try
        {
            //Get the last batch number + 1
            $batchnumber = $this->getLastBatchNo();

            //Voucher Type
            $voucherType = self::VOUCHER_TYPE_COUPON;
            
            /**
             * Check if voucherType is loyalty creditable or not
             */            
            $loyaltyCredit = Utilities::loyaltyCreditable($voucherType);
            
            $source = self::SOURCE_VMS;

            //Set Expiry date
            $dateExpiry = date('Y-m-d', strtotime('+'.$validity.' DAYS'));

            $query = "INSERT INTO voucherbatch (BatchNumber, Quantity, Amount, DateGenerated, DateExpiry, GeneratedByAID)
                      VALUES (:batchnumber, :quantity, :amount, now_usec(), :dateexpiry, :AID)";

            $sql = $conn->createCommand($query);
            $sql->bindValues(array(':batchnumber'=>$batchnumber,
                                    ':quantity'=>$quantity,
                                    ':amount'=>$amount,
                                    ':dateexpiry'=>$dateExpiry,
                                    ':AID'=>$AID
                                ));

            $result = $sql->execute();

            //Affected rows
            if($result == 1)
            {
                //Status = Inactive
                $status = self::VOUCHER_STATUS_INACTIVE; 

                for ($i = 0; $i < $quantity; $i++)
                {
                    //Generated random code
                    $voucherCode = CodeGenerator::generateCode(18,1);

                    //Query to insert into vouchers table
                    $query = "INSERT INTO vouchers (BatchNumber, VoucherTypeID, VoucherCode, Amount, CreatedByAID, DateCreated, DateExpiry, Source, LoyaltyCreditable, Status)
                             VALUES (:batchnumber, :voucherType, :voucherCode, :amount, :AID, now_usec(), :dateExpiry, :source, :loyaltycredit, :status)";

                    $sql = $conn->createCommand($query);
                    $sql->bindValues(array(":batchnumber"=>$batchnumber,
                                            ":voucherType"=>$voucherType,
                                            ":dateExpiry"=>$dateExpiry,
                                            ":voucherCode"=>$voucherCode[0],
                                            ":AID"=>$AID,
                                            ":amount"=>$amount,
                                            ":source"=>$source,
                                            ":loyaltycredit"=>$loyaltyCredit,
                                            ":status"=>$status,
                        ));

                    $sql->execute();

                }

            }

            $trx->commit();

            return array("TransMsg"=>"Voucher batch generation is successful",
                         "TransCode"=>0,
                         "BatchNumber"=>$batchnumber);
        }
        catch(Exception $e)
        {
            $trx->rollBack();            

            return array("TransMsg"=>"Voucher batch generation failed : " . $e->getMessage(),
                         "TransCode"=>1,
                         "BatchNumber"=>"");


        }
        
        
    }
    
}
?>
