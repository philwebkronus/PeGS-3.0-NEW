<?php

/*
 * @author : owliber
 * @date : 2013-04-24
 */

class ActivateMember extends BaseEntity {

    var $CardNumber;
    var $MID;
    var $CardID;

    public function ActivateMember() {
        $this->ConnString = "membership";
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->TableName = "membership.members";
    }

    private function insertMembers($arrMembers, $arrMemberInfo) {
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
                . $arrMemberInfo['CivilStatusID'] . ","
                . $arrMemberInfo['RegisterForID'] . ","
                . "Null,@ReturnCode,@ReturnMessage,@ReturnLastInsertedID)";
        $result = parent::RunQuery($query);
        return array('TransCode' => $result[0]['@OUT_ResultCode'],
            'TransMsg' => $result[0]['@OUT_Result'],
            'MID' => $result[0]['@OUT_MID']);
    }

    /**
     * Migrate temporary member records
     * to permanent database
     * @param string $cardnumber
     */
    public function Migrate($cardnumber, $siteID) {
        //Membership DB

        $this->StartTransaction();

        $this->CardNumber = $cardnumber;

        App::LoadModuleClass("Membership", "TempMembers");
        App::LoadModuleClass("Membership", "TempMemberInfo");
        App::LoadModuleClass("Loyalty", "CardTypes");
        App::LoadModuleClass("Membership", "MemberServices");

        $_TempMembers = new TempMembers();
        $_TempMemberInfo = new TempMemberInfo();
        $_CardTypes = new CardTypes();
        $_Log = new AuditTrail();
        $_MemberServices = new MemberServices();

        $queryMember = "SELECT Password, DateCreated, DateVerified
                        FROM membership_temp.members
                        WHERE TemporaryAccountCode = '$this->CardNumber'";

        $result = $_TempMembers->RunQuery($queryMember);
        $accountpassword = $result[0]['Password'];
        $neededfields = 'UserName,MID';
        $query1 = "CALL membership.sp_select_data(0,0,1,'$this->CardNumber', '$neededfields', @ReturnCode, @ReturnMessage, @ReturnFields);";
        $data = $_MemberServices->RunQuery($query1);
        $keys = explode(",", $neededfields);
        $infodata = explode(';', $data[0]['OUTfldListRet']);
        foreach ($keys as $key => $value) {
            $result[0][trim($value, " '")] = $infodata[$key];
        }

        $arrMembers['UserName'] = $result[0]['UserName'];
        $arrMembers['Password'] = NULL;
        $password = $result[0]['Password'];
        $arrMembers['DateCreated'] = 'NOW(6)';
        $tempMID = $result[0]['MID'];
        $arrMembers['Status'] = 1;

        $queryMemberInfo = "SELECT Birthdate, Gender, NationalityID, OccupationID, ReferrerID, IdentificationID, RegistrationOrigin, EmailSubscription, 
                                                SMSSubscription, IsSmoker, IsCompleteInfo, DateVerified, ReferrerCode,RegisterForID,CivilStatusID
                                                FROM membership_temp.memberinfo mi
                                                    INNER JOIN membership_temp.members m ON mi.MID = m.MID
                                                WHERE m.TemporaryAccountCode = '$this->CardNumber'";
        $result2 = $_TempMemberInfo->RunQuery($queryMemberInfo);
        $neededfields = "FirstName,MiddleName,LastName,NickName,Email,AlternateEmail,MobileNumber,AlternateMobileNumber,Address1,Address2,IdentificationNumber";
        $queryMemberInfo2 = "CALL membership.sp_select_data(0,1,0,$tempMID, '$neededfields', @ReturnCode, @ReturnMessage, @ReturnFields);";

        $data2 = $_MemberServices->RunQuery($queryMemberInfo2);
        $keys = explode(",", $neededfields);
        $infodata = explode(';', $data2[0]['OUTfldListRet']);
        foreach ($keys as $key => $value) {
            $result2[0][trim($value, " '")] = $infodata[$key];
        }

        $arrMemberInfo['FirstName'] = $result2[0]['FirstName'];
        $arrMemberInfo['MiddleName'] = $result2[0]['MiddleName'];
        $arrMemberInfo['LastName'] = $result2[0]['LastName'];
        $arrMemberInfo['NickName'] = $result2[0]['NickName'];
        $arrMemberInfo['Address1'] = $result2[0]['Address1'];
        $arrMemberInfo['Address2'] = $result2[0]['Address2'];
        $arrMemberInfo['IdentificationNumber'] = $result2[0]['IdentificationNumber'];
        $arrMemberInfo['IdentificationID'] = $result2[0]['IdentificationID'];
        $arrMemberInfo['MobileNumber'] = $result2[0]['MobileNumber'];
        $arrMemberInfo['AlternateMobileNumber'] = $result2[0]['AlternateMobileNumber'];
        $arrMemberInfo['Email'] = $result2[0]['Email'];
        $arrMemberInfo['AlternateEmail'] = $result2[0]['AlternateEmail'];
        $arrMemberInfo['Birthdate'] = $result2[0]['Birthdate'];
        $arrMemberInfo['NationalityID'] = $result2[0]['NationalityID'];
        $arrMemberInfo['OccupationID'] = $result2[0]['OccupationID'];
        $arrMemberInfo['Gender'] = $result2[0]['Gender'];
        $arrMemberInfo['IsSmoker'] = $result2[0]['IsSmoker'];
        $arrMemberInfo['EmailSubscription'] = $result2[0]['EmailSubscription'];
        $arrMemberInfo['SMSSubscription'] = $result2[0]['SMSSubscription'];
        $arrMemberInfo['IsCompleteInfo'] = $result2[0]['IsCompleteInfo'];
        $arrMemberInfo['DateCreated'] = 'NOW(6)';
        $arrMemberInfo['DateVerified'] = $result[0]['DateVerified'];
        $arrMemberInfo['ReferrerCode'] = $result2[0]['ReferrerCode'];
        $arrMemberInfo['RegisterForID'] = $result2[0]['RegisterForID'];
        $arrMemberInfo['CivilStatusID'] = $result2[0]['CivilStatusID'];

        try {
            $IsInsert = $this->insertMembers($arrMembers, $arrMemberInfo);

            if (!App::HasError() && $IsInsert["MID"] > 0) {
                $this->MID = $IsInsert["MID"];
                $isupdated = $this->ExecuteQuery("UPDATE membership.members SET Password = '$password' WHERE MID = $this->MID");
                if ($isupdated) {
                    $this->TableName = "loyaltydb.cards";
                    App::LoadModuleClass("Loyalty", "CardStatus");
                    App::LoadModuleClass("Membership", "Helper");

                    $this->CardNumber = $cardnumber;

                    $arrEntries['CardNumber'] = $this->CardNumber;
                    $arrEntries['CardTypeID'] = $_CardTypes->getCardTypeByName('Temporary');
                    $arrEntries['DateCreated'] = 'NOW(6)';
                    $arrEntries['CreatedByAID'] = 1;
                    $arrEntries['Status'] = CardStatus::ACTIVE_TEMPORARY;

                    $this->Insert($arrEntries);
                    $this->CardID = $this->LastInsertID;

                    if (!App::HasError()) {
                        $this->TableName = "loyaltydb.membercards";

                        $arrMemberCard['MID'] = $this->MID;
                        $arrMemberCard['CardID'] = $this->CardID;
                        $arrMemberCard['CardNumber'] = $this->CardNumber;

                        $arrMemberCard['SiteID'] = $siteID; //To be supplied from the cashier
                        $arrMemberCard['DateCreated'] = 'NOW(6)';
                        $arrMemberCard['CreatedByAID'] = 1; //To be supplied from the cashier
                        $arrMemberCard['Status'] = CardStatus::ACTIVE_TEMPORARY;
                        ; //Active card

                        $this->Insert($arrMemberCard);

                        if (!App::HasError()) {
                            $this->CommitTransaction();

                            $this->StartTransaction();

                            App::LoadModuleClass("CasinoProvider", "PlayTechAPI");
                            App::LoadModuleClass("CasinoProvider", "CasinoProviders");
                            App::LoadModuleClass("Kronus", "CasinoServices");
                            App::LoadModuleClass("CasinoProvider", "CasinoAPI");
                            App::LoadModuleClass("Membership", "GeneratedPasswordBatch");

                            $_CasinoServices = new CasinoServices();
                            $_GeneratedPasswordBatch = new GeneratedPasswordBatch();

                            //Added : John Aaron Vida 
                            $UBusermode = App::getParam('UBusermode');
                            $casinoservices = $_CasinoServices->getUserBasedCasinoDetails($UBusermode);
                            // End

                            $apierror = '';
                            $arraycasinoservices = array();
                            $MID = $this->MID;

                            //Create dummy info base on MID
                            $email = $MID . "@philweb.com.ph";
                            $firstName = "NA";
                            $lastName = "NA";
                            $birthDate = "1970-01-01";
                            $address = "NA";
                            $city = "NA";
                            $phone = '123-4567';
                            $zip = 'NA';
                            $countryCode = 'PH';
                            $gender = 1;

                            $casinoAPI = new CasinoAPI();

                            //Get hashed and plain password from password pool table
                            $genpassbatchid = $_GeneratedPasswordBatch->getExistingPasswordBatch($this->MID);
                            if (empty($genpassbatchid)) {
                                $genpassbatchid = $_GeneratedPasswordBatch->getInactivePasswordBatch();
                            }

                            foreach ($casinoservices as $casinoservice) {

                                $serviceID = $casinoservice['ServiceID'];
                                $serviceName = $casinoservice['ServiceGroupName'];
                                $serviceGrpID = $casinoservice['ServiceGroupID'];
                                $MemberServiceMID = $MID;

                                $this->TableName = "membership.memberservices";

                                if (strpos($serviceName, 'RTG2') !== false) {

                                    //Generation of casino username to be passed in the casino API
                                    $casinoAccounts = $_CasinoServices->generateCasinoAccounts($MID, $serviceID, $serviceName);
                                    $userName = $casinoAccounts[0]['ServiceUsername'];
                                    $vipLevel = $casinoAccounts[0]['VIPLevel'];

                                    //Get hashed and plain password from password pool table
                                    $rpassword = $_GeneratedPasswordBatch->getPasswordByCasino($genpassbatchid, $serviceGrpID);
                                    if (!empty($rpassword)) {
                                        $password = $rpassword[0]['PlainPassword'];
                                        $hashpassword = $rpassword[0]['EncryptedPassword'];

                                        $casinoAccounts[0]['ServicePassword'] = $password;
                                        $casinoAccounts[0]['HashedServicePassword'] = $hashpassword;

                                        //START: Call Casino Create Account API Method
                                        $apiResult = $casinoAPI->createAccount($serviceName, $serviceID, $userName, $password, 
						$firstName, $lastName, $birthDate, $gender, $email, $phone, $address, $city, $countryCode, $vipLevel);

                                        if (!$apiResult) {
                                            $apierror = "There was an error encountered in mapping the RTG casino.";
                                            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber . ':Failed', $apiResult['ErrorMessage']); //logging of API Error
                                        } else {

                                            //Checking if casino reply is successful, then push array result
                                            if ($apiResult['IsSucceed'] == true && $apiResult['ErrorID'] == 1) {

                                                if ($vipLevel == 1) {
                                                    App::LoadModuleClass("CasinoProvider", "RealtimeGamingCashierAPI2");

                                                    $serviceapi = App::getParam('service_api');

                                                    $url = $serviceapi[$serviceID - 1];
                                                    $certFilePath = App::getParam('rtg_cert_dir') . $serviceID . '/cert.pem';
                                                    $keyFilePath = App::getParam('rtg_cert_dir') . $serviceID . '/key.pem';

                                                    $_RTGCashierAPI = new RealtimeGamingCashierAPI2($url, $certFilePath, $keyFilePath, '');

                                                    $apiResult = $_RTGCashierAPI->GetPIDFromLogin($userName);

                                                    $pid = $apiResult['GetPIDFromLoginResult'];

                                                    if (!empty($pid)) {
                                                        $userID = 0;

                                                        $casinoAPI->ChangePlayerClassification($serviceName, $pid, $vipLevel, $userID, $serviceID);
                                                    }
                                                }

                                                array_push($arraycasinoservices, $casinoAccounts);
                                            } else {

                                                //Checking when casino reply is failed, validate if account was already existing
                                                if ($apiResult['ErrorID'] == 5) {

                                                    //Get old password from the database
                                                    $memberservicesdetails = $_MemberServices->CheckMemberService($MID, $serviceID);

                                                    foreach ($memberservicesdetails as $val) {
                                                        $servpassword = $val['ServicePassword'];
                                                    }

                                                    //Call Casino API Change Password Method
                                                    $vapiResult = $casinoAPI->ChangePassword($serviceName, $userName, $servpassword, $password, $serviceID);

                                                    if (isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                                                        $apisuccess = 1;
                                                    else {
                                                        $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber . ':Failed', $vapiResult['ErrorMessage']);
                                                    }
                                                } else {
                                                    $apierror = "There was an error encountered in mapping the RTG casino.";
                                                    $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber . ':Failed', $apierror);
                                                }
                                            }
                                        }
                                    } else {
                                        $apierror = "No available plain and hashed password for RTG2 casino.";
                                        $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber . ':Failed', $apierror);
                                    }
                                }

                                $SameUsername = $userName;

                                if (strpos($serviceName, 'HAB') !== false) {

                                    //Generation of casino username to be passed in the casino API
                                    $casinoAccounts = $_CasinoServices->generateCasinoAccounts($MID, $serviceID, $serviceName);
                                    //$userName = $casinoAccounts[0]['ServiceUsername'];
                                    $userName = $SameUsername;
                                    $vipLevel = $casinoAccounts[0]['VIPLevel'];

                                    $casinoAccounts[0]['ServiceUsername'] = $SameUsername;

                                    //Get hashed and plain password from password pool table
                                    $rpassword = $_GeneratedPasswordBatch->getPasswordByCasino($genpassbatchid, $serviceGrpID);
                                    if (!empty($rpassword)) {
                                        $password = $rpassword[0]['PlainPassword'];
                                        $hashpassword = $rpassword[0]['EncryptedPassword'];

                                        $casinoAccounts[0]['ServicePassword'] = $password;
                                        $casinoAccounts[0]['HashedServicePassword'] = $hashpassword;

                                        //START: Call Casino Create Account API Method
                                        $apiResult = $casinoAPI->habaneroCreateAccount($serviceName, $serviceID, $userName, $password, $vipLevel);

                                        if (!$apiResult) {
                                            $apierror = "There was an error encountered in mapping the RTG casino.";
                                            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber . ':Failed', $apiResult['ErrorMessage']); //logging of API Error
                                        }
                                        array_push($arraycasinoservices, $casinoAccounts);
                                    } else {
                                        $apierror = "No available plain and hashed password for Habanero casino.";
                                        $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber . ':Failed', $apierror);
                                    }
                                }
                            }

                            header("Content-Type:text/html");

                            if (count($arraycasinoservices) > 0) {

                                foreach ($arraycasinoservices as $casinoservices) {
                                    $this->InsertMultiple($casinoservices);
                                }

                                $_GeneratedPasswordBatch->updatePasswordBatch($this->MID, $genpassbatchid);

                                $this->CommitTransaction();
                                return array("MID" => $this->MID, "status" => 'OK', "apierror" => $apierror, "password" => $accountpassword);
                            } else {
                                return array("MID" => $this->MID, "status" => 'error', "apierror" => $apierror, "password" => $accountpassword);
                            }

                            // CCT 12/11/2017 BEGIN - moved this here
                            $apierror = '';
                            return array("MID" => $this->MID, "status" => 'OK', "apierror" => $apierror, "password" => $accountpassword);
                            // CCT 12/11/2017 END - moved this here
                        } else {
                            $this->RollBackTransaction();
                            return array("MID" => $this->MID, "status" => 'error');
                        }
                    } else {
                        $this->RollBackTransaction();
                        return array("MID" => $this->MID, "status" => 'error');
                    }
                } else {
                    $this->RollBackTransaction();
                    return array("Failed to update password: MID " => $this->MID, "status" => "error");
                }
            } else {
                $this->RollBackTransaction();
                return array("Failed to transfer members data: MID" => $this->MID, "status" => 'error');
            }
        } catch (Exception $e) {
            $this->RollBackTransaction();
            return array("MID" => "", "status" => 'error');
        }
    }

}

?>
