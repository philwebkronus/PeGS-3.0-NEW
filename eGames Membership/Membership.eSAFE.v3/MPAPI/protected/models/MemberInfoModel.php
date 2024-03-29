<?php

/*
 * @author fdlsison
 * @date : 2014-06-18
 */

class MemberInfoModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new MemberInfoModel();
        return self::$_instance;
    }
    
    //@author Ralph Sison
    //@date 6-13-2014
    //@purpose get details using email
    public function getDetailsUsingEmail($email) {
        $sql = "SELECT MID, FirstName, LastName, Status
                  FROM memberinfo 
                  WHERE Email = :email";
        $param = array(':email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    public function getDetailsUsingEmailWithSP($email) {
        $sql = "CALL sp_select_data(1,1,2,'$email','MID,FirstName,MiddleName,LastName,NickName,Email, AlternateEmail,MobileNumber,AlternateMobileNumber,Address1,IdentificationNumber',@ReturnCode, @ReturnMessage, @ReturnFields)";
        //$param = array(':email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true);
        if($result['OUTfldListRet'] == '')
            return array();
        else
        {
            $result = explode(";", $result['OUTfldListRet']);
            return array('MID' => $result[0], 'FirstName' => $result[1], 'MiddleName' => $result[2], 'LastName' => $result[3], 'NickName' => $result[4], 'Email' => $result[5],
                         'AlternateEmail' => $result[6], 'MobileNumber' => $result[7], 'AlternateMobileNumber' => $result[8],
                         'Address1' => $result[9], 'IdentificationNumber' => $result[10]);
        }
    }

    //@author Ralph Sison
    //@date 6-19-2014
    //@purpose get member info using MID
    public function getMemberInfoUsingMID($MID) {
        $sql = "SELECT MID, Gender, IdentificationID,
                       NationalityID, OccupationID, IsSmoker, Birthdate, CityID, RegionID
                FROM memberinfo
                WHERE MID = :MID";
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 6-24-2014
    //@purpose get Email, name and status using MID
    public function getEmailFNameUsingMID($MID) {
        $sql = "SELECT Email, FirstName, LastName, Status
                FROM memberinfo
                WHERE MID = :MID LIMIT 1";
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    public function getEmailFNameUsingMIDWIthSP($MID) {
        $sql = "CALL sp_select_data(1,1,0,'$MID','Email,FirstName,MiddleName,LastName',@ReturnCode, @ReturnMessage, @ReturnFields)";
        //$param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true);
        $result = explode(";", $result['OUTfldListRet']);

        return array('Email' => $result[0], 'FirstName' => $result[1], 'MiddleName' => $result[2], 'LastName' => $result[3]);
    }

    //@date 6-25-2014
    public function checkIfEmailExistsWithMID($MID, $email) {
        $sql = 'SELECT COUNT(Email) AS COUNT FROM memberinfo WHERE MID != :MID AND Email = :Email'; //AND Status = 9';
        $param = array(':MID' => $MID, ':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    public function checkIfEmailExistsWithMIDWithSP($MID,$email) {
        $sql = "CALL sp_select_data(1,1,3,'$MID,$email', 'Email', @OUTRetCode, @OUTRetMessage, @OUTfldListRet)"; //AND Status = 9';
        //$param = array(':MID' => $MID, ':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true);
        if($result['OUTfldListRet'] == "")
            return array();
        else
            return $result['OUTfldListRet'];

    }

    public function updateProfile($firstname, $middlename, $lastname, $nickname, $MID, $permanentAddress, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $birthdate, $nationalityID, $occupationID, $idNumber, $idPresented, $gender, $isSmoker) {
        $startTrans = $this->_connection->beginTransaction();
        
        if($gender == '')
            $gender = 1;
        if($nationalityID == '')
            $nationalityID = 1;
        if($occupationID == '')
            $occupationID = 1;
        if($isSmoker == '')
            $isSmoker = 2;
        
        try {
            $sql = "UPDATE memberinfo
                    SET FirstName = :FirstName, MiddleName = :MiddleName, LastName = :LastName,
                        NickName = :NickName, Address1 = :Address, MobileNumber = :MobileNumber,
                        AlternateMobileNumber = :AlternateMobileNumber, Email = :Email, AlternateEmail = :AlternateEmail,
                        Birthdate = :Birthdate, NationalityID = :NationalityID, OccupationID = :OccupationID,
                        IdentificationNumber = :IdentificationNumber, IdentificationID = :IdentificationID,
                        Gender = :Gender, IsSmoker = :IsSmoker
                        WHERE MID = :MID";
            $param = array(':FirstName' => $firstname,':MiddleName' => $middlename, ':LastName' => $lastname, ':NickName' => $nickname,
                           ':Address' => $permanentAddress,':MobileNumber' => $mobileNumber, ':AlternateMobileNumber' => $alternateMobileNumber,
                           ':Email' => $emailAddress,':AlternateEmail' => $alternateEmail, ':Birthdate' => $birthdate,
                           ':NationalityID' => $nationalityID,':OccupationID' => $occupationID, ':IdentificationNumber' => $idNumber, ':IdentificationID' => $idPresented,
                           ':Gender' => $gender, ':IsSmoker' => $isSmoker, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);
            
            $command->bindValues($param);
            $command->execute();
                      
            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }

        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
    
    public function updateProfileWithSP($firstname, $middlename, $lastname, $nickname, $MID, $permanentAddress, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $birthdate, $nationalityID, $occupationID, $idNumber, $idPresented, $gender, $isSmoker) {
        $startTrans = $this->_connection->beginTransaction();

        if($gender == '')
            $gender = 1;
        if($nationalityID == '')
            $nationalityID = 1;
        if($occupationID == '')
            $occupationID = 1;
        if($isSmoker == '')
            $isSmoker = 2;

        try {
            $sql = "CALL sp_update_data(1,1,'MID',$MID,'FirstName,MiddleName,LastName,NickName,Address1,MobileNumber,AlternateMobileNumber,Email,AlternateEmail,BirthDate,NationalityID,OccupationID,IdentificationNumber,IdentificationID,Gender,IsSmoker','$firstname;$middlename;$lastname;$nickname;$permanentAddress;$mobileNumber;$alternateMobileNumber;$emailAddress;$alternateEmail;$birthdate;$nationalityID;$occupationID;$idNumber;$idPresented;$gender;$isSmoker',@OUT_intResultCode,@OUT_strResult);";
//            $param = array(':FirstName' => $firstname,':MiddleName' => $middlename, ':LastName' => $lastname, ':NickName' => $nickname,
//                           ':Address' => $permanentAddress,':MobileNumber' => $mobileNumber, ':AlternateMobileNumber' => $alternateMobileNumber,
//                           ':Email' => $emailAddress,':AlternateEmail' => $alternateEmail, ':Birthdate' => $birthdate,
//                           ':NationalityID' => $nationalityID,':OccupationID' => $occupationID, ':IdentificationNumber' => $idNumber, ':IdentificationID' => $idPresented,
//                           ':Gender' => $gender, ':IsSmoker' => $isSmoker, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);

           // $command->bindValues($param);
            $command->execute();

            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }

        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
    
    public function updateProfileDateUpdated($MID, $mid) {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'UPDATE memberinfo
                    SET DateUpdated = NOW(6), UpdatedByAID = :mid
                    WHERE MID = :MID';
            $param = array(':mid' => $mid, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
                       
            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
            
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
    
    public function updateProfileDateUpdatedWithSP($MID, $mid) {
        $startTrans = $this->_connection->beginTransaction();

        try {
            $sql = "CALL sp_update_data(1,1,'MID',$MID,'DateUpdated,UpdatedByAID','NOW(6);$mid',@OUT_intResultCode,@OUT_strResult);";
            //$param = array(':mid' => $mid, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);
           // $command->bindValues($param);
            $command->execute();

            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }

        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
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
    
    //@date 10-13-2014
    //@purpose initialize token to be used in redemption
    public function initializeToken($MID) {
        $startTrans = $this->_connection->beginTransaction();
        
        $token = 1;
        
        try {
            $sql = 'UPDATE memberinfo
                    SET Option1 = :token
                    WHERE MID = :MID';
            $param = array(':token' => $token, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
                       
            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
            
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
        
    }
    
    //@purpose update token when redemption is taking place
    public function updateToken($MID) {
        $startTrans = $this->_connection->beginTransaction();
        
        $token = 0;
        
        try {
            $sql = 'UPDATE memberinfo
                    SET Option1 = :token
                    WHERE MID = :MID';
            $param = array(':token' => $token, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
                       
            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
            
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
        
    }
    
    //@purpose check if token is valid for redemption
//    public function checkToken($MID) {
//        $sql = 'SELECT Option1 FROM memberinfo
//                WHERE Option1 = 1 AND MID =:MID';
//        $param = array('')
//    }
    
    public function updateProfilev2($firstname, $middlename, $lastname, $nickname, $MID, $permanentAddress, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $birthdate, $nationalityID, $occupationID, $gender, $isSmoker, $region, $city) {
        $startTrans = $this->_connection->beginTransaction();
        
        if($gender == '')
            $gender = 1;
        if($nationalityID == '')
            $nationalityID = 1;
        if($occupationID == '')
            $occupationID = 1;
        if($isSmoker == '')
            $isSmoker = 2;
        
        try {
            $sql = "UPDATE memberinfo
                    SET FirstName = :FirstName, MiddleName = :MiddleName, LastName = :LastName,
                        NickName = :NickName, Address1 = :Address, MobileNumber = :MobileNumber,
                        AlternateMobileNumber = :AlternateMobileNumber, Email = :Email, AlternateEmail = :AlternateEmail,
                        Birthdate = :Birthdate, NationalityID = :NationalityID, OccupationID = :OccupationID,
                        Gender = :Gender, IsSmoker = :IsSmoker, RegionID = :Region, CityID = :City
                        WHERE MID = :MID";
            $param = array(':FirstName' => $firstname,':MiddleName' => $middlename, ':LastName' => $lastname, ':NickName' => $nickname,
                           ':Address' => $permanentAddress,':MobileNumber' => $mobileNumber, ':AlternateMobileNumber' => $alternateMobileNumber,
                           ':Email' => $emailAddress,':AlternateEmail' => $alternateEmail, ':Birthdate' => $birthdate,
                           ':NationalityID' => $nationalityID,':OccupationID' => $occupationID,
                           ':Gender' => $gender, ':IsSmoker' => $isSmoker,':Region' => $region, ':City' => $city, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);
            
            $command->bindValues($param);
            $command->execute();
                      
            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
    
    public function updateProfilev2WithSP($firstname, $middlename, $lastname, $nickname, $MID, $permanentAddress, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $birthdate, $nationalityID, $occupationID, $gender, $isSmoker, $region, $city) {
        $startTrans = $this->_connection->beginTransaction();
        
        if($gender == '')
            $gender = 1;
        if($nationalityID == '')
            $nationalityID = 1;
        if($occupationID == '')
            $occupationID = 1;
        if($isSmoker == '')
            $isSmoker = 2;

        try {
            $sql = "CALL sp_update_data(1,1,'MID',$MID,'FirstName,MiddleName,LastName,NickName,Address1,MobileNumber,AlternateMobileNumber,Email,AlternateEmail,BirthDate,NationalityID,OccupationID,Gender,IsSmoker,RegionID,CityID','$firstname;$middlename;$lastname;$nickname;$permanentAddress;$mobileNumber;$alternateMobileNumber;$emailAddress;$alternateEmail;$birthdate;$nationalityID;$occupationID;$gender;$isSmoker;$region;$city',@OUT_intResultCode,@OUT_strResult)";
//            $param = array(':FirstName' => $firstname,':MiddleName' => $middlename, ':LastName' => $lastname, ':NickName' => $nickname,
//                           ':Address' => $permanentAddress,':MobileNumber' => $mobileNumber, ':AlternateMobileNumber' => $alternateMobileNumber,
//                           ':Email' => $emailAddress,':AlternateEmail' => $alternateEmail, ':Birthdate' => $birthdate,
//                           ':NationalityID' => $nationalityID,':OccupationID' => $occupationID,
//                           ':Gender' => $gender, ':IsSmoker' => $isSmoker,':Region' => $region, ':City' => $city, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);

            //$command->bindValues($param);
            $command->execute();

            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }

        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    public function updateProfilev3($firstname, $middlename, $lastname, $nickname, $MID, $permanentAddress, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $birthdate, $nationalityID, $occupationID, $idNumber, $idPresented, $gender, $isSmoker, $region, $city) {
        $startTrans = $this->_connection->beginTransaction();

        if($gender == '')
            $gender = 1;
        if($nationalityID == '')
            $nationalityID = 1;
        if($occupationID == '')
            $occupationID = 1;
        if($isSmoker == '')
            $isSmoker = 2;

        try {
            $sql = "UPDATE memberinfo
                    SET FirstName = :FirstName, MiddleName = :MiddleName, LastName = :LastName,
                        NickName = :NickName, Address1 = :Address, MobileNumber = :MobileNumber,
                        AlternateMobileNumber = :AlternateMobileNumber, Email = :Email, AlternateEmail = :AlternateEmail,
                        Birthdate = :Birthdate, NationalityID = :NationalityID, OccupationID = :OccupationID,
                        IdentificationNumber = :IdentificationNumber, IdentificationID = :IdentificationID,
                        Gender = :Gender, IsSmoker = :IsSmoker, RegionID = :Region, CityID = :City
                        WHERE MID = :MID";
            $param = array(':FirstName' => $firstname,':MiddleName' => $middlename, ':LastName' => $lastname, ':NickName' => $nickname,
                           ':Address' => $permanentAddress,':MobileNumber' => $mobileNumber, ':AlternateMobileNumber' => $alternateMobileNumber,
                           ':Email' => $emailAddress,':AlternateEmail' => $alternateEmail, ':Birthdate' => $birthdate,
                           ':NationalityID' => $nationalityID,':OccupationID' => $occupationID, ':IdentificationNumber' => $idNumber, ':IdentificationID' => $idPresented,
                           ':Gender' => $gender, ':IsSmoker' => $isSmoker,':Region' => $region, ':City' => $city, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);
            
            $command->bindValues($param);
            $command->execute();
                      
            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }

        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
    
    public function updateProfilev3WithSP($firstname, $middlename, $lastname, $nickname, $MID, $permanentAddress, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $birthdate, $nationalityID, $occupationID, $idNumber, $idPresented, $gender, $isSmoker, $region, $city) {
        $startTrans = $this->_connection->beginTransaction();

        if($gender == '')
            $gender = 1;
        if($nationalityID == '')
            $nationalityID = 1;
        if($occupationID == '')
            $occupationID = 1;
        if($isSmoker == '')
            $isSmoker = 2;

        try {
            $sql = "CALL sp_update_data(1,1,'MID',$MID,'FirstName,MiddleName,LastName,NickName,Address1,MobileNumber,AlternateMobileNumber,Email,AlternateEmail,BirthDate,NationalityID,OccupationID,IdentificationNumber,IdentificationID,Gender,IsSmoker,RegionID,CityID','$firstname;$middlename;$lastname;$nickname;$permanentAddress;$mobileNumber;$alternateMobileNumber;$emailAddress;$alternateEmail;$birthdate;$nationalityID;$occupationID;$idNumber;$idPresented;$gender;$isSmoker;$region;$city',@OUT_intResultCode,@OUT_strResult);";
//            $param = array(':FirstName' => $firstname,':MiddleName' => $middlename, ':LastName' => $lastname, ':NickName' => $nickname,
//                           ':Address' => $permanentAddress,':MobileNumber' => $mobileNumber, ':AlternateMobileNumber' => $alternateMobileNumber,
//                           ':Email' => $emailAddress,':AlternateEmail' => $alternateEmail, ':Birthdate' => $birthdate,
//                           ':NationalityID' => $nationalityID,':OccupationID' => $occupationID, ':IdentificationNumber' => $idNumber, ':IdentificationID' => $idPresented,
//                           ':Gender' => $gender, ':IsSmoker' => $isSmoker,':Region' => $region, ':City' => $city, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);

            //$command->bindValues($param);
            $command->execute();

            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }

        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
}

?>