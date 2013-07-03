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
