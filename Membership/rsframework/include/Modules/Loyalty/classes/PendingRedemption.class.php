<?php

/**
 * @Description: Class for manipulating pendingredemption table
 * @Author: aqdepliyan
 * @DateCreated: 2013-09-18
 */

class PendingRedemption extends BaseEntity
{
    
    public function PendingRedemption()
    {
        $this->TableName = "pendingredemption";
        $this->ConnString = "loyalty";
        $this->Identity = "MID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    /**
     * @Description: Check Pending Redemption Per MID
     * @param int $MID
     * @return bool
     */
    public function checkPendingRedemption($MID){
        $query = "SELECT * FROM $this->TableName WHERE MID=".$MID;
        $result = parent::RunQuery($query);
        if(isset($result[0]['MID']) && $result[0]['MID'] != ''){
            return true;
        } else {
            return false;
        }
    }
}
?>
