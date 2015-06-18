<?php

class MembersTemp extends BaseEntity
{

    public function MembersTemp()
    {
        $this->ConnString = 'membershiptemp';
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->TableName = "members";
        $this->Identity = "MID";
    }

    public function getMemberStatus($MID)
    {
        $query = "SELECT Status FROM $this->TableName WHERE MID = '$MID'";
        return parent::RunQuery($query);
    }    
    
    public function getTempCode($MID)
    {
        $query = "SELECT TemporaryAccountCode FROM $this->TableName WHERE MID = '$MID'";
        return parent::RunQuery($query);
    }

}

?>
