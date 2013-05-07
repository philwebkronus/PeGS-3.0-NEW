<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-04-08
 * Company: Philweb
 * ***************** */

class TempMembers extends BaseEntity
{
    CONST VERIFY_EMAIL_SUCCESS = 1;
    CONST VERIFY_EMAIL_FAILED = 2;

    function TempMembers()
    {
        $this->TableName = "members";
        $this->ConnString = "tempmembership";
        $this->Identity = "MID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }

    public function getMembersByAccount( $TempAccountCode )
    {
        $query = "SELECT * FROM members WHERE TemporaryAccountCode = '$TempAccountCode'";
        
        $result = parent::RunQuery($query);
        
        return $result;
    }
    
    public function getTempMemberInfo($tempAccountCode)
    {
        $query = "SELECT m.*, mi.*
                   FROM
                     members m
                   INNER JOIN memberinfo mi
                   ON m.MID = mi.MID
                   WHERE
                     m.TemporaryAccountCode = '$tempAccountCode'";
        
        $result = parent::RunQuery($query);
        return $result;
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
            $query2 = "UPDATE members SET DateVerified = now_usec(), IsVerified = 1
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
    
    function Register($arrMembers,$arrMemberInfo)
    {
        //Load module and instantiate model
        App::LoadModuleClass("Membership", "Helper");
        $_Helper = new Helper();
        
        $this->StartTransaction();
        
        try 
        {
            App::LoadCore('Randomizer.class.php');
            $randomizer = new Randomizer();
            
            //Generate temporary Account code;
            $tempcode = 'eGames' . strtoupper($randomizer->GenerateAlphaNumeric(5));
                    
            $arrMembers['TemporaryAccountCode'] = $tempcode;
            
            $this->Insert($arrMembers);        
        
            if(!App::HasError())
            {
                App::LoadModuleClass("Membership", "TempMemberInfo");
                $_MemberInfo = new TempMemberInfo();
                $_MemberInfo->PDODB = $this->PDODB;
                
                $arrMemberInfo['MID'] = $this->LastInsertID;
                $_MemberInfo->Insert($arrMemberInfo);

                if(!App::HasError())     
                {
                    $this->CommitTransaction();
                    
                    $Recipient = $arrMemberInfo['FirstName'] . ' ' . $arrMemberInfo['LastName'];                    
                    $_Helper->sendEmailVerification($arrMemberInfo['Email'], $Recipient, $tempcode);
                    
                } else
                {
                    $this->RollBackTransaction ();
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
    
    public function Migrate( $cardnumber ) //Temporary account code
    {
        //Load module class from permanent database
        App::LoadModuleClass("Membership", "Members");
        $_Members = new Members();
        
        $queryMember = "SELECT Username,Password, AccountTypeID, 
                               TemporaryAccountCode, DateCreated, DateVerified
                        FROM members
                        WHERE TemporaryAccountCode = '$cardnumber'";
        
        $arrMembers = parent::RunQuery($queryMember);
        
        $queryMemberInfo = "SELECT FirstName, MiddleName, LastName, NickName, Birthdate, Gender, Email,
                                   AlternateEmail, MobileNumber, AlternateMobileNumber, NationalityID,
                                   OccupationID, ReferrerID, Address1, Address2, IdentificationID, IdentificationNumber,
                                   RegistrationOrigin, EmailSubscription, SMSSubscription, IsSmoker, IsCompleteInfo,
                                   DateVerified
                            FROM memberinfo mi
                                INNER JOIN members m ON mi.MID = m.MID
                            WHERE m.TemporaryAccountCode = '$cardnumber'";
        
        $arrMemberInfo = parent::RunQuery($queryMemberInfo);
        
        $_Members->Migrate($arrMembers, $arrMemberInfo);
                
        
        
    }
    

}

?>
