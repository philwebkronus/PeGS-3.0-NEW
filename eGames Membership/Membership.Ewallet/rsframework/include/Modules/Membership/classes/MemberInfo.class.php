<?php

/*
 * @author : owliber
 * @date : 2013-04-18
 */

class MemberInfo extends BaseEntity {

    public function MemberInfo() {
        $this->ConnString = 'membership';
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->TableName = "memberinfo";
        $this->Identity = "MID";
    }

    /**
     * 
     * @param int $MID - Member ID
     * @return string array of member details
     */
    public function getMemberInfo($MID) {

        $query = "SELECT
                    m.*,
                    mi.*
                  FROM memberinfo mi
                    INNER JOIN members m ON mi.MID = m.MID
                  WHERE m.MID = $MID";

        return parent::RunQuery($query);
    }
    public function getGenericInfo($MID) {
        $query = "SELECT
                    m.Status, m.DateCreated, mi.Gender, mi.Birthdate, mi.IsCompleteInfo, mi.DateVerified, 
                    mi.Gender, mi.IsSmoker, mi.Birthdate, mi.IdentificationID, mi.OccupationID, mi.NationalityID, 
                    mi.RegionID, mi.CityID, m.IsVIP, mi.MemberInfoID        
                  FROM memberinfo mi
                    INNER JOIN members m ON mi.MID = m.MID
                  WHERE m.MID = $MID";
        return parent::RunQuery($query);
    }
    /**
     * @Description: Call SP for Select
     * @param int $MID - Member ID
     * @return string array of member details
     */
    public function getMemInfoUsingSP($MID, $forBanning = null) {
        
        if (is_null($forBanning)) {
            $query = "SELECT
                        m.Status, m.DateCreated, mi.Birthdate, mi.IsCompleteInfo, mi.DateVerified, mi.RegionID, mi.CityID, 
                        mi.IdentificationID, mi.MemberInfoID 
                      FROM memberinfo mi
                        INNER JOIN members m ON mi.MID = m.MID
                      WHERE m.MID = $MID";
        }
        else {
            $query = "SELECT
                        m.Status, m.DateCreated, mi.Birthdate, mi.IsCompleteInfo, mi.DateVerified, mi.RegionID, mi.CityID, 
                        mi.IdentificationID, mi.MemberInfoID, ri.IdentificationName  
                      FROM memberinfo mi
                        INNER JOIN members m ON mi.MID = m.MID 
                        INNER JOIN ref_identifications ri ON mi.IdentificationID = ri.IdentificationID 
                      WHERE m.MID = $MID";
        }
        
        $data1 = parent::RunQuery($query);
        
        $neededfields = "'FirstName,LastName,MiddleName,NickName,Email,MobileNumber,AlternateMobileNumber,AlternateEmail,Address1,Address2,IdentificationNumber'";
        $infos =  array();
        $query1 = "CALL sp_select_data(1,1,0,$MID,$neededfields,@ReturnCode, @ReturnMessage, @ReturnFields);";
        $query2 = "SELECT @ReturnCode, @ReturnMessage, @ReturnFields;";
        $query3 = "CALL sp_select_data(1,0,0,$MID,'UserName',@ReturnCode, @ReturnMessage, @ReturnFields);";
        parent::RunQuery($query1);
        $data = parent::RunQuery($query2);
        $username = parent::RunQuery($query3);
        $keys = explode(",", $neededfields);
        $infodata = explode(';', $data[0]['@ReturnFields']);
        foreach ($keys as $key => $value) {
            $infos[trim($value," '")] = $infodata[$key];
        }
        $info = array_merge($data1[0],$infos);
        isset($username[0]["OUTfldListRet"]) ? $info["UserName"] = $username[0]["OUTfldListRet"]:$info["UserName"] = "";
        return $info;
        unset($infos,$info);
    }

