<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-04-08
 * Company: Philweb
 * ***************** */

class Members extends BaseEntity
{
    public $hashpassword;
    public $password;

    function Members()
    {
    
        $this->ConnString = "membership";
        $this->TableName = "members";
        $this->Identity = "MID";
        $this->DatabaseType = DatabaseTypes::PDO;
    
    }

    
    function Migrate($arrMembers,$arrMemberInfo, $isTemp = true)
    {

        $this->StartTransaction();

        try 
        {
            App::LoadCore('Randomizer.class.php');
            $randomizer = new Randomizer(); 
            
            /**
             * If records are from Old Loyalty Card
             */
            if(!$isTemp)
            {
                $password = $randomizer->GenerateAlphaNumeric(8);
                $hashpassword = md5($password);
                $arrMembers['Password'] = $hashpassword;
                
                $this->password = $password;
                $this->hashpassword = $hashpassword;
            }
            
            $this->Insert($arrMembers);        

            if(!App::HasError())
            {
                
                $this->TableName = "memberinfo";
                $MID = $this->LastInsertID;
                $arrMemberInfo['MID'] = $MID;
                
                $this->Insert($arrMemberInfo);
                
                if(!App::HasError())
                {
                    $this->CommitTransaction();
                }
                else
                {
                    $this->RollBackTransaction();
                }

            }
            else
            {
                $this->RollBackTransaction();
            }
        }
        catch (Exception $e)
        {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }
    
    //DO NOT DELETE!!! -ish. PLEASEEEE!!!
    function getMID($UserName){
        $query = "Select MID, Password from members where UserName = '$UserName'";
      return parent::RunQuery($query);
    }
    
    function UpdateProfile($arrMemberInfo)
    {
        $this->TableName = "memberinfo";
        $this->Identity = "MID";
        
        $this->StartTransaction();
        try
        {
            $this->UpdateByArray($arrMemberInfo);
            if(!App::HasError())
            {
                $this->CommitTransaction();
            }
            else
            {
                $this->RollBackTransaction();
            }
                    
        }
        catch(Exception $e)
        {
            $this->RollBackTransaction();
        }
    }
    
    function Authenticate($username, $password, $hashing='')
    {
        $retval = false;
        $strpass = $password;
        if($hashing != '')
        {
            App::LoadCore("Hashing.class.php");
            if($hashing == Hashing::MD5)
            {
                $strpass = md5($password);
            }
        }
        $query = "select * from members where username='".$username."' -- and password='".$password."'";
        $result = parent::RunQuery($query);
        if(isset($result) && count($result) >0)
        {
            $row = $result[0];
            $mid = $row["MID"];
            if($row["Status"] == 1)
            {
                if($row["Password"] != $strpass)
                {
                    if($row["LoginAttempts"] < 2)
                    {
                        App::SetErrorMessage("Invalid Password");
                        $this->IncrementLoginAttempts($mid);
                    }
                    else
                    {
                        App::SetErrorMessage("Invalid Password. Account Locked");
                        $this->LockAccountForAttempts($mid);
                    }
                }
                else
                {
                    $this->ResetLoginAttempts($mid);
                    $retval = $row;
                }
            }
            elseif($row["Status"] == 0)
            {
                App::SetErrorMessage("Account Inactive");
            }
            elseif($row["Status"] == 2)
            {
                App::SetErrorMessage("Account Suspended");
            }
            elseif($row["Status"] == 3)
            {
                App::SetErrorMessage("Account Locked (Login Attempts)");
            }
            elseif($row["Status"] == 4)
            {
                App::SetErrorMessage("Account Locked (By Admin)");
            }
            elseif($row["Status"] == 5)
            {
                App::SetErrorMessage("Account Banned");
            }
            elseif($row["Status"] == 6)
            {
                App::SetErrorMessage("Account Terminated");
            }
        }
        else
        {
            App::SetErrorMessage("Invalid Account");
        }
        return $retval;
    }
    
    function IncrementLoginAttempts($mid)
    {
        $query = "update $this->TableName set LoginAttempts = LoginAttempts + 1 where MID=$mid";
        return parent::ExecuteQuery($query);
    }
    
    function LockAccountForAttempts($mid)
    {
        $query = "update $this->TableName set Status = 3, LoginAttempts = 0 where MID=$mid";
        return parent::ExecuteQuery($query);
    }
    function ResetLoginAttempts($mid)
    {
        $query = "update $this->TableName set LoginAttempts = 0 where MID=$mid";
        return parent::ExecuteQuery($query);
    }
  
}

?>
