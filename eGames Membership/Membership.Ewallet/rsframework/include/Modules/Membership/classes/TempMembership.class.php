<?php

/*
 * @author : owliber
 * @date : 2013-04-18
 */

class TempMembership extends BaseEntity
{
    CONST VERIFY_EMAIL_SUCCESS = 1;
    CONST VERIFY_EMAIL_FAILED = 2;
    
    public function TempMembership()
    {
        $this->ConnString = 'tempmembership';
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    public function getTempAccountCode($code)
    {
        $query = "SELECT TemporaryAccountCode 
                    FROM members
                    WHERE TemporaryAccountCode = '$code'";
        
        return parent::RunQuery($query);
    }
    
    public function getTempMemberInfo($tempAccountCode)
    {
        $query = "SELECT m.Username
                        , concat(mi.FirstName, ' ', mi.MiddleName, ' ', mi.LastName) AS MemberName
                        , m.DateCreated AS RegistrationDate
                        , mi.Birthdate
                        , mi.IsCompleteInfo
                        , m.MID
                        , m.DateVerified
                        , mi.MobileNumber
                        , mi.Email
                   FROM
                     members m
                   INNER JOIN memberinfo mi
                   ON m.MID = m.MID
                   WHERE
                     m.TemporaryAccountCode = '$tempAccountCode'";
        
        $result = parent::RunQuery($query);
        return $result[0];
    }
    
    public function verifyEmailAccount($email,$tempcode)
    {
        $this->StartTransaction();
        
        $query = "SELECT * FROM members
                  WHERE UserName = '$email'
                     AND TemporaryAccountCode = '$tempcode'
                     AND IsVerified = 0";
        
        $result = parent::RunQuery($query);
        
        if(count($result) > 0) //Account exist
        {
            $query2 = "UPDATE members SET DateVerified = NOW(6), IsVerified = 1
                       WHERE UserName = '$email'
                        AND TemporaryAccountCode = '$tempcode'";
            
            $this->ExecuteQuery($query2);
            //parent::ExecuteQuery($query2);
            
            if(!App::HasError())
            {
                $this->CommitTransaction();
                return self::VERIFY_EMAIL_SUCCESS;
            }
            else
            {
                $this->RollBackTransaction();
            }
        }
        else
        {
            return self::VERIFY_EMAIL_FAILED;
        }
    }
}
?>
