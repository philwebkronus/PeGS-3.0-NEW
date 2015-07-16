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
App::LoadModuleClass("Kronus", "TerminalSessions");
App::LoadModuleClass("Membership", "PcwsWrapper");

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
$_TerminalSessions = new TerminalSessions();
$_PcwsWrapper = new PcwsWrapper();
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

                    $status = $_Cards->getStatusByCard($cardnumber);
                    if (empty($status)) {
                        $status = 20;
                    } else {
                        $status = $status[0]['Status'];
                    }

                    if ($status == 1) {
                        if ($countMD == 0) {
                            $profile->MID = '';
                            $profile->Name = '';
                            $profile->Birthdate = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring: Card is invalid.';
                            $profile->Msg = $msg;
                        } else {
                            $cardpoints = $_MemberCards->getPointsByCard($cardnumber);

                            $currentpoints = $_PcwsWrapper->getCompPoints($cardnumber, 0);
                            $currentpoints = $currentpoints['GetCompPoints']['CompBalance'];
                            
                            $MemberInfoResult2 = $_MemberInfo->getMemberInfoByID($MIDResult[0]['MID']);
                            $MemberInfoResult = $_MemberInfo->getPlayerName($MIDResult[0]['MID']);
                            
                            $MemberInfoResult[0] = array_merge($MemberInfoResult[0], $MemberInfoResult2[0]);
                            if (isset($MemberInfoResult[0]['MID']) && $MemberInfoResult[0]['MID'] != '') {
                                $count = $_TerminalSessions->isSessionExists($MemberInfoResult[0]['MID']);
                                if ($count[0]['ctrTerminalID'] == 0) {
                                    $memberinfovalue['FirstName'] = $MemberInfoResult[0]['FirstName'];
                                    $memberinfovalue['LastName'] = $MemberInfoResult[0]['LastName'];
                                    $memberinfovalue['Birthdate'] = $MemberInfoResult[0]['Birthdate'];
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
                                    $profile->Name = $memberinfovalue['FirstName'] . ' ' . $memberinfovalue['LastName'];
                                    $profile->Birthdate = $memberinfovalue['Birthdate'];
                                    $profile->Age = $memberinfovalue['Age'];
                                    $profile->Gender = $memberinfovalue['Gender'];
                                    $profile->Status = $memberinfovalue['Status'];
                                    $profile->LifeTimePoints = number_format($cardpoints['LifeTimePoints']);
                                    $profile->CurrentPoints = number_format($currentpoints);
                                    $profile->RedeemedPoints = number_format($cardpoints['RedeemedPoints']);
                                    $profile->BonusPoints = number_format($cardpoints['BonusPoints']);
                                } else {
                                    $profile->MID = '';
                                    $profile->Name = '';
                                    $profile->Birthdate = '';
                                    $profile->Age = '';
                                    $profile->Gender = '';
                                    $profile->Status = '';
                                    $msg = 'Red Card Transferring:  Cannot transfer points with an active session.';
                                    $profile->Msg = $msg;
                                }
                            } else {
                                $profile->MID = '';
                                $profile->Name = '';
                                $profile->Birthdate = '';
                                $profile->Age = '';
                                $profile->Gender = '';
                                $profile->Status = '';
                                $msg = 'Red Card Transferring:  Card is invalid';
                                $profile->Msg = $msg;
                            }
                        }
                    } else if ($status == 0) {
                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring: Card is inactive.';
                        $profile->Msg = $msg;
                    } else if ($status == 5) {
                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring: Temporary account is not allowed. Please enter Red Card only.';
                        $profile->Msg = $msg;
                    } else if ($status == 7) {
                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring : Red card is already migrated.';
                        $profile->Msg = $msg;
                    } else if ($status == 8) {
                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring : Temporary account is not allowed. Please enter red card only.';
                        $profile->Msg = $msg;
                    } else if ($status == 9) {
                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring : Card is banned.';
                        $profile->Msg = $msg;
                    } else {
                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
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
                    $MIDResult = $_Members->getMIDbyUserNameSP($username);

                    $countMD = count($MIDResult);

                    if ($countMD > 0) {

                        $oldcard = $_MemberCards->getOldUBCardNumberUsingMID($MIDResult[0]['MID']);
                        $status = $_Cards->getStatusByCard($oldcard);
                        if (empty($status)) {
                            $status = 20;
                        } else {
                            $status = $status[0]['Status'];
                        }

                        if ($status == 1) {
                            if ($countMD == 0) {
                                $profile->MID = '';
                                $profile->Name = '';
                                $profile->Birthdate = '';
                                $profile->Age = '';
                                $profile->Gender = '';
                                $profile->Status = '';
                                $msg = 'Red Card Transferring: Invalid UserName';
                                $profile->Msg = $msg;
                            } else {
                                $cardpoints = $_MemberCards->getPointsByCard($oldcard);

                                $currentpoints = $_PcwsWrapper->getCompPoints($oldcard, 0);
                                $currentpoints = $currentpoints['GetCompPoints']['CompBalance'];
                                        
                                $MemberInfoResult2 = $_MemberInfo->getMemberInfoByID($MIDResult[0]['MID']);
                                $MemberInfoResult = $_MemberInfo->getPlayerName($MIDResult[0]['MID']);
                                $MemberInfoResult[0] = array_merge($MemberInfoResult[0], $MemberInfoResult2[0]);

                                if (isset($MemberInfoResult[0]['MID']) && $MemberInfoResult[0]['MID'] != '') {
                                    $count = $_TerminalSessions->isSessionExists($MemberInfoResult[0]['MID']);
                                    if ($count[0]['ctrTerminalID'] == 0) {
                                        $memberinfovalue['FirstName'] = $MemberInfoResult[0]['FirstName'];
                                        $memberinfovalue['LastName'] = $MemberInfoResult[0]['LastName'];
                                        $memberinfovalue['Birthdate'] = $MemberInfoResult[0]['Birthdate'];
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
                                        $profile->Name = $memberinfovalue['FirstName'] . ' ' . $memberinfovalue['LastName'];
                                        $profile->Birthdate = $memberinfovalue['Birthdate'];
                                        $profile->Age = $memberinfovalue['Age'];
                                        $profile->Gender = $memberinfovalue['Gender'];
                                        $profile->Status = $memberinfovalue['Status'];
                                        $profile->LifeTimePoints = number_format($cardpoints['LifeTimePoints']);
                                        $profile->CurrentPoints = number_format($currentpoints);
                                        $profile->RedeemedPoints = number_format($cardpoints['RedeemedPoints']);
                                        $profile->BonusPoints = number_format($cardpoints['BonusPoints']);
                                    } else {
                                        $profile->MID = '';
                                        $profile->Name = '';
                                        $profile->Birthdate = '';
                                        $profile->Age = '';
                                        $profile->Gender = '';
                                        $profile->Status = '';
                                        $msg = 'Red Card Transferring:  Cannot transfer points with an active session.';
                                        $profile->Msg = $msg;
                                    }
                                } else {
                                    $profile->MID = '';
                                    $profile->Name = '';
                                    $profile->Birthdate = '';
                                    $profile->Age = '';
                                    $profile->Gender = '';
                                    $profile->Status = '';
                                    $msg = 'Red Card Transferring: Invalid UserName';
                                    $profile->Msg = $msg;
                                }
                            }
                        } else if ($status == 0) {
                            $profile->MID = '';
                            $profile->Name = '';
                            $profile->Birthdate = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring: Inactive Username.';
                            $profile->Msg = $msg;
                        } else if ($status == 5) {
                            $profile->MID = '';
                            $profile->Name = '';
                            $profile->Birthdate = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring: Temporary account is not allowed.';
                            $profile->Msg = $msg;
                        } else if ($status == 7) {
                            $profile->MID = '';
                            $profile->Name = '';
                            $profile->Birthdate = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring : User was already migrated.';
                            $profile->Msg = $msg;
                        } else if ($status == 8) {
                            $profile->MID = '';
                            $profile->Name = '';
                            $profile->Birthdate = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring : Temporary account is not allowed.';
                            $profile->Msg = $msg;
                        } else if ($status == 9) {
                            $profile->MID = '';
                            $profile->Name = '';
                            $profile->Birthdate = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring : User is banned.';
                            $profile->Msg = $msg;
                        } else {
                            $profile->MID = '';
                            $profile->Name = '';
                            $profile->Birthdate = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring:  Invalid Username';
                            $profile->Msg = $msg;
                        }
                    } else {
                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
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

                $datecreated = "NOW(6)";

                $carddetails = $_MemberCards->getCardDetails($oldcard);
                $carddetails = $carddetails[0];
                $carddetailsnew = $_Cards->getCardDetails($newcard);
                $newcarddetails = $_MemberCards->getCardDetails($newcard);
                $fromMemberCardID = $_MemberCards->getMemCardIDByCardNumber($oldcard);
                $toMemberCardID = $_Cards->getMemCardIDByCardNumber($newcard);
                $status = $_Cards->getStatusByCard($newcard);
                if (empty($status)) {
                    $status = 20;
                } else {
                    $status = $status[0]['Status'];
                }

                if (!empty($carddetailsnew)) {
                    if (empty($newcarddetails)) {
                        $newcarddetailz['LifetimePoints'] = 0;
                        $newcarddetailz['CurrentPoints'] = 0;
                        $newcarddetailz['RedeemedPoints'] = 0;
                    } else {
                        $newcarddetailz = $newcarddetails[0];
                    }
                    $siteid = $carddetails['SiteID'];
                    //get comp points from RTG
                    $comp_points = $_PcwsWrapper->getCompPoints($oldcard, 0);
                    $comp_points = $comp_points['GetCompPoints']['CompBalance'];
                    if ($comp_points == "") { $comp_points = 0; }
                    
                    $lifetimepoints = $carddetails['LifetimePoints'] + $newcarddetailz['LifetimePoints'];
                    //$currentpoints = $carddetails['CurrentPoints'] + $newcarddetailz['CurrentPoints'];
                    $currentpoints = $comp_points;
                    $redeemedpoints = $carddetails['RedeemedPoints'] + $newcarddetailz['RedeemedPoints'];

                    $newcardnumber = $newcard;
                    $mid = $MID;
                    $aid = $_SESSION['aID'];
                    $status1 = CardStatus::ACTIVE;

                    $oldubcardnumber = $carddetails[0]['CardNumber'];
                    $status2 = CardStatus::NEW_MIGRATED;

                    $cardidB = $_Cards->getCardDetails($newcard);
                    foreach ($cardidB as $value) {
                        $cardid2 = $value['CardID'];
                    }
                    $cardid2 = $cardid2;
                    $cardidA = $_Cards->getCardDetails($oldcard);
                    foreach ($cardidA as $value) {
                        $cardid1 = $value['CardID'];
                        $cardtypeid1 = $value['CardTypeID'];
                    }
                    $cardid1 = $cardid1;
                    $cardtypeid1 = $cardtypeid1;

                    if ($status == 0 && $newcard != $oldcard) {
                        if ($currentpoints < 0) {
                            $isSuccess = false;
                            $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $carddetails["CurrentPoints"] . '; To Card: ' . $newcard . ', Pts: ' . $newcarddetailz["CurrentPoints"] . ', Failed', $_SESSION['aID']);
                            $error = "Card has negative current points.";
                            $logger->logger($logdate, $logtype, $error);

                            $profile->MID = '';
                            $profile->Name = '';
                            $profile->Birthdate = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Migration failed. Card has negative current points.';
                            $profile->Msg = $msg;
                        } else {
                            $_MemberCards->transferMemberCard($MID, $cardid2, $siteid, $lifetimepoints, $currentpoints, $redeemedpoints, $newcardnumber, $oldcard, $status1, $status2, $aid, $datecreated, $cardtypeid1);

                            if (!App::HasError()) {

                                $cardidA = $_Cards->getCardDetails($oldcard);
                                foreach ($cardidA as $value) {
                                    $cardid1 = $value['CardID'];
                                }
                                $cardid1 = $cardid1;
                                $status1 = CardStatus::NEW_MIGRATED;

                                $cardidB = $_Cards->getCardDetails($newcard);
                                foreach ($cardidB as $value) {
                                    $cardid2 = $value['CardID'];
                                }
                                $cardid2 = $cardid2;
                                $status2 = CardStatus::ACTIVE;

                                $_Cards->updateCardsStatus2($cardid1, $cardid2, $status1, $status2, $aid, $datecreated);

                                if (!App::HasError()) {
                                    $dateupdated = $datecreated;
                                    $_MemberCards->updateMemberCardsStatus($cardid1, $cardid2, $status1, $status2, $aid, $dateupdated);
                                    if (!App::HasError()) {
                                        $fromMemberCardIDA = $_MemberCards->getMemCardIDByCardNumber($oldcard);
                                        foreach ($fromMemberCardIDA as $value) {
                                            $fromMemberCardID = $value['MemberCardID'];
                                        }
                                        $fromMemberCardID = $fromMemberCardID;
                                        $toMemberCardIDA = $_MemberCards->getMemCardIDByCardNumber($newcard);
                                        foreach ($toMemberCardIDA as $value) {
                                            $toMemberCardID = $value['MemberCardID'];
                                        }
                                        $toMemberCardID = $toMemberCardID;
                                        $isSuccess = true;
                                        $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $carddetails["CurrentPoints"] . '; To Card: ' . $newcard . ', Pts: ' . $newcarddetailz["CurrentPoints"] . ', Success', $_SESSION['aID']);
                                        $_MemberPointsTransferLog->logPointsTransfer($fromMemberCardID, $toMemberCardID, $lifetimepoints, $currentpoints, $redeemedpoints, $datecreated, $aid);
                                        $profile->MID = '';
                                        $profile->Name = '';
                                        $profile->Birthdate = '';
                                        $profile->Age = '';
                                        $profile->Gender = '';
                                        $profile->Status = '';
                                        $msg = 'Red Card Transferring: Transaction Successful';
                                        $profile->Msg = $msg;
                                    } else {
                                        $isSuccess = false;
                                        $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $currentpoints . '; To Card: ' . $newcard . ', Pts: ' . $newcarddetailz["CurrentPoints"] . ', Failed', $_SESSION['aID']);
                                        $error = "Failed to transfer points";
                                        $logger->logger($logdate, $logtype, $error);

                                        $profile->MID = '';
                                        $profile->Name = '';
                                        $profile->Birthdate = '';
                                        $profile->Age = '';
                                        $profile->Gender = '';
                                        $profile->Status = '';
                                        $msg = 'Red Card Transferring: Transaction Failed';
                                        $profile->Msg = $msg;
                                    }
                                } else {
                                    $isSuccess = false;
                                    $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $currentpoints . '; To Card: ' . $newcard . ', Pts: ' . $newcarddetailz["CurrentPoints"] . ', Failed', $_SESSION['aID']);
                                    $error = "Failed to transfer points";
                                    $logger->logger($logdate, $logtype, $error);

                                    $profile->MID = '';
                                    $profile->Name = '';
                                    $profile->Birthdate = '';
                                    $profile->Age = '';
                                    $profile->Gender = '';
                                    $profile->Status = '';
                                    $msg = 'Red Card Transferring: Transaction Failed';
                                    $profile->Msg = $msg;
                                }
                            } else {
                                
                                $isSuccess = false;
                                $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $currentpoints . '; To Card: ' . $newcard . ', Pts: ' . $newcarddetailz["CurrentPoints"] . ', Failed', $_SESSION['aID']);
                                $error = "Failed to transfer points";
                                $logger->logger($logdate, $logtype, $error);

                                $profile->MID = '';
                                $profile->Name = '';
                                $profile->Birthdate = '';
                                $profile->Age = '';
                                $profile->Gender = '';
                                $profile->Status = '';
                                $msg = 'Red Card Transferring: Transaction Failed';
                                $profile->Msg = $msg;
                            }
                        }
                    } else if ($newcard == $oldcard) {

                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring:  Cannot transfer to same Card.';
                        $profile->Msg = $msg;
                    } else if ($status == 1) {

                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring:  Active Red Card is not allowed.';
                        $profile->Msg = $msg;
                    } else if ($status == 7 || $status == 8) {

                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring:  Red Card is already migrated.';
                        $profile->Msg = $msg;
                    } else if ($status == 5) {

                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring:  Temporary Card is not allowed.';
                        $profile->Msg = $msg;
                    } else if ($status == 9) {

                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring:  Red Card is banned.';
                        $profile->Msg = $msg;
                    } else if ($status == 2) {

                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Red Card Transferring:  Card has already been deactivated.';
                        $profile->Msg = $msg;
                    }
                } else if ($status == 1) {

                    $profile->MID = '';
                    $profile->Name = '';
                    $profile->Birthdate = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring:  Active Red Card is not allowed.';
                    $profile->Msg = $msg;
                } else if ($status == 7 || $status == 8) {

                    $profile->MID = '';
                    $profile->Name = '';
                    $profile->Birthdate = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring:  Red Card is already migrated.';
                    $profile->Msg = $msg;
                } else if ($status == 2) {

                    $profile->MID = '';
                    $profile->Name = '';
                    $profile->Birthdate = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring:  Card has already been deactivated.';
                    $profile->Msg = $msg;
                } else if ($newcard == $oldcard) {

                    $profile->MID = '';
                    $profile->Name = '';
                    $profile->Birthdate = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring:  Cannot transfer to same Card.';
                    $profile->Msg = $msg;
                } else {
                    $profile->MID = '';
                    $profile->Name = '';
                    $profile->Birthdate = '';
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

                $datecreated = "NOW(6)";

                $MIDResult = $_Members->getMIDbyUserNameSP($username);
                $MID = $MIDResult[0]['MID'];

                $oldcard = $_MemberCards->getOldUBCardNumberUsingMID($MID);
                $carddetails = $_MemberCards->getCardDetails($oldcard);
                $carddetailsnew = $_Cards->getCardDetails($newcard);
                $status = $_Cards->getStatusByCard($newcard);

                if (empty($status)) {
                    $status = 20;
                } else {
                    $status = $status[0]['Status'];
                }

                $siteid = $carddetails[0]['SiteID'];
                $lifetimepoints = $carddetails[0]['LifetimePoints'];
                //$currentpoints = $carddetails[0]['CurrentPoints'];
                $redeemedpoints = $carddetails[0]['RedeemedPoints'];

                $newcardnumber = $newcard;

                $mid = $MID;
                $aid = $_SESSION['aID'];
                $status1 = CardStatus::ACTIVE;

                $oldubcardnumber = $oldcard;
                $status2 = CardStatus::NEW_MIGRATED;
                $fromMemberCardID = $_MemberCards->getMemCardIDByCardNumber($oldcard);
                $toMemberCardID = $_Cards->getMemCardIDByCardNumber($newcard);

                if ($status == 0 && $newcard != $oldcard) {
                    if ($currentpoints < 0) {
                        $isSuccess = false;
                        $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $carddetails[0]["CurrentPoints"] . '; To Card: ' . $newcard . ', Pts: ' . $carddetails[0]["CurrentPoints"] . ', Failed', $_SESSION['aID']);
                        $error = "Card has negative current points.";
                        $logger->logger($logdate, $logtype, $error);

                        $profile->MID = '';
                        $profile->Name = '';
                        $profile->Birthdate = '';
                        $profile->Age = '';
                        $profile->Gender = '';
                        $profile->Status = '';
                        $msg = 'Migration failed. Card has negative current points.';
                        $profile->Msg = $msg;
                    } else {
                        //get comp points 
                        $currentpoints = $_PcwsWrapper->getCompPoints($oldcard, 0);
                        $currentpoints = $currentpoints['GetCompPoints']['CompBalance'];
                
                        if ($lifetimepoints == '') {
                            $lifetimepoints = 0;
                        } else {
                            $lifetimepoints = $lifetimepoints;
                        }
                        if ($currentpoints == '') {
                            $currentpoints = 0;
                        } 
                        
                        if ($redeemedpoints == '') {
                            $redeemedpoints = 0;
                        } else {
                            $redeemedpoints = $redeemedpoints;
                        }

                        $cardidB = $_Cards->getCardDetails($newcard);
                        foreach ($cardidB as $value) {
                            $cardid2 = $value['CardID'];
                        }
                        $cardid2 = $cardid2;

                        $cardtypeid1 = $_Cards->getCardDetails($oldcard);
                        foreach ($cardtypeid1 as $value) {
                            $cardtypeid1 = $value['CardTypeID'];
                        }
                        $cardtypeid1 = $cardtypeid1;
                        $_MemberCards->transferMemberCard($MID, $cardid2, $siteid, $lifetimepoints, $currentpoints, $redeemedpoints, $newcardnumber, $oldcard, $status1, $status2, $aid, $datecreated, $cardtypeid1);

                        if (!App::HasError()) {

                            $cardidA = $_Cards->getCardDetails($oldcard);
                            foreach ($cardidA as $value) {
                                $cardid1 = $value['CardID'];
                            }
                            $cardid1 = $cardid1;
                            $status1 = CardStatus::NEW_MIGRATED;

                            $cardidB = $_Cards->getCardDetails($newcard);
                            foreach ($cardidB as $value) {
                                $cardid2 = $value['CardID'];
                            }
                            $cardid2 = $cardid2;
                            $status2 = CardStatus::ACTIVE;

                            $_Cards->updateCardsStatus2($cardid1, $cardid2, $status1, $status2, $aid, $datecreated);

                            if (!App::HasError()) {
                                $dateupdated = $datecreated;
                                $_MemberCards->updateMemberCardsStatus($cardid1, $cardid2, $status1, $status2, $aid, $dateupdated);
                                if (!App::HasError()) {
                                    $fromMemberCardIDA = $_MemberCards->getMemCardIDByCardNumber($oldcard);
                                    foreach ($fromMemberCardIDA as $value) {
                                        $fromMemberCardID = $value['MemberCardID'];
                                    }
                                    $fromMemberCardID = $fromMemberCardID;
                                    $toMemberCardIDA = $_MemberCards->getMemCardIDByCardNumber($newcard);
                                    foreach ($toMemberCardIDA as $value) {
                                        $toMemberCardID = $value['MemberCardID'];
                                    }
                                    $toMemberCardID = $toMemberCardID;
                                    $isSuccess = true;
                                    $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $currentpoints . '; To Card: ' . $newcard . ', Pts: 0, Success', $_SESSION['aID']);
                                    $_MemberPointsTransferLog->logPointsTransfer($fromMemberCardID, $toMemberCardID, $lifetimepoints, $currentpoints, $redeemedpoints, $datecreated, $aid);
                                    $profile->MID = '';
                                    $profile->Name = '';
                                    $profile->Birthdate = '';
                                    $profile->Age = '';
                                    $profile->Gender = '';
                                    $profile->Status = '';
                                    $msg = 'Red Card Transferring: Transaction Successful';
                                    $profile->Msg = $msg;
                                } else {
                                    $isSuccess = false;
                                    $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $currentpoints . '; To Card: ' . $newcard . ', Pts: 0, Failed', $_SESSION['aID']);
                                    $error = "Failed to transfer points";
                                    $logger->logger($logdate, $logtype, $error);

                                    $profile->MID = '';
                                    $profile->Name = '';
                                    $profile->Birthdate = '';
                                    $profile->Age = '';
                                    $profile->Gender = '';
                                    $profile->Status = '';
                                    $msg = 'Red Card Transferring: Transaction Failed';
                                    $profile->Msg = $msg;
                                }
                            } else {
                                $isSuccess = false;
                                $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $currentpoints . '; To Card: ' . $newcard . ', Pts: 0, Failed', $_SESSION['aID']);
                                $error = "Failed to transfer points";
                                $logger->logger($logdate, $logtype, $error);

                                $profile->MID = '';
                                $profile->Name = '';
                                $profile->Birthdate = '';
                                $profile->Age = '';
                                $profile->Gender = '';
                                $profile->Status = '';
                                $msg = 'Red Card Transferring: Transaction Failed';
                                $profile->Msg = $msg;
                            }
                        } else {
                            $isSuccess = false;
                            $_Log->logAPI(AuditFunctions::MARKETING_RED_CARD_TRANSFERRING, 'From Card: ' . $oldcard . ', Pts: ' . $currentpoints . '; To Card: ' . $newcard . ', Pts: 0, Failed', $_SESSION['aID']);
                            $error = "Failed to transfer points";
                            $logger->logger($logdate, $logtype, $error);

                            $profile->MID = '';
                            $profile->Name = '';
                            $profile->Birthdate = '';
                            $profile->Age = '';
                            $profile->Gender = '';
                            $profile->Status = '';
                            $msg = 'Red Card Transferring: Transaction Failed';
                            $profile->Msg = $msg;
                        }
                    }
                } else if ($newcard == $oldcard) {

                    $profile->MID = '';
                    $profile->Name = '';
                    $profile->Birthdate = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring:  Cannot transfer to same Card.';
                    $profile->Msg = $msg;
                } else if ($status == 1) {

                    $profile->MID = '';
                    $profile->Name = '';
                    $profile->Birthdate = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring:  Active Red Card is not allowed.';
                    $profile->Msg = $msg;
                } else if ($status == 7 || $status == 8) {

                    $profile->MID = '';
                    $profile->Name = '';
                    $profile->Birthdate = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring:  Red Card is already migrated.';
                    $profile->Msg = $msg;
                } else if ($status == 5) {

                    $profile->MID = '';
                    $profile->Name = '';
                    $profile->Birthdate = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring:  Temporary Card is not allowed.';
                    $profile->Msg = $msg;
                } else if ($status == 9) {

                    $profile->MID = '';
                    $profile->Name = '';
                    $profile->Birthdate = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring:  Red Card is banned.';
                    $profile->Msg = $msg;
                } else if ($status == 2) {

                    $profile->MID = '';
                    $profile->Name = '';
                    $profile->Birthdate = '';
                    $profile->Age = '';
                    $profile->Gender = '';
                    $profile->Status = '';
                    $msg = 'Red Card Transferring:  Card has already been deactivated.';
                    $profile->Msg = $msg;
                } else {
                    $profile->MID = '';
                    $profile->Name = '';
                    $profile->Birthdate = '';
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