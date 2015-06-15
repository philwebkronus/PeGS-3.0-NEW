<?php

/*
 * Description: Helper file to transfer points from red card to new UB card
 * @Author: Gerardo Jagolino Jr.
 */

//Attach and Initialize framework
require_once("../../init.inc.php");


//Load Modules to be use.
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "MemberServices");
App::LoadModuleClass("Kronus", "TransactionSummary");
App::LoadModuleClass("Kronus", "Sites");
App::LoadModuleClass("Kronus", "Sites");
App::LoadModuleClass("Loyalty", "Cards");
App::LoadModuleClass("Loyalty", "CardStatus");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("Loyalty", "MemberPointsTransferLog");
App::LoadModuleClass("Admin", "AccountSessions");
App::LoadModuleClass("CasinoProvider", "PlayTechAPI");
App::LoadModuleClass("CasinoProvider", "CasinoProviders");
App::LoadModuleClass("Kronus", "CasinoServices");

//Load Needed Core Class.
App::LoadCore('Validation.class.php');

//Initialize Modules
$_MemberCards = new MemberCards();
$_MemberServices = new MemberServices();
$_Members = new Members();
$_MemberInfo = new MemberInfo();
$_Sites = new Sites();
$_Log = new AuditTrail();
$_AccountSessions = new AccountSessions();
$_CasinoServices = new CasinoServices();    
$profile = null;
$resultmsg = null;
$response = null;

$logger = new ErrorLogger();
$logdate = $logger->logdate;
$logtype = "Error ";

if (isset($_SESSION['sessionID'])) {
    $sessionid = $_SESSION['sessionID'];
    $aid = $_SESSION['aID'];
} else {
    $sessionid = 0;
    $aid = 0;
}
//session checking
$sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid);

foreach ($sessioncount as $value) {
    foreach ($value as $value2) {
        $sessioncount = $value2['Count'];
    }
}

