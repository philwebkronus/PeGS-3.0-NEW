<?php
/**
* @Description: Use For Manipulating Table itemserialcodes
* @Author: aqdepliyan
* @DateCreated: 2013-09-10
*/

Class ItemSerialCodes extends BaseEntity {
    
    public function ItemSerialCodes() {
        $this->TableName = "itemserialcodes";
        $this->ConnString = "loyalty";
        $this->Identity = "ItemSerialCodeID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    /**
     * @Description: for fetching Pre-Gen Item Serial Code from database using Reward Item ID
     * @param int $rewarditemid
     * @return varchar
     */
    public function getSerialCodeForRedemptionCopy($rewarditemid){
        $query = "SELECT min(SerialCode) SerialCode, ItemSerialCodeID FROM $this->TableName WHERE Status = 1 AND RewardItemID =".$rewarditemid;
        return parent::RunQuery($query);
    }
    
    /**
     * @Description: for updating Pre-Gen Item Serial Code  status item serial code id
     * @param int $itemserialcodeid
     * @param int $UpdatedByAID
     * @return varchar
     */
    public function updateSerialCodeStatus($itemserialcodeid, $UpdatedByAID){
        
        $query = "LOCK TABLES $this->TableName WRITE, rewarditems  READ;";
        parent::ExecuteQuery($query);
        if ($this->HasError){
            App::SetErrorMessage($this->getError());
            return false;
        }
        
        $query = "UPDATE $this->TableName SET Status=2, UpdatedByAID=$UpdatedByAID, DateUpdated=now_usec()
                            WHERE ItemSerialCodeID=".$itemserialcodeid;
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
}

?>
