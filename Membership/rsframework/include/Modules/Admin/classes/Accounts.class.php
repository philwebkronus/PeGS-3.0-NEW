<?php

/*
 * @author : owliber
 * @date : 2013-05-17
 */

class Accounts extends BaseEntity
{
    
    public function Accounts()
    {
        $this->ConnString = "membership";
        $this->TableName = "accounts";
        $this->Identity = "AID";
    }
    
    public function validate($username)
    {        
        
        $query = "SELECT Status FROM accounts WHERE UserName = '$username'";
        $result = parent::RunQuery($query);
        
        return $result[0]['Status'];  
        
        
        
//        if( $status == AccountStatus::Active)        
//            return true;
//        else
//            return false;
        
    }
    
    public function authenticate($username, $password)
    {
        $query = "SELECT * FROM accounts 
                  WHERE UserName = '$username'
                    AND Password = md5('$password')";
        
        $result = parent::RunQuery($query);
        
        if(count($result) > 0)
            return true;
        else
            return false;
    }
    
    public function getAccountStatus($username)
    {
        $query = "SELECT * FROM accounts WHERE UserName = '$username'";
        $result = parent::RunQuery($query);
        
        return $result[0]['Status']; 
    }
    
}
?>
