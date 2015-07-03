<?php

/*
* Description: Wrapper for Banning and Unbanning player.
* @author: aqdepliyan
* DateCreated: 2013-06-18 06:47:08PM
*/

Class PlayerBanningWrapper extends BaseEntity{
    
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
        
        $MemberCardID = $entries['MemberCardID'];
        $MID = $entries['MID'];
        $Status = $entries['Status'] == "5" ? "1":"0";
        $Remarks = $entries['txtRemarks'];
        $DateCreated = 'now_usec()';
        $CreatedByAID = $entries['AID'];
        
        $_BanningHistory = new BanningHistory();
        
        $msg = $_BanningHistory->insertBanningHistory($entries, $MemberCardID, $MID, $Status, $Remarks, $DateCreated, $CreatedByAID);
    
        return $msg;
    }
}

?>
