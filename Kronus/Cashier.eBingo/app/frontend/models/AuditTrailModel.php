<?php
/**
 * Description of AuditTrailModel
 *
 * @author bryan
 */
class AuditTrailModel extends MI_Model {
    //log to audit trail
    public function logToAudit($zsessionID, $zaid, $ztransdetails, $zdate, $zipaddress, $zauditfunctionID) {
        $sql = 'Insert into audittrail 
            (SessionID, AID, TransDetails, TransDateTime, RemoteIP, AuditTrailFunctionID) 
            values 
            (:sessionid, :aid, :transdetails, :date, :ipaddress, :auditfunctionid)';
        $param = array(':aid' => $zaid, ':sessionid' => $zsessionID, ':transdetails' => $ztransdetails, ':date' => $zdate, ':ipaddress' => $zipaddress, ':auditfunctionid' => $zauditfunctionID);
        $this->exec($sql, $param);
    }     
}

?>
