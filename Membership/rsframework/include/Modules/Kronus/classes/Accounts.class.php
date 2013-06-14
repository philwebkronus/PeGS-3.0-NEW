<?php

/*
 * @author : owliber
 * @date : 2013-06-14
 */

class Accounts extends BaseEntity
{
    public function Accounts()
    {
        $this->TableName = "accounts";
        $this->ConnString = "kronus";
        $this->Identity = "AID";
    }
    
    public function validate($username)
    {        
        
        $query = "SELECT Status FROM accounts WHERE UserName = '$username'";
        $result = parent::RunQuery($query);
        
        return $result;  
        
    }
    
    public function authenticate($username, $password)
    {
        $query = "SELECT * FROM accounts 
                  WHERE UserName = '$username'
                    AND Password = sha1('$password')";
        
        $result = parent::RunQuery($query);
        
        return $result;
    }
    
        
    public function getAccountStatus($username)
    {
        $query = "SELECT * FROM accounts WHERE UserName = '$username'";
        $result = parent::RunQuery($query);
        
        return $result[0]['Status']; 
    }
}
?>
