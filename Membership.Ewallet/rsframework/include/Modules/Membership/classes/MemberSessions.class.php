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
    
    public function checkSession($aid)
    {
        $query = "SELECT COUNT(MemberSessionID) as Count FROM membersessions WHERE MID = $aid";
        
        return parent::RunQuery($query);
    }
    
    public function updateSession($sessionid, $aid, $remoteip)
    {
        $null = null;
        $query = "UPDATE membersessions SET SessionID = '$sessionid', RemoteIP = '$remoteip', 
            DateStarted = 'NOW(6)', TransactionDate = 'NOW(6)', DateEnded = '$null' WHERE MID = $aid";
        
        return parent::ExecuteQuery($query);
    }
    
    public function checkifsessionexist($aid, $sessionid){
        $query = "SELECT COUNT(*) as Count FROM membersessions WHERE MID = $aid AND SessionID = '$sessionid'";
        
        return parent::RunQuery($query);
    }
    
    public function deleteifsessionexist($aid, $sessionid){
        $query = "DELETE FROM membersessions WHERE MID = $aid AND SessionID = '$sessionid'";
        
        return parent::ExecuteQuery($query);
    }
}
?>
