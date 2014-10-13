<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CouponBatchModel
 *
 * @author elperez
 */
class CouponBatchModel {
   
    public function getActiveCouponBatch($couponBatchTable, $voucherCode){
        
        $query = "SELECT Status, Amount, DateCreated, LoyaltyCreditable FROM $couponBatchTable WHERE CouponCode = :couponcode";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValues(array(
                ":couponcode"=>$voucherCode
            ));
        return $sql->queryRow();
    }
    /**
     * Insert Coupons. Generate Coupon Codes and insert with other info
     * @param int $count Coupon to generate
     * @param mixed $couponcode Generated coupon code
     * @param int $amount Amount of each coupon per batch
     * @param int $distributiontag
     * @param int $iscreditable
     * @author Mark Kenneth Esguerra
     * @date October 31, 2013
     */
    public function insertCoupons($count, $amount, $distributiontag, $iscreditable, $promoname, $user, $status, $validfrom, $validto)
    {
        $model = new GenerationToolModel();

        $connection = Yii::app()->db;
        
        $pdo = $connection->beginTransaction();
        
        $firstquery = "INSERT INTO couponbatch (CouponCount,
                                                Amount,
                                                DistributionTagID,
                                                Status, 
                                                Promoname, 
                                                DateCreated,
                                                CreatedByAID
                        ) 
                        VALUES (:couponcount,
                                :amount,
                                :distributiontag,
                                :status, 
                                :promoname, 
                                NOW(6), 
                                :createdbyAID
                               )";
        $sql = $connection->createCommand($firstquery);
        $sql->bindParam(":couponcount", $count);
        $sql->bindParam(":amount", $amount);
        $sql->bindParam(":distributiontag", $distributiontag);
        $sql->bindParam(":promoname", $promoname);
        $sql->bindParam(":createdbyAID", $user);
        $sql->bindParam(":status", $status);
        
        $firstresult = $sql->execute();
        //Get Last Inserted ID
        $couponbatch = $connection->getLastInsertID();
        if ($firstresult > 0)
        {
            $ctrunique = 0;
            
            //Start generation of coupons
            for ($i = 0; $count > $i; $i++)
            { 
                //Generate Coupon Code
                $code = "";
                $code = $model->mt_rand_str(6);
                $couponcode = "C".$code;
//                if ($i != 500)
//                    $couponcode = "C".$code;
//                else
//                $couponcode = "CSLAA0H";
                try
                {
                    $secondquery = "INSERT INTO coupons (CouponBatchID,
                                                         CouponCode,
                                                         Amount,
                                                         Status,
                                                         DateCreated,
                                                         CreatedByAID, 
                                                         ValidFromDate, 
                                                         ValidToDate, 
                                                         IsCreditable
                                    ) VALUES(:couponbatch,
                                             :couponcode,
                                             :amount,
                                             :status,
                                             NOW(6),
                                             :aid, 
                                             :validfrom, 
                                             :validto, 
                                             :iscreditable
                                    )";
                    $sql = $connection->createCommand($secondquery);
                    $sql->bindParam(":couponbatch", $couponbatch);
                    $sql->bindParam(":couponcode", $couponcode);
                    $sql->bindParam(":amount", $amount);
                    $sql->bindParam(":aid", $user);
                    $sql->bindParam(":iscreditable", $iscreditable);
                    $sql->bindParam(":validfrom", $validfrom);
                    $sql->bindParam(":validto", $validto);
                    $sql->bindParam(":status", $status);
                    $secondresult = $sql->execute();
                    if ($secondresult > 0)
                    {
                        continue;
                    }
                    else
                    {
                        $pdo->rollback();
                        return array('TransCode' => 0,
                                     'TransMsg' => 'An error occured while generating the coupons [0001]');
                    }

                }
                catch(CDbException $e)
                {
                    //Check if error is 'Duplicate Key constraints violation'
                    $errcode = $e->getCode();
                    if ($errcode == 23000)
                    {
                        $ctrunique = 1;
                        try
                        {
                            $pdo->commit();
                            //get how many coupons have been generated before duplication
                            $querycount = "SELECT COUNT(CouponID) as CouponCount FROM coupons
                                           WHERE CouponBatchID = :couponbatch";
                            $sql = $connection->createCommand($querycount);
                            $sql->bindParam(":couponbatch", $couponbatch);
                            $couponcount = $sql->queryAll();
                            
                            $remainingcoupon = $count - $couponcount[0]['CouponCount'];

                            return array('TransCode' => 2, 
                                         'TransMsg' => 'Coupon already exist. There are '.$remainingcoupon.' remaining coupons 
                                                to generate. Click Continue.',
                                         'CouponBatchID' => $couponbatch,
                                         'RemainingCoupon' => $remainingcoupon,
                                         'Amount' =>$amount,
                                         'IsCreditable' => $iscreditable, 
                                         'Status' => $status, 
                                         'ValidFrom' => $validfrom, 
                                         'ValidTo' => $validto);
                        }
                        catch(CDbException $e)
                        {
                            $pdo->rollback();
                            return array('TransCode' => 0,
                                         'TransMsg' => 'An error occured while generating the coupons [0002]');
                        }
                    }
                    else
                    {
                        $pdo->rollback();
                        return array('TransCode' => 0, 'TransMsg' => 'An error occured while generating the coupons [0003]');
                    }
                }
            }
            