    public function getEmail($MID) {
        $query = "SELECT Email FROM $this->TableName WHERE MID=$MID";
        $result = parent::RunQuery($query);
        return $result[0]["Email"];
    }

    public function getMIDFNameUsingEmail($email) {

        $query = "SELECT MID, FirstName, LastName FROM " . $this->TableName . "
                  WHERE Email = '$email' ";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function getEmailFNameUsingMID($MID) {

        $query = "SELECT Email, FirstName, LastName, Status FROM " . $this->TableName . "
                  WHERE MID = $MID ";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function getMemberInfoByUsername($Username) {

        $query = "SELECT
                    m.Password, m.IsVIP, mc.Status
                    mi.*
                  FROM memberinfo mi
                    INNER JOIN members m ON mi.MID = m.MID
                    INNER JOIN membercards mc ON m.MID = mc.MID
                  WHERE m.Username = '$Username'";

        return parent::RunQuery($query);
    }

    /*
     * Description: Get Member Info using name that match either the firstname, middlename or the lastname.
     * @author: aqdepliyan
     * DateCreated: 2013-06-17 05:40:20PM
     */

    public function getMemberInfoByName($name) {

        $query = "SELECT mi.MID, mi.FirstName, mi.LastName , mi.Birthdate, mi.IdentificationNumber, ri.IdentificationName
                            FROM memberinfo as mi
                            INNER JOIN ref_identifications as ri ON ri.IdentificationID = mi.IdentificationID
                            WHERE mi.FirstName like '%" . $name . "%' OR mi.LastName like '%" . $name . "%' OR
                            CONCAT(mi.FirstName, ' ', mi.LastName) LIKE  '%" . $name . "%'";
        return parent::RunQuery($query);
    }

    /*
     * Description: Get Member Info using MID with a status limit only to active, active temporary and banned cards..
     * @author: aqdepliyan
     * DateCreated: 2013-06-17 05:40:20PM
     */

    public function getMemberInfoByMID($MID) {

        $query = "SELECT m.Status, mi.FirstName, mi.LastName, mi.Birthdate, mi.IdentificationNumber, ri. IdentificationName
                            FROM members as m
                            INNER JOIN memberinfo as mi ON mi.MID = m.MID
                            INNER JOIN ref_identifications as ri ON ri.IdentificationID = mi.IdentificationID
                            WHERE m.MID =" . $MID;

        return parent::RunQuery($query);
    }

    /*
     * Description: Get the number of Active Account Status
     * @author: Junjun S. Hernandez
     * DateCreated: June 27, 2013 11:40:05AM
     */

    public function getActiveAccountStatus() {

        $query = "SELECT COUNT(MID), Status FROM members WHERE Status = 1";

        return parent::RunQuery($query);
    }

    /*
     * Description: Get the number of Banned Account Status
     * @author: Junjun S. Hernandez
     * DateCreated: June 27, 2013 11:41:43AM
     */

    public function getBannedAccountStatus() {

        $query = "SELECT COUNT(MID), Status FROM members WHERE Status = 5";

        return parent::RunQuery($query);
    }

    public function getDateVerifiedByMID($MID) {
        $query = "SELECT DateVerified FROM $this->TableName WHERE MID = $MID;";
        $result = parent::RunQuery($query);
        return $result;
    }

    /*
     * Description: Get the number of both Active and Banned Account Status
     * @author: Junjun S. Hernandez
     * DateCreated: June 27, 2013 01:07:35PM
     */

    public function getActiveAndBannedAccountStatus() {

        $query = "SELECT COUNT(MID) FROM members WHERE Status IN (1, 5)";

        return parent::RunQuery($query);
    }

    /*
     * Description: Get the number of both Active and Banned Account Status
     * @author: Junjun S. Hernandez
     * DateCreated: June 27, 2013 01:07:35PM
     */

