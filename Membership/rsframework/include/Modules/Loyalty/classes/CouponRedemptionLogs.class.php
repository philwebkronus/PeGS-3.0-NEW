<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-06-04
 * Company: Philweb
 * ***************** */

class CouponRedemptionLogs extends BaseEntity
{
    
    function CouponRedemptionLogs()
    {
        $this->TableName = "couponredemptionlogs";
        $this->ConnString = "loyalty";
        $this->Identity = "CouponRedemptionLogID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    function Redeem($mid, $rewarditemid, $couponcount, $siteid, $serviceid)
    {
        $arrEntries["MID"] = $mid;
        $arrEntries["RewardItemID"] = $rewarditemid;
        $arrEntries["CouponCount"] = $couponcount;
        $arrEntries["SiteID"] = $siteid;
        $arrEntries["ServiceID"] = $serviceid;
        $retval = parent::Insert($arrEntries);
        if ($this->HasError)
        {
            App::SetErrorMessage($this->getError());
        }
        return $retval;
    }
}
?>
