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
        
        if(!empty($email)){
        foreach ($email as $value) {
           $email = $value['Email'];
        }
        
        $checkemail = $_TempMemberInfo->checkExistingEmail($email);
        $checkemailcount = $checkemail[0]['COUNT'];
        
        $_Members = new Members();
        
        $message = $_Members->TerminateAccount($entries['Status'], $entries['MID'], $email.$entries['MID'], 
                $email, $entries['CardNumber'], $checkemailcount);
        if(!$message){
            $message = $message;
        }
        else{
            $message = 'Player Termination: Transaction Successful.';
        }
        
         return $message;
        
        }
        else{
            $tempinfo = $_TempMemberInfo->getMembersByMID($entries['MID']);
            
            if(!empty($tempinfo)){
                foreach ($tempinfo as $value) {
                    $email = $value['Email'];
                 }
        
                $_TempMembers->TerminateTempAccount($entries['MID'], $email.$entries['MID']);
                
                $message = 'Player Termination: Transaction Successful.';
            }
            else{
                $message = 'Player Termination: Transaction Failed.';
            }
            
            
            return $message;
        }
        
    }

            
}

?>
