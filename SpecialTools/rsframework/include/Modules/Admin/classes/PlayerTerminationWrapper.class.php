<?php

/*
* Description: Wrapper for Termination and Activation of player.
* @author: gvjagolino
* DateCreated: 2013-09-30 10:48:10AM
*/

Class PlayerTerminationWrapper {
    
    public function terminatePlayerStatus($arrEntries){
        
        App::LoadModuleClass("Membership", "BanningHistory");
        App::LoadModuleClass("Membership", "TempMembers");
        App::LoadModuleClass("Membership", "TempMemberInfo");
        App::LoadModuleClass("Membership", "Members");
        App::LoadModuleClass("Membership", "MemberInfo");
        App::LoadModuleClass("Loyalty", "MemberCards");
        App::LoadModuleClass("Loyalty", "Cards");
        App::LoadModuleClass("Membership", "AuditTrail");
        App::LoadModuleClass("Membership", "AuditFunctions");
        
        parse_str($arrEntries, $entries);
        foreach ($entries as $key => $val)
        {
            $entries[$key] = urldecode($val);
        }
        
        $_MemberInfo = new MemberInfo();
        $_TempMemberInfo = new TempMemberInfo();
        $_TempMembers = new TempMembers();
        
        $email = $_MemberInfo->getEmailByMID2($entries['MID']);
        foreach ($email as $value) {
           $email = $value['Email'];
        }
        
        $checkemail = $_TempMemberInfo->checkExistingEmail($email);
        $checkemail = $checkemail[0]['COUNT'];
        
        $_Members = new Members();
        $_Members->StartTransaction();
        
        $_Members->TerminateUsingMID($entries['Status'], $entries['MID'], $email.$entries['MID']);
        $CommonPDOConn = $_Members->getPDOConnection();
        if($entries['Status'] == "6"){
            $status = 2;
        } else {
            $status = strpos($entries['CardNumber'], 'eGames') !== false ? 6:1;
        }
        
        if($checkemail > 0){
            $_TempMemberInfo->setPDOConnection($CommonPDOConn);
            $_TempMemberInfo->deactivateAccount($email, $email.$entries['MID']);
            
            $_TempMembers->setPDOConnection($CommonPDOConn);
            $_TempMembers->deactivateAccount($email, $email.$entries['MID']);
        }
        
        $_MemberInfo->setPDOConnection($CommonPDOConn);
        $_MemberInfo->updateAppendUsingMID($status, $entries['MID'], $email.$entries['MID']);
        
        $_MemberCards = new MemberCards();
        $_MemberCards->setPDOConnection($CommonPDOConn);
        $_MemberCards->updateMemberCardStatusUsingCardNumber($status, $entries['CardNumber']);
        if(!App::HasError()){
            $_Members->CommitTransaction();
            if($entries['Status'] == "6"){
                $message = "Player Termination: Transaction Successful.";
                $_AuditTrail = new AuditTrail();
                $_AuditTrail->StartTransaction();
                $_AuditTrail->logEvent(AuditFunctions::TERMINATE, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                if(!App::HasError()){
                    $_AuditTrail->CommitTransaction();
                    return $message;
                } else {
                    $message = "Failed to log event on database.";
                    $_AuditTrail->RollBackTransaction();
                    return $message;
                }
            } else {
                $message = "Player Termination: Transaction Successful.";
                $_AuditTrail = new AuditTrail();
                $_AuditTrail->StartTransaction();
                $_AuditTrail->logEvent(AuditFunctions::ACTIVATE, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                if(!App::HasError()){
                    $_AuditTrail->CommitTransaction();
                    return $message;
                } else {
                    $message = "Failed to log event on database.";
                    $_AuditTrail->RollBackTransaction();
                    return $message;
                }
            }
            
        } else {
            $_Members->RollBackTransaction();
            $message = "Player Termination: Transaction Failed.";
            return $message;
        }
    }

            
}

?>
