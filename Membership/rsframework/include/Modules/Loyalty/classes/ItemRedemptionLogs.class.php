<?php
/*
* Description: Use For Manipulating Table ItemRedemptionLogs
* @author: aqdepliyan
* DateCreated: 2013-07-10 09:52AM
*/

Class ItemRedemptionLogs extends BaseEntity {
    
    function ItemRedemptionLogs() {
        $this->TableName = "itemredemptionlogs";
        $this->ConnString = "loyalty";
        $this->Identity = "ItemRedemptionLogID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
     function insertItemLogs($redeemeddate,$mid, $rewarditemid, $itemcount,$source, $siteid='', $serviceid='') {
        $arrEntries["MID"] = $mid;
        $arrEntries["RewardItemID"] = $rewarditemid;
        $arrEntries["ItemCount"] = $itemcount;
        $arrEntries["SiteID"] = $siteid;
        $arrEntries["ServiceID"] = $serviceid;
        $arrEntries["Source"] = $source;
        $arrEntries["DateCreated"] = $redeemeddate;
        if($arrEntries["Source"] == 1){
            $arrEntries["CreatedByAID"] = $arrEntries["MID"];
        } else {
            $arrEntries["CreatedByAID"] = $_SESSION['userinfo']['AID'];
        }
        
        $retval = parent::Insert($arrEntries);
        if ($this->HasError)
        {
            App::SetErrorMessage($this->getError());
        }
        return $retval;
    }
    
    function getSource($ItemRedemptionLogID){
        $query = "SELECT MID, Source FROM $this->TableName
                            WHERE ItemRedemptionLogID = $ItemRedemptionLogID";
        $result = parent::RunQuery($query);
        return $result[0];
    }


    function updateLogsStatus($ItemRedemptionLogID, $source, $status, $mid=''){
        if($source == 1){
            $updatedbyaid = $mid;
        } else {
            $updatedbyaid = $_SESSION['userinfo']['AID'];
        }
        $query = "UPDATE $this->TableName SET Status = $status, DateUpdated = now_usec(),UpdatedByAID = $updatedbyaid
                            WHERE ItemRedemptionLogID = $ItemRedemptionLogID";
        return parent::ExecuteQuery($query);
    }
    
}

?>
