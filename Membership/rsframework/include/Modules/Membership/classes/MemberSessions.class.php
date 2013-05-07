<?php

class MemberSessions extends BaseEntity
{
    function MemberSessions()
    {
        $this->TableName = "membersessions";
        $this->ConnString = "membership";
        $this->Identity = "MemberSessionID";
    }
    
    function getMemberSessions($mid)
    {
        $query = "select * from membersessions where MID = $mid";
        $result = parent::RunQuery($query);
        
        return $result;
    }
}
?>
