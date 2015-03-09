<?php

/*
 * @author : owliber
 * @date : 2013-04-24
 */

class MigrateMember extends BaseEntity
{
    var $CardNumber;
    var $MID;
    var $CardID;
        
    public function MigrateMember()
    {
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->ConnString = "membership";
    }
    
    /**
     * Migrate temporary member records
     * to permanent database
     * @param string $cardnumber
     */
    public function Migrate( $cardnumber )
    {
                
        $this->StartTransaction();
        
        $this->CardNumber = $cardnumber;
        
        App::LoadModuleClass("Membership", "TempMembers");
        App::LoadModuleClass("Membership", "TempMemberInfo");
        
        $_TempMembers = new TempMembers();
        $_TempMemberInfo = new TempMemberInfo();
                
        $queryMember = "SELECT UserName, Password, AccountTypeID, DateCreated, DateVerified
                        FROM members
                        WHERE TemporaryAccountCode = '$this->CardNumber'";
        
        $result = $_TempMembers->RunQuery($queryMember);
        
        $arrMembers['UserName'] = $result[0]['UserName'];
        $arrMembers['Password'] = $result[0]['Password'];
        $arrMembers['AccountTypeID'] = $result[0]['AccountTypeID'];
        $arrMembers['DateCreated'] = 'now_usec()';
        
        $queryMemberInfo = "SELECT FirstName, MiddleName, LastName, NickName, Birthdate, Gender, Email,
                                   AlternateEmail, MobileNumber, AlternateMobileNumber, NationalityID,
                                   OccupationID, ReferrerID, Address1, Address2, IdentificationID, IdentificationNumber,
                                   RegistrationOrigin, EmailSubscription, SMSSubscription, IsSmoker, IsCompleteInfo,
                                   DateVerified
                            FROM memberinfo mi
                                INNER JOIN members m ON mi.MID = m.MID
                            WHERE m.TemporaryAccountCode = '$this->CardNumber'";
        
        $result2 = $_TempMemberInfo->RunQuery($queryMemberInfo);
        
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
        $arrMemberInfo['DateCreated'] = 'now_usec()';
        $arrMemberInfo['DateVerified'] = $result[0]['DateVerified'];                
                          
        $this->TableName = "members";
        $this->Insert($arrMembers);                       
        $this->MID = $this->LastInsertID;

        if(!App::HasError())
        {

            $this->TableName = "memberinfo";                

            $arrMemberInfo['MID'] = $this->MID;
            $this->Insert($arrMemberInfo);

            if(!App::HasError())
            {
                $this->CommitTransaction();
                return array("MID"=>$this->MID);
            }

        }
        else
        {
            $this->RollBackTransaction();
        }
        
    }
    
    public function createCard( $cardnumber )
    {
                            
        /**
         * Generate member card based on the 
         * issued temporary account number
         */
        
        App::LoadModuleClass("Membership", "Helper");
        App::LoadModuleClass("Loyalty", "Cards");
        $_Cards = new Cards();
        
        $_Cards->StartTransaction();
        
        $this->CardNumber = $cardnumber;

        $arrEntries['CardNumber'] = $this->CardNumber;
        $arrEntries['CardTypeID'] = Helper::getCardTypeByName('Temporary');
        $arrEntries['DateCreated'] = 'now_usec()';
        $arrEntries['CreatedByAID'] = 1;
        $arrEntries['Status'] = CardStatus::ACTIVE_TEMPORARY;

        //Insert card info

        $_Cards->Insert($arrEntries);
        $this->CardID = $_Cards->LastInsertID;

        if(!App::HasError())
        {

            $_Cards->CommitTransaction();
            
            App::LoadModuleClass("Loyalty", "MemberCards");
            App::LoadModuleClass("Membership", "MemberInfo");
            $_MemberCards = new MemberCards();
            $_MemberInfo = new MemberInfo();
            
            $_Cards->PDODB = $_MemberCards->PDODB;

            $_MemberCards->StartTransaction();
            
            $memberinfo = $_MemberInfo->getMemberInfo($this->MID);
                    
            $arrMemberCard['MID'] = $this->MID;
            $arrMemberCard['CardID'] = $this->CardID;
            $arrMemberCard['CardNumber'] = $this->CardNumber;

            $MemberName = $memberinfo[0]['FirstName'] . ' ' . $memberinfo[0]['LastName'];
            $arrMemberCard['MemberCardName'] = $MemberName;
            $arrMemberCard['SiteID'] = 1; //To be supplied from the cashier
            $arrMemberCard['DateCreated'] = 'now_usec()';
            $arrMemberCard['CreatedByAID'] = 1; //To be supplied from the cashier
            $arrMemberCard['Status'] = 1; //Active card

            
            $_MemberCards->Insert($arrMemberCard);

            if(!App::HasError())
            {
               $_MemberCards->CommitTransaction();
               return array("CardID"=>$this->CardID);
            }       
            else
            {
                $_MemberCards->RollBackTransaction();
            }

        }
        else
        {
            $_Cards->RollBackTransaction();
        }
    }
    
