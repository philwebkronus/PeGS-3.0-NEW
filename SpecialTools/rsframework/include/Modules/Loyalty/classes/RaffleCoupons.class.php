<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-06-05
 * Company: Philweb
 * ***************** */

class RaffleCoupons extends BaseEntity
{

    function RaffleCoupons()
    {

        $this->TableName = "rafflecoupons";
        $this->ConnString = "loyalty";
        $this->Identity = "RaffleCouponID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    function getAvailableCoupons($RewardItemID,$CouponQuantity){
        $query = "SELECT RaffleCouponID FROM $this->TableName
                            WHERE Status = 0 AND RewardItemID = $RewardItemID 
                            ORDER BY CouponNumber LIMIT $CouponQuantity";
        return parent::RunQuery($query);
    }
    
    function updateRaffleCouponsStatus($RaffleCouponID, $CouponRedemptionLogID,$RewardItemID, $updatedbyaid){
        $query = "LOCK TABLES $this->TableName WRITE;";
        parent::ExecuteQuery($query);
        if ($this->HasError){
            App::SetErrorMessage($this->getError());
            return false;
        }
        
        $query = "UPDATE $this->TableName SET CouponRedemptionLogID = $CouponRedemptionLogID, Status = 1, UpdatedByAID = $updatedbyaid,
                            DateUpdated = now_usec() WHERE Status = 0 AND RewardItemID = $RewardItemID AND RaffleCouponID = $RaffleCouponID";
        return parent::ExecuteQuery($query);
        
        if ($this->HasError) {
            App::SetErrorMessage($this->getError());
            return false;
        }
        
        $query = "UNLOCK TABLES;";
        parent::ExecuteQuery($query);
        if ($this->HasError){
            App::SetErrorMessage($this->getError());
            return false;
        }
    }

    function Redeem($CouponRedemptionLogID, $rewarditemid, $quantity)
    {
        $query = "LOCK TABLES $this->TableName WRITE;";
        parent::ExecuteQuery($query);
        if ($this->HasError)
        {
            App::SetErrorMessage($this->getError());
            return false;
        }

        $query = "update $this->TableName set CouponRedemptionLogID = $CouponRedemptionLogID, Status = 1
               where Status = 0 and RewardItemID = $rewarditemid order by CouponNumber limit $quantity";
        parent::ExecuteQuery($query);
        if ($this->HasError)
        {
            App::SetErrorMessage($this->getError());
            return false;
        }
        else
        {
            if ($this->AffectedRows == 0)
            {
                App::SetErrorMessage("Redemption Failed. Current points may not be sufficient or Item may not be available.");
            }
        }
        
        $query = "unlock tables;";
        parent::ExecuteQuery($query);
        if ($this->HasError)
        {
            App::SetErrorMessage($this->getError());
            return false;
        } else {
            $query = "UPDATE loyaltydb.couponredemptionlogs SET Status = 1, DateUpdated = now_usec() WHERE CouponRedemptionLogID = $CouponRedemptionLogID";
            parent::ExecuteQuery($query);
            if ($this->HasError)
            {
                App::SetErrorMessage($this->getError());
                return false;
            }
        }
    }
    
    function getCouponRedemptionInfo($couponredemptionlogid)
    {
        $query = "select min(CouponNumber) MinCouponNumber, max(CouponNumber) MaxCouponNumber, count(CouponNumber) CouponNumberCount from $this->TableName where CouponRedemptionLogID = $couponredemptionlogid";
        return parent::RunQuery($query);
    }
    
    function getMod10($stringval)
    {
        $oddvalue = $stringval[0] + $stringval[2];
        $evenvalue = $stringval[1] + $stringval[3];
        $mod1 = abs($oddvalue * $evenvalue) % 10;
        $oddvalue = $stringval[4] + $stringval[6];
        $evenvalue = $stringval[5];
        $mod2 = abs($oddvalue * $evenvalue) % 10;
        return $mod1 . $mod2;
    }
    /**
     * Update The Raffle Coupon Status
     * @param int $couponID The CouponRedemptionLogID
     * @param int $status New status
     * @return bool TRUE if updated, FALSE if failed
     * @author Mark Kenneth Esguerra
     * @date October 9, 2013
     */
    public function updateRaffleCouponStat($couponID, $status)
    {
        $query1 = "SELECT CouponBatchID FROM loyaltydb.couponbatches WHERE Status = 1 
                   ORDER BY DateGenerated DESC";
        $result = parent::RunQuery($query1);
        if (count($result) > 0)
        {
            $batch = $this->TableName."_".$result[0]['CouponBatchID'];
            $query2 = "UPDATE $batch SET Status = $status 
                      WHERE CouponRedemptionLogID = $couponID";
            
            return parent::ExecuteQuery($query2);
        }
    }
}

?>
