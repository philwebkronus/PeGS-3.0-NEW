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
}

?>