    public function getMemberInfoByID($MID) {
        $query = "SELECT mi.Birthdate, YEAR(current_date)-YEAR(mi.Birthdate) as Age, mi.MID, mi.Gender, m.Status FROM memberinfo mi
                    INNER JOIN members m ON mi.MID = m.MID
                  WHERE m.MID = $MID";
        return parent::RunQuery($query);
    }

    public function updateProfile($arrMembers, $arrMemberInfo) {
        $FirstName = $arrMemberInfo['FirstName'];
        $MiddleName = $arrMemberInfo['MiddleName'];
        $LastName = $arrMemberInfo['LastName'];
        $NickName = $arrMemberInfo['NickName'];
        $Birthdate = $arrMemberInfo['Birthdate'];
        $Gender = $arrMemberInfo['Gender'];
        $Email = $arrMemberInfo['Email'];
        $AlternateEmail = $arrMemberInfo['AlternateEmail'];
        $MobileNumber = $arrMemberInfo['MobileNumber'];
        $AlternateMobileNumber = $arrMemberInfo['AlternateMobileNumber'];
        $NationalityID = $arrMemberInfo['NationalityID'];
        $OccupationID = $arrMemberInfo['OccupationID'];
        $Address1 = $arrMemberInfo['Address1'];
        $Address2 = $arrMemberInfo['Address2'];
        $IdentificationID = $arrMemberInfo['IdentificationID'];
        $IdentificationNumber = $arrMemberInfo['IdentificationNumber'];
        $IsSmoker = $arrMemberInfo['IsSmoker'];
        $MID = $arrMemberInfo['MID'];
        $query = "UPDATE membership.memberinfo SET FirstName = '$FirstName',
                                     MiddleName = '$MiddleName', LastName = '$LastName', NickName = '$NickName',
                                     Birthdate = '$Birthdate', Gender = $Gender, Email = '$Email', AlternateEmail = '$AlternateEmail',
                                     MobileNumber = '$MobileNumber', AlternateMobileNumber = '$AlternateMobileNumber', NationalityID = '$NationalityID',
                                     OccupationID = '$OccupationID', Address1 = '$Address1', Address2 = '$Address2',
                                     IdentificationID = '$IdentificationID', IdentificationNumber = '$IdentificationNumber', IsSmoker = '$IsSmoker'
                                     WHERE MID = '$MID'";
        parent::ExecuteQuery($query);
    }
    
    public function updateProfileDateUpdated($HiddenMID, $arrMemberInfo, $aid) {
        $arrMemberInfo['DateUpdated'] = 'NOW(6)';
        $DateUpdated = $arrMemberInfo['DateUpdated'];
        $query = "UPDATE membership.memberinfo SET DateUpdated = '$DateUpdated',
                                     UpdatedByAID = '$aid'
                                     WHERE MID = '$HiddenMID'";
        parent::ExecuteQuery($query);
    }

    public function updateProfileAdmin($HiddenMID, $arrMemberInfo) {
        $FirstName = $arrMemberInfo['FirstName'];
        $MiddleName = $arrMemberInfo['MiddleName'];
        $LastName = $arrMemberInfo['LastName'];
        $NickName = $arrMemberInfo['NickName'];
        $Birthdate = $arrMemberInfo['Birthdate'];
        $Gender = $arrMemberInfo['Gender'];
        $Email = $arrMemberInfo['Email'];
        $AlternateEmail = $arrMemberInfo['AlternateEmail'];
        $MobileNumber = $arrMemberInfo['MobileNumber'];
        $AlternateMobileNumber = $arrMemberInfo['AlternateMobileNumber'];
        $NationalityID = $arrMemberInfo['NationalityID'];
        $OccupationID = $arrMemberInfo['OccupationID'];
        $Address1 = $arrMemberInfo['Address1'];
        $Address2 = $arrMemberInfo['Address2'];
        $IdentificationID = $arrMemberInfo['IdentificationID'];
        $IdentificationNumber = $arrMemberInfo['IdentificationNumber'];
        $IsSmoker = $arrMemberInfo['IsSmoker'];
        $query = "UPDATE membership.memberinfo SET FirstName = '$FirstName',
                                     MiddleName = '$MiddleName', LastName = '$LastName', NickName = '$NickName',
                                     Birthdate = '$Birthdate', Gender = $Gender, Email = '$Email', AlternateEmail = '$AlternateEmail',
                                     MobileNumber = '$MobileNumber', AlternateMobileNumber = '$AlternateMobileNumber', NationalityID = '$NationalityID',
                                     OccupationID = '$OccupationID', Address1 = '$Address1', Address2 = '$Address2',
                                     IdentificationID = '$IdentificationID', IdentificationNumber = '$IdentificationNumber', IsSmoker = '$IsSmoker',
                                     UpdatedByAID = '$HiddenMID'
                                     WHERE MID = '$HiddenMID'";
        parent::ExecuteQuery($query);
    }
    
