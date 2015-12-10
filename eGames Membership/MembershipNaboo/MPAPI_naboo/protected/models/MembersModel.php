<?php

/**
 * 
 * @purpose Description of Members model
 * @author fdlsison
 * @date 06-13-2014
 */
class MembersModel
{

    public static $_instance = null;
    public $_connection;

    public function __construct()
    {
        $this->_connection = Yii::app()->db;
    }

    public static function model()
    {
        if (self::$_instance == null)
            self::$_instance = new MembersModel();
        return self::$_instance;
    }

    //@purpose get Login details
    public function getLoginInfo($username, $password)
    {
        $sql = 'SELECT a.MID, a.UserName, a.IsVip, b.SessionID, c.CardTypeID
                FROM members a
                INNER JOIN membersessions b ON a.MID = b.MID
                INNER JOIN loyaltydb.membercards d ON b.MID = d.MID
                INNER JOIN loyaltydb.cards c ON d.CardID = c.CardID
                WHERE a.UserName = :UserName AND a.Password = :Password';
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':UserName' => $username, ':Password' => $password));
        $result = $command->queryRow();

        return $result;
    }

    //@date 6-23-2014
    public function updateForChangePasswordUsingMID($MID, $changePassword)
    {
        $startTrans = $this->_connection->beginTransaction();

        try
        {
            $sql = "UPDATE members SET ForChangePassword = :ChangePassword WHERE MID = :MID";
            $param = array(':ChangePassword' => $changePassword, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            try
            {
                $startTrans->commit();
                return 1;
            }
            catch (PDOException $e)
            {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        }
        catch (Exception $e)
        {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    public function updateForChangePasswordUsingMIDWithSP($MID, $changePassword)
    {
        $startTrans = $this->_connection->beginTransaction();

        try
        {
            $sql = "CALL sp_update_data(1, 0, 'MID', $MID, 'ForChangePassword', '$changePassword', @OUT_intResultCode, @OUT_strResult)";
            //$param = array(':ChangePassword' => $changePassword,':MID' => $MID);
            $command = $this->_connection->createCommand($sql);
            //$command->bindValues($param);
            $command->execute();

            try
            {
                $startTrans->commit();
                return 1;
            }
            catch (PDOException $e)
            {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        }
        catch (Exception $e)
        {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    //@date 6-26-2014
    public function updateMemberUsername($MID, $email, $password)
    {
        $startTrans = $this->_connection->beginTransaction();

        try
        {
            if ($password == '')
            {
                $sql = 'UPDATE members SET UserName = :UserName WHERE MID = :MID';
                $param = array(':UserName' => $email, ':MID' => $MID);
            }
            else
            {
                $sql = 'UPDATE members SET UserName = :UserName, Password = :Password WHERE MID = :MID';
                $param = array(':UserName' => $email, ':Password' => $password, ':MID' => $MID);
            }
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            try
            {
                $startTrans->commit();
                return 1;
            }
            catch (PDOException $e)
            {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        }
        catch (Exception $e)
        {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    public function updateMemberUsernameWithSP($MID, $email, $password)
    {
        $startTrans = $this->_connection->beginTransaction();

        try
        {
            if ($password == '')
            {
                $sql = "CALL sp_update_data(1,0,'MID',$MID,'UserName','$email',@OUT_intResultCode,@OUT_strResult)";
                //$param = array(':UserName' => $email,':MID' => $MID);
            }
            else
            {
                $sql = "CALL sp_update_data(1,0,'MID',$MID,'UserName,Password','$email;$password',@OUT_intResultCode,@OUT_strResult)";
                //$param = array(':UserName' => $email, ':Password' => $password, ':MID' => $MID);
            }
            $command = $this->_connection->createCommand($sql);
            // $command->bindValues($param);
            $command->execute();

            try
            {
                $startTrans->commit();
                return 1;
            }
            catch (PDOException $e)
            {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        }
        catch (Exception $e)
        {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    //@date 6-30-2014
    //@purpose check if email is verified in live membership db
    public function checkIfActiveVerifiedEmail($email)
    {
        $sql = 'SELECT COUNT(MID)
                FROM members
                WHERE Email = :Email';
        $param = array(':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }

    //@date 07-04-2014
    //@purpose get member's details
    public function getMembersDetails($userName)
    {
        $sql = 'SELECT *
                FROM members
                WHERE UserName = :userName';
        $param = array(':userName' => $userName);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }

    public function getMembersDetailsWithSP($userName)
    {
        $sql = "CALL sp_select_data_mp(1,0,4,'$userName','MID,Status,Password',@ReturnCode, @ReturnMessage, @ReturnFields)";
        //$param = array(':userName' => $userName);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true);
        //var_dump($result);exit;
        //$result = explode(";", $result['OUTfldListRet']);

        if ($result['OUTfldListRet'] == "")
            return array();
        else
        {
            $result = explode(";", $result['OUTfldListRet']);
            return array("MID" => $result[0], "Status" => $result[1], "Password" => $result[2]);
        }
    }

    //@date 07-07-2014
    //@purpose get member's details by MID
    public function getMemberDetailsByMID($MID)
    {
        $sql = 'SELECT *
                FROM members
                WHERE MID = :MID';
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }

//    public function getMemberDetailsByMIDWithSP($MID) {
//        $sql = "CALL sp_select_data(1,0,0,'$MID','MID,Status,UserName,Password',@ReturnCode, @ReturnMessage, @ReturnFields)";
//        //$param = array(':MID' => $MID);
//        $command = $this->_connection->createCommand($sql);
//        $result = $command->queryRow(true);
//        var_dump($result);exit;
//        $result = explode(";", $result['OUTfldListRet']);
//        
//        return array("MID" => $result[0], "Status" => $result[1], "Password" => $result[2]);
//    }
    //@date 07-28-2014
    //@purpose check if member is permitted to change password
    public function getForChangePasswordUsingCardNumber($cardNumber)
    {
        $sql = 'SELECT m.ForChangePassword
                FROM members m
                INNER JOIN loyaltydb.membercards mc
                    ON mc.MID = m.MID
                WHERE mc.CardNumber = :cardNumber';
        $command = $this->_connection->createCommand($sql);
        $param = array(':cardNumber' => $cardNumber);
        $result = $command->queryRow(true, $param);

        return $result;
    }

    //@purpose update password using MID
    public function updatePasswordUsingMID($MID, $password)
    {
        $startTrans = $this->_connection->beginTransaction();

        try
        {
            $sql = 'UPDATE members
                    SET Password = md5(:password)
                    WHERE MID = :MID';
            $param = array(':password' => $password, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            try
            {
                $startTrans->commit();
                return 1;
            }
            catch (PDOException $e)
            {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        }
        catch (Exception $e)
        {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    //@date 10-09-2014
    public function checkIfUsernameExistsWithMID($MID, $email)
    {
        $sql = 'SELECT COUNT(UserName) AS COUNT FROM members WHERE MID != :MID AND UserName = :Email'; //AND Status = 9';
        $param = array(':MID' => $MID, ':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }

    //@date 06-14-2015
    public function getUsernameByMIDWithSP($MID)
    {
        $sql = "CALL sp_select_data_mp(1,0,0,'$MID','UserName',@ReturnCode, @ReturnMessage, @ReturnFields)";
        //$param = array(':MID' => $MID, ':Email' => $email);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true);
        $result = explode(";", $result['OUTfldListRet']);

        return array('UserName' => $result[0]);
    }

    public function getIsVIPUsingMID($MID)
    {
        $sql = 'SELECT IsVIP
                FROM members
                WHERE MID = :MID';
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }

    //@date 07-20-2015
    public function checkIfEwallet($MID)
    {
        $sql = 'SELECT IsEwallet FROM members WHERE MID = :MID'; //AND Status = 9';
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }

    //@date 08-12-2015
    public function insertMembers($arrMembers, $arrMemberInfo)
    {
        //Defaults 
        $query = "CALL membership.sp_insert_data(0,'" . $arrMembers['UserName'] . "','"
                . $arrMemberInfo['FirstName'] . "','"
                . $arrMemberInfo['MiddleName'] . "','"
                . $arrMemberInfo['LastName'] . "','"
                . $arrMemberInfo['LastName'] . "','"
                . $arrMemberInfo['Email'] . "','"
                . $arrMemberInfo['AlternateEmail'] . "','"
                . $arrMemberInfo['MobileNumber'] . "','"
                . $arrMemberInfo['AlternateMobileNumber'] . "','"
                . $arrMemberInfo['Address1'] . "','"
                . $arrMemberInfo['Address2'] . "','"
                . $arrMemberInfo['IdentificationNumber'] . "','"
                . $arrMembers['Password'] . "',"
                . "0" . ",'"
                . "" . "',"
                . $arrMembers['Status'] . ",'"
                . $arrMemberInfo['Birthdate'] . "',"
                . $arrMemberInfo['Gender'] . ","
                . $arrMemberInfo['NationalityID'] . ","
                . $arrMemberInfo['OccupationID'] . ","
                . $arrMemberInfo['IdentificationID'] . ","
                . $arrMemberInfo['IsSmoker'] . ",'"
                . $arrMemberInfo['ReferrerCode'] . "',"
                . $arrMemberInfo['EmailSubscription'] . ","
                . $arrMemberInfo['SMSSubscription'] . ","
                . "Null" . ","
                . "0" . ",'"
                . $arrMemberInfo['DateVerified'] . "',"
                . "Null,@ReturnCode,@ReturnMessage,@ReturnLastInsertedID)";
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow();
        return array('TransCode' => $result['@OUT_ResultCode'],
            'TransMsg' => $result['@OUT_Result'],
            'MID' => $result['@OUT_MID']);
    }

}