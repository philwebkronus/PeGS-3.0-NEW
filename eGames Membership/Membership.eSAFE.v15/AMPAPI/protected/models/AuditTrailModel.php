<?php

/**
 * Description of AuditTrailModel
 *
 * @author jdlachica
 * @date 07/24/2014
 */
class AuditTrailModel
{
    CONST LOGIN = 1;
    CONST FORGOT_PASSWORD = 2;
    CONST REGISTER_MEMBER = 3;
    CONST UPDATE_PROFILE = 4;
    CONST GET_PROFILE = 5;
    CONST CHECK_POINTS = 6;
    CONST LIST_ITEMS = 7;
    CONST REDEEM_ITEMS = 8;
    CONST AUTHENTICATE_SESSION = 9;
    CONST GET_ACTIVE_SESSION = 10;
    CONST LOGOUT = 16;
    CONST CREATE_MOBILE_INFO = 21;
    CONST CHANGE_PASSWORD = 22;
    CONST GET_BALANCE = 23;
    CONST LIST_PROMOS = 24;
    CONST LIST_LOCATIONS = 25;
    
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db5;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new AuditTrailModel();
        return self::$_instance;
    }
    
    public function logEvent($auditfunctionID, $transdetails, $info) {
        $startTrans = $this->_connection->beginTransaction();
        $aid = 0;
        $sessionID = ' ';
        
        $remoteip = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        if (is_array($info) && count($info) > 0)
        {
            $aid= $info['AID']; $sessionID = $info['SessionID'];
        }
        try {
            $sql = 'INSERT INTO audittrail(SessionID, AID, TransDetails, TransDate, DateCreated, RemoteIP, AuditTrailFunctionID)
                    VALUES(:SessionID, :AID, :TransDetails, NOW(6), NOW(6),:RemoteIP, :AuditTrailFunctionID)';
            $param = array(':SessionID' => $sessionID, ':AID' => $aid, ':TransDetails' => $transdetails, ':RemoteIP' => $remoteip, ':AuditTrailFunctionID' => $auditfunctionID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);$command->execute();
        
            try {
                $startTrans->commit();return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();Utilities::log($e->getMessage());return 0;
            }
        
        } catch (Exception $e) {
            $startTrans->rollback();Utilities::log($e->getMessage());return 0;
        }
     }
}
?>