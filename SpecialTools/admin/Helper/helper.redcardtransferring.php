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
App::LoadModuleClass("Kronus", "TransactionSummary");
App::LoadModuleClass("Kronus", "Sites");
App::LoadModuleClass("Kronus", "Sites");
App::LoadModuleClass("Loyalty", "Cards");
App::LoadModuleClass("Loyalty", "CardStatus");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("Loyalty", "MemberPointsTransferLog");
App::LoadModuleClass("Admin", "AccountSessions");

//Load Needed Core Class.
App::LoadCore('Validation.class.php');

//Initialize Modules
$_MemberCards = new MemberCards();
$_MemberInfo = new MemberInfo();
$_Cards = new Cards();
$_Members = new Members();
$_TransactionSummary = new TransactionSummary();
$_Sites = new Sites();
$_Log = new AuditTrail();
$_MemberPointsTransferLog = new MemberPointsTransferLog();
$_AccountSessions = new AccountSessions();
$profile = null;
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
            //for Card Number dropdown
            case "ProfileData":
                if (isset($_POST['Card']) && $_POST['Card'] != '') {
                    $cardnumber = $_POST['Card'];
                    $MIDResult = $_MemberCards->getMIDByCard($cardnumber);
                    $countMD = count($MIDResult);

                    $status = $_MemberCards->getStatusByCard($cardnumber);
                    if (empty($status)) {
                        $status = 20;
                    } else {
                        $status = $status[0]['Status'];
                    }

                    if ($status == 1) {
                        if ($countMD == 0) {
                            $profile->MID = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring: Card is invalid.';
                            $profile->Msg = $msg;
                        } else {
                            $cardpoints = $_MemberCards->getPointsByCard($cardnumber);

                            $MemberInfoResult = $_MemberInfo->getMemberInfoByID($MIDResult[0]['MID']);
                            if (isset($MemberInfoResult[0]['MID']) && $MemberInfoResult[0]['MID'] != '') {
                                $memberinfovalue['Age'] = $MemberInfoResult[0]['Age'];
                                $memberinfovalue['Gender'] = $MemberInfoResult[0]['Gender'] == 1 ? "Male" : "Female";

                                if ($MemberInfoResult[0]['Status'] == 1) {
                                    $memberinfovalue['Status'] = 'Active';
                                } else if ($MemberInfoResult[0]['Status'] == 2) {
                                    $memberinfovalue['Status'] = 'Suspended';
                                } else if ($MemberInfoResult[0]['Status'] == 3) {
                                    $memberinfovalue['Status'] = 'Locked (Attempts)';
                                } else if ($MemberInfoResult[0]['Status'] == 4) {
                                    $memberinfovalue['Status'] = 'Locked (Admin)';
                                } else if ($MemberInfoResult[0]['Status'] == 5) {
                                    $memberinfovalue['Status'] = 'Banned';
                                } else if ($MemberInfoResult[0]['Status'] == 6) {
                                    $memberinfovalue['Status'] = 'Terminated';
                                }
                                $profile->MID = $MIDResult[0]['MID'];
                                $profile->Age = $memberinfovalue['Age'];
                                $profile->Gender = $memberinfovalue['Gender'];
                                $profile->Status = $memberinfovalue['Status'];
                                $profile->LifeTimePoints = $cardpoints['LifeTimePoints'];
                                $profile->CurrentPoints = $cardpoints['CurrentPoints'];
                                $profile->RedeemedPoints = $cardpoints['RedeemedPoints'];
                                $profile->BonusPoints = $cardpoints['BonusPoints'];
                            } else {
                                $profile->MID = '';
                                $profile->Age = '';
                                $profile->Gender = '';
                                $profile->Status = '';
                                $msg = 'Red Card Transferring:  Card is invalid';
                                $profile->Msg = $msg;
                            }
                        }
                    } else if ($status == 0) {
                        $profile->MID = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring: Card is inactive.';
                        $profile->Msg = $msg;
                    } else if ($status == 5) {
                        $profile->MID = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring: Temporary account is not allowed. Please enter Red Card only.';
                        $profile->Msg = $msg;
                    } else if ($status == 7) {
                        $profile->MID = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring : Red card is already migrated.';
                        $profile->Msg = $msg;
                    } else if ($status == 8) {
                        $profile->MID = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring : Temporary account is not allowed. Please enter red card only.';
                        $profile->Msg = $msg;
                    } else if ($status == 9) {
                        $profile->MID = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring : Card is banned.';
                        $profile->Msg = $msg;
                    } else {
                        $profile->MID = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring:  Card is invalid';
                        $profile->Msg = $msg;
                    }


                    echo json_encode($profile);
                }

                break;


            case "ProfileData2":
                if (isset($_POST['UserName']) && $_POST['UserName'] != '') {
                    $username = $_POST['UserName'];
                    $MIDResult = $_Members->getMIDbyUserName($username);
                    $countMD = count($MIDResult);

                    if ($countMD > 0) {

                        $oldcard = $_MemberCards->getOldUBCardNumberUsingMID($MIDResult[0]['MID']);
                        $status = $_MemberCards->getStatusByMID($MIDResult[0]['MID']);
                        if (empty($status)) {
                            $status = 20;
                        } else {
                            $status = $status[0]['Status'];
                        }
                        
                        if ($status == 1) {
                            if ($countMD == 0) {
                                $profile->MID = '';
                                $profile->Age = '';
                                $profile->Gender = '';
                                $profile->Status = '';
                                $msg = 'Red Card Transferring: Invalid UserName';
                                $profile->Msg = $msg;
                            } else {
                                $cardpoints = $_MemberCards->getPointsByCard($oldcard);

                                $MemberInfoResult = $_MemberInfo->getMemberInfoByID($MIDResult[0]['MID']);
                                if (isset($MemberInfoResult[0]['MID']) && $MemberInfoResult[0]['MID'] != '') {
                                    $memberinfovalue['Age'] = $MemberInfoResult[0]['Age'];
                                    $memberinfovalue['Gender'] = $MemberInfoResult[0]['Gender'] == 1 ? "Male" : "Female";

                                    if ($MemberInfoResult[0]['Status'] == 1) {
                                        $memberinfovalue['Status'] = 'Active';
                                    } else if ($MemberInfoResult[0]['Status'] == 2) {
                                        $memberinfovalue['Status'] = 'Suspended';
                                    } else if ($MemberInfoResult[0]['Status'] == 3) {
                                        $memberinfovalue['Status'] = 'Locked (Attempts)';
                                    } else if ($MemberInfoResult[0]['Status'] == 4) {
                                        $memberinfovalue['Status'] = 'Locked (Admin)';
                                    } else if ($MemberInfoResult[0]['Status'] == 5) {
                                        $memberinfovalue['Status'] = 'Banned';
                                    } else if ($MemberInfoResult[0]['Status'] == 6) {
                                        $memberinfovalue['Status'] = 'Terminated';
                                    }
                                    $profile->MID = $MIDResult[0]['MID'];
                                    $profile->Age = $memberinfovalue['Age'];
                                    $profile->Gender = $memberinfovalue['Gender'];
                                    $profile->Status = $memberinfovalue['Status'];
                                    $profile->LifeTimePoints = $cardpoints['LifeTimePoints'];
                                    $profile->CurrentPoints = $cardpoints['CurrentPoints'];
                                    $profile->RedeemedPoints = $cardpoints['RedeemedPoints'];
                                    $profile->BonusPoints = $cardpoints['BonusPoints'];
                                } else {
                                    $profile->MID = '';
                                    $profile->Age = '';
                                    $profile->Gender = '';
                                    $profile->Status = '';
                                    $msg = 'Red Card Transferring: Invalid UserName';
                                    $profile->Msg = $msg;
                                }
                            }
                        } else if ($status == 0) {
                            $profile->MID = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring: Inactive Username.';
                            $profile->Msg = $msg;
                        } else if ($status == 5) {
                            $profile->MID = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring: Temporary account is not allowed.';
                            $profile->Msg = $msg;
                        } else if ($status == 7) {
                            $profile->MID = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring : User was already migrated.';
                            $profile->Msg = $msg;
                        } else if ($status == 8) {
                            $profile->MID = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring : Temporary account is not allowed.';
                            $profile->Msg = $msg;
                        } else if ($status == 9) {
                            $profile->MID = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring : User is banned.';
                            $profile->Msg = $msg;
                        } else {
                            $profile->MID = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring:  Invalid Username';
                            $profile->Msg = $msg;
                        }
                    } else {
                        $profile->MID = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring: Invalid UserName';
                        $profile->Msg = $msg;
                    }

                    echo json_encode($profile);
                }

                break;


            case "ProcessPoints":
                $oldcard = $_POST['OldCard'];
                $newcard = $_POST['NewCard'];
                $MID = $_POST['MID'];

                $datecreated = "now_usec()";

                $carddetails = $_MemberCards->getCardDetails($oldcard);
                $carddetails = $carddetails[0];

                $newcarddetails = $_MemberCards->getCardDetails($newcard);

                $fromMemberCardID = $_MemberCards->getMemCardIDByCardNumber($oldcard);
                $toMemberCardID = $_MemberCards->getMemCardIDByCardNumber($newcard);

                $status = $_MemberCards->getStatusByCard($newcard);
                if (empty($status)) {
                    $status = 20;
                } else {
                    $status = $status[0]['Status'];
                }

                if (!empty($newcarddetails)) {
                    $newcarddetailz = $newcarddetails[0];

                    $lifetimepoints = $carddetails['LifetimePoints'] + $newcarddetailz['LifetimePoints'];
                    $currentpoints = $carddetails['CurrentPoints'] + $newcarddetailz['CurrentPoints'];
                    $redeemedpoints = $carddetails['RedeemedPoints'] + $newcarddetailz['RedeemedPoints'];

                    $newcardnumber = $newcarddetailz['CardNumber'];
                    $mid = $MID;
                    $aid = $_SESSION['aID'];
                    $status1 = CardStatus::ACTIVE;

                    $oldubcardnumber = $carddetails['CardNumber'];
                    $status2 = CardStatus::NEW_MIGRATED;

                    $status = $_MemberCards->getStatusByCard($newcard);
                    if (empty($status)) {
                        $status = 20;
                    } else {
                        $status = $status[0]['Status'];
                    }

                    if ($status == 1 && $newcard != $oldcard) {
                        if ($currentpoints < 0) {
                            $isSuccess = false;
                            $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $carddetails["CurrentPoints"] . '; To Card: ' . $newcard . ', Pts: ' . $newcarddetailz["CurrentPoints"] . ', Failed', $_SESSION['aID']);
                            $error = "Card has negative current points.";
                            $logger->logger($logdate, $logtype, $error);

                            $profile->MID = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Migration failed. Card has negative current points.';
                            $profile->Msg = $msg;
                        } else {
                            $_MemberCards->transferMemberCard($lifetimepoints, $currentpoints, $redeemedpoints, $newcardnumber, $oldubcardnumber, $status1, $status2, $aid, $datecreated);

                            if (!App::HasError()) {

                                $cardid1 = $newcarddetailz['CardID'];
                                $status1 = CardStatus::ACTIVE;

                                $cardid2 = $carddetails['CardID'];
                                $status2 = CardStatus::NEW_MIGRATED;

                                $_Cards->updateCardsStatus2($cardid1, $cardid2, $status1, $status2, $aid, $datecreated);

                                if (!App::HasError()) {
                                    $fromMemberCardID = $_MemberCards->getMemCardIDByCardNumber($oldcard);
                                    $toMemberCardID = $_MemberCards->getMemCardIDByCardNumber($newcard);
                                    $isSuccess = true;
                                    $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $carddetails["CurrentPoints"] . '; To Card: ' . $newcard . ', Pts: ' . $newcarddetailz["CurrentPoints"] . ', Success', $_SESSION['aID']);
                                    $_MemberPointsTransferLog->logPointsTransfer($fromMemberCardID[0]['MemberCardID'], $toMemberCardID[0]['MemberCardID'], $lifetimepoints, $currentpoints, $redeemedpoints, $datecreated, $aid);
                                    $profile->MID = '';
                                    $profile->Age = '';
                                    $profile->Gender = '';
                                    $profile->Status = '';
                                    $msg = 'Red Card Transferring: Transaction Successful';
                                    $profile->Msg = $msg;
                                } else {
                                    $isSuccess = false;
                                    $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $carddetails["CurrentPoints"] . '; To Card: ' . $newcard . ', Pts: ' . $newcarddetailz["CurrentPoints"] . ', Failed', $_SESSION['aID']);
                                    $error = "Failed to transfer points";
                                    $logger->logger($logdate, $logtype, $error);

                                    $profile->MID = '';
                                    $profile->Age = '';
                                    $profile->Gender = '';
                                    $profile->Status = '';
                                    $msg = 'Red Card Transferring: Transaction Failed';
                                    $profile->Msg = $msg;
                                }
                            } else {

                                $isSuccess = false;
                                $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $carddetails["CurrentPoints"] . '; To Card: ' . $newcard . ', Pts: ' . $newcarddetailz["CurrentPoints"] . ', Failed', $_SESSION['aID']);
                                $error = "Failed to transfer points";
                                $logger->logger($logdate, $logtype, $error);

                                $profile->MID = '';
                                $profile->Age = '';
                                $profile->Gender = '';
                                $profile->Status = '';
                                $msg = 'Red Card Transferring: Transaction Failed';
                                $profile->Msg = $msg;
                            }
                        }
                    } else if ($status == 7 || $status == 8) {

                        $profile->MID = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring:  Red Card is already migrated.';
                        $profile->Msg = $msg;
                    } else {

                        $profile->MID = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring:  Cannot transfer to same Card.';
                        $profile->Msg = $msg;
                    }
                } else if ($status == 7 || $status == 8) {

                    $profile->MID = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring:  Red Card is already migrated.';
                    $profile->Msg = $msg;
                } else {
                    $profile->MID = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring: Invalid Card';
                    $profile->Msg = $msg;
                }

                echo json_encode($profile);

                break;


            case "ProcessPoints2":
                $username = $_POST['UserName'];
                $newcard = $_POST['NewCard'];
                $MID = $_POST['MID'];

                $datecreated = "now_usec()";

                $MIDResult = $_Members->getMIDbyUserName($username);
                $MID = $MIDResult[0]['MID'];

                $oldcard = $_MemberCards->getOldUBCardNumberUsingMID($MID);

                $carddetails = $_MemberCards->getCardDetails($oldcard);
                $carddetails = $carddetails[0];

                $newcarddetails = $_MemberCards->getCardDetails($newcard);

                $status = $_MemberCards->getStatusByCard($newcard);
                if (empty($status)) {
                    $status = 20;
                } else {
                    $status = $status[0]['Status'];
                }
                
                if (!empty($newcarddetails)) {
                    $newcarddetailz = $newcarddetails[0];

                    $lifetimepoints = $carddetails['LifetimePoints'] + $newcarddetailz['LifetimePoints'];
                    $currentpoints = $carddetails['CurrentPoints'] + $newcarddetailz['CurrentPoints'];
                    $redeemedpoints = $carddetails['RedeemedPoints'] + $newcarddetailz['RedeemedPoints'];

                    $newcardnumber = $newcarddetailz['CardNumber'];
                    $mid = $MID;
                    $aid = $_SESSION['aID'];
                    $status1 = CardStatus::ACTIVE;

                    $oldubcardnumber = $carddetails['CardNumber'];
                    $status2 = CardStatus::NEW_MIGRATED;

                    $status = $_MemberCards->getStatusByCard($newcard);
                    if (empty($status)) {
                        $status = 20;
                    } else {
                        $status = $status[0]['Status'];
                    }

                    if ($status == 1 && $newcard != $oldcard) {
                        if ($currentpoints < 0) {
                            $isSuccess = false;
                            $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $carddetails["CurrentPoints"] . '; To Card: ' . $newcard . ', Pts: ' . $newcarddetailz["CurrentPoints"] . ', Failed', $_SESSION['aID']);
                            $error = "Card has negative current points.";
                            $logger->logger($logdate, $logtype, $error);

                            $profile->MID = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Migration failed. Card has negative current points.';
                            $profile->Msg = $msg;
                        } else {
                            $_MemberCards->transferMemberCard($lifetimepoints, $currentpoints, $redeemedpoints, $newcardnumber, $oldubcardnumber, $status1, $status2, $aid, $datecreated);

                            if (!App::HasError()) {

                                $cardid1 = $newcarddetailz['CardID'];
                                $status1 = CardStatus::ACTIVE;

                                $cardid2 = $carddetails['CardID'];
                                $status2 = CardStatus::NEW_MIGRATED;

                                $_Cards->updateCardsStatus2($cardid1, $cardid2, $status1, $status2, $aid, $datecreated);

                                if (!App::HasError()) {
                                    $fromMemberCardID = $_MemberCards->getMemCardIDByCardNumber($oldcard);
                                    $toMemberCardID = $_MemberCards->getMemCardIDByCardNumber($newcard);
                                    $isSuccess = true;
                                    $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $carddetails["CurrentPoints"] . '; To Card: ' . $newcard . ', Pts: ' . $newcarddetailz["CurrentPoints"] . ', Success', $_SESSION['aID']);
                                    $_MemberPointsTransferLog->logPointsTransfer($fromMemberCardID[0]['MemberCardID'], $toMemberCardID[0]['MemberCardID'], $lifetimepoints, $currentpoints, $redeemedpoints, $datecreated, $aid);
                                    $profile->MID = '';
                                    $profile->Age = '';
                                    $profile->Gender = '';
                                    $profile->Status = '';
                                    $msg = 'Red Card Transferring: Transaction Successful';
                                    $profile->Msg = $msg;
                                } else {
                                    $isSuccess = false;
                                    $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $carddetails["CurrentPoints"] . '; To Card: ' . $newcard . ', Pts: ' . $newcarddetailz["CurrentPoints"] . ', Failed', $_SESSION['aID']);
                                    $error = "Failed to transfer points";
                                    $logger->logger($logdate, $logtype, $error);

                                    $profile->MID = '';
                                    $profile->Age = '';
                                    $profile->Gender = '';
                                    $profile->Status = '';
                                    $msg = 'Red Card Transferring: Transaction Failed';
                                    $profile->Msg = $msg;
                                }
                            } else {

                                $isSuccess = false;
                                $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $carddetails["CurrentPoints"] . '; To Card: ' . $newcard . ', Pts: ' . $newcarddetailz["CurrentPoints"] . ', Failed', $_SESSION['aID']);
                                $error = "Failed to transfer points";
                                $logger->logger($logdate, $logtype, $error);

                                $profile->MID = '';
                                $profile->Age = '';
                                $profile->Gender = '';
                                $profile->Status = '';
                                $msg = 'Red Card Transferring: Transaction Failed';
                                $profile->Msg = $msg;
                            }
                        }
                    } else if ($status == 7 || $status == 8) {

                        $profile->MID = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring:  Red Card is already migrated.';
                        $profile->Msg = $msg;
                        
                    } else if($newcard == $oldcard) {

                        $profile->MID = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring: Cannot transfer to same Card.';
                        $profile->Msg = $msg;
                    }
                    
                } else if ($status == 7 || $status == 8) {

                    $profile->MID = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring:  Red Card is already migrated.';
                    $profile->Msg = $msg;
                } else if($newcard == $oldcard) {
                        $profile->MID = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring: Cannot transfer to same Card.';
                        $profile->Msg = $msg;
                } else {
                    $profile->MID = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring: Invalid Card';
                    $profile->Msg = $msg;
                }


                echo json_encode($profile);

                //$_MemberCards->processMemberCard($arrMemberCards, $arrTempMemberCards);

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