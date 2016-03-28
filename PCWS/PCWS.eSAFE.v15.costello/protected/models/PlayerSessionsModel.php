<?php
/*
class PlayerSessionsModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
    public function getTerminalState($cardnumber, $terminalid, $sessionid) {
        $sql = "SELECT MID, Status FROM playersessions WHERE LoyaltyCardNumber = :cardnumber AND TerminalID = :terminalid AND PlayerTransactionSummaryID = :sessionid";
        $param = array(':cardnumber' => $cardnumber, ':terminalid' => $terminalid, ':sessionid' => $sessionid);
        $command = $this->connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
        
    }
    
    public function updateTerminalState($cardnumber, $terminalid, $sessionid, $state) {
        $startTrans = $this->connection->beginTransaction();
        
        try {
            $sql = "UPDATE playersessions SET Status = :state WHERE LoyaltyCardNumber = :cardnumber AND TerminalID = :terminalid AND PlayerTransactionSummaryID = :sessionid";
            $param = array(':cardnumber' => $cardnumber, ':terminalid' => $terminalid, ':sessionid' => $sessionid, ':state' => $state);
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
    
    public function hasExistingSession($cardnumber) {
        $sql = "SELECT COUNT(MID) AS SessionCount FROM playersessions WHERE LoyaltyCardNumber = :cardnumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":cardnumber", $cardnumber);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function insert($mid, $serviceid, $loyaltycardnumber, $terminalid, $usermode, $ubservicelogin, $ubservicepassword, $ubhashedservicepassword, $playertransid, $hasloyalty, $status) {
        $startTrans = $this->connection->beginTransaction();
        
        try {
            $sql = "INSERT INTO playersessions (MID, ServiceID, LoyaltyCardNumber, TerminalID, UserMode, UBServiceLogin, UBServicePassword, UBHashedServicePassword, DateStarted, LastTransactionDate, PlayerTranactionSummaryID, HasLoyalty, Status) 
                VALUES (:mid, :serviceid, :loyaltycardnumber, :terminalid, :usermode, :ubservicelogin, :ubservicepassword, :ubhashedservicepassword, NOW(6), NOW(6), :playertransid, :hasloyalty, :status)";
            $param = array(':mid' => $mid, ':serviceid' => $serviceid, ':loyaltycardnumber' => $loyaltycardnumber, ':terminalid' => $terminalid, ':usermode' => $usermode, ':ubservicelogin' => $ubservicelogin, ':ubservicepassword' => $ubservicepassword, 
                ':ubhashedservicepassword' => $ubhashedservicepassword, ':playertransid' => $playertransid, ':hasloyalty' => $hasloyalty, ':status' => $status);
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
    
}*/
?>
