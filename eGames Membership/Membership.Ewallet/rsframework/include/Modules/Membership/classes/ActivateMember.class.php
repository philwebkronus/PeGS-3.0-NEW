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
        App::LoadModuleClass("Membership", "MemberServices");

        $_TempMembers = new TempMembers();
        $_TempMemberInfo = new TempMemberInfo();
        $_CardTypes = new CardTypes();
        $_Log = new AuditTrail();
        $_MemberServices = new MemberServices();
               
        $queryMember = "SELECT UserName, Password, DateCreated, DateVerified
                        FROM membership_temp.members
                        WHERE TemporaryAccountCode = '$this->CardNumber'";
        
        $result = $_TempMembers->RunQuery($queryMember);
        
        $arrMembers['UserName'] = $result[0]['UserName'];
        $arrMembers['Password'] = $result[0]['Password'];
        $arrMembers['DateCreated'] = 'NOW(6)';
        
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
        $arrMemberInfo['DateCreated'] = 'NOW(6)';
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
                    $arrEntries['DateCreated'] = 'NOW(6)';
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
                        $arrMemberCard['DateCreated'] = 'NOW(6)';
                        $arrMemberCard['CreatedByAID'] = 1; //To be supplied from the cashier
                        $arrMemberCard['Status'] = CardStatus::ACTIVE_TEMPORARY;; //Active card

                        $this->Insert($arrMemberCard);

                        if(!App::HasError())
                        {
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
                            $MID = $this->MID;
                            
                            //Create dummy info base on MID
                            $email = $MID. "@philweb.com.ph";
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

                                if(strpos($serviceName, 'RTG2') !== false){

                                    //Generation of casino username to be passed in the casino API
                                    $casinoAccounts = $_CasinoServices->generateCasinoAccounts($MID, $serviceID, $serviceName);
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
                                                $firstName,$lastName, $birthDate, $gender, $email, $phone, $address, $city, $countryCode,$vipLevel);
                                        
                                        if(!$apiResult){
                                            $apierror = "There was an error encountered in mapping the RTG casino.";
                                            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber.':Failed', $apiResult['ErrorMessage']); //logging of API Error
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
                                                        $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber.':Failed', $vapiResult['ErrorMessage']);
                                                    }
                                                } else {
                                                    $apierror = "There was an error encountered in mapping the RTG casino.";
                                                    $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber.':Failed', $apierror);
                                                }
                                            }
                                        }
                                    } else{
                                        $apierror = "No available plain and hashed password for RTG2 casino.";
                                        $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber.':Failed', $apierror);
                                    }
                                }

                                if(strpos($serviceName, 'MG')){
                                              $casinoAccounts = $_CasinoServices->generateCasinoAccounts( $MID, $serviceID, $serviceName );
                                              $userName = $casinoAccounts[0]['ServiceUsername'];
                                              $vipLevel = $casinoAccounts[0]['VIPLevel'];
                                              
                                              //Get hashed and plain password from password pool table
                                              $rpassword = $_GeneratedPasswordBatch->getPasswordByCasino($genpassbatchid, $serviceGrpID);
                                              if(!empty($rpassword)){
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

                                                    $casinoAccounts[0]['ServicePassword'] = $password;
                                                    $casinoAccounts[0]['HashedServicePassword'] = $hashpassword;

                                                    $casinoAPI = new CasinoAPI();
                                                    $apiResult = $casinoAPI->createAccount($serviceName, $serviceID, $userName,$password,
                                                            $firstName,$lastName, $birthDate, $gender, $email, $phone, $address, $city, $countryCode,$vipLevel);

                                                    if(!$apiResult){
                                                        $apierror = "There was an error encountered in mapping the MG casino.";

                                                        $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber.':Failed', $apiResult['ErrorMessage']);
                                                    }
                                                    else{
                                                        if($apiResult['IsSucceed'] == true){
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
                                                                    $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber.':Failed', $vapiResult['ErrorMessage']);
                                                            }
                                                        }
                                                        else {
                                                            $apierror = "There was an error encountered in mapping the MG casino.";
                                                            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber.':Failed', $apierror);
                                                        }
                                                    }
                                              }else{
                                                 $apierror = "No available plain and hashed password for RTG2 casino.";
                                                 $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber.':Failed', $apierror);
                                              }

                                           }

                                if(strpos($serviceName, 'PT') !== false){
                                        //Generation of casino username to be passed in the casino API
                                        $casinoAccounts = $_CasinoServices->generateCasinoAccounts( $MID, $serviceID, $serviceName );
                                        $userName = $casinoAccounts[0]['ServiceUsername'];
                                        $vipLevel = $casinoAccounts[0]['VIPLevel'];
                                        
                                        //Get hashed and plain password from password pool table
                                        $rpassword = $_GeneratedPasswordBatch->getPasswordByCasino($genpassbatchid, $serviceGrpID);
                                        if(!is_null($rpassword)){
                                            $password = $rpassword[0]['PlainPassword'];
                                            $hashpassword = $rpassword[0]['EncryptedPassword'];

                                            $casinoAccounts[0]['ServicePassword'] = $password;
                                            $casinoAccounts[0]['HashedServicePassword'] = $hashpassword;

                                            //START: Call Casino Create Account API Method
                                            $apiResult = $casinoAPI->createAccount($serviceName, $serviceID, $userName,$password,
                                                    $firstName,$lastName, $birthDate, $gender, $email, $phone, $address, $city, $countryCode,$vipLevel);

                                            if(!$apiResult){
                                                $apierror = "There was an error encountered in mapping the PT casino.";
                                                $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber.':Failed', $apiResult['ErrorMessage']);
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
                                                            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber.':Failed', $vapiResult['ErrorMessage']);
                                                    } else {
                                                        $apierror = "There was an error encountered in mapping the PT casino.";

                                                        $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $this->CardNumber.':Failed', $apierror);
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
                                
                                foreach ($arraycasinoservices as $casinoservices){
                                    $this->InsertMultiple($casinoservices);
                                }
                                
                                $_GeneratedPasswordBatch->updatePasswordBatch($this->MID, $genpassbatchid);
                                
                                $this->CommitTransaction();
                                return array("MID"=>$this->MID,"status"=>'OK',"apierror"=>$apierror);
                            }
                            else
                            {
                                return array("MID"=>$this->MID,"status"=>'error',"apierror"=>$apierror);
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
