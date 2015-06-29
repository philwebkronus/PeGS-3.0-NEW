<?php

/*
 * @author : owliber
 * @date : 2013-04-18
 */

class MemberInfo extends BaseEntity
{

    public function MemberInfo()
    {
        $this->ConnString = 'membership';
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->TableName = "memberinfo";
        $this->Identity = "MID";
    }

    /**
     *
     * @param int $MID - Member ID
     * @return string array of member details
     */
    public function getMemberInfo($MID)
    {
        $query = "SELECT
                    m.*,
                    mi.*
                  FROM $this->TableName mi
                    INNER JOIN members m ON mi.MID = m.MID
                  WHERE m.MID = $MID";

        return parent::RunQuery($query);
    }

    public function checkIfSFIDExists($SFID)
    {
        $query = "SELECT SFID FROM $this->TableName WHERE SFID = '$SFID'";
        return parent::RunQuery($query);
    }
       
    public function getUpdatedRecords($date)
    {
        $query = "SELECT FirstName, LastName, Birthdate, Status, MID, SFID FROM $this->TableName WHERE DateUpdated > '$date'";
        return parent::RunQuery($query);        
    }

}

?>
