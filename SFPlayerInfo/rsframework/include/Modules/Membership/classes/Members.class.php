<?php

class Members extends BaseEntity
{

    public function Members()
    {
        $this->ConnString = 'membership';
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->TableName = "members";
        $this->Identity = "MID";
    }

    public function getMemberStatus($MID)
    {
        $query = "SELECT Status FROM $this->TableName WHERE MID = '$MID'";
        return parent::RunQuery($query);
    }

    public function getUpdatedRecords($date)
    {
        $query = "SELECT m.IsVIP, m.DateMigrated, mi.MID, mi.SFID FROM $this->TableName m INNER JOIN membership_v1.memberinfo mi ON
                m.MID = mi.MID WHERE m.DateCreated > '$date'";
        return parent::RunQuery($query);
    }
    
    public function checkisVIPStatus($date)
    {
        $query = "SELECT isVIP FROM $this->TableName where";
        return parent::RunQuery($query);
    }
    
    public function checkIfEwallet($MID)
    {
        $query = "SELECT IsEwallet FROM $this->TableName WHERE MID = '$MID'";
        $result = parent::RunQuery($query);
        return $result[0];
    }
    
    public function getDateConverted($MID)
    {
        $query = "SELECT DateMigrated FROM $this->TableName WHERE MID = '$MID'";
        $result = parent::RunQuery($query);
        return $result[0];
    }
}

?>
