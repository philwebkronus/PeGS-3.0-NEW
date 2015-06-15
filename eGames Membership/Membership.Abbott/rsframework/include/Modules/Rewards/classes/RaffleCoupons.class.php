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
        $this->ConnString = "rewardsdb";
        $this->Identity = "RaffleCouponID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    /**
     * @Description: Get all the available raffle coupons.
     * @param int $RewardItemID
     * @param int $CouponQuantity
     * @return array
     */
    function getAvailableCoupons($RewardItemID,$CouponQuantity){
        $query = "SELECT RaffleCouponID FROM $this->TableName
                            WHERE Status = 0 AND RewardItemID = $RewardItemID 
                            ORDER BY CouponNumber LIMIT $CouponQuantity";
        return parent::RunQuery($query);
    }
    
    /**
     * @Description: For updating raffle coupon status
     * @Author: aqdepliyan
     * @DateCreated: 2013-07-13
     * @DateModified: 2013-10-24
     * @param int $Quantity
     * @param int $CouponRedemptionLogID
     * @param int $RewardItemID
     * @param int $updatedbyaid
     * @return boolean/string
     */
    public function updateRaffleCouponsStatus($Quantity, $CouponRedemptionLogID,$RewardItemID, $updatedbyaid) {
        
        $unlockedmsg = '';
        $resultmsg = '';
        $returningarray = array();
        //Status Code 1-Error in locking, 2-Error in unlocking, 3-Error in updating, 4-serialcode unavailable
        
        $lock = "LOCK TABLES $this->TableName WRITE;";
        $islocked = parent::ExecuteQuery($lock);
        if ($this->HasError && $islocked == false){
            $returningarray["IsSuccess"] = $islocked;
            $returningarray["StatusCode"] = 1;
            return $returningarray;
        } else {
            
            //Check if the available coupon is greater than or match with the quantity avail by the player.
            $availablecoupon = $this->getAvailableCoupons($RewardItemID, $Quantity);
            
            if(count($availablecoupon) == $Quantity) {
                //Proceed with update query if the table is already locked.
                $update = "UPDATE $this->TableName SET CouponRedemptionLogID = $CouponRedemptionLogID, Status = 1, UpdatedByAID = $updatedbyaid, DateUpdated = now_usec() 
                                    WHERE CouponRedemptionLogID IS NULL AND Status = 0 AND RewardItemID = $RewardItemID ORDER BY CouponNumber LIMIT $Quantity";
                parent::ExecuteQuery($update);
                $result = $this->AffectedRows;

                if ($this->HasError && $result == 0) {
                    $resultmsg = App::GetErrorMessage();

                    //Unlock the table if the update query failed.
                    $unlock = "UNLOCK TABLES;";
                    $isunlocked = parent::ExecuteQuery($unlock);
                    if ($this->HasError && $isunlocked == false) {
                        $unlockedmsg = App::GetErrorMessage();
                    }
                    $result == 0 ? $returnvalue = 3: $returnvalue = 2;
                    $returningarray["IsSuccess"] = false;
                    $returningarray["StatusCode"] = $returnvalue;
                    return $returningarray;
                }
                
                //Unlock the table if the update query succeed.
                $unlock = "UNLOCK TABLES;";
                $isunlocked = parent::ExecuteQuery($unlock);
                if ($this->HasError && $isunlocked == false) {
                    $unlockedmsg = App::GetErrorMessage();
                    $returningarray["IsSuccess"] = $isunlocked;
                    $returningarray["StatusCode"] = 2;
                    return $returningarray;
                }
                
                //Return Results Value if the Lock, Update and Unlock Query Succeeds.
                $returningarray["IsSuccess"] = true;
                $returningarray["StatusCode"] = $result;
                return $returningarray;
            } else {
                //Unlock the table if the update query succeed.
                $unlock = "UNLOCK TABLES;";
                $isunlocked = parent::ExecuteQuery($unlock);
                if ($this->HasError && $isunlocked == false) {
                    $unlockedmsg = App::GetErrorMessage();
                    $returningarray["IsSuccess"] = $isunlocked;
                    $returningarray["StatusCode"] = 2;
                    return $returningarray;
                }
                $returningarray["IsSuccess"] = false;
                $returningarray["StatusCode"] = 4;
                return $returningarray;
            }

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

}

?>
