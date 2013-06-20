<?php

/*
 * @author : owliber
 * @date : 2013-06-06
 */

class AuditFunctions extends BaseEntity
{
    CONST LOGIN = 1;
    CONST LOGOUT = 2;
    CONST UPDATE_PROFILE = 3;
    CONST VERIFY_EMAIL = 4;
    CONST PROCESS_POINTS = 5;
    CONST TRANSFER_POINTS = 6;
    CONST MIGRATE_OLD = 7;
    CONST MIGRATE_TEMP = 8;
    CONST BAN_PLAYER = 9;
    CONST UNBAN_PLAYER = 10;
    
    public function AuditFunctions()
    {
        $this->ConnString = "membership";
        $this->TableName = "ref_auditfunctions";
        $this->Identity = "AuditFunctionID";
    }
    
    public function getAuditFunctions($auditfunctionid)
    {
        $query = "SELECT * FROM " . $this->TableName .
                 " WHERE AuditFunctionID = $auditfunctionid";
        
        $result = parent::RunQuery($query);
        return $result[0]['AuditFunctionName'];
    }
}

?>
