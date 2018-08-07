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
App::LoadModuleClass("Membership", "GeneratedPasswordBatch");
App::LoadModuleClass("Loyalty", "MemberPointsTransferLog");
App::LoadModuleClass("Admin", "AccountSessions");
App::LoadModuleClass("CasinoProvider", "PlayTechAPI");
App::LoadModuleClass("CasinoProvider", "CasinoProviders");
App::LoadModuleClass("Kronus", "CasinoServices");
App::LoadModuleClass("CasinoProvider", "CasinoAPI");

//Load Needed Core Class.
App::LoadCore('Validation.class.php');

//Initialize Modules
$_MemberCards = new MemberCards();
$_MemberServices = new MemberServices();
$_GeneratedPasswordBatch = new GeneratedPasswordBatch();
$_Members = new Members();
$_MemberInfo = new MemberInfo();
$_Sites = new Sites();
$_Log = new AuditTrail();
$_AccountSessions = new AccountSessions();
$_CasinoServices = new CasinoServices();
$casinoAPI = new CasinoAPI();
$profile = null;
$resultmsg = null;
$response = null;
//$flag = 1;

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
            case "ChangePassword":
                if (isset($_POST['Card']) && isset($_POST['CasinoService'])) {
                    $cardnumber = $_POST['Card'];
                    $serviceID = $_POST['CasinoService'];
                    $DateCreated = "NOW(6)";

                    //get card details using cardnumber
                    $cardinfo = $_MemberCards->getMemberCardInfoByCardNumber($cardnumber);

                    $servicegroupID = $_CasinoServices->getServiceGroupID($serviceID);

                    if (!empty($cardinfo)) {
                        foreach ($cardinfo as $value) {
                            $status = $value['Status'];
                            $MID = $value['MID'];
                        }
                        //Allow temporary and actie membership cards only
                        if ($status == 5 || $status == 1) {

                            $service = $_CasinoServices->getCasinoServiceName($serviceID);

                            foreach ($service as $value) {
                                $serviceName = $value['ServiceGroupName'];
                            }

//                            $genpassbatchid = $_GeneratedPasswordBatch->getExistingPasswordBatch2($MID);
//                            if (empty($genpassbatchid)) {
//                                $genpassbatchid = $_GeneratedPasswordBatch->getInactivePasswordBatch2();
//                                $flag = 2;
//                            }

                            $genpassresult = $_GeneratedPasswordBatch->getInactivePasswordBatchInfo();

                            $genpassbatchid = $genpassresult[0]['GeneratedPasswordBatchID'];
                            $rpassword = $_GeneratedPasswordBatch->getPasswordByCasino($genpassbatchid, $servicegroupID);

                            $newpassword = $rpassword[0]['PlainPassword'];
                            $hashednewpassword = $rpassword[0]['EncryptedPassword'];

                            switch (true) {
                                case strstr($serviceName, "RTG2"):

                                    $checkMS = $_MemberServices->CheckMemberService($MID, $serviceID);
                                    if (!empty($checkMS)) {

                                        $userName = $checkMS[0]['ServiceUsername'];
                                        $password = $checkMS[0]['ServicePassword'];
                                        //Call API to get Account Info
                                        $vapiResult = $casinoAPI->GetAccountInfo($serviceName, $userName, $password, $serviceID);
                                        //Verify if API Call was successful
                                        if (isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true) {
                                            //$newpassword = $vapiResult['AccountInfo']['password'];
                                            //Call API Change Password
                                            $vapiResult = $casinoAPI->ChangePassword($serviceName, $userName, $password, $newpassword, $serviceID);
                                            if (isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true) {
                                                $isMemberServicesUpdated = $_MemberServices->updateMemberServicesUBPassword($newpassword, $hashednewpassword, $MID, $serviceID, $genpassbatchid);
                                                if ($isMemberServicesUpdated)
                                                    $apisuccess = 1;
                                                else {
                                                    $apisuccess = 0;
                                                    //Call API Change Password again to revert back to original password
                                                    $vapiResult = $casinoAPI->ChangePassword($serviceName, $userName, $newpassword, $password, $serviceID);
                                                }
                                            } else {
                                                $apisuccess = 0;
                                            }
                                        } else {
                                            $apisuccess = 0;
                                        }
                                    } else {

                                        $apisuccess = 0;
                                    }

                                    break;

                                case strstr($serviceName, "HAB"):

                                    $checkMS = $_MemberServices->CheckMemberService($MID, $serviceID);
                                    if (!empty($checkMS)) {

                                        $userName = $checkMS[0]['ServiceUsername'];
                                        $password = $checkMS[0]['ServicePassword'];
                                        //Call API to get Account Info
                                        $vapiResult = $casinoAPI->GetAccountInfo($serviceName, $userName, $password, $serviceID);
                                        //Verify if API Call was successful
                                        if (isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true) {
                                            //$newpassword = $vapiResult['AccountInfo']['password'];
                                            //Call API Change Password
                                            $vapiResult = $casinoAPI->ChangePassword($serviceName, $userName, $password, $newpassword, $serviceID);
                                            if (isset($vapiResult['IsSucceed']) && $vapiResult['IsSucceed'] == true) {
                                                $isMemberServicesUpdated = $_MemberServices->updateMemberServicesUBPassword($newpassword, $hashednewpassword, $MID, $serviceID, $genpassbatchid);
                                                if ($isMemberServicesUpdated)
                                                    $apisuccess = 1;
                                                else {
                                                    $apisuccess = 0;
                                                    //Call API Change Password again to revert back to original password
                                                    $vapiResult = $casinoAPI->ChangePassword($serviceName, $userName, $newpassword, $password, $serviceID);
                                                }
                                            } else {
                                                $apisuccess = 0;
                                            }
                                        } else {
                                            $apisuccess = 0;
                                        }
                                    } else {

                                        $apisuccess = 0;
                                    }

                                    break;


                                default:
                                    break;
                            }

                            if ($apisuccess > 0) {
                                $_Log->logAPI(AuditFunctions::CHANGE_PLAYER_PASSWORD, 'UB Player Password is successfully changed for UB Card' . $cardnumber, $_SESSION['sessionID'], $_SESSION['aID']);
                                $profile->Msg = 'UB Player Password is successfully changed';
                            } else {
                                $_Log->logAPI(AuditFunctions::CHANGE_PLAYER_PASSWORD, 'Failed to Create UserBased Account for UB Card' . $cardnumber, $_SESSION['sessionID'], $_SESSION['aID']);
                                $profile->Msg = 'Failed to Change UB Player Password';
                            }
                        } else {
                            //if card status is not temporary or active
                            switch ($status) {
                                case 0: $vstatus = 'Card is inactive.';
                                    break;
                                case 1: $vstatus = 'Card is active.';
                                    break;
                                case 2: $vstatus = 'Card is deactivated.';
                                    break;
                                case 5: $vstatus = 'Card is active temporary.';
                                    break;
                                case 7: $vstatus = 'Membership card was already migrated to another red card.';
                                    break;
                                case 8: $vstatus = 'Temporary account was already migrated to a red card.';
                                    break;
                                case 9: $vstatus = 'Card is banned.';
                                    break;
                                default: $vstatus = 'Card not found.';
                                    break;
                            }

                            $profile->Msg = $vstatus;
                        }
                    } else {
                        $profile->Msg = 'Card is invalid.';
                    }
                }
                echo json_encode($profile);
                break;
        }
    } else {
        $profile->Msg = "Session Expired";
        session_destroy();
        $profile->RedirectToPage = "login.php?mess=" . $profile->Msg;
        echo json_encode($profile);
    }
}
?>
