<?php

class OthersMemberInfo extends BaseEntity
{
    function OthersMemberInfo()
    {
        $this->ConnString = "membership";
        $this->TableName = "othersmemberinfo";
    }
    
    function GetNonMemberName($search)
    {
        $query = "select * from $this->TableName where FirstName like '%$search%'";
        
        $result = parent::RunQuery($query);
        return $result;
    }
}
?>
