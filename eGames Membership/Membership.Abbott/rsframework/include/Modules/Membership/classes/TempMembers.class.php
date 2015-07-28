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
    
    /**
     * @Description: for fetching account code and date created using MID
     * @Author aqdepliyan
     * @param int $MID
     * @return array
     */
    public function getTempMemberInfoForSMS($MID)
    {
        $query = "SELECT m.TemporaryAccountCode, m.DateCreated, mi.MobileNumber
                            FROM
                             membership_temp.members m
                            INNER JOIN membership_temp.memberinfo mi
                            ON m.MID = mi.MID
                            WHERE m.MID = ".$MID;
        
        $result = parent::RunQuery($query);
        if(is_array($result) && isset($result[0])){
            return $result[0];
        } else { return $result = ''; }
    }
    
    public function verifyEmailAccountSP($email,$tempcode)
    {
        $this->StartTransaction();
        
        $query = "CALL membership.sp_select_data_mp(0, 0, 16, '$tempcode,$email', 'UserName', @RetCode, @RetMsg, @RetFields)";       
        $result = parent::RunQuery($query);
        if ($result[0]['OUTfldListRet'] > 0) {
            $query2 = "CALL membership.sp_update_data(0, 0, 'UserName,TemporaryAccountCode','$email;$tempcode','DateVerified,IsVerified','NOW(6);1',@ResultCode,@Result)";
            $result = parent::RunQuery($query2);

            if ($result[0]['@OUT_intResultCode'] == 0) {
                
                return self::VERIFY_EMAIL_SUCCESS;
            }
            else {
                return self::VERIFY_EMAIL_FAILED;
            }
        }
        else {
            return self::VERIFY_EMAIL_FAILED;
        }
    }
    
    function Register($arrMembers,$arrMemberInfo)
    {
        $MID = '';
        
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
                    $MID = $arrMemberInfo['MID'];
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
        
        return $MID;
        
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
    
    /**
     * Check if email was already verified in temp db
     * @author elperez
     * @date 06/25/13
     * @param str $email
     * @return int
     */
    public function chkTmpVerifiedEmailAddress($email){
        $query = "SELECT COUNT(m.MID) ctrtemp, m.TemporaryAccountCode  FROM members m
                INNER JOIN memberinfo mi ON m.MID = mi.MID
                WHERE m.IsVerified = 1 AND mi.Email = '$email'";
        
        $result = parent::RunQuery($query);
        
        return $result;//$result[0]['ctrtemp'];
    }
    
    /**
     * @author Edson Perez
     * @date 7/10/2013
     * @purpose count if account is exist in temp db
     * @param str $username email | card
     * @return int count
     */
    public function chkTempUser($username){
        App::LoadCore("Validation.class.php");
        $validate = new Validation();
        
        //if supplied username is email
        if ($validate->validateEmail($username)) {
            $query = "SELECT COUNT(MID) as ctruser FROM members WHERE UserName='$username'";
            
        } else {
            $query = "SELECT COUNT(MID) as ctruser FROM members WHERE TemporaryAccountCode='$username'";
        }
        
        $result = parent::RunQuery($query);
        return $result[0]['ctruser'];
    }
    /**
     * Check if the UserName entered in the login Form is exist in Membership Temp
     * 
     * @author Mark Kenneth Esguerra
     * @date July 19, 2013
     * @param string $username Email Address as Username
     * @return int count
     */
    public function checkIfUsernameExist($username)
    {
        $query = "SELECT COUNT(UserName) as Count FROM $this->TableName WHERE UserName = '$username'";
        $result = parent::RunQuery($query);
        return $result[0]['Count'];
    }
    
    
    public function deactivateAccount( $email , $newemail)
    {
        $query = "UPDATE members SET UserName = '$newemail' WHERE UserName = '$email'";
        
        $this->ExecuteQuery($query);
        if ($this->HasError) {
            App::SetErrorMessage($this->getError());
            return false;
        }
    }
    
    public function TerminateTempAccount($MID,$newemail){
        
        $errorLogger = new ErrorLogger();
         $this->StartTransaction();
         try {
                    $query2 = "UPDATE membership_temp.memberinfo SET Status = 2, Email = '$newemail' WHERE MID = '$MID'";

                    $ismeminfotempupdated = parent::ExecuteQuery($query2);

                    if($ismeminfotempupdated){
                        $query3 = "UPDATE membership_temp.members SET UserName = '$newemail' WHERE MID = '$MID'";

                        $ismemtempupdated = parent::ExecuteQuery($query3);

                        if(!$ismemtempupdated){

                            $this->RollBackTransaction();
                            $errMsg = "Player Termination: Transaction Failed.";
                            $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                            return $errMsg;
                        }
                        else{
                            $this->CommitTransaction();
                            return true;
                        }
                    }
                    else{
                        $this->RollBackTransaction();
                        $errMsg = "Player Termination: Transaction Failed.";
                        $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                        return $errMsg;
                    }
                
                
                }catch(Exception $e){
             $this->RollBackTransaction();
             $errorLogger->log($errorLogger->logdate, "error", $e->getMessage());
             $errMsg = "Player Termination: Transaction Failed.";
             return $errMsg;
         }
    }
    /**
     * @author Mark Kenneth Esguerra
     * @param type $MID
     * @param type $newemail
     * @return string|boolean
     */
    public function TerminateTempAccountSP($MID,$newemail){
        
        $errorLogger = new ErrorLogger();
         $this->StartTransaction();
         try {
                    //$query2 = "UPDATE membership_temp.memberinfo SET Status = 2, Email = '$newemail' WHERE MID = '$MID'";
                    $query2 = "CALL membership.sp_update_data(0, 1, 'MID', $MID, 'Status,Email', '2;$newemail', @ResultCode, @ResultMsg)";
                    $ismeminfotempupdated = parent::ExecuteQuery($query2);

                    if($ismeminfotempupdated){
                        //$query3 = "UPDATE membership_temp.members SET UserName = '$newemail' WHERE MID = '$MID'";
                        $query3 = "CALL membership.sp_update_data(0, 1, 'MID', $MID, 'UserName', '$newemail', @ResultCode, @ResultMsg)";
                        $ismemtempupdated = parent::ExecuteQuery($query3);

                        if(!$ismemtempupdated){

                            $this->RollBackTransaction();
                            $errMsg = "Player Termination: Transaction Failed.";
                            $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                            return $errMsg;
                        }
                        else{
                            $this->CommitTransaction();
                            return true;
                        }
                    }
                    else{
                        $this->RollBackTransaction();
                        $errMsg = "Player Termination: Transaction Failed.";
                        $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                        return $errMsg;
                    }
                
                
                }catch(Exception $e){
             $this->RollBackTransaction();
             $errorLogger->log($errorLogger->logdate, "error", $e->getMessage());
             $errMsg = "Player Termination: Transaction Failed.";
             return $errMsg;
         }
    }
    
    /**
     * Check if email was already verified in temp db using SP
     * @author aqdepliyan
     * @date 06/04/15
     * @param str $email
     * @return int
     */
    public function chkTmpVerifiedEmailAddressWithSP($email){
        $query1 = "CALL membership.sp_select_data(0,1,2,'$email', 'FirstName', @ReturnCode, @ReturnMessage, @ReturnFields);";
        $query2 = "SELECT @ReturnCode, @ReturnMessage, @ReturnFields;";
        parent::RunQuery($query1);
        $result = parent::RunQuery($query2);
        return $result[0]['@ReturnCode'];
    }
    
    /*
     * get temp code of already verified email
     * @author fdlsison
     * @date 07-27-2015 
     */
    public function getTempCodeOfVerifiedEmail($email)
    {
        $query1 = "CALL membership.sp_select_data(0,1,2,'$email', 'MID', @ReturnCode, @ReturnMessage, @ReturnFields)";
        $query2 = "SELECT @ReturnCode, @ReturnMessage, @ReturnFields;";
        parent::RunQuery($query1);
        $result = parent::RunQuery($query2);
        $MID = $result[0]['@ReturnFields'];
        $query3 = "SELECT TemporaryAccountCode FROM membership_temp.members WHERE IsVerified = 1 AND MID = $MID";
        $result2 = parent::RunQuery($query3);
        return $result2[0]['TemporaryAccountCode'];
    }
            
}
    
?>