            if($ctrunique == 0){
                try
                {
                    $pdo->commit();

                    AuditLog::logTransactions(31, " - Generate Coupons");
                    return array('TransCode' => 1, 
                    'TransMsg' => 'Coupons successfully generated');
                }
                catch(PDOException $e)
                {
                    $pdo->rollback();
                    return array('TransCode' => 0,
                                 'TransMsg' => 'An error occured while generating the coupons [0004]');
                }
            }
            
        }
        else
        {
            $pdo->rollback();
            return array("TransCode" => 0, "TransMsg" => "An error occured while generating the coupons [0005]");
        }
    }
    
    /**
     * Get Coupon Batch/Batches
     * @param int $batchID Coupon Batch ID, can be NULL if all coupon batch list
     * @author Mark Kenneth Esguerra
     * @date November 4, 2013
     * @return array Array of Coupon batches
     */
    public function getCouponBatch($batchID = null, $amount = null, $distributiontag = null, $creditable = null, 
                                   $generatedfrom = null, $generatedto = null, $generatedby = null, $validfrom = null, 
                                   $validto = null, $status = null, $promoname = null, $start = null, $limit = null)
    {
        $couponModel = new CouponModel();
                
        $connection = Yii::app()->db;
        
        $pagination = "";
        if (!is_null($start) && !is_null($limit))
        {
            $pagination = "LIMIT $start, $limit";
        }
        
        if (is_null($batchID) && is_null($amount) && is_null($distributiontag) && 
            is_null($creditable) && is_null($generatedfrom) && is_null($generatedto) &&
            is_null($generatedby) && is_null($validfrom) && is_null($validto) &&
            is_null($status) && is_null($promoname))
        {
            $query = "SELECT CouponBatchID, 
                             CouponCount, 
                             Amount, 
                             DistributionTagID, 
                             Status, 
                             DateCreated, 
                             CreatedByAID, 
                             DateUpdated, 
                             UpdatedByAID, 
                             PromoName
                      FROM couponbatch 
                      ORDER BY CouponBatchID ASC 
                      $pagination";
            $command = $connection->createCommand($query);
            $result = $command->queryAll();
        }
        else if ($batchID != "")
        {
            $query = "SELECT CouponBatchID, 
                             CouponCount, 
                             Amount, 
                             PromoName, 
                             DistributionTagID, 
                             Status, 
                             DateCreated, 
                             CreatedByAID, 
                             DateUpdated, 
                             UpdatedByAID, 
                             PromoName 
                      FROM couponbatch 
                      WHERE CouponBatchID = :batchID 
                      $pagination";
            $command = $connection->createCommand($query);
            $command->bindValue(":batchID", $batchID);
            $result = $command->queryAll();
        }
        
        return $result;
   }
   /**
    * Get Batch Status
    * @param int $batch Batch ID
    * @return array Status
    * @author Mark Kenneth Esguerra
    * @date November 4, 2013
    */
   public function getBatchStatus($batch)
   {
       $connection = Yii::app()->db;
       
       $query = "SELECT Status FROM couponbatch WHERE CouponBatchID = :batch";
       $command = $connection->createCommand($query);
       $command->bindParam(":batch", $batch);
       $result = $command->queryRow();
       
       return $result;
   }
   /**
    * Change Coupon status
    * @param int $batch
    * @param int $status
    * @param date $validfrom
    * @param date $validfrom
    * @param date $validto
    * @return array TransCode and TransMsg
    * @author Mark Kenneth Esguerra
    * @date November 4, 2013
    */
   public function changeStatus($batch, $status, $validfrom, $validto, $user)
   {
        $connection = Yii::app()->db;

        $pdo = $connection->beginTransaction();
        //get current status
        $getstat = "SELECT cb.Status, c.ValidFromDate, c.ValidToDate  
                  FROM couponbatch cb 
                  INNER JOIN coupons c ON c.CouponBatchID = cb.CouponBatchID 
                  WHERE cb.CouponBatchID = :batch 
                  GROUP BY c.CouponBatchID";
        $sql = $connection->createCommand($getstat);
        $sql->bindParam(":batch", $batch);
        $currdetails = $sql->queryRow();
        //check if details are unchanged
        if ($currdetails['Status'] != $status 
          || $currdetails['ValidFromDate'] != $validfrom 
          || $currdetails['ValidToDate'] != $validto)
        {
            $firstquery = "UPDATE couponbatch SET Status = :status, 
                                             DateUpdated = NOW(6),
                                             UpdatedByAID = :AID
                      WHERE CouponBatchID = :batch";
            $command = $connection->createCommand($firstquery);
            $command->bindParam(":status", $status);
            $command->bindParam(":batch", $batch);
            $command->bindParam(":AID", $user);
            $firstresult = $command->execute();
            if ($firstresult > 0)
            {
                try
                {
                    $secondquery = "UPDATE coupons SET Status = :status,
                                                       ValidFromDate = :validfrom,
                                                       ValidToDate = :validto,
                                                       DateUpdated = NOW(6),
                                                       UpdatedByAID = :AID
                                    WHERE CouponBatchID = :batch AND Status <> 3";
                    $command = $connection->createCommand($secondquery);
                    $command->bindParam(":batch", $batch);
                    $command->bindParam(":status", $status);
                    $command->bindParam(":validfrom", $validfrom);
                    $command->bindParam(":validto", $validto);
                    $command->bindParam(":AID", $user);
                    $secondresult = $command->execute();
                    if ($secondresult > 0)
                    {
                        try
                        {
                            $pdo->commit();

                            AuditLog::logTransactions(32, " - Update Coupon Status Batch ".$batch);
                            return array('TransCode' => 1, 
                                         'TransMsg' => 'Coupons status successfully updated', 
                                         'BatchID' => $batch);
                        }
                        catch(CDbException $e)
                        {
                            $pdo->rollback();
                            return array('TransCode' => 0,
                                         'TransMsg' => $e->getMessage());
                        }
                    }
                    else
                    {
                        return array('TransCode' => 2,
                                     'TransMsg' => 'There are no coupons in batch');
                    }
                }
                catch (CDbException $e)
                {
                    $pdo->rollback();
                    return array('TransCode' => 0,
                                 'TransMsg' => 'An error occured while updating the status of the coupons');
                }
            }
            else
            {
                return array('TransCode' => 2,
                             'TransMsg' => 'Coupon status unchanged');
            }
        }
        else
        {
            return array('TransCode' => 2, 
                         'TransMsg' => 'Coupon details unchanged.');
        }
   }
}

?>
