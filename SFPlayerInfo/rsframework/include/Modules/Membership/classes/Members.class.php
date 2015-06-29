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
                m.MID = mi.MID WHERE m.DateCreated > '$date'"; var_dump($query);exit;
        return parent::RunQuery($query);
    }
}

?>
