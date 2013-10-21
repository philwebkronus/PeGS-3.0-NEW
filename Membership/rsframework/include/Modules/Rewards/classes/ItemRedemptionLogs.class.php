<?php
/**
* @Description: Use For Manipulating Table ItemRedemptionLogs
* @Author: aqdepliyan
* @DateCreated: 2013-07-10 09:52AM
*/

Class ItemRedemptionLogs extends BaseEntity {
    
    function ItemRedemptionLogs() {
        $this->TableName = "itemredemptionlogs";
        //this->ConnString = "loyalty";
        $this->ConnString = "rewardsdb";
        $this->Identity = "ItemRedemptionLogID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    /**
     * @Description: For Inserting Redemptiion logs on database
     * @param date $redeemeddate
     * @param int $mid
     * @param int $rewarditemid
     * @param int $itemcount
     * @param int $source
     * @param int $siteid
     * @param int $serviceid
     * @return int
     */
     function insertItemLogs($redeemeddate,$mid, $rewarditemid, $itemcount,$source, $siteid='', $serviceid='') {
        $arrEntries["MID"] = $mid;
        $arrEntries["RewardItemID"] = $rewarditemid;
        $arrEntries["ItemCount"] = $itemcount;
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
        if ($this->HasError)
        {
            App::SetErrorMessage($this->getError());
        }
        return $retval;
    }
    
    /**
     * @Description: For fetching MID and Source(0-Cashier, 1-Player) from database
     * @param int $ItemRedemptionLogID
     * @return array
     */
    function getSource($ItemRedemptionLogID){
        $query = "SELECT MID, Source FROM $this->TableName
                            WHERE ItemRedemptionLogID = $ItemRedemptionLogID";
        $result = parent::RunQuery($query);
        return $result[0];
    }

    /**
     * @Description: For Updating Redemption Log Status(0-pending, 1, Successful and 2-Failed)
     * @param int $ItemRedemptionLogID
     * @param int $source
     * @param int $status
     * @param int $mid
     * @param int $totalitempoints
     * @param string $serialcode
     * @param string $securitycode
     * @param date $validfrom
     * @param date $validto
     * @return bool
     */
    function updateLogsStatus($ItemRedemptionLogID, $source, $status, $mid='', $totalitempoints = 0, $serialcode='', $securitycode='', $validfrom='', $validto=''){
        if($source == 1){
            $updatedbyaid = $mid;
        } else {
            $updatedbyaid = $_SESSION['userinfo']['AID'];
        }
        $query = "UPDATE $this->TableName SET Status = $status, DateUpdated = now_usec(),UpdatedByAID = $updatedbyaid, 
                            SerialCode='$serialcode', SecurityCode='$securitycode', ValidFrom='$validfrom', ValidTo='$validto', RedeemedPoints=$totalitempoints
                            WHERE ItemRedemptionLogID = $ItemRedemptionLogID";
        return parent::ExecuteQuery($query);
    }
    
    /*
* Description: get codes For Verification of items
* @author: JunJun S. Hernandez
* DateCreated: 2013-09-13 09:50AM
*/
   
    //get Serial Code for verification of items
    function getSerialCode($SerialCode){
        $query = "SELECT SerialCode FROM $this->TableName WHERE SerialCode = '$SerialCode'";
        return parent::RunQuery($query);
    }
    
    //get Security Code for verification of items
    function getSecurityCode($SecurityCode){
        $query = "SELECT SecurityCode FROM $this->TableName WHERE SecurityCode = '$SecurityCode'";
        return parent::RunQuery($query);
    }
    
    //get Both Serial and Security Code for verification of items
    function getItemCode($SerialCode, $SecurityCode){
        $query = "SELECT ItemRedemptionLogID, SerialCode, SecurityCode FROM $this->TableName WHERE SerialCode = '$SerialCode' AND SecurityCode = '$SecurityCode'";
        return parent::RunQuery($query);
    }
    
}

?>
