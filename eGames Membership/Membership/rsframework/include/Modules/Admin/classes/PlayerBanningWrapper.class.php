<?php

/*
* Description: Wrapper for Banning and Unbanning player.
* @author: aqdepliyan
* DateCreated: 2013-06-18 06:47:08PM
*/

Class PlayerBanningWrapper {
    
    public function updatePlayerStatus($arrEntries){
        
        App::LoadModuleClass("Membership", "BanningHistory");
        App::LoadModuleClass("Membership", "Members");
        App::LoadModuleClass("Loyalty", "MemberCards");
        App::LoadModuleClass("Membership", "AuditTrail");
        App::LoadModuleClass("Membership", "AuditFunctions");
        
        parse_str($arrEntries, $entries);
        foreach ($entries as $key => $val)
        {
            $entries[$key] = urldecode($val);
        }
        
        $banningdata['MemberCardID'] = $entries['MemberCardID'];
        $banningdata['MID'] = $entries['MID'];
        $banningdata['Status'] = $entries['Status'] == "5" ? "1":"0";
        $banningdata['Remarks'] = $entries['txtRemarks'];
        $banningdata['DateCreated'] = 'now_usec()';
        $banningdata['CreatedByAID'] = $entries['AID'];
        $_BanningHistory = new BanningHistory();
        $_BanningHistory->StartTransaction();
        $_BanningHistory->Insert($banningdata);
        $CommonPDOConn = $_BanningHistory->getPDOConnection();
        
        $_Members = new Members();
        $_Members->setPDOConnection($CommonPDOConn);
        $_Members->updateMemberStatusUsingMID($entries['Status'], $entries['MID']);
        
        if($entries['Status'] == "5"){
            $status = 9;
        } else {
            $status = strpos($entries['CardNumber'], 'eGames') !== false ? 5:1;
        }
        
        $_MemberCards = new MemberCards();
        $_MemberCards->setPDOConnection($CommonPDOConn);
        $_MemberCards->updateMemberCardStatusUsingCardNumber($status, $entries['CardNumber']);
        if(!App::HasError()){
            $_BanningHistory->CommitTransaction();
            if($entries['Status'] == "5"){
                $message = "Change Player Status: Transaction Successful.";
                $_AuditTrail = new AuditTrail();
                $_AuditTrail->StartTransaction();
                $_AuditTrail->logEvent(AuditFunctions::BAN_PLAYER, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                if(!App::HasError()){
                    $_AuditTrail->CommitTransaction();
                    return $message;
                } else {
                    $message = "Failed to log event on database.";
                    $_AuditTrail->RollBackTransaction();
                    return $message;
                }
            } else {
                $message = "Change Player Status: Transaction Successful.";
                $_AuditTrail = new AuditTrail();
                $_AuditTrail->StartTransaction();
                $_AuditTrail->logEvent(AuditFunctions::UNBAN_PLAYER, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
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
            $_BanningHistory->RollBackTransaction();
            $message = "Change Player Status: Transaction Failed.";
            return $message;
        }
    }

            
}

?>
