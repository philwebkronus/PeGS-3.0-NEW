<?php

/*
 * @author : owliber
 * @date : 2013-06-06
 */

class AuditTrail extends BaseEntity
{
    public function AuditTrail()
    {
        $this->ConnString = "membership";
        $this->TableName = "audittrail";
        $this->Identity = "AuditTrailID";
    }
    
    public function logEvent($auditfunctionid, $details, $accountypeid, $info)
    {
        App::LoadModuleClass("Membership", "AccountTypes");
        App::LoadModuleClass("Membership", "AuditFunctions");
        
        $_AccountTypes = new AccountTypes();
        $_AuditFunction = new AuditFunctions();
        
        if(is_array($info) && count($info) > 0)
        {
            $id = $info['ID'];
            $sessionid = $info['SessionID'];
        }
                
        $remoteIP = $_SERVER['REMOTE_ADDR'];
        
        $accounttype = $_AccountTypes->GetAccountTypeNameByID($accountypeid);
                
        if( $accounttype == AccountTypes::MEMBER )        
            $arrEntries['MID'] = $id;
        else
            $arrEntries['AID'] = $id;
        
        $arrEntries['AuditFunctionID'] = $auditfunctionid;
        $arrEntries['SessionID'] = $sessionid;
        $arrEntries['TransactionDetails'] = $_AuditFunction->getAuditFunctions($auditfunctionid) . ' - ' . $details;
        $arrEntries['TransactionDateTime'] = "now_usec()";
        $arrEntries['RemoteIP'] = $remoteIP;
        
        $this->Insert($arrEntries);
        
    }
    
    public function logAPI($auditfunctionid, $details, $sessionid="", $AID="")
    {
        App::LoadModuleClass("Membership", "AuditFunctions");
        $_AuditFunction = new AuditFunctions();
        
        $remoteIP = $_SERVER['REMOTE_ADDR'];
                
        $arrEntries['AuditFunctionID'] = $auditfunctionid;
        $arrEntries['AID'] = $AID;
        $arrEntries['SessionID'] = $sessionid;
        $arrEntries['TransactionDetails'] = $_AuditFunction->getAuditFunctions($auditfunctionid) . ' - ' . $details;
        $arrEntries['TransactionDateTime'] = "now_usec()";
        $arrEntries['RemoteIP'] = $remoteIP;
        
        $this->Insert($arrEntries);
        
    }
    
    
}
?>
