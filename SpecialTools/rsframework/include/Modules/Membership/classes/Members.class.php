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

                    if (empty($oldCardEmail)) {
                        $UserName = $newCard;
                    } else {
                        $UserName = $oldCardEmail;
                    }

                    $_OldCards = new OldCards();
                    $_Cards = new Cards();

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

                                    App::LoadModuleClass("CasinoProvider", "PlayTechAPI");
                                    App::LoadModuleClass("CasinoProvider", "CasinoProviders");
                                    App::LoadModuleClass("Kronus", "CasinoServices");

                                    $_CasinoServices = new CasinoServices();
                                    $casinoservices = $_CasinoServices->getUserBasedCasinoServices();

                                    foreach ($casinoservices as $casinoservice) {

                                        $serviceID = $casinoservice['ServiceID'];
                                        $MemberServiceMID = $MID;

                                        $this->TableName = "membership.memberservices";

                                        switch ($serviceID) {

                                            case CasinoProviders::PT;

                                                $arrServices = $_CasinoServices->generateCasinoAccounts($MemberServiceMID, $serviceID, $isVIP);

                                                //$this->InsertMultiple($arrServices);

                                                /*
                                                 * Member account info
                                                 */
                                                $userName = $arrServices[0]['ServiceUsername'];
                                                $password = $arrServices[0]['ServicePassword'];

                                                //Create fake info base on MID
                                                $email = $MID . "@philweb.com.ph";
                                                $firstName = "NA";
                                                $lastName = "NA";
                                                $birthDate = "1970-01-01";
                                                $address = "NA";
                                                $city = "NA";
                                                $phone = '123-4567';
                                                $zip = 'NA';
                                                $countryCode = 'PH';

                                                //$arrServices[0]['isVIP'] == 0 ? $vipLevel = 1 : $vipLevel = 2;
                                                $vipLevel = $arrServices[0]['VIPLevel'];

                                                /*
                                                 * PlayTech Configurations
                                                 */
                                                $arrplayeruri = App::getParam("player_api");
                                                $URI = $arrplayeruri[$serviceID - 1];
                                                $casino = App::getParam("pt_casino_name");
                                                $playerSecretKey = App::getParam("pt_secret_key");
                                                
                                                $arrServices[0]['isVIP'] == 0 ? $vipLevel = App::getParam("ptreg") : $vipLevel = App::getParam("ptvip");

                                                $playtechAPI = new PlayTechAPI($URI, $casino, $playerSecretKey);

                                                /*
                                                 * Create account
                                                 */
                                                $apiResult = $playtechAPI->NewPlayer($userName, $password, $email, $firstName, $lastName, $birthDate, $address, $city, $countryCode, $phone, $zip, $vipLevel);

                                                break;

                                            case CasinoProviders::MG;
                                                break;
                                            case CasinoProviders::RTG_ALPHA_11;
                                                break;
                                            case CasinoProviders::RTG_GAMMA_11;
                                                break;
                                            case CasinoProviders::RTG_SIGMA_11;
                                                break;
                                            default:
                                                break;
                                        }
                                    }

                                    $result = $apiResult['transaction']['@attributes']['result'];

                                    if ($result == 'OK') {
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

                                        $arrServices[0]['PlayerCode'] = $playerCode;

                                        $this->InsertMultiple($arrServices);
                                        
                                        $this->CommitTransaction();
                                        return array('status' => 'OK', 'error' => '');
                                    } else {
                                        $this->RollBackTransaction();
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
}

?>
