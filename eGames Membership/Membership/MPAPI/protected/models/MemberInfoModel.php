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

    //@author Ralph Sison
    //@date 6-19-2014
    //@purpose get member info using MID
    public function getMemberInfoUsingMID($MID) {
        $sql = "SELECT MID, FirstName, MiddleName, LastName, NickName, Address1, MobileNumber,
                       AlternateMobileNumber, Email, AlternateEmail, Gender, IdentificationID,
                       IdentificationNumber, NationalityID, OccupationID, IsSmoker, Birthdate
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

    //@date 6-25-2014
    public function checkIfEmailExistsWithMID($MID, $email) {
        $sql = 'SELECT COUNT(Email) AS COUNT FROM memberinfo WHERE MID != :MID AND Email = :Email'; //AND Status = 9';
        $param = array(':MID' => $MID, ':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }

    public function updateProfile($firstname, $middlename, $lastname, $nickname, $MID, $permanentAddress, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $birthdate, $nationalityID, $occupationID, $idNumber, $idPresented, $gender, $isSmoker) {
        $startTrans = $this->_connection->beginTransaction();
        $SFID = $this->_getSF($MID);

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
                        NickName = :NickName, Address1 = :Address1, MobileNumber = :MobileNumber,
                        AlternateMobileNumber = :AlternateMobileNumber, Email = :Email, AlternateEmail = :AlternateEmail,
                        Birthdate = :Birthdate, NationalityID = :NationalityID, OccupationID = :OccupationID,
                        IdentificationNumber = :IdentificationNumber, IdentificationID = :IdentificationID,
                        Gender = :Gender, IsSmoker = :IsSmoker
                        WHERE MID = :MID";
            $param = array(':FirstName' => $firstname,':MiddleName' => $middlename, ':LastName' => $lastname, ':NickName' => $nickname,
                           ':Address1' => $permanentAddress,':MobileNumber' => $mobileNumber, ':AlternateMobileNumber' => $alternateMobileNumber,
                           ':Email' => $emailAddress,':AlternateEmail' => $alternateEmail, ':Birthdate' => $birthdate,
                           ':NationalityID' => $nationalityID,':OccupationID' => $occupationID, ':IdentificationNumber' => $idNumber, ':IdentificationID' => $idPresented,
                           ':Gender' => $gender, ':IsSmoker' => $isSmoker, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            try {
                $startTrans->commit();
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
                if($sfSuccessful)
                {
                    $newBaseUrl = $sfSuccessful->instance_url;
                    $accessToken = $sfSuccessful->access_token;

                    $isUpdated = $sfapi->update_account($SFID, $firstname, $lastname, $birthdate, null, null, null, $newBaseUrl, $accessToken);
                    return 1;
//                    if($isUpdated)
//                    {
//                        $startTrans->commit();
//                    }
//                    else
//                    {
//                        $startTrans->rollback();
//                    }
                }
                else
                {
                    //$startTrans->rollback();
                    return 0;
                }
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

    public function updateProfilev2($firstname, $middlename, $lastname, $nickname, $MID, $permanentAddress, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $birthdate, $nationalityID, $occupationID, $gender, $isSmoker, $region, $city) {
        $startTrans = $this->_connection->beginTransaction();

        $SFID = $this->_getSF($MID);

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
                if($sfSuccessful)
                {
                    $newBaseUrl = $sfSuccessful->instance_url;
                    $accessToken = $sfSuccessful->access_token;

                    $isUpdated = $sfapi->update_account($SFID, $firstname, $lastname, $birthdate, null, null, null, $newBaseUrl, $accessToken);
                    return 1;
//		    if($isUpdated)
//                    {
//                        $startTrans->rollback();                           
//                    }
//                    else
//                    {
//                        $startTrans->rollback();
//                    }
                }
                else
                {
                    //$startTrans->rollback();
		    return 0;
                }
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

        $SFID = $this->_getSF($MID);

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
                if($sfSuccessful)
                {
                    $newBaseUrl = $sfSuccessful->instance_url;
                    $accessToken = $sfSuccessful->access_token;

                    $isUpdated = $sfapi->update_account($SFID, $firstname, $lastname, $birthdate, null, null, null, $newBaseUrl, $accessToken);
                    return 1;
//		    if($isUpdated)
//                    {
//                        $startTrans->rollback();
//                    }
//                    else
//                    {
//                        $startTrans->rollback();
//                    }
                }
                else
                {
                    //$startTrans->rollback();
		    return 0;
                }
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

    //@date 06-11-2015
    private function _getSF($MID)
    {
        $sql = "SELECT SFID as SFID
                FROM memberinfo
                WHERE MID = :MID";
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result['SFID'];
    }
}

?>