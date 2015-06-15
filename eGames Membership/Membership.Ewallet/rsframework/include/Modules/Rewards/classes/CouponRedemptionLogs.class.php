<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-06-04
 * Company: Philweb
 * ***************** */

class CouponRedemptionLogs extends BaseEntity
{
    
    public function CouponRedemptionLogs()
    {
        $this->TableName = "couponredemptionlogs";
        $this->ConnString = "rewardsdb";
        $this->Identity = "CouponRedemptionLogID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    /**
     * @Description: Insert new record in Coupon Redemption Logs
     * @param int $mid
     * @param int $rewarditemid
     * @param int $couponcount
     * @param int $source
     * @param string $redeemeddate
     * @param int $siteid
     * @param int $serviceid
     * @return int
     */
    public function insertCouponLogs($mid, $rewarditemid, $couponcount,$source, $redeemeddate = '', $siteid='', $serviceid='')
    {
        $arrEntries["MID"] = $mid;
        $arrEntries["RewardItemID"] = $rewarditemid;
        $arrEntries["CouponCount"] = $couponcount;
        $arrEntries["ServiceID"] = $serviceid;
        $arrEntries["Source"] = $source;
        $arrEntries["DateCreated"] = $redeemeddate;
        
        if($arrEntries["Source"] == 1){
            $arrEntries["CreatedByAID"] = $arrEntries["MID"];
            $arrEntries["SiteID"] = $siteid;
        } else {
            $arrEntries["CreatedByAID"] = $_SESSION['userinfo']['AID'];
            $arrEntries["SiteID"] = $_SESSION['userinfo']['SiteID'];
        }
        
        $retval = parent::Insert($arrEntries);
        if ($this->HasError && $retval == "")
        {
            App::SetErrorMessage($this->getError());
        }
        return $retval;
    }
    
    /**
     * @Description: Get the Source(via cashier - 0 or via portal - 1) of Redemption.
     * @Author: aqdepliyan
     * @param int $CouponRedemptionLogID
     * @return array
     */
    public function getSource($CouponRedemptionLogID){
        $query = "SELECT MID, Source FROM $this->TableName
                            WHERE CouponRedemptionLogID = $CouponRedemptionLogID";
        $result = parent::RunQuery($query);
        return $result[0];
    }

    /**
     * @Description: Update the Coupon Redemption Details and Status as (1-Success, 2-Failed)
     * @param int $CouponRedemptionLogID
     * @param int $source
     * @param int $status
     * @param int $mid
     * @return int
     */
    public function updateLogsStatus($CouponRedemptionLogID, $source, $status, $mid='',$totalitempoints='',
                                                        $serialcode='',$securitycode='',$validfrom=null,$validto=null){
       
        if($source == 1){
            $updatedbyaid = $mid;
        } else {
            $updatedbyaid = $_SESSION['userinfo']['AID'];
        }
        $query = "UPDATE ".$this->GetDBName().".".$this->TableName." SET Status = $status, DateUpdated = NOW(6),UpdatedByAID = $updatedbyaid,
                            SerialCode='$serialcode', SecurityCode='$securitycode', ValidFrom='$validfrom', ValidTo='$validto', RedeemedPoints=$totalitempoints
                            WHERE CouponRedemptionLogID = $CouponRedemptionLogID";
        parent::ExecuteQuery($query);
        return $this->AffectedRows;
    }
}
?>