    public function createMemberServices( $MID )
    {
        
        App::LoadModuleClass("Kronus", "CasinoServices");
        $_CasinoServices = new CasinoServices();

        $this->StartTransaction();
        
        $this->MID = $MID;
        
        $casinoAccounts = $_CasinoServices->generateCasinoAccounts( $this->MID );

        $this->TableName = "memberservices";
        $this->InsertMultiple($casinoAccounts);

        if(!App::HasError())
            $this->CommitTransaction();
        else
            $this->RollBackTransaction();
    }
    
    /**
     * obsolete class
     */   
//    public function processCasinoAccount( $MID )
//    {
//        /*
//         * Load Classes
//         */
//        App::LoadModuleClass("CasinoProvider", "CasinoProviders");
//        App::LoadModuleClass("CasinoProvider", "PlayTechAPI");
//        App::LoadModuleClass("Membership", "MemberInfo");
//        App::LoadModuleClass("Membership", "MemberServices");
//        App::LoadModuleClass("Kronus", "CasinoServices");                
//        
//        /*
//         * Instantiate Models
//         */
//        $_MemberInfo = new MemberInfo();
//        $_MemberServices = new MemberServices();
//        $_CasinoServices = new CasinoServices();
//        
//        $memberservices = $_MemberServices->getUserBasedMemberServices( $MID );
//        $memberinfo = $_MemberInfo->getMemberInfo( $MID );
//        $casinoservices = $_CasinoServices->getUserBasedCasinoServices();
//        
//        /*
//         * Member account info
//         */
//        $userName = $memberservices[0]['ServiceUsername'];
//        $password = $memberservices[0]['ServicePassword'];
//        $email = str_replace(' ','_',$memberinfo[0]['Email']);
//        $firstName = str_replace(' ','',$memberinfo[0]['FirstName']);
//        $lastName = str_replace(' ','_',$memberinfo[0]['LastName']);
//        $birthDate = date('Y-m-d',strtotime($memberinfo[0]['Birthdate']));
//        (!empty($memberinfo[0]['Address1'])) ? $address = str_replace(' ','_',$memberinfo[0]['Address1']) : $address = 'NA';
//        (!empty($memberinfo[0]['Address2'])) ? $city = str_replace(' ','_',$memberinfo[0]['Address2']) : $city = "NA";
//        $countryCode = 'PH';
//        (!empty($memberinfo[0]['MobileNumber'])) ? $phone = str_replace(' ','',$memberinfo[0]['MobileNumber']) : $phone = '338-3838';
//        $zip = 'NA';
//        
//        $memberservices[0]['isVIP'] == 0 ? $vipLevel = App::getParam("ptreg") : $vipLevel = App::getParam("ptvip");
//        //$vipLevel = 1; //1-reg ; 2-vip
//        
//        foreach( $casinoservices as $casinoservice )
//        {
//           
//           switch( $casinoservice['ServiceID'] )
//            {
//                case CasinoProviders::PT;
//
//                     /*
//                      * PlayTech Configurations
//                      */
//                    $arrplayeruri = App::getParam("player_api");
//                    $URI = $arrplayeruri[$casinoservice['ServiceID'] - 1];
//                    $casino = App::getParam("pt_casino_name");
//                    $playerSecretKey = App::getParam("pt_secret_key");                 
//
//                    $playtechAPI = new PlayTechAPI($URI, $casino, $playerSecretKey);                
//
//                    /*
//                     * Create account
//                     */
//                    $apiResult = $playtechAPI->NewPlayer($userName, $password, $email, $firstName, 
//                                    $lastName, $birthDate, $address, $city, $countryCode, $phone, 
//                                    $zip, $vipLevel);
//                    break;
//
//                case CasinoProviders::MG;
//                    break;
//                case CasinoProviders::RTG_ALPHA;
//                    break;
//                case CasinoProviders::RTG_GAMMA;
//                    break;
//                case CasinoProviders::RTG_SIGMA;
//                    break;
//                default:
//                    break;
//            }   
//        }
//                
//              
//        
//        return $apiResult;
//    }
}
?>
