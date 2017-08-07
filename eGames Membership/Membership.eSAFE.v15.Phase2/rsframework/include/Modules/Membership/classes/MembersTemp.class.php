<?php
/*
 *@Author: Claire Marie C. Tamayo
 *@DateCreated: 07/02/2017 15:30
 */
class MembersTemp extends BaseEntity 
{
    public function MembersTemp() 
    {
        $this->ConnString = 'membership';
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->TableName = "memberstemp";
    }

    public function checkifpregeneratedtemp($tempcode) 
    {
        $query = "SELECT TemporaryAccountCode, IsEdited FROM $this->TableName WHERE TemporaryAccountCode = '$tempcode' AND IsEdited = 0";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function updateIsEditedTag($tempcode, $updatedbyaid) 
    {
        $query = "UPDATE $this->TableName SET IsEdited = 1, DateUpdated = now(6), UpdatedByAID = '$updatedbyaid' "
                . "WHERE TemporaryAccountCode = '$tempcode' AND IsEdited = 0";
        $result = parent::ExecuteQuery($query);    
        return $result;
    }                
}
?>