<?php

class MembershipTempInfo extends BaseEntity
{

    public function MembershipTempInfo()
    {
        $this->ConnString = 'membershiptemp';
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->TableName = "memberinfo";
        $this->Identity = "MID";
    }

    public function getMemberInfo($MID)
    {
        $query = "SELECT
                    m.*,
                    mi.*
                  FROM memberinfo mi
                    INNER JOIN members m ON mi.MID = m.MID
                  WHERE m.MID = $MID";

        return parent::RunQuery($query);
    }

    public function checkIfSFIDExists($SFID)
    {
        $query = "SELECT FirstName, MiddleName, LastName, Birthdate, Address1, MobileNumber, Email, IdentificationID, IdentificationNumber, DateCreated, SFID, MID FROM $this->TableName WHERE SFID = '$SFID'";
        return parent::RunQuery($query);
    }

}

?>
