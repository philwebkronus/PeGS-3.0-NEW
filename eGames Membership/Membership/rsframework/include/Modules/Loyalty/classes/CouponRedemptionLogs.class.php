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
    
    /**
     * 
     * @param type $mid
     * @param type $rewarditemid
     * @param type $couponcount
     * @param type $source
     * @param type $redeemeddate
     * @param type $siteid
     * @param type $serviceid
     * @return type
     */
    function insertCouponLogs($mid, $rewarditemid, $couponcount,$source, $redeemeddate = '', $siteid='', $serviceid='')
    {
        $arrEntries["MID"] = $mid;
        $arrEntries["RewardItemID"] = $rewarditemid;
        $arrEntries["CouponCount"] = $couponcount;
        
        $arrEntries["ServiceID"] = $serviceid;
        $arrEntries["Source"] = $source;
        
        if($arrEntries["Source"] == 1){
            $arrEntries["CreatedByAID"] = $arrEntries["MID"];
            $arrEntries["DateCreated"] = $redeemeddate;
            $arrEntries["SiteID"] = $siteid;
        } else {
            $arrEntries["CreatedByAID"] = $_SESSION['userinfo']['AID'];
            $arrEntries["DateCreated"] = 'now_usec()';
            $arrEntries["SiteID"] = $_SESSION['userinfo']['SiteID'];
        }
        
        $retval = parent::Insert($arrEntries);
        if ($this->HasError)
        {
            App::SetErrorMessage($this->getError());
        }
        return $retval;
    }
    
    function getSource($CouponRedemptionLogID){
        $query = "SELECT MID, Source FROM $this->TableName
                            WHERE CouponRedemptionLogID = $CouponRedemptionLogID";
        $result = parent::RunQuery($query);
        return $result[0];
    }

    /**
     * 
     * @param type $CouponRedemptionLogID
     * @param type $source
     * @param type $status
     * @param type $mid
     * @return type
     */
    function updateLogsStatus($CouponRedemptionLogID, $source, $status, $mid='',$totalitempoints='',
                                                        $serialcode='',$securitycode='',$validfrom='',$validto=''){
       
        if($source == 1){
            $updatedbyaid = $mid;
        } else {
            $updatedbyaid = $_SESSION['userinfo']['AID'];
        }
        $query = "UPDATE $this->TableName SET Status = $status, DateUpdated = now_usec(),UpdatedByAID = $updatedbyaid,
                            SerialCode='$serialcode', SecurityCode='$securitycode', ValidFrom='$validfrom', ValidTo='$validto', RedeemedPoints=$totalitempoints
                            WHERE CouponRedemptionLogID = $CouponRedemptionLogID";
        return parent::ExecuteQuery($query);
    }
}
?>