    public function updateProfileDateUpdatedAdmin($HiddenMID, $arrMemberInfo, $aid) {
        $arrMemberInfo['DateUpdated'] = 'NOW(6)';
        $DateUpdated = $arrMemberInfo['DateUpdated'];
        $query = "UPDATE membership.memberinfo SET DateUpdated = '$DateUpdated',
                                     UpdatedByAID = '$aid'
                                     WHERE MID = '$HiddenMID'";
        parent::ExecuteQuery($query);
    }

    function updateProfileForCouponAjax($arrEntries) {
        unset($_SESSION["PreviousRemdeption"]);
        $this->Identity = "MemberInfoID";
        parse_str($arrEntries, $entries);
        if (isset($entries["TermsAndConditions"])) {
            unset($entries["TermsAndConditions"]);
        }

        foreach ($entries as $key => $val) {
            $entries[$key] = urldecode($val);
        }
        //$query = "Update tbl_Players set Name='$name', BirthDate='$birthdate', Address='$address', CityID='$city', RegionID='$region', EmailAdd='$email', ContactNumber='$contactno' where ID = $playerid;";
        parent::UpdateByArray($entries);
        //$retval = $this->LastQuery;
        if ($this->HasError) {
            $retval = $this->getError();
        } else {
            $retval = "Profile Updated Successfully.";
        }
//        App::LoadCore("File.class.php");
//        $filename = dirname(__FILE__) . "/posts.txt";
//        $fp = new File($filename);
//        $fp->WriteAppend("Last Query: " . $this->LastQuery . "\r\n");
        return $retval;
    }

    function updateProfileWithNoEmail($arrEntries) {
        $_MemberInfo = new MemberInfo();
        
        unset($_SESSION["PreviousRedemption"]);
        $this->Identity = "MemberInfoID";
        parse_str($arrEntries, $entries);
        if (isset($entries["TermsAndConditions"])) {
            unset($entries["TermsAndConditions"]);
        }
        foreach ($entries as $key => $val) {
            $entries[$key] = urldecode($val);
        }
        if (isset($_SESSION["CardRed"])) {
            $entries["MemberInfoID"] = $_SESSION["CardRed"]["MemberInfoID"];
        }
        $forRedemption = 1;
        //check if email address already exist
        $isExist = $this->getMIDByEmailSP($entries['Email']);
        //var_dump($isExist);exit;
        if ($isExist[0]['MID'] != "") { //email exists
            parse_str($arrEntries, $entries);
            $retval = "Sorry, " . $entries['Email'] . " already belongs to an existing account. Please enter another email address!";
        } else {
            //var_dump($arrEntries, $entries);exit;
            $MID = $this->getMIDByMemberInfoID($entries["MemberInfoID"]);
            $_MemberInfo->updateMemberProfileSP($MID, $entries, $forRedemption);
            $retval = "Profile Updated Successfully.";
        }
        return $retval;
    }
    
    
    

