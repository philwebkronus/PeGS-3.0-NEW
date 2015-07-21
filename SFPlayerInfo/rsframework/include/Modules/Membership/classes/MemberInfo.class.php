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
    
    //@date 07-13-2015
    //get encrypted info
    public function getEncPOCDetails($MID)
    {
        $query = "CALL membership.sp_select_data(1, 1, 0, $MID, 'FirstName,MiddleName,LastName,NickName,Email,AlternateEmail,MobileNumber,AlternateMobileNumber,Address1,Address2,IdentificationNumber', @ResultCode, @ResultMsg, @ResultFields)";
        $result = parent::RunQuery($query);
        
        $exp = explode(";", $result[0]['OUTfldListRet']);
        $arrdtls = array(0 => array('MID' => $MID, 
                                    'FirstName' => $exp[0], 
                                    'MiddleName' => $exp[1], 
                                    'LastName' => $exp[2], 
                                    'NickName' => $exp[3],
                                    'Email' => $exp[4], 
                                    'AlternateEmail' => $exp[5], 
                                    'MobileNumber' => $exp[6], 
                                    'AlternateMobileNumber' => $exp[7], 
                                    'Address1' => $exp[8], 
                                    'Address2' => $exp[9], 
                                    'IdentificationNumber' => $exp[10]));
        
        return $arrdtls;
    }
    
    //get MID using SFID
    public function getMIDUsingSFID($SFID)
    {
        $query = "SELECT MID FROM $this->TableName WHERE SFID = '$SFID'";
        $result = parent::RunQuery($query);
        return $result[0]['MID'];
    }
    //get non-encrypted player data
    public function getGenericInfo($MID) {
        $query = "SELECT
                    m.Status, m.DateCreated, mi.Gender, mi.Birthdate, mi.IsCompleteInfo, mi.DateVerified, 
                    mi.Gender, mi.IsSmoker, mi.Birthdate, mi.IdentificationID, mi.OccupationID, mi.NationalityID, 
                    mi.RegionID, mi.CityID, m.IsVIP, mi.MemberInfoID        
                  FROM memberinfo mi
                    INNER JOIN members m ON mi.MID = m.MID
                  WHERE m.MID = $MID";
        return parent::RunQuery($query);
    }

}

?>
