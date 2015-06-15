<?php

/*
 * @author fdlsison
 * @date : 2014-06-25
 */

class MembershipTempSPModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() { 
        $this->_connection = Yii::app()->db3;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new MembershipTempSPModel();
        return self::$_instance;
    }
   
    //@purpose get MID using email
    public function getMID($email) {
        $sql = "SELECT MID
                FROM members
                WHERE UserName = :Email";
        $param = array(':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    public function checkIfEmailExistsWithMID($MID, $email) {
        $sql = 'SELECT COUNT(Email) AS COUNT
                FROM memberinfo
                WHERE MID != :MID AND Email = :Email AND Status = 2';
        $param = array(':MID' => $MID, ':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 6-26-2014
    public function updateTempEmail($MID, $email) {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'UPDATE memberinfo
                    SET Email = :Email
                    WHERE MID = :MID';
            $param = array(':Email' => $email,':MID' => $MID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
                
            try {
                $startTrans->commit();
                return 1;
            } catch(PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            } 
        } catch(Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
    
    
    public function updateTempMemberUsername($MID, $email, $password) {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            if($password == '') {
                $sql = 'UPDATE members
                        SET UserName = :UserName
                        WHERE MID = :MID';
                $param = array(':UserName' => $email,':MID' => $MID);
            }
            else {
                $sql = 'UPDATE members
                        SET UserName = :UserName, Password = :Password
                        WHERE MID = :MID';
                $param = array(':UserName' => $email, ':Password' => $password, ':MID' => $MID);
            }
            
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
                
            try {
                $startTrans->commit();
                return 1;
            } catch(PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            } 
        } catch(Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
    
    public function updateTempProfileDateUpdated($MID, $mid) {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'UPDATE memberinfo
                    SET DateUpdated = NOW(6), UpdatedByMID = :mid
                    WHERE MID = :MID';
            $param = array(':mid' => $mid, ':MID' => $MID);
            
            
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
                
            try {
                $startTrans->commit();
                return 1;
            } catch(PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            } 
        } catch(Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
    
    //@date 6-30-2014
    //@purpose Check if email is/was already verified in temp db
    public function checkTempVerifiedEmail($email) {
        $sql = 'SELECT COUNT(a.MID)
                FROM members a
                INNER JOIN memberinfo b
                    ON a.MID = b.MID
                WHERE a.IsVerified = 1 AND b.Email = :Email';
        $param = array(':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 07-07-2014
    //@purpose check if account exists in temp db
    public function checkTempUser($username) {
        if(Utilities::validateEmail($username)) {
            $sql = 'SELECT COUNT(MID)
                    FROM members
                    WHERE UserName = :username';
        }
        else {
            $sql = 'SELECT COUNT(MID)
                    FROM members
                    WHERE TemporaryAccountCode = :username'; 
        }
        
        $param = array(':username' => $username);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;        
    }
    
//    public function getMembersDetailsWithSP($username) {
//        $sql = "CALL membership.sp_select_data(1,0,4,'$username','MID,Status,Password',@OUTRetCode,@OUTRetMessage,@OUTfldListRet)";
//        //$param = array(':username' => $username);
//        $command = $this->_connection->createCommand($sql);
//        $result = $command->queryRow(true);
//        $result = explode(";", $result['OUTfldListRet']);
//        
//        return array('MID' => $result[0], 'Status' => $result[1], 'Password' => $result[2]);
//        
//    }
    
    public function checkTempUserWithSP($username) {
        if(Utilities::validateEmail($username)) {
            $sql = "CALL sp_select_data(0,0,4,'$username','COUNT(MID)',@OUTRetCode,@OUTRetMessage,@OUTfldListRet)";
        }
        else {
            $sql = "CALL membership_temp.sp_select_data(1,0,1,'$username','COUNT(MID)',@OUTRetCode,@OUTRetMessage,@OUTfldListRet)";
        }
        
        $param = array(':username' => $username);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        $result = explode(";", $result['OUTfldListRet']);

        return $result;        
    }
    
    //@date 07-24-2014
    //@purpose member registration in temp db
    public function register($email, $firstname, $middlename, $lastname, $nickname, $password, $permanentAddress, $mobileNumber, $alternateMobileNumber, $alternateEmail, $idNumber, $idPresented, $gender, $referrerCode, $birthdate, $occupation, $nationality, $isSmoker, $referrerID, $emailSubscription, $smsSubscription) {
        $MID = '';
        if($gender == '')
            $gender = 1;
        if($nationality == '')
            $nationality = 1;
        if($occupation == '')
            $occupation = 1;
        if($referrerID == '')
            $referrerID = 1;
        if($emailSubscription == '')
            $emailSubscription = 0;
        if($smsSubscription == '')
            $smsSubscription = 0;
        if($isSmoker == '')
            $isSmoker = 2;
        
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $tempCode = 'eGames' . strtoupper(Utilities::generateAlphaNumeric(5));
            
            $sql = 'INSERT INTO membership_temp.members(UserName, Password, ForChangePassword, TemporaryAccountCode, DateCreated, Status)
                    VALUES(:Email, :Password, 1, :TempCode, NOW(6), 1)';
            $param = array(':Email' => $email, ':Password' => $password, ':TempCode' => $tempCode);
            $command = $this->_connection->createCommand($sql);
            
            $command->bindValues($param);
            
            $command->execute();
            
            $mid = Yii::app()->db3->getLastInsertID();
            
            try {
                $sql = 'INSERT INTO memberinfo(MID, FirstName, MiddleName, LastName, NickName, Birthdate, Gender, Email, AlternateEmail, MobileNumber, AlternateMobileNumber, NationalityID, OccupationID, Address1, IdentificationID, IdentificationNumber, IsSmoker, DateCreated, ReferrerCode, EmailSubscription, SMSSubscription, ReferrerID)
                        VALUES(:MID, :FirstName, :MiddleName, :LastName, :NickName, :Birthdate, :Gender, :Email, :AlternateEmail, :MobileNumber, :AlternateMobileNumber, :Nationality, :Occupation, :PermanentAddress, :IDPresented, :IDNumber, :IsSmoker, NOW(6), :ReferrerCode, :emailSubscription, :smsSubscription, :referrerID)';
                $param = array(':MID' => $mid, ':FirstName' => $firstname, ':MiddleName' => $middlename, ':LastName' => $lastname, ':PermanentAddress' => $permanentAddress,
                               ':IDPresented' => $idPresented, ':IDNumber' => $idNumber, ':NickName' => $nickname, ':MobileNumber' => $mobileNumber, ':AlternateMobileNumber' => $alternateMobileNumber,
                               ':Email' => $email, ':AlternateEmail' => $alternateEmail, ':Birthdate' => $birthdate, ':Nationality' => $nationality, ':Occupation' => $occupation, 
                               'ReferrerCode' => $referrerCode, ':Gender' => $gender, ':IsSmoker' => $isSmoker, ':emailSubscription' => $emailSubscription, ':smsSubscription' => $smsSubscription, ':referrerID' => $referrerID );
                $command = $this->_connection->createCommand($sql);
                $command->bindValues($param);
                $result = $command->execute();
                
                try {
                    $startTrans->commit();
                    $recipient = $firstname . ' ' . $lastname;
                    $helpers = new Helpers();
                    $helpers->sendEmailVerification($email, $recipient, $tempCode);
                    $MID = $mid;
                    
                    return $MID;
                    
                } catch(PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return $e->getMessage();
                } 
            } catch(PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return $e->getMessage();
            } 
            
        } catch(Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
//    public function registerMemberSP ($email, $firstname, $middlename, $lastname, $nickname, $password, 
//                                      $permanentAddress, $mobileNumber, $alternateMobileNumber, $alternateEmail, 
//                                      $idNumber, $idPresented, $gender, $referrerCode, $birthdate, $occupation, 
//                                      $nationality, $isSmoker, $referrerID, $emailSubscription, $smsSubscription){
//        
//        $sql = "CALL membership_aes.sp_insert(1, ".$email.", "")";
//   }
    
    //@date 07-24-2014
    //@purpose fetch account code and date created using MID
    public function getTempMemberInfoForSMS($MID) {
        $sql = 'SELECT m.TemporaryAccountCode, m.DateCreated, mi.MobileNumber
                FROM members m
                INNER JOIN memberinfo mi
                    ON m.MID = mi.MID
                WHERE m.MID = :MID';
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        if(is_array($result))
           return $result;
        else
            return $result = '';
    }
    
    //@date 6-30-2014
    //@purpose check if email is verified in live membership db
    public function checkIfActiveVerifiedEmail($email) {
        $sql = 'SELECT COUNT(MID)
                FROM memberinfo
                WHERE Email = :Email';
        $param = array(':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result; 
        
    }
    
    //@date 09-16-2014
    //@purpose member registration for BTA in temp db
    public function registerBT($email, $firstname, $lastname, $mobileNumber, $birthdate) { //,$password, $idPresented, $idNumber) {
    //public function register($membersArray, $memberInfoArray) {
        $MID = '';
        $password = sha1(str_replace('-', '', $birthdate));
        $middlename = null;
        $permanentAddress = null;
        $idPresented = null;
        $idNumber = null;
        $nickname = null;
        $alternateMobileNumber = null;
        $alternateEmail = null;
        $nationality = null;
        $occupation = null;
        $referrerCode = null;
        $gender = null;
        $isSmoker = null;
        $emailSubscription = null;
        $smsSubscription = null;
        $referrerID = null;
        
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $tempCode = 'eGames' . strtoupper(Utilities::generateAlphaNumeric(5));
            
            $sql = 'INSERT INTO membership_temp.members(UserName, Password, ForChangePassword, TemporaryAccountCode, DateCreated, Status)
                    VALUES(:Email, :Password, 1, :TempCode, NOW(6), 1)';
            $param = array(':Email' => $email, ':Password' => $password, ':TempCode' => $tempCode);
            $command = $this->_connection->createCommand($sql);
            
            $command->bindValues($param);
            
            $command->execute();
           
            $mid = Yii::app()->db3->getLastInsertID();
                             
            try {                           
                $sql2 = 'INSERT INTO memberinfo(MID, FirstName, MiddleName, LastName, NickName, Birthdate, Gender, Email, AlternateEmail, MobileNumber, AlternateMobileNumber, NationalityID, OccupationID, Address1, IdentificationID, IdentificationNumber, IsSmoker, DateCreated, ReferrerCode, EmailSubscription, SMSSubscription, ReferrerID)
                        VALUES(:MID, :FirstName, :MiddleName, :LastName, :NickName, :Birthdate, :Gender, :Email, :AlternateEmail, :MobileNumber, :AlternateMobileNumber, :Nationality, :Occupation, :PermanentAddress, :IDPresented, :IDNumber, :IsSmoker, NOW(6), :ReferrerCode, :emailSubscription, :smsSubscription, :referrerID)';
                $param2 = array(':MID' => $mid, ':FirstName' => $firstname, ':MiddleName' => $middlename, ':LastName' => $lastname, ':PermanentAddress' => $permanentAddress,
                               ':IDPresented' => $idPresented, ':IDNumber' => $idNumber, ':NickName' => $nickname, ':MobileNumber' => $mobileNumber, ':AlternateMobileNumber' => $alternateMobileNumber,
                               ':Email' => $email, ':AlternateEmail' => $alternateEmail, ':Birthdate' => $birthdate, ':Nationality' => $nationality, ':Occupation' => $occupation, 
                               'ReferrerCode' => $referrerCode, ':Gender' => $gender, ':IsSmoker' => $isSmoker, ':emailSubscription' => $emailSubscription, ':smsSubscription' => $smsSubscription, ':referrerID' => $referrerID);
                $command2 = $this->_connection->createCommand($sql2);
                $command2->bindValues($param2);
                $command2->execute();
                
                try {
                    $startTrans->commit();
                    $recipient = $firstname . ' ' . $lastname;
                    $helpers = new Helpers();
                    $helpers->sendEmailVerification($email, $recipient, $tempCode);
                    $MID = $mid;
                    
                    return $MID;
                    
                } catch(PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return $e->getMessage();
                } 
            } catch(PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return $e->getMessage();
            } 
            
        } catch(Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
    
    //@date 10-09-2014
    public function checkIfUsernameExistsWithMID($MID, $email) {
        $sql = 'SELECT COUNT(UserName) AS COUNT FROM members WHERE MID != :MID AND UserName = :Email'; //AND Status = 9';
        $param = array(':MID' => $MID, ':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
     
}

?>