    /**
     * @author Gerardo V. Jagolino Jr.
     * @return object array
     * get member information MID and Birthdate 
     */
    public function getBirthdays($gender, $fromdate, $todate) {

        $query = "SELECT mi.MemberInfoID, mi.MID, mi.Birthdate FROM memberinfo mi INNER JOIN members m ON m.MID = mi.MID 
            WHERE mi.Gender = $gender
                AND mi.DateVerified >= '$fromdate' AND mi.DateVerified <= '$todate' AND m.Status IN (1,5);";

        return parent::RunQuery($query);
    }

    /**
     * @author JunJun S. Hernandez
     * @return object array
     * check if email exists
     */
    public function checkIfEmailExists($Email) {
        $MID = $_SESSION["MemberInfo"]["MID"];
        $query = "SELECT COUNT(Email) AS COUNT FROM membership.memberinfo WHERE MID != $MID AND Email = '$Email' AND Status = 1;";
        return parent::RunQuery($query);
    }

    public function getEmailByMID() {
        $MID = $_SESSION["MemberInfo"]["MID"];
        $query = "SELECT Email FROM membership.memberinfo WHERE MID = $MID;";
        return parent::RunQuery($query);
    }
    
    public function checkIfEmailExistsWithMID($MID, $Email) {
        $query = "SELECT COUNT(Email) AS COUNT FROM memberinfo WHERE MID != $MID AND Email = '$Email' AND Status = 9;";
        return parent::RunQuery($query);
    }
    
    public function getMID($Email) {
        $query = "SELECT MID FROM membership.members WHERE UserName = '$Email'";
        return parent::RunQuery($query);
    }
    
    public function getMIDByFirstName($Name) {
        $query = "SELECT MID FROM memberinfo WHERE FirstName LIKE '$Name'";
        return parent::RunQuery($query);
    }
    
    public function getMIDByLastName($Name) {
        $query = "SELECT MID FROM memberinfo WHERE LastName LIKE '$Name'";
        return parent::RunQuery($query);
    }
    
    public function getMIDByEmail($Email) {
        $query = "SELECT MID FROM memberinfo WHERE Email = '$Email'";
        return parent::RunQuery($query);
    }
    
    public function getEmailByMID2($MID) {
       
        $query = "SELECT Email FROM membership.memberinfo WHERE MID = $MID;";
        return parent::RunQuery($query);
    }
    
    public function updateAppendUsingMID($status, $MID, $email) {
        $query = "UPDATE " . $this->TableName . " SET Status = " . $status . ", Email = '$email' WHERE MID = " . $MID;
        $this->ExecuteQuery($query);
        if ($this->HasError) {
            App::SetErrorMessage($this->getError());
            return false;
        }
    }
    
    public function getFirstNameByMID($MID){
        $query = "SELECT FirstName, LastName FROM membership.memberinfo WHERE MID = $MID;";
        return parent::RunQuery($query);
    }
    
