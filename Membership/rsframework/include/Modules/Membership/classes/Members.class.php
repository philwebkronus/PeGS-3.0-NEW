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
    
    public function updatePasswordUsingMID($MID,$password)
    {
        $query = "UPDATE $this->TableName SET Password = md5('$password') WHERE MID = $MID";
        parent::ExecuteQuery($query);
        if($this->HasError){
            App::SetErrorMessage($this->getError());
            return false;
        }
    }
    
    public function updateForChangePasswordUsingMID($MID, $changepassword)
    {
        $query = "UPDATE $this->TableName SET ForChangePassword = $changepassword WHERE MID = $MID";
        parent::ExecuteQuery($query);
        if($this->HasError){
            App::SetErrorMessage($this->getError());
            return false;
        }
    }
    
    public function getAllBannedAccountsInfo()
    {
        $query = "SELECT m.MID, mc.MemberCardID, mc.CardNumber, mi.DateCreated as DateCreated, YEAR(current_date)-YEAR(mi.Birthdate) as Age, 
                            mi.Gender, rn.Name as Nationality
                            FROM $this->TableName m
                            INNER JOIN loyaltydb.membercards mc ON mc.MID = m.MID
                            INNER JOIN membership.memberinfo mi ON mi.MID = mc.MID
                            INNER JOIN membership.ref_nationality rn ON rn.NationalityID = mi.NationalityID
                            WHERE m.Status = 5 AND mc.Status = 9 ORDER BY mc.CardNumber ASC;";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function getForChangePasswordUsingCardNumber($CardNumber)
    {
        $query = "SELECT m.ForChangePassword FROM $this->TableName m
                            INNER JOIN loyaltydb.membercards mc ON mc.MID =m.MID
                            WHERE mc.CardNumber = '$CardNumber' ";
        $result = parent::RunQuery($query);
        return $result[0]['ForChangePassword'];
    }

    function Migrate($arrMembers, $arrMemberInfo, $AID, $siteid, $loyaltyCard, $newCard, $oldCardEmail, $isVIP, $isTemp = true) 
    {

                list($year,$month,$day) = preg_split("/\-/", $arrMemberInfo['Birthdate']);
                $this->StartTransaction();
            try {
                App::LoadCore('Randomizer.class.php');
                $randomizer = new Randomizer();

                /**
                 * If records are from Old Loyalty Card
                 */
                if (!$isTemp) {


                    $password = $month.$day.$year;
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

                        $getMID = $this->getMID($UserName);
                        $arrgetMID = $getMID[0];
                        $ArrCardID = $_OldCards->getOldCardDetails($loyaltyCard);
                        $ArrayOldCardID = $ArrCardID[0];
                        $ArrNewCardID = $_Cards->getCardInfo($newCard);
                        $ArrayNewCardID = $ArrNewCardID[0];

                        App::LoadModuleClass("Loyalty", "CardStatus");
                        $this->TableName = "loyaltydb.membercards";

                        $arrMemberCards['MID'] = $arrgetMID['MID'];
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
                                $arrCardPointsTransfer['MID'] = $arrgetMID['MID'];
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
                                            $MemberServiceMID = $arrgetMID['MID'];

                                            $this->TableName = "membership.memberservices";

                                            switch ($serviceID) {

                                                case CasinoProviders::PT;                                                    

                                                    $arrServices = $_CasinoServices->generateCasinoAccounts($MemberServiceMID, $serviceID, $isVIP);

                                                    $this->InsertMultiple($arrServices);                                            

                                                   /*
                                                    * Member account info
                                                    */
                                                    $userName = $arrServices[0]['ServiceUsername'];
                                                    $password = $arrServices[0]['ServicePassword'];

                                                    //Create fake info base on MID
                                                    $email = $MID."@philweb.com.ph";                                        
                                                    $firstName = "NA";
                                                    $lastName = "NA";
                                                    $birthDate = "1970-01-01";
                                                    $address = "NA";
                                                    $city = "NA";
                                                    $phone = '123-4567';                                        
                                                    $zip = 'NA';
                                                    $countryCode = 'PH';

                                                    $arrServices[0]['isVIP'] == 0 ? $vipLevel = 1 : $vipLevel = 2;

                                                    /*
                                                     * PlayTech Configurations
                                                     */
                                                    $URI = 'https://extdev-devhead-cashier.extdev.eu';
                                                    $casino = 'playtech800041';
                                                    $playerSecretKey = 'PhilWeb123';
                                                    //$depositSecretKey = 'PhilWeb123';
                                                    //$withdrawSecretkey = 'PhilWeb123';                

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
                                            $this->CommitTransaction();
                                            return array('status'=>'OK','error'=>'');
                                        } else {
                                            $this->RollBackTransaction();
                                            return array('status'=>'ERROR','error'=>$apiResult['error']);
                                        }
                                    } else {
                                        $this->RollBackTransaction();
                                        return array('status'=>'ERROR','error'=>'Failed updating old cards.');
                                    }
                                } else {
                                    $this->RollBackTransaction();
                                    return array('status'=>'ERROR','error'=>'Failed transfering points.');
                                }
                            } else {
                                $this->RollBackTransaction();
                                if (strpos(App::GetErrorMessage(), " Integrity constraint violation: 1062 Duplicate entry") > 0)
                        {
                            App::SetErrorMessage("Card ID already exists. Please retry the transaction.");

                            return array('status'=>'ERROR','error'=>'Failed migrating member details');               
                        }else
                                return array('status'=>'ERROR','error'=>'Failed updating card status.');
                            }
                        } else {
                            $this->RollBackTransaction();
                            if (strpos(App::GetErrorMessage(), " Integrity constraint violation: 1062 Duplicate entry") > 0)
                        {
                            App::SetErrorMessage("Card ID already exists. Please retry the transaction.");

                            return array('status'=>'ERROR','error'=>'Failed migrating member details');               
                        }else
                            return array('status'=>'ERROR','error'=>'Failed inserting to member cards.');
                        }
                    } else {
                        $this->RollBackTransaction();

                        if (strpos(App::GetErrorMessage(), " Integrity constraint violation: 1062 Duplicate entry") > 0)
                        {
                            App::SetErrorMessage("Email already exists. Please choose a different email address.");

                            return array('status'=>'ERROR','error'=>'Failed migrating member details');               
                        }
                        else
                        {
                            return array('status'=>'ERROR','error'=>'Failed migrating member details.');                        
                        }

                    }
                } else {
                    $this->RollBackTransaction();
                    if (strpos(App::GetErrorMessage(), " Integrity constraint violation: 1062 Duplicate entry") > 0)
                        {
                            App::SetErrorMessage("Email already exists. Please choose a different email address.");

                            return array('status'=>'ERROR','error'=>'Failed migrating member details');               
                        }
                        else
                        {
                            return array('status'=>'ERROR','error'=>'Failed migrating member details.');                        
                        }
                }
            } catch (Exception $e) {
                $this->RollBackTransaction();
                return array('status'=>'ERROR','error'=>$e->getMessage());
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
    
    public function updateMemberStatusUsingMID($status,$MID){
        $query = "UPDATE ".$this->TableName." SET Status = ".$status." WHERE MID = ".$MID;
        parent::ExecuteQuery($query);
        if($this->HasError){
            App::SetErrorMessage($this->getError());
            return false;
        }
    }

    function Authenticate($username, $password, $hashing = '') {
        App::LoadModuleClass("Loyalty", "MemberCards");
        App::LoadCore("Validation.class.php");
        $validate = new Validation();

        if ($validate->validateEmail($username)) {
            $query = "select * from members where username='$username'";
            $result = parent::RunQuery($query);
        } else {
            $membercards = new MemberCards();
            $cardinfo = $membercards->getMIDByCard($username);

            if (is_array($cardinfo) && count($cardinfo) > 0) {
                $MID = $cardinfo[0]['MID'];
                $query = "select * from members where MID='$MID'";
                $result = parent::RunQuery($query);
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

            switch($row["Status"]){
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
//            if ($row["Status"] == 1) {
//                if ($row["Password"] != $strpass) {
//                    if ($row["LoginAttempts"] < 2) {
//                        App::SetErrorMessage("Invalid Password");
//                        $this->IncrementLoginAttempts($mid);
//                    } else {
//                        App::SetErrorMessage("Invalid Password. Account Locked");
//                        $this->LockAccountForAttempts($mid);
//                    }
//                } else {
//                    $this->ResetLoginAttempts($mid);
//                    $retval = $row;
//                }
//            } elseif ($row["Status"] == 0) {
//                App::SetErrorMessage("Account Inactive");
//            } elseif ($row["Status"] == 2) {
//                App::SetErrorMessage("Account Suspended");
//            } elseif ($row["Status"] == 3) {
//                App::SetErrorMessage("Account Locked (Login Attempts)");
//            } elseif ($row["Status"] == 4) {
//                App::SetErrorMessage("Account Locked (By Admin)");
//            } elseif ($row["Status"] == 5) {
//                App::SetErrorMessage("Account Banned");
//            } elseif ($row["Status"] == 6) {
//                App::SetErrorMessage("Account Terminated");
//            }
        } else {
            App::SetErrorMessage("Invalid Account");
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

    public function getUserName($MID)
    {
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
    public function chkActiveVerifiedEmailAddress($email){
        $query = "SELECT COUNT(mi.MID) ctractive FROM memberinfo mi
                  WHERE mi.Email = '$email'";       
        $result = parent::RunQuery($query);
        
        return $result[0]['ctractive'];
    }
}

?>
