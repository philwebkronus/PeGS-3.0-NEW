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
App::LoadModuleClass("Membership", "PcwsWrapper");
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
$_PcwsWrapper = new PcwsWrapper();
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
                            $isVIP = $_Members->getVIPLevel($MID);

                            $memberinfo = $_MemberInfo->getMemberInfo($MID);

                            if (!empty($memberinfo)) {
                                //Create fake info base on MID
                                $email = $MID . "@philweb.com.ph";
                                $lastName = "NA";
                                $firstName = "NA";
                                $birthDate = "1970-01-01";
                                $address = "NA";
                                $city = "NA";
                                $phone = '123-4567';
                                $zip = 'NA';
                                $countryCode = 'PH';
                                $gender = 1;
                            } else {
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
                                $gender = 1;
                            }

                            $service = $_CasinoServices->getCasinoServiceName($serviceID);

                            foreach ($service as $value) {
                                $serviceName = $value['ServiceGroupName'];
                            }

                            $genpassbatchid = $_GeneratedPasswordBatch->getExistingPasswordBatch($MID);
                            if (empty($genpassbatchid)) {
                                $genpassbatchid = $_GeneratedPasswordBatch->getInactivePasswordBatch();
                            }

                            $UBusermode = App::getParam('UBusermode');
                            $checkifUBMS = $_MemberServices->CheckUBMemberService($MID, $UBusermode);
                            if (count($checkifUBMS) > 0) {
                                $userName = $checkifUBMS[0]['ServiceUsername'];
                            } else {
                                $casinoAccounts = $_CasinoServices->generateCasinoAccounts($MID, $serviceID, $serviceName);
                                $userName = $casinoAccounts[0]['ServiceUsername'];
                            }
                            switch (true) {

                                case strstr($serviceName, "RTG"):

                                    if ($isVIP == 0) {
                                        $vipLevel = App::getParam("rtgreg");
                                    } else {
                                        $vipLevel = App::getParam("rtgvip");
                                    }

                                    $checkMS = $_MemberServices->CheckMemberService($MID, $serviceID);

                                    if (empty($checkMS)) {
                                        $rpassword = $_GeneratedPasswordBatch->getPasswordByCasino($genpassbatchid, $servicegroupID);
                                        if (!empty($rpassword)) {
                                            $genpassbatch = $_GeneratedPasswordBatch->getInactivePasswordBatchDetails();
                                            $password = $rpassword[0]['PlainPassword'];
                                            $hashedpassword = $rpassword[0]['EncryptedPassword'];

                                            $apiResult1 = $casinoAPI->createAccount($serviceName, $serviceID, $userName, $password, 
						$firstName, $lastName, $birthDate, $gender, $email, $phone, $address, $city, $countryCode, $vipLevel);

                                            if ($apiResult1['IsSucceed'] == true && $apiResult1['ErrorID'] == 1) {

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

                                                $checkMS = $_MemberServices->CheckMemberService($MID, $serviceID);

                                                if (empty($checkMS)) {
                                                    //add new memberservices record
                                                    $msresult = $_MemberServices->AddMemberServices($serviceID, $MID, $userName, $password, 
						    	$hashedpassword, 1, $DateCreated, $isVIP, $vipLevel, null, 1);
                                                    $msupdate = 'add';
                                                } else {
                                                    $msresult = $_MemberServices->UpdateMemberServices(null, $vipLevel, $MID, $serviceID);
                                                    $msupdate = 'update';
                                                }
                                            } else if ($apiResult1['IsSucceed'] == false && $apiResult1['ErrorID'] == 5) {
                                                $msresult = 'Account already exist in RTG, Please Try again.';
                                                $msupdate = 'exist2';
                                            } else {
                                                $msresult = 'failed';
                                                $msupdate = 'failed';
                                            }
                                        } else {
                                            if ($serviceID == 19)
                                                $apierror = "No available plain and hashed password for RTG casino.";
                                            else
                                                $apierror = "No available plain and hashed password for RTG2 casino.";

                                            $_Log->logAPI(AuditFunctions::MANUAL_CASINO_UB_ASSIGNMENT, $cardnumber . ':Failed, ' . $apierror);
                                            $msresult = 'failed';
                                            $msupdate = 'failed';
                                        }
                                    }
                                    else {
                                        $msresult = 'exist';
                                        $msupdate = 'exist';
                                    }

                                    break;

                                case strstr($serviceName, "HAB"):

                                    if ($isVIP == 0) {
                                        $vipLevel = App::getParam("habreg");
                                    } else {
                                        $vipLevel = App::getParam("habvip");
                                    }

                                    $MSCount = $_MemberServices->getMemberServiceByMID($MID, $serviceID);
                                    if (empty($checkMS)) {
                                        $rpassword = $_GeneratedPasswordBatch->getPasswordByCasino($genpassbatchid, $servicegroupID);
                                        if (!empty($rpassword)) {
                                            $genpassbatch = $_GeneratedPasswordBatch->getInactivePasswordBatchDetails();
                                            $password = $rpassword[0]['PlainPassword'];
                                            $hashedpassword = $rpassword[0]['EncryptedPassword'];

                                            $apiResult3 = $casinoAPI->habaneroCreateAccount($serviceName, $serviceID, $userName, $password, $vipLevel);

                                            //if pt account creation is seccessful generate ub pt playercode
                                            if ($apiResult3['IsSucceed'] == true && $apiResult3['ErrorCode'] == 0) {
                                                $checkMS = $_MemberServices->CheckMemberService($MID, $serviceID);

                                                if (empty($checkMS)) {
                                                    //add new memberservices record
                                                    $msresult = $_MemberServices->AddMemberServices($serviceID, $MID, $userName, $password, $hashedpassword, 1, $DateCreated, $isVIP, $vipLevel, null, 1);
                                                    $msupdate = 'add';
                                                } else {
                                                    $msresult = $_MemberServices->UpdateMemberServices(null, $vipLevel, $MID, $serviceID);
                                                    $msupdate = 'update';
                                                }
                                            } else {

                                                $msresult = 'failed';
                                                $msupdate = 'failed';
                                            }
                                        } else {
                                            $apierror = "No available plain and hashed password for HAB casino.";
                                            $_Log->logAPI(AuditFunctions::MANUAL_CASINO_UB_ASSIGNMENT, $cardnumber . ':Failed, ' . $apierror);
                                            $msresult = 'failed';
                                            $msupdate = 'failed';
                                        }
                                    } else {
                                        $msresult = 'exists';
                                        $msupdate = 'exists';
                                    }
                                    break;
                                default:
                                    break;
                            }

                            if (isset($msupdate) && $msupdate == 'failed') {

                                if (strstr($serviceName, "RTG")) {
                                    if ($serviceID == 19)
                                        $addiMsg = 'Failed to Create UserBased RTG Account for UB Card';
                                    else
                                        $addiMsg = 'Failed to Create UserBased RTG2 Account for UB Card';
                                } else if (strstr($serviceName, "HAB")) {
                                    $addiMsg = 'Failed to Create UserBased HAB Account for UB Card';
                                }

                                $_Log->logAPI(AuditFunctions::MANUAL_CASINO_UB_ASSIGNMENT, 
					$addiMsg . $cardnumber, $_SESSION['sessionID'], $_SESSION['aID']);
                                $profile->Msg = 'Failed to assign user based Casino';
                            } else {

                                if (isset($msupdate) && $msupdate = 'add' && $msresult > 0) {

                                    $genpassbatchcount = $_GeneratedPasswordBatch->checkGenPassBatch($MID);

                                    if ($genpassbatchcount == 0) {
                                        $_GeneratedPasswordBatch->updatePasswordBatch($MID, $genpassbatchid);
                                    }

                                    if (strstr($serviceName, "RTG")) {
                                        if ($serviceID == 19)
                                            $addiMsg = 'UB RTG Account Successfully Created for UB Card';
                                        else
                                            $addiMsg = 'UB RTG2 Account Successfully Created for UB Card';
                                    } else if (strstr($serviceName, "HAB")) {
                                        $addiMsg = 'UB HAB Account Successfully Created for UB Card';
                                    }

                                    //get current points
                                    $getpoints = $_MemberCards->getMemberPoints($cardnumber);
                                    $currentpoints = $getpoints[0]['CurrentPoints'];
                                    $getcarddetails = $_MemberCards->getCardDetails($cardnumber);
                                    $siteid = $getcarddetails[0]['SiteID'];
                                    $modulename = "Manual Casino UB Assignment";

                                    $pointsystem = App::getParam("PointSystem");
                                    if ($pointsystem == 2) {
                                        $addcomppoints = $_PcwsWrapper->addCompPoints($cardnumber, $siteid, 15, $currentpoints, 0);
                                        if ($addcomppoints) {
                                            $checkpoints = $_PcwsWrapper->getCompPoints($cardnumber, 0);
                                            $comppoints = $checkpoints['GetCompPoints']['CompBalance'];
                                            if ($comppoints == $currentpoints) {
                                                //zero out points
                                                $updatepoints = $_MemberCards->updateMemberBalance($cardnumber);
                                                $getpoints = $_MemberCards->getMemberPoints($cardnumber);
                                                $currentpoints2 = $getpoints[0]['CurrentPoints'];

                                                if (!$updatepoints) {
                                                    //log error zero out points
                                                    $logmessage = $modulename . ": Failed to zero out current points. [CardNumber: " . $cardnumber . " CurrentPoints: " . $currentpoints2 . "]";
                                                    $logger->logger($logdate, $logtype, $logmessage);
                                                } else {
                                                    if ($currentpoints2 != 0) {
                                                        //log error zero out points
                                                        $logmessage = $modulename . ": Failed to zero out current points. [CardNumber: " . $cardnumber . " CurrentPoints: " . $currentpoints2 . "]";
                                                        $logger->logger($logdate, $logtype, $logmessage);
                                                    }
                                                }
                                            } else {
                                                //log error add comppoints
                                                $logmessage = $modulename . ": Failed to add comp points. [CardNumber: " . $cardnumber . "]";
                                                $logger->logger($logdate, $logtype, $logmessage);
                                            }
                                        } else {
                                            //log error add comppoints
                                            $logmessage = $modulename . ": Failed to add comp points. [CardNumber: " . $cardnumber . "]";
                                            $logger->logger($logdate, $logtype, $logmessage);
                                        }
                                    }

                                    $_Log->logAPI(AuditFunctions::MANUAL_CASINO_UB_ASSIGNMENT, $addiMsg . $cardnumber, $_SESSION['sessionID'], $_SESSION['aID']);
                                    $profile->Msg = 'User based casino assignment was successfully created for this membership account';
                                } else {
                                    if (isset($msupdate) && $msupdate = 'exist') {
                                        $errormsg = 'This membership account has already been associated with a member service account.';
                                        $_Log->logAPI(AuditFunctions::MANUAL_CASINO_UB_ASSIGNMENT, $errormsg . ' for UB Card' . $cardnumber, $_SESSION['sessionID'], $_SESSION['aID']);
                                        $profile->Msg = $errormsg;
                                    } else {
                                        $errormsg = $msresult;
                                        $_Log->logAPI(AuditFunctions::MANUAL_CASINO_UB_ASSIGNMENT, $errormsg . ' for UB Card' . $cardnumber, $_SESSION['sessionID'], $_SESSION['aID']);
                                        $profile->Msg = $errormsg;
                                    }
                                }
                            }
                        } else {
                            //if card status is not temporary or active
                            switch ($status) {
                                case 0: $vstatus = 'Card is inactive.';
                                    break;
                                case 2: $vstatus = 'Card is deactivated.';
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