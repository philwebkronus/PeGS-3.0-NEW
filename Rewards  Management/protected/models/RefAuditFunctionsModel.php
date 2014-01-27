<?php
/**
 * Audit function Model
 * @author Mark Kenneth Esguerra
 * @date November 25, 2013
 * @copyright (c) 2013, Philweb Corporation
 */
class RefAuditFunctionsModel extends CFormModel
{
    CONST LOGIN = 1;
    CONST LOGOUT = 2;
    CONST CHANGE_PASSWORD = 3;
    CONST LOGIN_INVALID_ATTEMPTS = 4;
    CONST EXPORT_TO_PDF = 5;
    CONST EXPORT_TO_EXCEL = 6;
    CONST FORGOT_USERNAME_REQUEST = 7;
    CONST FORGOT_PASSWORD_REQUEST = 8;
    CONST CHANGE_PASSWORD_REQUEST = 9;
    CONST PASSWORD_EXPIRED = 10;
    
    CONST MARKETING_ADD_PARTNER = 11;
    CONST MARKETING_EDIT_PARTNER_DETAILS = 12;
    CONST MARKETING_UPDATE_PARTNER_STATUS = 13;
    CONST MARKETING_RPT_REWARDS_REDEMPTION = 14;
    CONST MARKETING_RPT_UNIQUE_MEMBER_PARTICIPATION = 15;
    CONST MARKETING_RPT_REWARDS_POINTS_USAGE = 16;
    CONST MARKETING_ADD_REWARDS = 17;
    CONST MARKETING_EDIT_REWARDS_DETAILS = 18;
    CONST MARKETING_ADD_RAFFLE = 19;
    CONST MARKETING_EDIT_RAFFLE_DETAILS = 20;
    CONST MARKETING_DELETE_REWARDS = 21;
    CONST MARKETING_DELETE_RAFFLE = 22;
    CONST MARKETING_REPLENISH_REWARD_INVENTORY = 23;
    CONST MARKETING_VERIFY_REWARDS = 24;
    CONST MARKETING_VERIFY_RAFFLE = 25;
    CONST MARKETING_RECORD_REWARDS = 26;
    
    CONST CS_VERIFY_REWARDS = 27;
    CONST CS_VERIFY_RAFFLE = 28;
    CONST CS_RECORD_REWARDS = 29;
    
    CONST PARTNER_VERIFY_REWARDS = 30;
    CONST PARTNER_VERIFY_RAFFLE = 31;
    CONST PARTNER_RECORD_REWARDS = 32;
    
    CONST AS_VERIFY_REWARDS = 33;
    CONST AS_VERIFY_RAFFLE = 34;
    CONST AS_RECORD_REWARDS = 35;
    CONST AS_RPT_REWARDS_REDEMPTION = 36;
    CONST AS_RPT_UNIQUE_MEMBER_PARTICIPATION = 37;
    CONST AS_RPT_REWARDS_POINTS_USAGE = 38;
    
    CONST MARKETING_VERIFY_MYSTERY_REWARDS = 39; 
    CONST MARKETING_ADD_MYSTERY_REWARDS = 40;
    CONST MARKETING_EDIT_MYSTERY_REWARDS_DETAILS = 41;
    CONST MARKETING_DELETE_MYSTERY_REWARDS = 42;
    
    /**
     * Get Audit function name
     * @param int $auditfunctionID AuditfunctionID
     * @return string Function name
     * @author Mark Kenneth Esguerra
     * @date November 25, 2013
     */
    public function getAuditFunctionName($auditfunctionID)
    {
       $connection = Yii::app()->db;
       
       $query = "SELECT AuditFunctionName FROM ref_auditfunctions 
                 WHERE AuditTrailFunctionID = :auditfunctionID";
       $command = $connection->createCommand($query);
       $command->bindParam(":auditfunctionID", $auditfunctionID);
       $result = $command->queryRow();
       
       return $result;
    }
}
?>
