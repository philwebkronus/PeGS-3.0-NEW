<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AuditTrailModel
 *
 * @author fdlsison
 */
class AuditTrailModel
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
    CONST PLAYER_REDEMPTION = 37;
    CONST MARKETING_ADD_REWARD_ITEM = 38;
    CONST MARKETING_UPDATE_REWARD_ITEM = 42;
    CONST MARKETING_ADD_PROMO = 39;
    CONST MARKETING_UPDATE_PROMO = 40;
    CONST MARKETING_CHANGE_PROMO_STATUS = 41;
    CONST MARKETING_RED_CARD_TRANSFERRING = 43;
    CONST TERMINATE = 44;
    CONST ACTIVATE = 45;
    CONST MANUAL_REDEMPTION_FULFILLMENT = 46;
    CONST PLAYER_ITEM_REDEMPTION = 47;
    CONST CASHIER_ITEM_REDEMPTION = 48;
    CONST PLAYER_BLACKLISTING = 49;
    CONST UPDATE_BLACKLISTED_PLAYER = 50;
    CONST REMOVE_BLACKLISTED_PLAYER = 51;
    CONST PLAYER_CLASSIFICATION_ASSIGNMENT = 52;
    CONST MANUAL_CASINO_UB_ASSIGNMENT = 53;
    CONST CHANGE_PLAYER_PASSWORD = 54;
    CONST API_LOGIN = 55;
    CONST API_LOGOUT = 56;
    CONST API_FORGOT_PASSWORD = 57;
    CONST API_REGISTER_MEMBER = 58;
    CONST API_UPDATE_PROFILE = 59;
    CONST API_GET_PROFILE = 60;
    CONST API_CHECK_POINTS = 61;
    CONST API_LIST_ITEMS = 62;
    CONST API_REDEEM_ITEMS = 63;
    CONST API_REGISTER_MEMBER_BT = 67;
    CONST API_CHANGE_PASSWORD = 69;
    CONST API_GET_BALANCE = 72;
    
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new AuditTrailModel();
        return self::$_instance;
    }
    
    
    public function logEvent($auditfunctionID, $transdetails, $info) {
        
        
        
        $startTrans = $this->_connection->beginTransaction();
        
        $sessionID = '';
        $aid = 0;
        
        $remoteip = $_SERVER['REMOTE_ADDR'];
        if (is_array($info) && count($info) > 0)
        {
            $aid        = $info['MID'];
            $sessionID  = $info['SessionID'];
        }
        
        
        
        try {
            $sql = 'INSERT INTO audittrail(AuditFunctionID, ID, SessionID, TransactionDetails, TransactionDateTime, RemoteIP)
                    VALUES(:AuditTrailFunctionID, :AID, :SessionID,  :TransDetails, NOW(6), :RemoteIP)';
            $param = array(':SessionID' => $sessionID, ':AID' => $aid, ':TransDetails' => $transdetails, ':RemoteIP' => $remoteip, ':AuditTrailFunctionID' => $auditfunctionID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
        
            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
     }
}