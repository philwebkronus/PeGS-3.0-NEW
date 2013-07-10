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
    
    public function logEvent($auditfunctionid, $details, $info)
    {
        App::LoadModuleClass("Membership", "AuditFunctions");
        
        $_AuditFunction = new AuditFunctions();
        
        if(is_array($info) && count($info) > 0)
        {
            $id = $info['ID'];
            $sessionid = $info['SessionID'];
        }
                
        $remoteIP = $_SERVER['REMOTE_ADDR'];        
        
        $arrEntries['ID'] = $id;        
        $arrEntries['AuditFunctionID'] = $auditfunctionid;
        $arrEntries['SessionID'] = $sessionid;
        $arrEntries['TransactionDetails'] = $_AuditFunction->getAuditFunctions($auditfunctionid) . ':' . $details;
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
        $arrEntries['ID'] = $AID;
        $arrEntries['SessionID'] = $sessionid;
        $arrEntries['TransactionDetails'] = $_AuditFunction->getAuditFunctions($auditfunctionid) . ':' . $details;
        $arrEntries['TransactionDateTime'] = "now_usec()";
        $arrEntries['RemoteIP'] = $remoteIP;
        
        $this->Insert($arrEntries);
        
    }
    /**
     * @author Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
     * Date Created: July 8, 2013
     * @param date $transactionDate Date for filter
     * @param array $array Array of AIDs
     */
    public function getTotalLogs ($arrAID, $fromTransactionDate)
    {
        $toTransactionDate =  $vdateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($fromTransactionDate)));
        $query = "SELECT COUNT(AuditTrailID) AS count FROM $this->TableName
                  WHERE ID IN ("."'".implode("','",$arrAID)."'".") AND 
                  TransactionDateTime >= '$fromTransactionDate' AND TransactionDateTime < '$toTransactionDate'";
        return parent::RunQuery($query);
    }
    /**
     * @author Mark Kenneth Esguerra
     * Date Created: July 8, 2013
     */
    public function LoadAuditLogs($start, $limit, $sidx, $sord, $arrAID, $fromTransactionDate)
    {
        $toTransactionDate =  $vdateto = date ( 'Y-m-d' , strtotime ('+1 day' , strtotime($fromTransactionDate)));
        $query = "SELECT * FROM $this->TableName
                  WHERE ID IN ("."'".implode("','",$arrAID)."'".") AND 
                  TransactionDateTime >= '$fromTransactionDate' AND TransactionDateTime < '$toTransactionDate'"."
                  ORDER BY $sidx $sord
                  LIMIT $start, $limit";
        return parent::RunQuery($query);
    }
}
?>
