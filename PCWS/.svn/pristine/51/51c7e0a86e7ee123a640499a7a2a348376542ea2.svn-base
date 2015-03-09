<?php

class AuditTrailModel extends CFormModel
{
    CONST GET_TERMINAL_STATE = 81;
    CONST UPDATE_TERMINAL_STATE = 82;
    CONST VALIDATE_MEMBERSHIP_CARD = 83;
    CONST GET_MEMBERSHIP_PROFILE = 84;
    CONST ASSIGN_PIN = 85;
    
    
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
    public function getSiteID($terminalid)
    {
        $sql = "SELECT SiteID FROM terminals WHERE TerminalID = :terminalid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":terminalid", $terminalid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getTerminalID($terminalname) {
        $prefix = Yii::app()->params['prefix'];
        $terminalcode = $prefix.$terminalname;
        $sql = "SELECT TerminalID FROM terminals WHERE TerminalCode = :terminalcode";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":terminalcode", $terminalcode);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function logEvent($auditfunctionID, $transdetails, $sessionID) {
        
        $startTrans = $this->connection->beginTransaction();
        date_default_timezone_set("Asia/Taipei");
        $date = date("Y-m-d H:i:s");
        
        $remoteip = $_SERVER['REMOTE_ADDR'];
        
        try {
            $sql = 'INSERT INTO audittrail(AuditTrailFunctionID, AID, SessionID, TransDetails, TransDateTime, DateCreated, RemoteIP)
                    VALUES(:AuditTrailFunctionID, null, :SessionID,  :TransDetails, NOW(6), :date, :RemoteIP)';
            $param = array(':SessionID' => $sessionID, ':TransDetails' => $transdetails, ':date' => $date, ':RemoteIP' => $remoteip, ':AuditTrailFunctionID' => $auditfunctionID);
            $command = $this->connection->createCommand($sql);
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
?>
