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
    public function Migrate( $cardnumber )
    {                
        //Membership DB
        
        $this->StartTransaction();
        
        $this->CardNumber = $cardnumber;
        
        App::LoadModuleClass("Membership", "TempMembers");
        App::LoadModuleClass("Membership", "TempMemberInfo");
        
        $_TempMembers = new TempMembers();
        $_TempMemberInfo = new TempMemberInfo();
                
        $queryMember = "SELECT UserName, Password, AccountTypeID, DateCreated, DateVerified
                        FROM membership_temp.members
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
                    $this->TableName = "membership.memberservices";
                    
                    App::LoadModuleClass("Kronus", "CasinoServices");
                    $_CasinoServices = new CasinoServices();
                                       
                    $casinoAccounts = $_CasinoServices->generateCasinoAccounts( $this->MID );
                                        
                    $this->InsertMultiple($casinoAccounts);
                    
                    if(!App::HasError())
                    {
                        $this->TableName = "loyaltydb.cards";
                        
                        App::LoadModuleClass("Loyalty", "CardStatus");
                        App::LoadModuleClass("Membership", "Helper");
                                                
                        $this->CardNumber = $cardnumber;

                        $arrEntries['CardNumber'] = $this->CardNumber;
                        $arrEntries['CardTypeID'] = Helper::getCardTypeByName('Temporary');
                        $arrEntries['DateCreated'] = 'now_usec()';
                        $arrEntries['CreatedByAID'] = 1;
                        $arrEntries['Status'] = CardStatus::ACTIVE;

                        $this->Insert($arrEntries);
                        $this->CardID = $this->LastInsertID;
                    
                        if(!App::HasError())
                        {
                            $this->TableName = "loyaltydb.membercards";

                            $arrMemberCard['MID'] = $this->MID;
                            $arrMemberCard['CardID'] = $this->CardID;
                            $arrMemberCard['CardNumber'] = $this->CardNumber;
                            
                            $arrMemberCard['SiteID'] = 1; //To be supplied from the cashier
                            $arrMemberCard['DateCreated'] = 'now_usec()';
                            $arrMemberCard['CreatedByAID'] = 1; //To be supplied from the cashier
                            $arrMemberCard['Status'] = 1; //Active card

                            $this->Insert($arrMemberCard);
                                                        
                            if(!App::HasError())
                            {
                                
                                
                                App::LoadModuleClass("CasinoProvider", "PlayTechAPI");
                                App::LoadModuleClass("Kronus", "CasinoServices");
                                App::LoadCore("Validation.class.php");
                                        
                                $validate = new Validation();
                                $_CasinoServices = new CasinoServices();        
      
                                $casinoservices = $_CasinoServices->getUserBasedCasinoServices();
                                
                               /*
                                * Member account info
                                */
                               $userName = $casinoAccounts[0]['ServiceUsername'];                               
                               $password = $casinoAccounts[0]['ServicePassword'];
                               
                               (!empty($arrMemberInfo['Email']) && $validate->validateEmail($arrMemberInfo['Email'])) ? $email = $arrMemberInfo['Email'] : $email = "noemail_".$this->MID."@gmail.com";
                               (!empty($arrMemberInfo['FirstName'])) ? $firstName = str_replace(' ','',$arrMemberInfo['FirstName']) : $firstName = "NA";
                               (!empty($arrMemberInfo['Birthdate'])) ? $birthDate = date('Y-m-d',strtotime($arrMemberInfo['Birthdate'])) : $birthDate = "1970-01-01";
                               (!empty($arrMemberInfo['Address1'])) ? $address = str_replace(' ','',$arrMemberInfo['Address1']) : $address = 'NA';
                               (!empty($arrMemberInfo['Address2'])) ? $city = str_replace(' ','',$arrMemberInfo['Address2']) : $city = "NA";
                               (!empty($arrMemberInfo['MobileNumber'])) ? $phone = str_replace(' ','',$arrMemberInfo['MobileNumber']) : $phone = '123-4567';
                               $lastName = "NA";
                               $zip = 'NA';
                               $countryCode = 'PH';
                               
                               if(strlen($firstName) > 15)
                                {
                                    $firstName = substr($firstName, 0, 15);
                                }
                                        
                               $casinoAccounts[0]['isVIP'] == 0 ? $vipLevel = 1 : $vipLevel = 2;
                               
                               foreach( $casinoservices as $casinoservice )
                               {

                                  switch( $casinoservice['ServiceID'] )
                                   {
                                       default:
                                       case CasinoProviders::PT;

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
                                           $apiResult = $playtechAPI->NewPlayer($userName, $password, $email, $firstName, 
                                                           $lastName, $birthDate, $address, $city, $countryCode, $phone, 
                                                           $zip, $vipLevel);
                                           break;

                                       case CasinoProviders::MG;
                                           break;
                                       case CasinoProviders::RTG_ALPHA;
                                           break;
                                       case CasinoProviders::RTG_GAMMA;
                                           break;
                                       case CasinoProviders::RTG_SIGMA;
                                           break;
                                   }   
                               }
                                
                                $result = $apiResult['transaction']['@attributes']['result'];                                
                               
                                if($result == 'OK')              
                                {
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
