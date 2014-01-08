<?php

/*
 * @author : owliber
 * @date : 2013-04-24
 */

class ActivateMember extends BaseEntity
{
    var $CardNumber;
    var $MID;
    var $CardID;
        
    public function ActivateMember()
    {
        $this->ConnString = "membership";
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->TableName = "membership.members";
    }
    
    /**
     * Migrate temporary member records
     * to permanent database
     * @param string $cardnumber
     */
    public function Migrate( $cardnumber, $siteID )
    {                
        //Membership DB
        
        $this->StartTransaction();
        
        $this->CardNumber = $cardnumber;
        
        App::LoadModuleClass("Membership", "TempMembers");
        App::LoadModuleClass("Membership", "TempMemberInfo");
        App::LoadModuleClass("Loyalty", "CardTypes");

        $_TempMembers = new TempMembers();
        $_TempMemberInfo = new TempMemberInfo();
        $_CardTypes = new CardTypes();
               
        $queryMember = "SELECT UserName, Password, DateCreated, DateVerified
                        FROM membership_temp.members
                        WHERE TemporaryAccountCode = '$this->CardNumber'";
        
        $result = $_TempMembers->RunQuery($queryMember);
        
        $arrMembers['UserName'] = $result[0]['UserName'];
        $arrMembers['Password'] = $result[0]['Password'];
        $arrMembers['DateCreated'] = 'now_usec()';
        
        $queryMemberInfo = "SELECT FirstName, MiddleName, LastName, NickName, Birthdate, Gender, Email,
                                   AlternateEmail, MobileNumber, AlternateMobileNumber, NationalityID,
                                   OccupationID, ReferrerID, Address1, Address2, IdentificationID, IdentificationNumber,
                                   RegistrationOrigin, EmailSubscription, SMSSubscription, IsSmoker, IsCompleteInfo,
                                   DateVerified, ReferrerCode
                            FROM membership_temp.memberinfo mi
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
        $arrMemberInfo['ReferrerCode'] = $result2[0]['ReferrerCode'];   
        
        try
        {
            $this->Insert($arrMembers);                       
            $this->MID = $this->LastInsertID;
        
            if(!App::HasError())
            {
                $this->TableName = "membership.memberinfo";
                                
                $arrMemberInfo['MID'] = $this->MID;
                $this->Insert($arrMemberInfo);
                
                if(!App::HasError())
                {
                    $this->TableName = "loyaltydb.cards";

                    App::LoadModuleClass("Loyalty", "CardStatus");
                    App::LoadModuleClass("Membership", "Helper");

                    $this->CardNumber = $cardnumber;

                    $arrEntries['CardNumber'] = $this->CardNumber;
                    $arrEntries['CardTypeID'] = $_CardTypes->getCardTypeByName('Temporary');
                    $arrEntries['DateCreated'] = 'now_usec()';
                    $arrEntries['CreatedByAID'] = 1;
                    $arrEntries['Status'] = CardStatus::ACTIVE_TEMPORARY;

                    $this->Insert($arrEntries);
                    $this->CardID = $this->LastInsertID;

                    if(!App::HasError())
                    {
                        $this->TableName = "loyaltydb.membercards";

                        $arrMemberCard['MID'] = $this->MID;
                        $arrMemberCard['CardID'] = $this->CardID;
                        $arrMemberCard['CardNumber'] = $this->CardNumber;

                        $arrMemberCard['SiteID'] = $siteID; //To be supplied from the cashier
                        $arrMemberCard['DateCreated'] = 'now_usec()';
                        $arrMemberCard['CreatedByAID'] = 1; //To be supplied from the cashier
                        $arrMemberCard['Status'] = CardStatus::ACTIVE_TEMPORARY;; //Active card

                        $this->Insert($arrMemberCard);

                        if(!App::HasError())
                        {

                           App::LoadModuleClass("CasinoProvider", "PlayTechAPI");
                           App::LoadModuleClass("CasinoProvider", "CasinoProviders");
                           App::LoadModuleClass("Kronus", "CasinoServices");

                           $_CasinoServices = new CasinoServices();        

                           $casinoservices = $_CasinoServices->getUserBasedCasinoServices();

                           $this->TableName = "membership.memberservices";

                           foreach( $casinoservices as $casinoservice )
                           {

                              $serviceID = $casinoservice['ServiceID'];

                              switch( $serviceID )
                               {
                                   case CasinoProviders::PT;

                                        $casinoAccounts = $_CasinoServices->generateCasinoAccounts( $this->MID, $serviceID );

                                        //$this->InsertMultiple($casinoAccounts);

                                       /*
                                        * Member account info
                                        */
                                       $userName = $casinoAccounts[0]['ServiceUsername'];                               
                                       $password = $casinoAccounts[0]['ServicePassword'];

                                       //Create fake info base on MID
                                       $email = $this->MID."@philweb.com.ph";
                                       $lastName = "NA";
                                       $firstName = "NA";
                                       $birthDate = "1970-01-01";
                                       $address = "NA";
                                       $city = "NA";
                                       $phone = '123-4567';                               
                                       $zip = 'NA';
                                       $countryCode = 'PH';

                                       //$casinoAccounts[0]['isVIP'] == 0 ? $vipLevel = App::getParam("ptreg") : $vipLevel = App::getParam("ptvip");
                                       $vipLevel = $casinoAccounts[0]['VIPLevel'];
                                        /*
                                         * PlayTech Configurations
                                         */
                                       $arrplayeruri = App::getParam("player_api");
                                       $URI = $arrplayeruri[$serviceID - 1];
                                       $casino = App::getParam("pt_casino_name");
                                       $playerSecretKey = App::getParam("pt_secret_key");                 

                                       $playtechAPI = new PlayTechAPI($URI, $casino, $playerSecretKey);                

                                       /*
                                        * Create account
                                        */
                                       $apiResult = $playtechAPI->NewPlayer($userName, $password, $email, $firstName, 
                                                       $lastName, $birthDate, $address, $city, $countryCode, $phone, 
                                                       $zip, $vipLevel);
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

                            if($result == 'OK')              
                            {
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
                                
                                $this->InsertMultiple($casinoAccounts);
                                
                                $this->CommitTransaction();
                                return array("MID"=>$this->MID,"status"=>'OK');
                            }
                            else
                            {
                                $this->RollBackTransaction();
                                return array("MID"=>$this->MID,"status"=>'error');
                            }
                        }
                        else
                        {
                            $this->RollBackTransaction();
                            return array("MID"=>$this->MID,"status"=>'error');
                        }
                    }
                    else
                    {
                        $this->RollBackTransaction();
                        return array("MID"=>$this->MID,"status"=>'error');
                    }
                }
                else
                {
                    $this->RollBackTransaction();
                    return array("MID"=>$this->MID,"status"=>'error');
                }

            }
            else
            {
                $this->RollBackTransaction();
                return array("MID"=>$this->MID,"status"=>'error');
            }
        }
        catch(Exception $e)
        {
            $this->RollBackTransaction();
            return array("MID"=>"","status"=>'error');
        }
        
    }
    
}
?>
