<?php

/*
 * Fulfillment of Card Migration
 */

include ('../../init.inc.php');

App::LoadModuleClass('Loyalty', "MemberCards");
App::LoadModuleClass('Loyalty', "Cards");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");

$process    = trim($_POST['process']);
        
switch ($process) {
    case "CheckDetails":
        $cardnumber = trim($_POST['cardnumber']);

        $membercards    = new MemberCards();
        $cards          = new Cards();
        if ($cardnumber != "") {
            //check if cards exist
            $isCardExist = $cards->isExist($cardnumber);
            if ($isCardExist) {
                //check if exist in member cards
                $isCardExistInMC = $membercards->isExistInMemberCards($cardnumber);
                if ($isCardExistInMC){
                    $mid = $membercards->getMID($cardnumber);
                    //get Temp Code and Status
                    $tempCode = $membercards->getTempCardByMID($mid[0]['MID']);

                    $cardstatus = $cards->getStatusByCard($cardnumber);
                    $cardstatus = $cardstatus[0]['Status']; //overwrite $cardstatus
                    if ($cardstatus == 0) {
                        if ($tempCode[0]['Status'] == 5){
                            //subject for fulfillment
                            $details = array(
                                'TempCode' => $tempCode[0]['CardNumber'], 
                                'TempStatus' => MemberCards::statusToStr($tempCode[0]['Status']), 
                                'CardNumber' => $cardnumber, 
                                'CardStatus' => Cards::statusToStr($cardstatus), 
                                'TransCode' => 0
                            );
                        }
                        else {
                            //check status
                            $details = array(
                                'TransCode' => 1, 
                                'Message' => 'Card is not subjected to Fulfillment.'
                            );
                        }
                    }
                    else {
                        if ($cardstatus == 1) {
                            $message = "Card is already";
                        }
                        else {
                            $message = "Card is";
                        }
                        $cardstatstr = Cards::statusToStr($cardstatus);
                        //check status
                        $details = array(
                            'TransCode' => 1, 
                            'Message' => "$message $cardstatstr."
                        );
                    }
                    
                }
                else { //
                    //not subject for fulfillment
                    $details = array(
                        'TransCode' => 1, 
                        'Message' => 'Card is not subjected to Fulfillment.'
                    );
                }
            }
            else {
                //card not exist
                $details = array(
                    'TransCode' => 2, 
                    'Message' => 'Card not exist.'
                );
            }   
        }
        else {
            $details = array(
                'TransCode' => 2, 
                'Message' => 'Please enter card number.'
            );
        }
        echo json_encode($details);
        break;
    case "Fulfill":

        $cardnumber = trim($_POST['cardnumber']);
        $tempcode   = trim($_POST['tempcode']);
        
        $membercards = new MemberCards();
        $_Log = new AuditTrail();
        
        if ($cardnumber != "" && $tempcode != "") {
            $result = $membercards->fulfillMigration($cardnumber, $tempcode);    
            if ($result['TransCode'] == 0) {
                //log to audit trail
                $AID = $_SESSION['userinfo']['AID'];
                $sessionID = $_SESSION['userinfo']['SessionID'];
                $_Log->logEvent(AuditFunctions::CARD_MIGRATION_FULFILLMENT, "tempcode:".$tempcode.";ubcard:".$cardnumber.":Successful", array('ID'=>$AID, 'SessionID'=>$sessionID));
            }
        }
        else {
            $result =  array('TransCode' => 2, 
                             'TransMsg' => 'Transaction failed1.');
        }
        echo json_encode($result);
        break;
    default: 
        break;
}
?>
