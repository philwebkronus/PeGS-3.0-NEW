<?php

/*
 * @author fdlsison
 * @date : 2014-06-25
 */

class MembershipTempModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db3;
    }

    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new MembershipTempModel();
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
                WHERE MID != :MID AND Email = :Email'; //AND Status = 2';
        $param = array(':MID' => $MID, ':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }

    public function checkIfEmailExistsWithMIDWithSP($MID, $email) {
        $sql = "CALL membership.sp_select_data(0,1,3,'$MID,$email', 'Email', @OUTRetCode, @OUTRetMessage, @OUTfldListRet)"; //AND Status = 2';
        //$param = array(':MID' => $MID, ':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true);
        if ($result['OUTfldListRet'] == "")
            return array();
        else
            return $result['OUTfldListRet'];
    }

    //@date 6-26-2014
    public function updateTempEmail($MID, $email) {
        $startTrans = $this->_connection->beginTransaction();

        try {
            $sql = 'UPDATE memberinfo
                    SET Email = :Email
                    WHERE MID = :MID';
            $param = array(':Email' => $email, ':MID' => $MID);
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

    public function updateTempEmailWithSP($MID, $email) {
        $startTrans = $this->_connection->beginTransaction();

        try {
            $sql = "CALL membership.sp_update_data(0,1,'MID',$MID,'Email','$email',@OUT_intResultCode,@OUT_strResult);";
            //$param = array(':Email' => $email,':MID' => $MID);
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

    public function updateTempMemberUsername($tempAcctCode, $email, $password) {
        $startTrans = $this->_connection->beginTransaction();

        try {
            if ($password == '') {
                $sql = 'UPDATE members
                        SET UserName = :UserName
                        WHERE TemporaryAccountCode = :TAC';
                $param = array(':UserName' => $email, ':TAC' => $tempAcctCode);
            } else {
                $sql = 'UPDATE members
                        SET UserName = :UserName, Password = :Password
                        WHERE TemporaryAccountCode = :TAC';
                $param = array(':UserName' => $email, ':Password' => $password, ':TAC' => $tempAcctCode);
            }

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

    public function updateTempMemberUsernameWithSP($tempAcctCode, $email, $password) {
        $startTrans = $this->_connection->beginTransaction();

        try {
            if ($password == '') {
                $sql = " CALL membership.sp_update_data(0,0,'TemporaryAccountCode','$tempAcctCode','UserName','$email',@OUT_intResultCode,@OUT_strResult)";
                //$param = array(':UserName' => $email,':TAC' => $tempAcctCode);
            } else {
                $sql = "CALL membership.sp_update_data(0,0,'TemporaryAccountCode','$tempAcctCode','UserName,Password','$email;$password',@OUT_intResultCode,@OUT_strResult);";
                //$param = array(':UserName' => $email, ':Password' => $password, ':TAC' => $tempAcctCode);
            }

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

    public function updateTempProfileDateUpdatedWithSP($MID, $mid) {
        $startTrans = $this->_connection->beginTransaction();

        try {
            $sql = "CALL membership.sp_update_data(0,1,'MID',$MID,'DateUpdated,UpdatedByMID','NOW(6);$mid',@OUT_intResultCode,@OUT_strResult)";
            //$param = array(':mid' => $mid, ':MID' => $MID);


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

    public function checkTempVerifiedEmailWithSP($email) {
        $sql = "CALL membership.sp_select_data(0,1,2,'$email','MID',@OUTRetCode, @OUTRetMessage, @OUTfldListRet)";
        //$param = array(':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true);
        if ($result['OUTfldListRet'] == "")
            return array('Count' => 0);
        else
            return array('Count' => 1);

        return $result;
    }

    //@date 07-07-2014
    //@purpose check if account exists in temp db
    public function checkTempUser($username) {
        if (Utilities::validateEmail($username)) {
            $sql = 'SELECT COUNT(MID)
                    FROM members
                    WHERE UserName = :username';
        } else {
            $sql = 'SELECT COUNT(MID)
                    FROM members
                    WHERE TemporaryAccountCode = :username';
        }

        $param = array(':username' => $username);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }

    //@date 07-24-2014
    //@purpose member registration in temp db
    public function register($email, $firstname, $middlename, $lastname, $nickname, $password, $permanentAddress, $mobileNumber, $alternateMobileNumber, $alternateEmail, $idNumber, $idPresented, $gender, $referrerCode, $birthdate, $occupation, $nationality, $isSmoker, $referrerID, $emailSubscription, $smsSubscription) {
        $MID = '';
        if ($gender == '')
            $gender = 1;
        if ($nationality == '')
            $nationality = 1;
        if ($occupation == '')
            $occupation = 1;
        if ($referrerID == '')
            $referrerID = 1;
        if ($emailSubscription == '')
            $emailSubscription = 0;
        if ($smsSubscription == '')
            $smsSubscription = 0;
        if ($isSmoker == '')
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
                $sql = 'INSERT INTO memberinfo(MID, FirstName, MiddleName, LastName, NickName, Birthdate, Gender, Email, AlternateEmail, MobileNumber, AlternateMobileNumber, NationalityID, OccupationID, Address1, IdentificationID, IdentificationNumber, IsSmoker, DateCreated, ReferrerCode, EmailSubscription, SMSSubscription, ReferrerID, RegistrationOrigin)
                        VALUES(:MID, :FirstName, :MiddleName, :LastName, :NickName, :Birthdate, :Gender, :Email, :AlternateEmail, :MobileNumber, :AlternateMobileNumber, :Nationality, :Occupation, :PermanentAddress, :IDPresented, :IDNumber, :IsSmoker, NOW(6), :ReferrerCode, :emailSubscription, :smsSubscription, :referrerID, 3)';
                $param = array(':MID' => $mid, ':FirstName' => $firstname, ':MiddleName' => $middlename, ':LastName' => $lastname, ':PermanentAddress' => $permanentAddress,
                    ':IDPresented' => $idPresented, ':IDNumber' => $idNumber, ':NickName' => $nickname, ':MobileNumber' => $mobileNumber, ':AlternateMobileNumber' => $alternateMobileNumber,
                    ':Email' => $email, ':AlternateEmail' => $alternateEmail, ':Birthdate' => $birthdate, ':Nationality' => $nationality, ':Occupation' => $occupation,
                    'ReferrerCode' => $referrerCode, ':Gender' => $gender, ':IsSmoker' => $isSmoker, ':emailSubscription' => $emailSubscription, ':smsSubscription' => $smsSubscription, ':referrerID' => $referrerID);
                $command = $this->_connection->createCommand($sql);
                $command->bindValues($param);
                $result = $command->execute();

                try {
                    $instanceURL = Yii::app()->params['instanceURL'];
                    $apiVersion = Yii::app()->params['apiVersion'];
                    $cKey = Yii::app()->params['cKey'];
                    $cSecret = Yii::app()->params['cSecret'];
                    $sfLogin = Yii::app()->params['sfLogin'];
                    $sfPassword = Yii::app()->params['sfPassword'];
                    $secToken = Yii::app()->params['secToken'];
                    //$redirectURI = Yii::app()->params['redirectURI'];

                    $sfapi = new SalesforceAPI($instanceURL, $apiVersion, $cKey, $cSecret);

                    $sfSuccessful = $sfapi->login($sfLogin, $sfPassword, $secToken);

                    if ($sfSuccessful) {
                        $newBaseUrl = $sfSuccessful->instance_url;
                        $accessToken = $sfSuccessful->access_token;

                        if ($gender == 1) {
                            $salutation = 'Mr.';
                        } else {
                            $salutation = 'Ms.';
                        }

                        $playertype = 'Regular';
                        $sfID = $sfapi->create_account($lastname, $firstname, $birthdate, $salutation, $playertype, $tempCode, $newBaseUrl, $accessToken);
                        if ($sfID) {
                            $sql = 'UPDATE memberinfo
                                    SET SFID = :SFID
                                    WHERE MID = :MID';
                            $param = array(':SFID' => $sfID, ':MID' => $mid);

                            $command = $this->_connection->createCommand($sql);
                            $command->bindValues($param);
                            $result = $command->execute();

                            if ($result > 0) {
                                $startTrans->commit();
                                $recipient = $firstname . ' ' . $lastname;
                                $helpers = new Helpers();
                                $helpers->sendEmailVerification($email, $recipient, $tempCode);
                                $MID = $mid;

                                return array('MID' => $MID, 'SFID' => $sfID);
                            } else {
                                $startTrans->rollback();
                            }
                        } else {
                            $startTrans->rollback();
                        }
                    } else {
                        $startTrans->rollback();
                    }
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return $e->getMessage();
                }
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return $e->getMessage();
            }
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    public function registerWithSP($email, $firstname, $middlename, $lastname, $nickname, $password, $permanentAddress, $mobileNumber, $alternateMobileNumber, $alternateEmail, $idNumber, $idPresented, $gender, $referrerCode, $birthdate, $occupation, $nationality, $isSmoker, $referrerID, $emailSubscription, $smsSubscription) {
        $MID = '';
        if ($gender == '')
            $gender = 1;
        if ($nationality == '')
            $nationality = 1;
        if ($occupation == '')
            $occupation = 1;
        if ($referrerID == '')
            $referrerID = 1;
        if ($emailSubscription == '')
            $emailSubscription = 0;
        if ($smsSubscription == '')
            $smsSubscription = 0;
        if ($isSmoker == '')
            $isSmoker = 2;

        $startTrans = $this->_connection->beginTransaction();

        try {
            $tempCode = 'eGames' . strtoupper(Utilities::generateAlphaNumeric(5));

            $sql = "CALL membership.sp_insert_data(1,'$email','$firstname','$middlename','$lastname','$nickname','$email','$alternateEmail','$mobileNumber','$alternateMobileNumber','$permanentAddress',Null,'$idNumber','$password',1,'$tempCode',1,'$birthdate',$gender,$nationality,$occupation,$idPresented,$isSmoker,'$referrerCode',$emailSubscription,$smsSubscription,$referrerID,0,Null,3,@OUT_ResultCode,@OUT_Result2,@OUT_MID)";
            //$param = array(':Email' => $email, ':Password' => $password, ':TempCode' => $tempCode);
            $command = $this->_connection->createCommand($sql);
            $result = $command->queryRow(true);

            try {
                //start add - SF push 07272015 mcs
                try {
                    $instanceURL = Yii::app()->params['instanceURL'];
                    $apiVersion = Yii::app()->params['apiVersion'];
                    $cKey = Yii::app()->params['cKey'];
                    $cSecret = Yii::app()->params['cSecret'];
                    $sfLogin = Yii::app()->params['sfLogin'];
                    $sfPassword = Yii::app()->params['sfPassword'];
                    $secToken = Yii::app()->params['secToken'];

                    $sfapi = new SalesforceAPI($instanceURL, $apiVersion, $cKey, $cSecret);
                    $sfSuccessful = $sfapi->login($sfLogin, $sfPassword, $secToken);

                    if ($sfSuccessful) {
                        $newBaseUrl = $sfSuccessful->instance_url;
                        $accessToken = $sfSuccessful->access_token;

                        if ($gender == 1) {
                            $salutation = 'Mr.';
                        } else {
                            $salutation = 'Ms.';
                        }

                        $playertype = 'Regular';
                        $MID = $result['@OUT_MID'];
                        $sfID = $sfapi->create_account($lastname, $firstname, $birthdate, $salutation, $playertype, $tempCode, $newBaseUrl, $accessToken);
                        if ($sfID) {
                            $sql = 'UPDATE memberinfo
                                    SET SFID = :SFID
                                    WHERE MID = :MID';
                            $param = array(':SFID' => $sfID, ':MID' => $MID);

                            $command = $this->_connection->createCommand($sql);
                            $command->bindValues($param);
                            $result = $command->execute();

                            if ($result > 0) {
                                $startTrans->commit();
                                $recipient = $firstname . ' ' . $lastname;
                                $helpers = new Helpers();
                                $helpers->sendEmailVerification($email, $recipient, $tempCode);
                                return $MID;
                            } else {
                                $startTrans->rollback();
                            }
                        } else {
                            $startTrans->rollback();
                        }
                    } else {
                        $startTrans->rollback();
                    }
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return $e->getMessage();
                }//end add - SF push 07272015 mcs
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return $e->getMessage();
            }
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

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

        if (is_array($result))
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

//    public function checkIfActiveVerifiedEmailWithSP($email) {
//        $sql = "CALL membership.sp_select_data(0,0,2,'$email','MID',@OUTRetCode, @OUTRetMessage, @OUTfldListRet)";
//        //$param = array(':Email' => $email);
//        $command = $this->_connection->createCommand($sql);
//        $result = $command->queryRow(true);
//        var_dump($result);exit;
//
//        return $result;
//
//    }
    //@date 09-16-2014
    //@purpose member registration for BTA in temp db
    public function registerBT($email, $firstname, $lastname, $mobileNumber, $birthdate) { //,$password, $idPresented, $idNumber) {
        //public function register($membersArray, $memberInfoArray) {
        $MID = '';
        $password = str_replace('-', '', $birthdate);
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
                $sql2 = 'INSERT INTO memberinfo(MID, FirstName, MiddleName, LastName, NickName, Birthdate, Gender, Email, AlternateEmail, MobileNumber, AlternateMobileNumber, NationalityID, OccupationID, Address1, IdentificationID, IdentificationNumber, IsSmoker, DateCreated, ReferrerCode, EmailSubscription, SMSSubscription, ReferrerID, RegistrationOrigin)
                        VALUES(:MID, :FirstName, :MiddleName, :LastName, :NickName, :Birthdate, :Gender, :Email, :AlternateEmail, :MobileNumber, :AlternateMobileNumber, :Nationality, :Occupation, :PermanentAddress, :IDPresented, :IDNumber, :IsSmoker, NOW(6), :ReferrerCode, :emailSubscription, :smsSubscription, :referrerID, 4)';
                $param2 = array(':MID' => $mid, ':FirstName' => $firstname, ':MiddleName' => $middlename, ':LastName' => $lastname, ':PermanentAddress' => $permanentAddress,
                    ':IDPresented' => $idPresented, ':IDNumber' => $idNumber, ':NickName' => $nickname, ':MobileNumber' => $mobileNumber, ':AlternateMobileNumber' => $alternateMobileNumber,
                    ':Email' => $email, ':AlternateEmail' => $alternateEmail, ':Birthdate' => $birthdate, ':Nationality' => $nationality, ':Occupation' => $occupation,
                    'ReferrerCode' => $referrerCode, ':Gender' => $gender, ':IsSmoker' => $isSmoker, ':emailSubscription' => $emailSubscription, ':smsSubscription' => $smsSubscription, ':referrerID' => $referrerID);
                $command2 = $this->_connection->createCommand($sql2);
                $command2->bindValues($param2);
                $command2->execute();

                try {
                    $instanceURL = Yii::app()->params['instanceURL'];
                    $apiVersion = Yii::app()->params['apiVersion'];
                    $cKey = Yii::app()->params['cKey'];
                    $cSecret = Yii::app()->params['cSecret'];
                    $sfLogin = Yii::app()->params['sfLogin'];
                    $sfPassword = Yii::app()->params['sfPassword'];
                    $secToken = Yii::app()->params['secToken'];
                    //$redirectURI = Yii::app()->params['redirectURI'];

                    $sfapi = new SalesforceAPI($instanceURL, $apiVersion, $cKey, $cSecret);

                    $sfSuccessful = $sfapi->login($sfLogin, $sfPassword, $secToken);

                    if ($sfSuccessful) {
                        $newBaseUrl = $sfSuccessful->instance_url;
                        $accessToken = $sfSuccessful->access_token;

                        if ($gender == 1) {
                            $salutation = 'Mr.';
                        } else {
                            $salutation = 'Ms.';
                        }

                        $playertype = 'Regular';
                        $sfID = $sfapi->create_account($lastname, $firstname, $birthdate, $salutation, $playertype, $tempCode, $newBaseUrl, $accessToken);
                        if ($sfID) {
                            $sql = 'UPDATE memberinfo
                                    SET SFID = :SFID
                                    WHERE MID = :MID';
                            $param = array(':SFID' => $sfID, ':MID' => $mid);

                            $command = $this->_connection->createCommand($sql);
                            $command->bindValues($param);
                            $result = $command->execute();

                            if ($result > 0) {
                                $startTrans->commit();
                                $recipient = $firstname . ' ' . $lastname;
                                $helpers = new Helpers();
                                $helpers->sendEmailVerification($email, $recipient, $tempCode);
                                $MID = $mid;

                                return array('MID' => $MID, 'SFID' => $sfID);
                            } else {
                                $startTrans->rollback();
                            }
                        } else {
                            $startTrans->rollback();
                        }
                    } else {
                        $startTrans->rollback();
                    }
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return $e->getMessage();
                }
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return $e->getMessage();
            }
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    public function registerBTWithSP($email, $firstname, $lastname, $mobileNumber, $birthdate) { //,$password, $idPresented, $idNumber) {
        //public function register($membersArray, $memberInfoArray) {
        $MID = '';
        $password = str_replace('-', '', $birthdate);
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

            $sql = "CALL membership.sp_insert_data(1,
  '$email', -- UserName
  '$firstname', -- FirstName
  '$middlename', -- MiddleName
  '$lastname', -- LastName
    '$nickname', -- NickName
    '$email', -- Email
    '$alternateEmail', -- AlterNate Email
    '$mobileNumber', -- MobileNumber
  '$alternateMobileNumber', -- AlterNate MobileNumber
    '$permanentAddress', -- Address1
    Null, -- Address2
    '$idNumber', -- IdentificationNumber
    '$password',  -- Password
  1, -- ForChangePassword
  '$tempCode',  -- TemporaryAccountCode
  1, -- Status
  '$birthdate', -- BirthDate
  1, -- Gender
  1, -- NationalityID
  1, -- OccupationID
  1, -- IdentificationID
  0, -- IsSmoker
  '$referrerCode', -- Referrer Code
  0, -- Email Subscription
  0, -- SMS Subscription
  1, -- Referrer ID
  0, -- IsCompleteInfo
  Null, -- DateVerified
  4, -- Registration Origin
  @OUT_ResultCode, @OUT_Result, @OUT_MID);";
            //$param = array(':Email' => $email, ':Password' => $password, ':TempCode' => $tempCode);
            $command = $this->_connection->createCommand($sql);

            //$command->bindValues($param);
            $result = $command->queryRow(true);
            //$command->execute();

            try {
                //start add - SF push 07272015 mcs
                try {
                    $instanceURL = Yii::app()->params['instanceURL'];
                    $apiVersion = Yii::app()->params['apiVersion'];
                    $cKey = Yii::app()->params['cKey'];
                    $cSecret = Yii::app()->params['cSecret'];
                    $sfLogin = Yii::app()->params['sfLogin'];
                    $sfPassword = Yii::app()->params['sfPassword'];
                    $secToken = Yii::app()->params['secToken'];
                    //$redirectURI = Yii::app()->params['redirectURI'];

                    $sfapi = new SalesforceAPI($instanceURL, $apiVersion, $cKey, $cSecret);

                    $sfSuccessful = $sfapi->login($sfLogin, $sfPassword, $secToken);

                    if ($sfSuccessful) {
                        $newBaseUrl = $sfSuccessful->instance_url;
                        $accessToken = $sfSuccessful->access_token;

                        if ($gender == 1) {
                            $salutation = 'Mr.';
                        } else {
                            $salutation = 'Ms.';
                        }

                        $playertype = 'Regular';
                        $MID = $result['@OUT_MID'];
                        $sfID = $sfapi->create_account($lastname, $firstname, $birthdate, $salutation, $playertype, $tempCode, $newBaseUrl, $accessToken);
                        if ($sfID) {
                            $sql = 'UPDATE memberinfo
                                    SET SFID = :SFID
                                    WHERE MID = :MID';
                            $param = array(':SFID' => $sfID, ':MID' => $MID);

                            $command = $this->_connection->createCommand($sql);
                            $command->bindValues($param);
                            $result = $command->execute();

                            if ($result > 0) {
                                $startTrans->commit();
                                $recipient = $firstname . ' ' . $lastname;
                                $helpers = new Helpers();
                                $helpers->sendEmailVerification($email, $recipient, $tempCode);
                                return $MID;
                            } else {
                                $startTrans->rollback();
                            }
                        } else {
                            $startTrans->rollback();
                        }
                    } else {
                        $startTrans->rollback();
                    }
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return $e->getMessage();
                }
                //end add - SF push 07272015 mcs
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return $e->getMessage();
            }
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    public function registerBTNoEmailWithSP($firstname, $lastname, $mobileNumber, $birthdate) {
        $MID = '';
        $password = str_replace('-', '', $birthdate);
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

            $sql = "CALL membership.sp_insert_data(1,
                    '$mobileNumber', -- UserName
                    '$firstname', -- FirstName
                    '$middlename', -- MiddleName
                    '$lastname', -- LastName
                    '$nickname', -- NickName
                    NULL, -- Email
                    '$alternateEmail', -- AlterNate Email
                    '$mobileNumber', -- MobileNumber
                    '$alternateMobileNumber', -- AlterNate MobileNumber
                    '$permanentAddress', -- Address1
                    NULL, -- Address2
                    '$idNumber', -- IdentificationNumber
                    '$password',  -- Password
                    1, -- ForChangePassword
                    '$tempCode',  -- TemporaryAccountCode
                    1, -- Status
                    '$birthdate', -- BirthDate
                    1, -- Gender
                    1, -- NationalityID
                    1, -- OccupationID
                    1, -- IdentificationID
                    0, -- IsSmoker
                    '$referrerCode', -- Referrer Code
                    0, -- Email Subscription
                    0, -- SMS Subscription
                    1, -- Referrer ID
                    0, -- IsCompleteInfo
                    NULL, -- DateVerified
                    5, -- Registration Origin
                    @OUT_ResultCode, @OUT_Result, @OUT_MID);";
            $command = $this->_connection->createCommand($sql);
            $result = $command->queryRow(true);
            try {
                //start add - SF push 07272015 mcs
                try {
                    $instanceURL = Yii::app()->params['instanceURL'];
                    $apiVersion = Yii::app()->params['apiVersion'];
                    $cKey = Yii::app()->params['cKey'];
                    $cSecret = Yii::app()->params['cSecret'];
                    $sfLogin = Yii::app()->params['sfLogin'];
                    $sfPassword = Yii::app()->params['sfPassword'];
                    $secToken = Yii::app()->params['secToken'];

                    $sfapi = new SalesforceAPI($instanceURL, $apiVersion, $cKey, $cSecret);
                    $sfSuccessful = $sfapi->login($sfLogin, $sfPassword, $secToken);
                    if ($sfSuccessful) {
                        $newBaseUrl = $sfSuccessful->instance_url;
                        $accessToken = $sfSuccessful->access_token;

                        if ($gender == 1) {
                            $salutation = 'Mr.';
                        } else {
                            $salutation = 'Ms.';
                        }

                        $playertype = 'Regular';
                        $MID = $result['@OUT_MID'];
                        $sfID = $sfapi->create_account($lastname, $firstname, $birthdate, $salutation, $playertype, $tempCode, $newBaseUrl, $accessToken);
                        if ($sfID) {
                            $sql = 'UPDATE memberinfo
                                    SET SFID = :SFID
                                    WHERE MID = :MID';
                            $param = array(':SFID' => $sfID, ':MID' => $MID);

                            $command = $this->_connection->createCommand($sql);
                            $command->bindValues($param);
                            $result = $command->execute();

                            if ($result > 0) {
                                $startTrans->commit();
                                return $MID;
                            } else {
                                $startTrans->rollback();
                            }
                        } else {
                            $startTrans->rollback();
                        }
                    } else {
                        $startTrans->rollback();
                    }
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return $e->getMessage();
                }
                //end add - SF push 07272015 mcs
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return $e->getMessage();
            }
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    //@date 10-09-2014
    public function checkIfUsernameExistsWithTAC($email, $tempAcctCode) {
        $sql = 'SELECT COUNT(UserName) AS COUNT FROM members WHERE UserName = :Email AND TemporaryAccountCode != :TAC AND IsVerified IN(0,1)'; //AND Status = 9';
        $param = array(':Email' => $email, ':TAC' => $tempAcctCode);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }

    public function checkIfUsernameExistsWithTACWithSP($email, $tempAcctCode) {
        $sql = "CALL membership.sp_select_data(0,0,3,'$tempAcctCode,$email', 'UserName', @OUTRetCode, @OUTRetMessage, @OUTfldListRet)"; //AND Status = 9';
        //$param = array(':Email' => $email, ':TAC' => $tempAcctCode);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true);
        if ($result['OUTfldListRet'] == "")
            return array();
        else
            return $result['OUTfldListRet'];
    }

    //@date 04-24-2015
    public function getTempCodeUsingCard($cardNumber) {
        $sql = 'SELECT mtm.TemporaryAccountCode AS TAC FROM membership_temp.members mtm
                        INNER JOIN loyaltydb.membercards mmc ON mtm.TemporaryAccountCode = mmc.CardNumber
                        INNER JOIN membership.members mm ON mmc.MID = mm.MID
                WHERE mmc.Status = 8 AND mmc.CardNumber = :cardNumber'; //AND Status = 9';
        $param = array(':cardNumber' => $cardNumber);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result['TAC'];
    }

    public function checkIfEmailExistsWithTAC($tempAcctCode, $email) {
        $sql = 'SELECT COUNT(UserName) AS COUNT
                FROM members
                WHERE UserName = :Email AND TemporaryAccountCode = :TAC';
        $param = array(':Email' => $email, ':TAC' => $tempAcctCode);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }

    //@date 06-11-2015
    private function _updateSFID($SFID, $MID) {
        $startTrans = $this->_connection->beginTransaction();

        try {
            $sql = 'UPDATE memberinfo
                    SET SFID = :SFID
                    WHERE MID = :MID';
            $param = array(':SFID' => $SFID, ':MID' => $MID);


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

    //07302015
    public function getSFIDFromTemp($tempAcctCode) {
        $sql = "SELECT MID
                FROM members
                WHERE TemporaryAccountCode = :TAC";
        $param = array(':TAC' => $tempAcctCode);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result;
    }

    //07302015
    public function getSF($MID) {
        $sql = "SELECT SFID
                FROM memberinfo
                WHERE MID = :MID";
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }

}

?>