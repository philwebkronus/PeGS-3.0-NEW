<?php

/**
 * @Description: Class for manipulating pendingredemption table
 * @Author: aqdepliyan
 * @DateCreated: 2013-09-18
 */

class PendingRedemption extends BaseEntity
{
    
    public function PendingRedemption()
    {
        $this->TableName = "pendingredemption";
        $this->ConnString = "loyalty";
        $this->Identity = "MID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    /**
     * @Description: Check Pending Redemption Per MID
     * @param int $MID
     * @return bool
     */
    public function checkPendingRedemption($MID){
        $query = "SELECT * FROM $this->TableName WHERE MID=".$MID;
        $result = parent::RunQuery($query);
        if(isset($result[0]['MID']) && $result[0]['MID'] != ''){
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Manual Transaction for Pending Transaction
     * @param type $mid
     * @param type $status_raffle
     * @param type $status_crl
     * @param type $points 0 - ADD, 1 - LESS
     * @return string|boolean
     */
    public function manualPendingTrans($mid, $status_raffle, $status_crl, $points = NULL){
         $errorLogger = new ErrorLogger();
         $this->StartTransaction();
         $AID = $_SESSION['userinfo']['AID'];
         try {
             
             //get coupon redemption log id
             $query = "SELECT CouponRedemptionLogID FROM loyaltydb.couponredemptionlogs 
                       WHERE MID = $mid AND Status = 0 LIMIT 1";
             $result = parent::RunQuery($query);
             
             if(isset($result[0]['CouponRedemptionLogID'])){
                 
                 $couponID = $result[0]['CouponRedemptionLogID'];
                 
                 //get coupon batch ID
                 $querybatch = "SELECT CouponBatchID FROM loyaltydb.couponbatches WHERE Status = 1";
                 
                 $resultbatch = parent::RunQuery($querybatch);
                 
                 if (isset($resultbatch[0]['CouponBatchID']))
                 {
                    $rafflecoupon = "rafflecoupons"."_".$resultbatch[0]['CouponBatchID'];
                    
                    //update rafflecoupons
                    $query2 = "UPDATE loyaltydb.$rafflecoupon SET Status = $status_raffle 
                               WHERE CouponRedemptionLogID = $couponID";
                    
                    $israffleupdated = parent::ExecuteQuery($query2);
                    
                    //validate if raffle coupon was updated
                    if($israffleupdated) {
                        //Get Redeemed Points
                        $getPoints = "SELECT crl.CouponCount, ri.RequiredPoints 
                                      FROM loyaltydb.couponredemptionlogs crl
                                      INNER JOIN loyaltydb.rewarditems ri 
                                      ON crl.RewardItemID = ri.RewardItemID
                                      WHERE crl.MID = $mid AND crl.Status = 0 LIMIT 1";

                        $result = parent::RunQuery($getPoints);
                        
                        $redeempoints = 0;
                        if(isset($result[0]['CouponCount']) && isset($result[0]['RequiredPoints'])){
                            $redeempoints =  $result[0]['CouponCount'] * $result[0]['RequiredPoints'];
                        }
                                
                        //update couponredemptionlogs
                        $query = "UPDATE loyaltydb.couponredemptionlogs SET Status = $status_crl, DateUpdated = NOW_USEC(), 
                                  UpdatedByAID = $AID 
                                  WHERE MID = $mid AND Status = 0";
                        $iscrlupdated = parent::ExecuteQuery($query);
                        
                        //validate is successfully updated
                        if($iscrlupdated){
                            
                            //Check if add option is set
                            if (isset($points) && !is_null($points))
                            {
                               
                                if ($redeempoints > 0)
                                {    
                                    //Update Redeemed Points
                                    //Check if Add or Less
                                    if ($points == 0) //ADD
                                    {
                                        $query = "UPDATE loyaltydb.membercards set RedeemedPoints = RedeemedPoints - $redeempoints, 
                                                  CurrentPoints = CurrentPoints + $redeempoints WHERE MID = $mid AND  Status IN (1,5)";
                                    }
                                    else //LESS
                                    {
                                        $query = "UPDATE loyaltydb.membercards set RedeemedPoints = RedeemedPoints + $redeempoints, 
                                                  CurrentPoints = CurrentPoints - $redeempoints WHERE MID = $mid AND  Status IN (1,5)";
                                    }
                                    $ispointsupdated = parent::ExecuteQuery($query);
                                    if ($ispointsupdated)
                                    {
                                        $this->CommitTransaction();
                                        return true;
                                    }
                                    else
                                    {
                                        $this->RollBackTransaction();
                                        $errMsg = "Failed to update player's points";
                                        $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                                        return $errMsg;
                                    }
                                }
                                else
                                {
                                    $this->RollBackTransaction();
                                    $errMsg = "Failed to update player's points";
                                    $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                                    return $errMsg;
                                }
                            }
                            else
                            {
                                $this->CommitTransaction();
                                return true;
                            }
                            
                        } else {
                            $this->RollBackTransaction();
                            $errMsg = "Failed to update transaction table for void transaction method";
                            $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                            return $errMsg;
                        }
                        
                    } else {
                        $this->RollBackTransaction();
                        $errMsg = "Failed to update transaction table for void transaction method";
                        $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                        return $errMsg;
                    }
                    
                 } else {
                    $this->RollBackTransaction();
                    $errMsg = "No active coupon batch";
                    $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                    return $errMsg;
                }
                
             } else {
                 $this->RollBackTransaction();
                 $errMsg = "No pending coupon transaction";
                 $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                 return $errMsg;
             }
             
         }catch(Exception $e){
             $this->RollBackTransaction();
             $errorLogger->log($errorLogger->logdate, "error", $e->getMessage());
             $errMsg = "Failed to start transaction for manual void transaction method";
             return $errMsg;
         }
    }
    /**
     * Update Raffle Status whether void or valid
     * @param type $mid
     * @param type $status_raffle
     */
    public function updateRaffleStat($mid, $status_raffle)
    {
        $errorLogger = new ErrorLogger();
        $this->StartTransaction();
        
        try
        {
            //get coupon redemption log id
             $query = "SELECT CouponRedemptionLogID FROM loyaltydb.couponredemptionlogs 
                       WHERE MID = $mid AND Status = 0 LIMIT 1";
             $result = parent::RunQuery($query);
             
             if(isset($result[0]['CouponRedemptionLogID'])){
                 
                 $couponID = $result[0]['CouponRedemptionLogID'];
                 
                 //get coupon batch ID
                 $querybatch = "SELECT CouponBatchID FROM loyaltydb.couponbatches WHERE Status = 1";
                 
                 $resultbatch = parent::RunQuery($querybatch);
                 
                 if (isset($resultbatch[0]['CouponBatchID']))
                 {
                    $rafflecoupon = "rafflecoupons"."_".$resultbatch[0]['CouponBatchID'];
                    
                    //update rafflecoupons
                    $query2 = "UPDATE loyaltydb.$rafflecoupon SET Status = $status_raffle 
                               WHERE CouponRedemptionLogID = $couponID";
                    
                    $israffleupdated = parent::ExecuteQuery($query2);
                    if ($israffleupdated)
                    {
                        $this->CommitTransaction();
                        return true;
                    }
                    else
                    {
                        $this->RollBackTransaction();
                        $errMsg = "No pending coupon transaction";
                        $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                        return $errMsg;
                    }
                 }
                 else
                 {
                    $this->RollBackTransaction();
                    $errMsg = "No active coupon batch";
                    $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                    return $errMsg;
                 }
             }
             else
             {
                $this->RollBackTransaction();
                $errMsg = "No pending coupon transaction";
                $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                return $errMsg;    
             }
        }
        catch(Exception $e)
        {
            $this->RollBackTransaction();
            $errorLogger->log($errorLogger->logdate, "error", $e->getMessage());
            $errMsg = "Failed to start transaction for manual void transaction method";
            return $errMsg;
        }
    }
    public function updatePoints($mid, $points)
    {
        $errorLogger = new ErrorLogger();
        $this->StartTransaction();
        //Get Redeemed Points
        $getPoints = "SELECT crl.CouponCount, ri.RequiredPoints 
                      FROM loyaltydb.couponredemptionlogs crl
                      INNER JOIN loyaltydb.rewarditems ri 
                      ON crl.RewardItemID = ri.RewardItemID
                      WHERE crl.MID = $mid AND crl.Status = 0 LIMIT 1";

        $result = parent::RunQuery($getPoints);

        $redeempoints = 0;
        if(isset($result[0]['CouponCount']) && isset($result[0]['RequiredPoints'])){
            $redeempoints =  $result[0]['CouponCount'] * $result[0]['RequiredPoints'];
        }
        if ($redeempoints > 0)
        {    
            //Update Redeemed Points
            //Check if Add or Less
            if ($points == 0) //ADD
            {
                $query = "UPDATE loyaltydb.membercards set RedeemedPoints = RedeemedPoints - $redeempoints, 
                          CurrentPoints = CurrentPoints + $redeempoints WHERE MID = $mid AND  Status IN (1,5)";
            }
            else //LESS
            {
                $query = "UPDATE loyaltydb.membercards set RedeemedPoints = RedeemedPoints + $redeempoints, 
                          CurrentPoints = CurrentPoints - $redeempoints WHERE MID = $mid AND  Status IN (1,5)";
            }
            $ispointsupdated = parent::ExecuteQuery($query);
            if ($ispointsupdated)
            {
                $this->CommitTransaction();
                return true;
            }
            else
            {
                $this->RollBackTransaction();
                $errMsg = "Failed to update player's points";
                $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                return $errMsg;
            }
        }
        else
        {
            $this->RollBackTransaction();
            $errMsg = "Failed to update player's points";
            $errorLogger->log($errorLogger->logdate, "error", $errMsg);
            return $errMsg;
        }
    }
    public function updateCRL($mid, $status_crl)
    {
        $errorLogger = new ErrorLogger();
        $this->StartTransaction();
        $AID = $_SESSION['userinfo']['AID'];
        //update couponredemptionlogs
        $query = "UPDATE loyaltydb.couponredemptionlogs SET Status = $status_crl, DateUpdated = NOW_USEC(), 
                  UpdatedByAID = $AID  
                  WHERE MID = $mid AND Status = 0";
        $iscrlupdated = parent::ExecuteQuery($query);

        //validate is successfully updated
        if($iscrlupdated){
            //Check if add option is set
            $this->CommitTransaction();
            return true;
            
        } else {
            $this->RollBackTransaction();
            $errMsg = "Failed to update transaction table for void transaction method";
            $errorLogger->log($errorLogger->logdate, "error", $errMsg);
            return $errMsg;
        }
    }
}
?>
