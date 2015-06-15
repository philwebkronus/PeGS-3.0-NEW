<?php

/*
 * @author : owliber
 * @date : 2013-06-14
 */

class AccountSessions extends BaseEntity
{
    public function AccountSessions()
    {
        $this->ConnString = "membership";
        $this->TableName = "accountsessions";
        $this->Identity = "AccountSessionID";
    }
    
    public function checkSession($aid)
    {
        $query = "SELECT COUNT(AccountSessionID) FROM accountsessions WHERE AID = $aid";
        
        return parent::RunQuery($query);
    }
    
    public function deleteSession($aid)
    {
        $query = "DELETE FROM accountsessions WHERE AID = $aid";
        
        return parent::ExecuteQuery($query);
    }
    
    public function updateSession($sessionid, $aid, $remoteip, $startdate)
    {
        $null = null;
        $query = "UPDATE accountsessions SET SessionID = '$sessionid', RemoteIp = '$remoteip', DateStarted = '$startdate', DateEnded = '$null' WHERE AID = $aid";
        
        return parent::ExecuteQuery($query);
    }
    
    public function checkifsessionexist($aid, $sessionid){
        $query = "SELECT COUNT(*) FROM accountsessions WHERE AID = $aid AND SessionID = '$sessionid'";
        
        return parent::RunQuery($query);
    }
    
    public function deleteifsessionexist($aid, $sessionid){
        $query = "DELETE FROM accountsessions WHERE AID = $aid AND SessionID = '$sessionid'";
        
        return parent::ExecuteQuery($query);
    }
    
}
?>