    public function getMemberDtlsByMID($MID) {
        $query = "CALL membership.sp_select_data(1, 1, 0, ".$MID.", 
                                                                 'FirstName, MiddleName, LastName, IdentificationNumber,Email',
                                                                 @RetCode, @Ret2, @Ret3)";
        $result = parent::RunQuery($query);
        return explode(";",$result[0]['OUTfldListRet']);
    }
     public function getMemberByMID($MID) {
        $query = "CALL membership.sp_select_data(1, 0, 'MID', ".$MID.", 
                                                                 'UserName',  
                                                                 @RetCode, @Ret2, @Ret3)";
        $result = parent::RunQuery($query);
        return explode(";",$result[0]['OUTfldListRet']);
    }
    public function getMemberInfoByNameSP($name) {
        $query = "CALL membership.sp_select_data(1, 1, 7, '$name', 'mi.MID,mi.FirstName,mi.LastName,mi.Birthdate,mi.IdentificationNumber,ri.IdentificationName', @OUTRetCode, @OUTRetMessage, @OUTfldListRet)";
        $result = parent::RunQuery($query);
        //get all records
        $arr_result = array();
        if (count($result) > 0) {
            foreach ($result as $row) {
                $exp = explode(';', $row['OUTfldListRet']);
                $arr_result[] = array('MID' => $exp[0], 
                                      'FirstName' => $exp[1], 
                                      'LastName' => $exp[2], 
                                      'Birthdate' => $exp[3], 
                                      'IdentificationNumber' => $exp[4], 
                                      'IdentificationName' => $exp[5]);
            }
        }
        return $arr_result;
    }
    public function getPlayerName($MID) {
        $query = "CALL membership.sp_select_data(1, 1, 0, $MID, 'FirstName,MiddleName,LastName,MID', @ResultCode, @ResultMsg, @ResultField)";
        $result = parent::RunQuery($query);

        $exp = explode(";", $result[0]['OUTfldListRet']);
        return array(0 => array('FirstName' => $exp[0], 
                                'MiddleName' => $exp[1], 
                                'LastName' => $exp[2], 
                                'MID' => $exp[3]));
    }
    public function getEmailSP($MID) {
        $query = "CALL membership.sp_select_data(1, 1, 0, $MID, 'Email', @ResultCode, @ResultMsg, @ResultField)";
        $result = parent::RunQuery($query);
        return $result[0]["OUTfldListRet"];
    }
    public function getMIDByEmailSP($email, $isTemp = null) {
        if (is_null($isTemp)) {
            $query = "CALL membership.sp_select_data(1, 1, 2, '$email','MID',@ReturnCode, @ReturnMessage, @ReturnFields)";
        }
        else {
            $query = "CALL membership.sp_select_data(0, 1, 2, '$email','MID',@ReturnCode, @ReturnMessage, @ReturnFields)";
        }
        $result = parent::RunQuery($query);
        $exp = explode(";",$result[0]['OUTfldListRet']);
        
        return array(0 => array('MID' => $exp[0]));
    }
    public function getMemberInfoByUsernameSP($Username) {
        
        $query = "CALL membership.sp_select_data(1, 1, 4, '$Username', 'mi.FirstName,mi.MiddleName,mi.LastName,mi.NickName,mi.Email,mi.AlternateEmail,mi.MobileNumber,mi.AlternateMobileNumber,mi.Address1,mi.Address2,mi.IdentificationNumber,mi.MID', @ResultCode, @ResultMsg, @ResultFields)";
        $result = parent::RunQuery($query);
        
        if (count($result) > 0) {
            $exp = explode(";", $result[0]['OUTfldListRet']);
            $MID = $exp[11];
            $result2 = $this->getGenericInfo($MID);

            $arrdtls = array(0 => array('FirstName' => $exp[0], 
                                    'MiddleName' => $exp[1], 
                                    'LastName' => $exp[2], 
                                    'NickName' => $exp[3],
                                    'Email' => $exp[4], 
                                    'AlternateEmail' => $exp[5], 
                                    'MobileNumber' => $exp[6], 
                                    'AlternateMobileNumber' => $exp[7], 
                                    'Address1' => $exp[8], 
                                    'Address2' => $exp[9], 
                                    'IdentificationNumber' => $exp[10], 
                                    'MID' => $MID));

            return array(array_merge($arrdtls[0], $result2[0]));
        }
        else {
            return array();
        }
    }
    /**
     * @author Mark Kenneth Esguerra
     * @param type $MID
     * @param type $Email
     * @return type
     */
    public function checkIfEmailExistsWithMIDSP($MID, $Email) {
        $query = "CALL membership.sp_select_data(1, 1, 5, '$MID,$Email', 'MID,Email', @OUTRetCode, @OUTRetMessage, @OUTfldListRet)";
        $result = parent::RunQuery($query);
        $exp = explode(";", $result[0]['OUTfldListRet']);
        
        return array(0 => array('COUNT' => $exp[0]));        
    }
    /**
     * @author Mark Kenneth Esguerra
     * @date June 24, 2015
     * @param type $HiddenMID
     * @param type $arrMemberInfo
     */
    public function updateMemberProfileSP($HiddenMID, $arrMemberInfo, $forRedemption = null){
        if (is_null($forRedemption)) {
            $FirstName = $arrMemberInfo['FirstName'];
            $MiddleName = $arrMemberInfo['MiddleName'];
            $LastName = $arrMemberInfo['LastName'];
            $NickName = $arrMemberInfo['NickName'];
            $Birthdate = $arrMemberInfo['Birthdate'];
            $Gender = $arrMemberInfo['Gender'];
            $Email = $arrMemberInfo['Email'];
            $AlternateEmail = $arrMemberInfo['AlternateEmail'];
            $MobileNumber = $arrMemberInfo['MobileNumber'];
            $AlternateMobileNumber = $arrMemberInfo['AlternateMobileNumber'];
            $NationalityID = $arrMemberInfo['NationalityID'];
            $OccupationID = $arrMemberInfo['OccupationID'];
            $Address1 = $arrMemberInfo['Address1'];
            $Address2 = $arrMemberInfo['Address2'];
            $IdentificationID = $arrMemberInfo['IdentificationID'];
            $IdentificationNumber = $arrMemberInfo['IdentificationNumber'];
            $IsSmoker = $arrMemberInfo['IsSmoker'];
            
            $field_to_update = 'FirstName,MiddleName,LastName,NickName,Email,AlternateEmail,MobileNumber,AlternateMobileNumber,Address1,Address2,IdentificationNumber';
            $query = "CALL membership.sp_update_data(1, 1, 'MID', $HiddenMID, '$field_to_update','$FirstName;$MiddleName;$LastName;$NickName;$Email;$AlternateEmail;$MobileNumber;$AlternateMobileNumber;$Address1;$Address2;$IdentificationNumber', @OUT_intResultCode, @OUT_intResultMsg)";
            $result = parent::ExecuteQuery($query);
            if (count($result > 0)){
                if ($result[0]['OUT_intResultCode'] == 0) {
                    $query2 = "UPDATE membership.memberinfo SET Birthdate = '$Birthdate', 
                                                                Gender = $Gender, 
                                                                NationalityID = $NationalityID,
                                                                OccupationID = $OccupationID, 
                                                                IdentificationID = $IdentificationID, 
                                                                IsSmoker = $IsSmoker 
                               WHERE MID = $HiddenMID";
                    parent::ExecuteQuery($query2);
                }
            }
        }
        else {
            $FirstName = $arrMemberInfo['FirstName'];
            $LastName = $arrMemberInfo['LastName'];
            $Birthdate = $arrMemberInfo['Birthdate'];
            $Email = $arrMemberInfo['Email'];
            $MobileNumber = $arrMemberInfo['MobileNumber'];
            $Address1 = $arrMemberInfo['Address1'];
            
            $field_to_update = 'FirstName,LastName,Email,MobileNumber,Address1';
            $query = "CALL membership.sp_update_data(1, 1, 'MID', $HiddenMID, '$field_to_update','$FirstName;$LastName;$Email;$MobileNumber;$Address1', @OUT_intResultCode, @OUT_intResultMsg)";
            $result = parent::ExecuteQuery($query);
            if (count($result > 0)){
                if ($result[0]['OUT_intResultCode'] == 0) {
                    $query2 = "UPDATE membership.memberinfo SET Birthdate = '$Birthdate' 
                               WHERE MID = $HiddenMID";
                    parent::ExecuteQuery($query2);
                }
            }
            
        }
    }
    private function getMIDByMemberInfoID ($MemInfoID) {
        $query = "SELECT MID FROM memberinfo WHERE MemberInfoID = $MemInfoID";
        $result = parent::RunQuery($query);
        
        return $result[0]['MID'];
    }
}

?>