if (isset($_POST['pager'])) {
    $vpage = $_POST['pager'];
    if ($sessioncount > 0) {
        switch ($vpage) {
            case "CreatePT":
                if(isset($_POST['Card'])){
                    $cardnumber = $_POST['Card'];
                    //get card details using cardnumber
                    $cardinfo = $_MemberCards->getMemberCardInfoByCardNumber($cardnumber);
                    
                    if(!empty($cardinfo)){
                        foreach ($cardinfo as $value) {
                            $status = $value['Status'];
                            $MID = $value['MID'];
                        }
                        //Allow temporary and actie membership cards only
                        if($status == 5 || $status == 1){
                            $isVIP = $_Members->getVIPLevel($MID);
                        
                            if($isVIP == 0){
                                $vipLevel = App::getParam("ptreg");
                            }
                            else{
                                $vipLevel = App::getParam("ptvip");
                            }

                            $memberinfo = $_MemberInfo->getMemberInfo($MID);

                            if(!empty($memberinfo)){
                                //Create fake info base on MID
                                $email = $MID."@philweb.com.ph";
                                $lastName = "NA";
                                $firstName = "NA";
                                $birthDate = "1970-01-01";
                                $address = "NA";
                                $city = "NA";
                                $phone = '123-4567';                               
                                $zip = 'NA';
                                $countryCode = 'PH';
                            }
                            else{
                                //Create fake info base on MID
                                $email = $memberinfo[0]['Email'];
                                $lastName = $memberinfo[0]['LastName'];
                                $firstName = $memberinfo[0]['FirstName'];
                                $birthDate = $memberinfo[0]['Birthdate'];
                                $address = $memberinfo[0]['Address1'];
                                $city = "NA";
                                $phone = $memberinfo[0]['MobileNumber'];                               
                                $zip = 'NA';
                                $countryCode = 'PH';
                            }


                            $casinoservices = $_CasinoServices->getUserBasedCasinoServices();

                            foreach( $casinoservices as $casinoservice )
                            {

                               $serviceID = $casinoservice['ServiceID'];

                               switch( $serviceID )
                                {
                                    case CasinoProviders::PT;

                                         $casinoAccounts = $_CasinoServices->generateCasinoAccounts( $MID, $serviceID, $isVIP );

                                         //$this->InsertMultiple($casinoAccounts);

                                        /*
                                         * Member account info
                                         */
                                        $userName = $casinoAccounts[0]['ServiceUsername'];                               
                                        $servpassword = $casinoAccounts[0]['ServicePassword'];

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
                                        $apiResult = $playtechAPI->NewPlayer($userName, $servpassword, $email, $firstName, 
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

                            $MSCount = $_MemberServices->getMemberServiceByMID($MID);

                            //if pt account creation is seccessful generate ub pt playercode
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

                                $DateCreated =  "now_usec()";


                                if(!empty($MSCount)){
                                    //update existing memberservices record
                                    $msresult = $_MemberServices->UpdateMemberServices($userName, $servpassword, 
                                            $servpassword, $playerCode, $vipLevel, $MID);
                                }
                                else{
                                    //add new memberservices record
                                    $msresult = $_MemberServices->AddMemberServices($serviceID, $MID, $userName, $servpassword, 
                                            $servpassword, 1, $DateCreated, $isVIP, $vipLevel, $playerCode, 1);
                                }

                                if($msresult > 0){
                                $_Log->logAPI(AuditFunctions::MANUAL_UB_PT_ASSIGNMENT, 
                                        'UB PT Account Successfully Created for UB Card'.$cardnumber, $_SESSION['sessionID'],$_SESSION['aID']);
                                $profile->Msg = 'PT account was successfully Created for this membership account';
                                }
                                else{
                                    $_Log->logAPI(AuditFunctions::MANUAL_UB_PT_ASSIGNMENT, 
                                            'Failed to Create UserBased PT Account for UB Card'.$cardnumber, $_SESSION['sessionID'],$_SESSION['aID']);
                                    $profile->Msg = 'Failed to Create PT Account';
                                }
                            }
                            else
                            {
                                //check specific memberservice record
                                if(!empty($MSCount)){

                                    foreach ($MSCount as $var) {
                                        $userName = $var['ServiceUsername'];
                                        $servicepassword = $var['ServicePassword'];
                                        $hashedpass = $var['HashedServicePassword'];
                                        $playercode = $var['PlayerCode'];
                                    }
                                    
                                    //check if player code is empty or null
                                    if(is_null($playercode) || $playercode == '' || strlen($playercode) < 8){

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

                                        $DateCreated =  "now_usec()";

                                        $msresult = $_MemberServices->UpdateMemberServices($userName, $servicepassword, 
                                            $hashedpass, $playerCode, $vipLevel, $MID);

                                        if($msresult > 0){
                                            $_Log->logAPI(AuditFunctions::MANUAL_UB_PT_ASSIGNMENT, 
                                                    'UB PT Account Successfully Updated for UB Card'.$cardnumber, $_SESSION['sessionID'],$_SESSION['aID']);
                                            $profile->Msg = 'PT Player Code was successfully tagged for this membership account';
                                        }
                                        else{
                                            $_Log->logAPI(AuditFunctions::MANUAL_UB_PT_ASSIGNMENT, 
                                                    'Failed to Update PlayerCode in Member Services for UB Card'.$cardnumber, $_SESSION['sessionID'],$_SESSION['aID']);
                                            $profile->Msg = 'Failed to Update PlayerCode';
                                        }
                                    }
                                    else{
                                        $errormsg = 'This membership account has already been associated with a PT account and playercode.';  
                                        $_Log->logAPI(AuditFunctions::MANUAL_UB_PT_ASSIGNMENT, 
                                            $errormsg.' for UB Card'.$cardnumber, $_SESSION['sessionID'],$_SESSION['aID']);
                                        $profile->Msg = $errormsg;
                                    }

                                }
                                else{
                                    $errormsg = 'This membership account has already been associated with a PT account and playercode.'; 
                                    $_Log->logAPI(AuditFunctions::MANUAL_UB_PT_ASSIGNMENT, 
                                             $errormsg.' for UB Card'.$cardnumber, $_SESSION['sessionID'],$_SESSION['aID']);
                                    $profile->Msg = $errormsg;
                                }



                            }
                        }
                        else{
                            //if card status is not temporary or active
                            switch($status)
                            {
                                case 0: $vstatus = 'InActive';break;
                                case 1: $vstatus = 'Active';    break;
                                case 2: $vstatus = 'Card was deactivated';break;
                                case 5: $vstatus = 'Active Temporary';break;
                                case 7: $vstatus = 'Membership card was already migrated to another red card'; break;   
                                case 8: $vstatus = 'Temporary account was already migrated to a red card';  break;
                                case 9: $vstatus = 'Card is banned.';  break;
                                default: $vstatus = 'Card Not Found'; break;
                            }
                            
                            $profile->Msg = $vstatus;
                            
                        }
                        
                        
                    }
                    else{
                        $profile->Msg = 'Invalid Card Number';
                    }
                }
                echo json_encode($profile);
            break;    
        }
        
    }
    else {
        $profile->Msg = "Session Expired";
        session_destroy();
        $profile->RedirectToPage = "login.php?mess=" . $profile->Msg;
        echo json_encode($profile);
    }
}
?>