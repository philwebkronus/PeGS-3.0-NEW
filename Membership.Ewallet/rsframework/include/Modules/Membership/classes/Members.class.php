<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-04-08
 * Company: Philweb
 * ***************** */

class Members extends BaseEntity {

    public $hashpassword;
    public $password;

    function Members() {

        $this->ConnString = "membership";
        $this->TableName = "membership.members";
        $this->Identity = "MID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }

    public function updatePasswordUsingMID($MID, $password) {
        $query = "UPDATE $this->TableName SET Password = md5('$password') WHERE MID = $MID";
        parent::ExecuteQuery($query);
        if ($this->HasError) {
            App::SetErrorMessage($this->getError());
            return false;
        }
    }

    public function updateForChangePasswordUsingMID($MID, $changepassword) {
        $query = "UPDATE $this->TableName SET ForChangePassword = $changepassword WHERE MID = $MID";
        parent::ExecuteQuery($query);
        if ($this->HasError) {
            App::SetErrorMessage($this->getError());
            return false;
        }
    }

    public function getAllBannedAccountsInfo() {
        $query = "SELECT m.MID, mc.MemberCardID, mc.CardNumber, mi.DateCreated as DateCreated, YEAR(current_date)-YEAR(mi.Birthdate) as Age, 
                            mi.DateVerified, mi.Gender, rn.Name as Nationality
                            FROM $this->TableName m
                            INNER JOIN loyaltydb.membercards mc ON mc.MID = m.MID
                            INNER JOIN membership.memberinfo mi ON mi.MID = mc.MID
                            INNER JOIN membership.ref_nationality rn ON rn.NationalityID = mi.NationalityID
                            WHERE m.Status = 5 AND mc.Status = 9 ORDER BY mc.CardNumber ASC;";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function getForChangePasswordUsingCardNumber($CardNumber) {
        $query = "SELECT m.ForChangePassword FROM $this->TableName m
                            INNER JOIN loyaltydb.membercards mc ON mc.MID =m.MID
                            WHERE mc.CardNumber = '$CardNumber' ";
        $result = parent::RunQuery($query);
        return $result[0]['ForChangePassword'];
    }

    function Migrate($arrMembers, $arrMemberInfo, $AID, $siteid, $loyaltyCard, $newCard, $oldCardEmail, $isVIP, $isTemp = true) {

        list($year, $month, $day) = preg_split("/\-/", $arrMemberInfo['Birthdate']);
        $this->StartTransaction();
        try {
            App::LoadCore('Randomizer.class.php');
            $randomizer = new Randomizer();

            /**
             * If records are from Old Loyalty Card
             */
            if (!$isTemp) {


                $password = $month . $day . $year;
                $hashpassword = md5($password);
                $arrMembers['Password'] = $hashpassword;
                $arrMembers['IsVIP'] = $isVIP;
                $this->password = $password;
                $this->hashpassword = $hashpassword;
            }

            $this->Insert($arrMembers);

            if (!App::HasError()) {
                $this->TableName = "membership.memberinfo";
                $MID = $this->LastInsertID;
                $arrMemberInfo['MID'] = $MID;

                $this->Insert($arrMemberInfo);

                if (!App::HasError()) {
                    App::LoadModuleClass("Loyalty", "OldCards");
                    App::LoadModuleClass("Loyalty", "Cards");
                    App::LoadModuleClass("Membership", "MemberServices");
                    App::LoadModuleClass("Membership", "PcwsWrapper");
                    
                    if (empty($oldCardEmail)) {
                        $UserName = $newCard;
                    } else {
                        $UserName = $oldCardEmail;
                    }

                    $_OldCards = new OldCards();
                    $_Cards = new Cards();
                    $_Log = new AuditTrail();
                    $_MemberServices = new MemberServices();
                    $_PcwsWrapper = new PcwsWrapper();
                    
                    $datecreated = "now_usec()";

                    $ArrCardID = $_OldCards->getOldCardDetails($loyaltyCard);
                    $ArrayOldCardID = $ArrCardID[0];
                    $ArrNewCardID = $_Cards->getCardInfo($newCard);
                    $ArrayNewCardID = $ArrNewCardID[0];

                    App::LoadModuleClass("Loyalty", "CardStatus");
                    $this->TableName = "loyaltydb.membercards";

                    $arrMemberCards['MID'] = $MID;
                    $arrMemberCards['CardID'] = $ArrayNewCardID['CardID'];
                    $arrMemberCards['SiteID'] = $siteid;
                    $arrMemberCards['CardNumber'] = $ArrayNewCardID['CardNumber'];
                    $arrMemberCards['LifetimePoints'] = $ArrayOldCardID['LifetimePoints'];
                    $arrMemberCards['CurrentPoints'] = $ArrayOldCardID['CurrentPoints'];
                    $arrMemberCards['RedeemedPoints'] = $ArrayOldCardID['RedeemedPoints'];
                    $arrMemberCards['DateCreated'] = $datecreated;
                    $arrMemberCards['CreatedByAID'] = $AID;
                    $arrMemberCards['Status'] = CardStatus::ACTIVE;

                    $this->Insert($arrMemberCards);

                    if (!App::HasError()) {

                        $this->TableName = "loyaltydb.cards";

                        $cardID = $arrMemberCards['CardID'];
                        $cardType = $ArrayOldCardID['CardTypeID'];

                        $this->ExecuteQuery("UPDATE loyaltydb.cards SET Status = 1, 
                                CardTypeID = $cardType WHERE CardID = $cardID");

                        if (!App::HasError()) {
                            
                            $arrCardPointsTransfer['ToMemberCardID'] = $this->LastInsertID;
                            $arrCardPointsTransfer['MID'] = $MID;
                            $arrCardPointsTransfer['FromOldCardID'] = $ArrayOldCardID['OldCardID'];
                            $arrCardPointsTransfer['LifeTimePoints'] = $ArrayOldCardID['LifetimePoints'];
                            $arrCardPointsTransfer['CurrentPoints'] = $ArrayOldCardID['CurrentPoints'];
                            $arrCardPointsTransfer['RedeemedPoints'] = $ArrayOldCardID['RedeemedPoints'];
                            $arrCardPointsTransfer['DateTransferred'] = $datecreated;
                            $arrCardPointsTransfer['TransferredByAID'] = $AID;
                            $arrCardPointsTransfer['OldToNew'] = '1';

                            $this->TableName = "loyaltydb.cardpointstransfer";

                            $this->Insert($arrCardPointsTransfer);

                            if (!App::HasError()) {

                                $this->TableName = "loyaltydb.oldcards";

                                $oldCardID = $arrCardPointsTransfer["FromOldCardID"];

                                $this->ExecuteQuery("UPDATE loyaltydb.oldcards SET CardStatus = 4 WHERE OldCardID = $oldCardID");

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

                                    $casinoservices = $_CasinoServices->getUserBasedCasinoServices();
                                    $apierror = '';
                                    $arraycasinoservices = array();

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
                                    $genpassbatchid = $_GeneratedPasswordBatch->getExistingPasswordBatch($MID);
                                    if (empty($genpassbatchid)) {
                                        $genpassbatchid = $_GeneratedPasswordBatch->getInactivePasswordBatch();
                                    }
                                    
                                    foreach ($casinoservices as $casinoservice) {

                                        $serviceID = $casinoservice['ServiceID'];
                                        $serviceName = $casinoservice['ServiceGroupName'];
                                        $serviceGrpID = $casinoservice['ServiceGroupID'];
                                        $MemberServiceMID = $MID;

                                        $this->TableName = "membership.memberservices";
                                        
                                        if(strpos($serviceName, 'RTG2') !== false){
                                            
                                            //Generation of casino username to be passed in the casino API
                                            $casinoAccounts = $_CasinoServices->generateCasinoAccounts($MID, $serviceID, $serviceName, $isVIP);
                                            $userName = $casinoAccounts[0]['ServiceUsername'];
                                            $vipLevel = $casinoAccounts[0]['VIPLevel'];
                                            
                                            
                                            //Get hashed and plain password from password pool table
                                            $rpassword = $_GeneratedPasswordBatch->getPasswordByCasino($genpassbatchid, $serviceGrpID);
                                            if(!empty($rpassword)){
                                                $password = $rpassword[0]['PlainPassword'];
                                                $hashpassword = $rpassword[0]['EncryptedPassword'];
                                                
                                                $casinoAccounts[0]['ServicePassword'] = $password;
                                                $casinoAccounts[0]['HashedServicePassword'] = $hashpassword;

                                                //START: Call Casino Create Account API Method
//                                                $apiResult = $casinoAPI->createAccount($serviceName, $serviceID, $userName,$password,
//                                                        $firstName,$lastName, $birthDate, $gender, $email, $phone, $address, $city, $countryCode, $vipLevel);
                                                
                                                $apiResult = array("IsSucceed" => true, "ErrorID" => 1);
                                                
                                                if(!$apiResult){
                                                    $apierror = "There was an error encountered in mapping the RTG casino.";
                                                    $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $newCard.':Failed', $apiResult['ErrorMessage']); //logging of API Error
                                                } else {

                                                    //Checking if casino reply is successful, then push array result
                                                    if($apiResult['IsSucceed'] == true && $apiResult['ErrorID'] == 1){
                                                        
                                                        if($vipLevel == 1){
                                                            App::LoadModuleClass("CasinoProvider", "RealtimeGamingCashierAPI2");
                                                            
                                                            $serviceapi = App::getParam('service_api');
            
                                                            $url = $serviceapi[$serviceID - 1];
                                                            $certFilePath = App::getParam('rtg_cert_dir').$serviceID.'/cert.pem';
                                                            $keyFilePath = App::getParam('rtg_cert_dir').$serviceID.'/key.pem';

                                                            $_RTGCashierAPI = new RealtimeGamingCashierAPI2($url, $certFilePath, $keyFilePath, '');

                                                            $apiResult = $_RTGCashierAPI->GetPIDFromLogin($userName);

                                                            $pid = $apiResult['GetPIDFromLoginResult'];
                                                            
                                                            if(!empty($pid)){
                                                                $userID = 0;
            
                                                                $casinoAPI->ChangePlayerClassification($serviceName, $pid, $vipLevel, $userID, $serviceID);
                                                            }
                                                            
                                                        }
                                                        
                                                        array_push($arraycasinoservices, $casinoAccounts);
                                                    } else {

                                                        //Checking when casino reply is failed, validate if account was already existing
                                                        if($apiResult['ErrorID'] == 5){

                                                            //Get old password from the database
                                                            $memberservicesdetails = $_MemberServices->CheckMemberService($MID, $serviceID);

                                                            foreach ($memberservicesdetails as $val) {
                                                                $servpassword = $val['ServicePassword'];
                                                            }

                                                            //Call Casino API Change Password Method
                                                            $vapiResult = $casinoAPI->ChangePassword($serviceName, $userName, $servpassword, $password, $serviceID);

                                                            if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                                                                $apisuccess = 1;
                                                            else{
                                                                $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $newCard.':Failed', $vapiResult['ErrorMessage']);
                                                            }
                                                        } else {
                                                            $apierror = "There was an error encountered in mapping the RTG casino.";
                                                            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $newCard.':Failed', $apierror);
                                                        }
                                                    }
                                                }
                                            } else{
                                                $apierror = "No available plain and hashed password for RTG2 casino.";
                                                $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $newCard.':Failed', $apierror);
                                            }
                                        }

                                        if(strpos($serviceName, 'MG')){
                                                      $casinoAccounts = $_CasinoServices->generateCasinoAccounts( $MID, $serviceID, $serviceName, $isVIP);

                                                        /*
                                                         * Member account info
                                                         */
                                                        $userName = $casinoAccounts[0]['ServiceUsername'];

                                                        $existpassbatch = $_GeneratedPasswordBatch->getExistingPasswordBatch($MID);
                                                        if(empty($existpassbatch)){
                                                            $genpassbatch = $_GeneratedPasswordBatch->getInactivePasswordBatch();

                                                            $password = $genpassbatch[0]['PlainPassword'];
                                                            $hashpassword = $genpassbatch[0]['EncryptedPassword'];
                                                            $genpassbatchid = $genpassbatch[0]['GeneratedPasswordBatchID'];
                                                        }
                                                        else{
                                                            $password = $existpassbatch[0]['PlainPassword'];
                                                            $hashpassword = $existpassbatch[0]['EncryptedPassword'];
                                                            $genpassbatchid = $existpassbatch[0]['GeneratedPasswordBatchID'];
                                                        }

                                                        //$arrServices[0]['isVIP'] == 0 ? $vipLevel = 1 : $vipLevel = 2;
                                                        $vipLevel = $casinoAccounts[0]['VIPLevel'];

                                                        $casinoAccounts[0]['ServicePassword'] = $password;
                                                        $casinoAccounts[0]['HashedServicePassword'] = $hashpassword;

                                                        $casinoAPI = new CasinoAPI();
                                                        $apiResult = $casinoAPI->createAccount($serviceName, $serviceID, $userName,$password,
                                                                $firstName,$lastName, $birthDate, $gender, $email, $phone, $address, $city, $countryCode, $vipLevel);

                                                        if(!$apiResult){
                                                            $apierror = "There was an error encountered in mapping the MG casino.";

                                                            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $newCard.':Failed', $apiResult['ErrorMessage']);
                                                        }
                                                        else{
                                                            if($apiResult['IsSucceed'] == true){
                                                                //$this->InsertMultiple($casinoAccounts);
                                                                array_push($arraycasinoservices, $casinoAccounts);
                                                            } 
                                                            else if($apiResult['ErrorID'] == 1 || $apiResult['ErrorID'] == 5 || $apiResult['ErrorID'] == 3){
                                                                $vaccountExist = '';

                                                                //Call API to get Account Info
                                                                $vapiResult = $casinoAPI->GetAccountInfo($serviceName, $userName, $password, $serviceID);

                                                                //Verify if API Call was successful
                                                                if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                                                                {
                                                                     $vaccountExist = $vapiResult['AccountInfo']['UserExists'];

                                                                     //check if account exists for MG Casino
                                                                     if($vaccountExist)
                                                                     {
                                                                         //Call API Change Password
                                                                        $vapiResult = $casinoAPI->ChangePassword($serviceName, $userName, $password, $password, $serviceID);
                                                                     }

                                                                     if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                                                                        $apisuccess = 1;
                                                                     else
                                                                        $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $newCard.':Failed', $vapiResult['ErrorMessage']);
                                                                }
                                                            }
                                                            else {
                                                                $apierror = "There was an error encountered in mapping the MG casino.";

                                                                $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $newCard.':Failed', $apierror);
                                                            }
                                                        }

                                                   }

                                        if(strpos($serviceName, 'PT') !== false){
                                                //Generation of casino username to be passed in the casino API
                                                $casinoAccounts = $_CasinoServices->generateCasinoAccounts( $MID, $serviceID, $serviceName, $isVIP);
                                                $userName = $casinoAccounts[0]['ServiceUsername'];
                                                $vipLevel = $casinoAccounts[0]['VIPLevel'];
                                                
                                                //Get hashed and plain password from password pool table
                                                 $rpassword = $_GeneratedPasswordBatch->getPasswordByCasino($genpassbatchid, $serviceGrpID);
                                                if(!empty($rpassword)){
                                                    $password = $rpassword[0]['PlainPassword'];
                                                    $hashpassword = $rpassword[0]['EncryptedPassword'];
                                                    
                                                    $casinoAccounts[0]['ServicePassword'] = $password;
                                                    $casinoAccounts[0]['HashedServicePassword'] = $hashpassword;

                                                    //START: Call Casino Create Account API Method
                                                    $apiResult = $casinoAPI->createAccount($serviceName, $serviceID, $userName,$password,
                                                            $firstName,$lastName, $birthDate, $gender, $email, $phone, $address, $city, $countryCode, $vipLevel);

                                                    if(!$apiResult){
                                                        $apierror = "There was an error encountered in mapping the PT casino.";
                                                        $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $newCard.':Failed', $apiResult['ErrorMessage']);
                                                    } else {
                                                        //Checking if casino reply is successful, then push array result
                                                        if($apiResult['IsSucceed'] == true && $apiResult['ErrorCode'] == 0){

                                                            App::LoadModuleClass("CasinoProvider", "PlayTechReportViewAPI");

                                                            $reportUri = App::getParam("pt_rpt_uri");
                                                            $casino = App::getParam("pt_rpt_casinoname");
                                                            $admin = App::getParam("pt_rpt_admin");
                                                            $password = App::getParam("pt_rpt_password");
                                                            $reportCode = App::getParam("pt_rpt_code");
                                                            $playerCode = null;

                                                            $_PTReportAPI = new PlayTechReportViewAPI($reportUri, $casino, $admin, $password);

                                                            $rptResult = $_PTReportAPI->export($reportCode, 'exportxml', array('username'=>$userName));

                                                            $playerCode = $rptResult['PlayerCode']; //get player code from PT Report API

                                                            $casinoAccounts[0]['PlayerCode'] = $playerCode;

                                                            array_push($arraycasinoservices, $casinoAccounts);
                                                        } else {
                                                            if($apiResult['ErrorCode'] == 1 || $apiResult['ErrorCode'] == 5 || $apiResult['ErrorCode'] == 3){
                                                                $vaccountExist = '';
                                                                $voldpw = '';

                                                                //Call Reset Password API if PT
                                                                $vapiResult = $casinoAPI->ChangePassword($serviceName, $userName, $voldpw, $password, $serviceID);

                                                                if(isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true)
                                                                    $apisuccess = 1;
                                                                else
                                                                    $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $newCard.':Failed', $vapiResult['ErrorMessage']);
                                                            } else {
                                                                $apierror = "There was an error encountered in mapping the PT casino.";

                                                                $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $newCard.':Failed', $apierror);
                                                            }
                                                        } 
                                                    }
                                                } else{
                                                    $apierror = "No available plain and hashed password for PT casino.";
                                                    $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $newCard.':Failed', $apierror);
                                                }
                                          }            
                                   }
                                   
                                   header("Content-Type:text/html");

                                   if(count($arraycasinoservices) > 0)              
                                   {
                                        $serviceID = 15;
                                        foreach ($arraycasinoservices as $casinoservices){
                                            $this->InsertMultiple($casinoservices);
                                        }                                        
                                        $_GeneratedPasswordBatch->updatePasswordBatch($MID, $genpassbatchid);
                                        $this->CommitTransaction();
                                        //add comp points
                                        $addCompPoints = $_PcwsWrapper->addCompPoints($ArrayNewCardID['CardNumber'], $siteid, $serviceID, $ArrayOldCardID['CurrentPoints'], 0);
                                        foreach ($addCompPoints as $row)
                                        {
                                            $errorCode = $row['ErrorCode'];
                                        }
                                        if ($errorCode == 0)
                                        {
                                            //update current points of membercard
                                            $this->updateCurrentPoints($MID);
                                        }
                                        return array('status' => 'OK', 'error' => '');
                                   }
                                   else
                                   {
                                        return array('status' => 'ERROR', 'error' => $apiResult['error']);
                                   }
                                } else {
                                    $this->RollBackTransaction();
                                    return array('status' => 'ERROR', 'error' => 'Failed updating old cards.');
                                }
                            } else {
                                $this->RollBackTransaction();
                                return array('status' => 'ERROR', 'error' => 'Failed transfering points.');
                            }
                        } else {
                            $this->RollBackTransaction();
                            if (strpos(App::GetErrorMessage(), " Integrity constraint violation: 1062 Duplicate entry") > 0) {
                                App::SetErrorMessage("Card ID already exists. Please retry the transaction.");

                                return array('status' => 'ERROR', 'error' => 'Failed migrating member details');
                            }
                            else
                                return array('status' => 'ERROR', 'error' => 'Failed updating card status.');
                        }
                    } else {
                        $this->RollBackTransaction();
                        if (strpos(App::GetErrorMessage(), " Integrity constraint violation: 1062 Duplicate entry") > 0) {
                            App::SetErrorMessage("Card ID already exists. Please retry the transaction.");

                            return array('status' => 'ERROR', 'error' => 'Failed migrating member details');
                        }
                        else
                            return array('status' => 'ERROR', 'error' => 'Failed inserting to member cards.');
                    }
                } else {
                    $this->RollBackTransaction();

                    if (strpos(App::GetErrorMessage(), " Integrity constraint violation: 1062 Duplicate entry") > 0) {
                        App::SetErrorMessage("Email already exists. Please choose a different email address.");

                        return array('status' => 'ERROR', 'error' => 'Failed migrating member details');
                    } else {
                        return array('status' => 'ERROR', 'error' => 'Failed migrating member details.');
                    }
                }
            } else {
                $this->RollBackTransaction();
                if (strpos(App::GetErrorMessage(), " Integrity constraint violation: 1062 Duplicate entry") > 0) {
                    App::SetErrorMessage("Email already exists. Please choose a different email address.");

                    return array('status' => 'ERROR', 'error' => 'Failed migrating member details');
                } else {
                    return array('status' => 'ERROR', 'error' => 'Failed migrating member details.');
                }
            }
        } catch (Exception $e) {
            $this->RollBackTransaction();
            return array('status' => 'ERROR', 'error' => $e->getMessage());
        }
    }

    function getMID($UserName) {
        $query = "Select MID, Password from members where UserName = '$UserName'";
        return parent::RunQuery($query);
    }

    function UpdateProfile($arrMemberInfo) {
        $this->TableName = "memberinfo";
        $this->Identity = "MID";

        $this->StartTransaction();
        try {
            $this->UpdateByArray($arrMemberInfo);
            if (!App::HasError()) {
                $this->CommitTransaction();
            } else {
                $this->RollBackTransaction();
            }
        } catch (Exception $e) {
            $this->RollBackTransaction();
        }
    }

    public function updateMemberStatusUsingMID($status, $MID) {
        $query = "UPDATE " . $this->TableName . " SET Status = " . $status . " WHERE MID = " . $MID;
        parent::ExecuteQuery($query);
        if ($this->HasError) {
            App::SetErrorMessage($this->getError());
            return false;
        }
    }
    
    
    public function TerminateUsingMID($status, $MID, $email) {
        $query = "UPDATE " . $this->TableName . " SET Status = " . $status . ", UserName = '$email'  WHERE MID = " . $MID;
        $this->ExecuteQuery($query);
        if ($this->HasError) {
            App::SetErrorMessage($this->getError());
            return false;
        }
    }

    /**
     * Login Authentication
     * @author
     * @modified Mark Kenneth Esguerra
     * @date July 19, 2013
     * @param string $username
     * @param string $password
     * @param string $hashing 
     */
    function Authenticate($username, $password, $hashing = '') {
        App::LoadModuleClass("Loyalty", "MemberCards");
        App::LoadModuleClass("Membership", "TempMembers");
        App::LoadCore("Validation.class.php");
        $validate = new Validation();
        
        //Check if the Username is in Membership_Temp and its already verified
        if ($validate->validateEmail($username)) {
            
            $query = "select * from membership.members where username='$username'";
            $result = parent::RunQuery($query);
        } else {
            $membercards = new MemberCards();
            $cardinfo = $membercards->getMIDByCard($username);

            if (is_array($cardinfo) && count($cardinfo) > 0) {
                if ($cardinfo[0]['Status'] == 1 || $cardinfo[0]['Status'] == 5) {
                    $MID = $cardinfo[0]['MID'];
                    $query = "select * from membership.members where MID='$MID'";
                    $result = parent::RunQuery($query);
                } elseif ($cardinfo[0]['Status'] == 9) {
                    $result = "Card is banned";
                } else {
                    $result = 0;
                }
            } else {
                $result = array();
            }
        }
        $retval = "";
        $strpass = $password;

        if ($hashing != '') {
            App::LoadCore("Hashing.class.php");

            if ($hashing == Hashing::MD5) {
                $strpass = md5($password);
            }
        }

        if (is_array($result) && count($result) > 0) {
            $row = $result[0];
            $mid = $row["MID"];

            switch ($row["Status"]) {
                case 1 :
                    if ($row["Password"] != $strpass)
                        App::SetErrorMessage("Invalid Password");
                    else
                        $retval = $row;
                    break;
                case 0 :
                    App::SetErrorMessage("Account Inactive");
                    break;
                case 2 :
                    App::SetErrorMessage("Account Suspended");
                    break;
                case 3 :
                    App::SetErrorMessage("Account Locked (Login Attempts)");
                    break;
                case 4 :
                    App::SetErrorMessage("Account Locked (By Admin)");
                    break;
                case 5:
                    App::SetErrorMessage("Account Locked (By Admin)");
                    break;
                case 6 :
                    App::SetErrorMessage("Account Terminated");
                    break;
                default :
                    App::SetErrorMessage("Invalid Account");
                    break;
            }
        }
        elseif (is_string($result)) {
            App::SetErrorMessage($result);
        } else if ($result == 0) {
            App::SetErrorMessage("Invalid Account.");
        } else {
            App::SetErrorMessage("Invalid Account");
            $_tempMembers = new TempMembers();

            $isTempAcctExist = $_tempMembers->chkTempUser($username);

            //check if account has no transactions yet in kronus cashier
            if ($isTempAcctExist > 0)
                App::SetErrorMessage("You need to transact at least one transaction before you can login.");
            else
                App::SetErrorMessage("Invalid Account.");
        }
        return $retval;
    }

    function IncrementLoginAttempts($mid) {
        $query = "update $this->TableName set LoginAttempts = LoginAttempts + 1 where MID=$mid";
        return parent::ExecuteQuery($query);
    }

    function LockAccountForAttempts($mid) {
        $query = "update $this->TableName set Status = 3, LoginAttempts = 0 where MID=$mid";
        return parent::ExecuteQuery($query);
    }

    function ResetLoginAttempts($mid) {
        $query = "update $this->TableName set LoginAttempts = 0 where MID=$mid";
        return parent::ExecuteQuery($query);
    }

    function getUserName($MID) {
        $query = "SELECT UserName FROM members WHERE MID = $MID";
        $result = parent::RunQuery($query);
        return $result[0]['UserName'];
    }

    /**
     * Check if email was already verified in live membership db
     * @author elperez
     * @date 06/25/13
     * @param str $email
     * @return int
     */
    function chkActiveVerifiedEmailAddress($email) {
        $query = "SELECT COUNT(mi.MID) ctractive FROM memberinfo mi
                  WHERE mi.Email = '$email'";
        $result = parent::RunQuery($query);

        return $result[0]['ctractive'];
    }
    
    public function updateMemberUsername($MID, $arrMemberInfo) {
        $Email = $arrMemberInfo['Email'];
        $Password = $arrMemberInfo['Password'];
        if($Password == ''){
            $query = "UPDATE membership.members SET UserName = '$Email' WHERE MID = $MID";
        } else {
            $query = "UPDATE membership.members SET UserName = '$Email', Password = '$Password' WHERE MID = $MID";
        }
        return parent::ExecuteQuery($query);
    }
        
    public function updateMemberUsernameAdmin($MID, $Email) {
        $query = "UPDATE membership.members SET UserName = '$Email' WHERE MID = $MID";
        return parent::ExecuteQuery($query);
    }
    
    public function getMIDbyUserName($username){
        $query = "SELECT MID FROM members WHERE UserName = '$username'";       
        $result = parent::RunQuery($query);
        
        return $result;
    }
    
    public function chkEmailAddress($email){
        $query = "SELECT COUNT(m.MID) ctrtemp FROM members m 
                INNER JOIN memberinfo mi ON m.MID = mi.MID
                WHERE mi.Email = '$email' AND m.Status IN (1,2,3,4,5);";
        
        $result = parent::RunQuery($query);
        
        return $result[0]['ctrtemp'];
    }
    
    public function TerminateAccount($memberstatus, $MID, $newemail, $email, $cardnumber, $checkemailcount){
         $errorLogger = new ErrorLogger();
         $this->StartTransaction();
         try {
             
             //get coupon redemption log id
             $query = "UPDATE " . $this->TableName . " SET Status = " . $memberstatus . ", UserName = '$newemail'  WHERE MID = " . $MID;
                    
            $ismembersupdated = parent::ExecuteQuery($query);

            //validate if raffle coupon was updated
            if($ismembersupdated) {

                //if record exist in temp tables
                if($checkemailcount > 0){
                    $query2 = "UPDATE membership_temp.memberinfo SET Status = 2, Email = '$newemail' WHERE Email = '$email'";

                    $ismeminfotempupdated = parent::ExecuteQuery($query2);

                    if($ismeminfotempupdated){
                        $query3 = "UPDATE membership_temp.members SET UserName = '$newemail' WHERE UserName = '$email'";

                        $ismemtempupdated = parent::ExecuteQuery($query3);

                        if(!$ismemtempupdated){

                            $this->RollBackTransaction();
                            $errMsg = "Player Termination: Transaction Failed.";
                            $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                            return $errMsg;
                        }
                    }
                    else{
                        $this->RollBackTransaction();
                        $errMsg = "Player Termination: Transaction Failed.";
                        $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                        return $errMsg;
                    }
                }

                if($memberstatus == "6"){
                    $memberinfostatus = 2;
                } else {
                    $memberinfostatus = strpos($cardnumber, 'eGames') !== false ? 6:1;
                }

                //update couponredemptionlogs
                $query4 = "UPDATE membership.memberinfo SET Status = " . $memberinfostatus . ", Email = '$newemail' WHERE MID = " . $MID;

                $ismeminfoupdated = parent::ExecuteQuery($query4);

                //validate is successfully updated
                if($ismeminfoupdated){

                        $query5 = "UPDATE loyaltydb.membercards SET Status = " . $memberinfostatus . " WHERE CardNumber = '" . $cardnumber . "'";

                        $ismembercardsupdated = parent::ExecuteQuery($query5);

                        if ($ismembercardsupdated)
                        {
                            $query6 = "UPDATE loyaltydb.cards SET Status = " . $memberinfostatus . " WHERE CardNumber = '" . $cardnumber . "'";
                            
                            $iscardsupdated = parent::ExecuteQuery($query6);
                            
                            if ($iscardsupdated)
                            {
                                $this->CommitTransaction();
                                return true;
                            }
                            else
                            {
                                $this->RollBackTransaction();
                                $errMsg = "Player Termination: Transaction Failed.";
                                $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                                return $errMsg;
                            }
                        }
                        else
                        {
                            $this->RollBackTransaction();
                            $errMsg = "Player Termination: Transaction Failed.";
                            $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                            return $errMsg;
                        }

                } else {
                    $this->RollBackTransaction();
                    $errMsg = "Player Termination: Transaction Failed.";
                    $errorLogger->log($errorLogger->logdate, "error", $errMsg);
                    return $errMsg;
                }

            } else {
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
    
    public function getVIP($mid){
        $query = "SELECT isVIP FROM members WHERE MID = '$mid'";       
        $result = parent::RunQuery($query);
        
        return $result;
    }
    
    
    public function checkEwalletStatus($mid){
        $query = "SELECT IsEwallet FROM members WHERE MID = '$mid'";       
        $result = parent::RunQuery($query);
        
        return $result;
    }
    
 
    /**
     * This function is use to update the player classification.
     * @author Noel Antonio 11-25-2013
     * @param tinyint $isVip (0 - Regular, 1 - VIP)
     * @param int $mid Member Card ID
     * @return boolean
     * 
     */
    public function changeIsVipByMid($isVip, $mid)
    {
        $query = "UPDATE $this->TableName SET IsVIP = '$isVip' WHERE MID = '$mid'";
        parent::ExecuteQuery($query);
    }
    
    public function getVIPLevel($MID){
        $query = "SELECT IsVIP FROM members WHERE MID = $MID;";
        
        $result = parent::RunQuery($query);
        
        return $result[0]['IsVIP'];
    }
    
    //@author fdlsison
    //@date 09-01-2014
    //@purpose updates player classification
    public function updatePlayerClassificationByMID($isVIP, $MID) {
        $query = "UPDATE $this->TableName
                  SET IsVIP = $isVIP
                  WHERE MID = $MID";
        
        return parent::ExecuteQuery($query);
    }
    
    //updatePin
    public function updatePin($pinDetails,$mid) {
       
        $newPin = sha1($pinDetails['NEWPIN']);
        $MID = $mid;
        
        $query = "UPDATE membership.members SET PIN = '$newPin',
                                     DatePINLastChange = now(6)
                                     WHERE MID = '$MID'";
        parent::ExecuteQuery($query);
    }
    public function updateCurrentPoints($MID) {
        
        $query = "UPDATE loyaltydb.membercards SET CurrentPoints = 0 WHERE MID = $MID";
        parent::ExecuteQuery($query);
    }
}

?>
