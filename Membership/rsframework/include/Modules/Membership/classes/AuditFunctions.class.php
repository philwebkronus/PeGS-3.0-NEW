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
    CONST LOCKED_ACCOUNT = 11;
    CONST ADMIN_PLAYER_PROFILE = 12;
    CONST ADMIN_UPDATE_MEMBERSHIP_PROFILE = 13;
    CONST ADMIN_REPORTS_TRANSACTION_HISTORY = 14;
    CONST ADMIN_REPORTS_BANNED_PLAYERS = 15;
    CONST CASHIER_PLAYER_PROFILE = 16;
    CONST CASHIER_UPDATE_MEMBERSHIP_PROFILE = 17;
    CONST CASHIER_REDEMPTION = 18;
    CONST CS_PLAYER_PROFILE = 19;
    CONST CS_UPDATE_MEMBERSHIP_PROFILE = 20;
    CONST OPERATIONS_PLAYER_PROFILE = 21;
    CONST OPERATIONS_UPDATE_MEMBERSHIP_PROFILE = 22;
    CONST OPERATIONS_TRANSACTION_HISTORY = 23;
    CONST OPERATIONS_BANNED_PLAYERS = 24;
    CONST PAGCOR_BAN_PLAYERS = 25;
    CONST PAGCOR_UNBAN_PLAYERS = 26;
    CONST PAGCOR_BANNED_PLAYERS = 27;
    CONST PLAYER_REGISTRATION = 28;
    CONST PLAYER_EMAIL_VERIFICATION = 29;
    CONST PLAYER_ACCOUNT_VERIFICATION = 30;
    CONST PLAYER_UPDATE_MEMBERSHIP_PROFILE = 31;
    CONST MARKETING_REDEMPTION = 36;
    
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
