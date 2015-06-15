<?php
/**
* @Description: Use For Manipulating Table itemserialcodes
* @Author: aqdepliyan
* @DateCreated: 2013-09-10
*/

Class ItemSerialCodes extends BaseEntity {
    
    public function ItemSerialCodes() {
        $this->TableName = "itemserialcodes";
        //$this->ConnString = "loyalty";
        $this->ConnString = "rewardsdb";
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
     * @Description: Get Available Serial Code Count
     * @param int $rewarditemid
     * @return varchar
     */
    public function getAvailableSerialCodeCount($rewarditemid, $quantity){
        $query = "SELECT ItemSerialCodeID FROM $this->TableName WHERE Status = 1 AND RewardItemID =".$rewarditemid." LIMIT ".$quantity;
        return parent::RunQuery($query);
    }
    
    /**
     * @Description: for updating Pre-Gen Item Serial Code  status item serial code id
     * @DateModified: 2013-10-24
     * @param int $UpdatedByAID
     * @return boolean/string
     */
    public function updateSerialCodeStatus($UpdatedByAID, $RewardItemID){
        
        $unlockedmsg = '';
        $resultmsg = '';
        $returningarray = array();
        //Status Code 1-Error in locking, 2-Error in unlocking, 3-Error in updating, 4-serialcode unavailable
        
        $lock = "LOCK TABLES $this->TableName WRITE, rewarditems  READ;";
        $islocked = parent::ExecuteQuery($lock);
        if ($this->HasError && $islocked == false){
            App::ClearStatus();
            $returningarray["IsSuccess"] = $islocked;
            $returningarray["StatusCode"] = 1;
            return $returningarray;
        } else {
            
            $ItemSerialCodeID = $this->getSerialCodeForRedemptionCopy($RewardItemID);
            if(isset($ItemSerialCodeID[0]["ItemSerialCodeID"]) && $ItemSerialCodeID[0]["ItemSerialCodeID"] != ""){
                //Proceed with update query if the table is already locked.
                $update = "UPDATE $this->TableName SET Status=2, UpdatedByAID=$UpdatedByAID, DateUpdated=NOW(6)
                                WHERE Status = 1 AND ItemSerialCodeID = ".$ItemSerialCodeID[0]["ItemSerialCodeID"];
                parent::ExecuteQuery($update);
                $result = $this->AffectedRows;
                
                if ($this->HasError && $result == 0) {
                    $resultmsg = App::GetErrorMessage();
                    App::ClearStatus();
                    //Unlock the table if the update query failed.
                    $unlock = "UNLOCK TABLES;";
                    $isunlocked = parent::ExecuteQuery($unlock);
                    if ($this->HasError && $isunlocked == false){
                        App::ClearStatus();
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
                if ($this->HasError && $isunlocked == false){
                    $unlockedmsg = App::GetErrorMessage();
                    $returningarray["IsSuccess"] = $isunlocked;
                    $returningarray["StatusCode"] = 2;
                    return $returningarray;
                }
                
                //Return Results Value if the Lock, Update and Unlock Query Succeeds.
                $returningarray["IsSuccess"] = true;
                $returningarray["StatusCode"] = $ItemSerialCodeID[0]["SerialCode"];
                return $returningarray;
            } else {
                //Unlock the table if the update query succeed.
                $unlock = "UNLOCK TABLES;";
                $isunlocked = parent::ExecuteQuery($unlock);
                if ($this->HasError && $isunlocked == false){
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
}

?>
