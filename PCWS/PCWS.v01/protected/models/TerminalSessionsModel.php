<?php

class TerminalSessionsModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    public function getSession($mid)
    {
        $sql = "SELECT TransactionSummaryID FROM terminalsessions WHERE MID = :mid AND ServiceID = 20";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function checkSessionID($sessionid)
    {
        $sql = "SELECT COUNT(TerminalID) AS SessionCount FROM terminalsessions WHERE TransactionSummaryID = :session AND ServiceID = 20";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":session", $sessionid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function deleteTerminalSession($sessionid)
    {
        $sql = "DELETE FROM terminalsessions WHERE TransactionSummaryID = :session AND ServiceID = 20";
        $param = array(':session'=>$sessionid);
        $command = $this->connection->createCommand($sql);
        return $command->execute($param);
    }
    
    
    public function checkSessionIDwithcard($cardnumber, $serviceid)
    {
        $sql = "SELECT TerminalID FROM terminalsessions WHERE LoyaltycardNumber = :cardnumber AND ServiceID = :serviceid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":cardnumber", $cardnumber);
        $command->bindValue(":serviceid", $serviceid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function isSessionActive($terminalID, $cardNumber){
        $sql = "SELECT TransactionSummaryID FROM terminalsessions WHERE TerminalID = :terminal_id AND LoyaltyCardNumber = :cardNumber AND DateEnded = 0";
        $command = $this->connection->createCommand($sql);
        $command->bindValues(array(':terminal_id'=>$terminalID, ':cardNumber'=>$cardNumber));
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function isTerminalHasActiveSession($terminalID){
        $sql = "SELECT COUNT(TerminalID) AS Cnt FROM terminalsessions WHERE TerminalID = :terminal_id AND DateEnded = 0";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':terminal_id', $terminalID);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function isCardHasActiveSession($cardNumber){
        $sql = "SELECT COUNT(TerminalID) AS Cnt FROM terminalsessions WHERE LoyaltyCardNumber = :cardNumber AND DateEnded = 0";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':cardNumber', $cardNumber);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function insertTerminalSessions($terminalID, $serviceID, $loyaltyCardNumber, $mid, $userMode, $ubServiceLogin, $ubServicePassword, $ubHashedServicePassword,$lastBalance){
        $startTrans = $this->connection->beginTransaction();
        
        try {
            $sql = "INSERT INTO terminalsessions (TerminalID, ServiceID, LoyaltyCardNumber, MID, UserMode, UBServiceLogin, UBServicePassword, UBHashedServicePassword, DateStarted, LastBalance, LastTransactionDate, HasLoyalty, Status) 
                VALUES (:terminalID,:serviceID, :loyaltyCardNumber, :mid, :userMode, :ubServiceLogin, :ubServicePassword, :ubHashedServicePassword, now(6), :lastBalance, now(6),  :hasLoyalty, 0)";
            $param = array(
                ':terminalID'=>$terminalID,
                ':serviceID'=>$serviceID,
                ':loyaltyCardNumber'=>$loyaltyCardNumber,
                ':mid'=>$mid,
                ':userMode'=>$userMode,
                ':ubServiceLogin'=>$ubServiceLogin,
                ':ubServicePassword'=>$ubServicePassword,
                ':ubHashedServicePassword'=>$ubHashedServicePassword,
                ':lastBalance'=>$lastBalance,
                ':hasLoyalty'=>0
                );
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
    
    public function getTransSummaryID($mid, $serviceid){
        $sql = "SELECT TransactionSummaryID FROM terminalsessions WHERE MID = :mid AND ServiceID = :serviceid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':mid', $mid);
        $command->bindValue(":serviceid", $serviceid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function updateTerminalState($terminalid, $serviceid, $cardnumber){
        $sql = "UPDATE terminalsessions SET Status = 1 WHERE TerminalID = :terminalID AND ServiceID = :serviceID AND LoyaltyCardNumber = :cardNumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':terminalID', $terminalid);
        $command->bindValue(":serviceID", $serviceid);
        $command->bindValue(":cardNumber", $cardnumber);
        try {
            $command->execute();
            return 1;
        } catch (PDOException $e) {
            Utilities::log($e->getMessage());
            return 0;
        }
    }
    
    public function checkSessionValidity($terminalid,$serviceid,$cardnumber){
        $sql = "SELECT COUNT(TerminalID) AS Cnt FROM terminalsessions WHERE TerminalID = :terminalID AND ServiceID = :serviceID AND LoyaltyCardNumber = :cardNumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':terminalID', $terminalid);
        $command->bindValue(":serviceID", $serviceid);
        $command->bindValue(":cardNumber", $cardnumber);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getCasinoDetailsByUBServiceLogin($ServiceUsername){
        $sql = "SELECT TerminalID, ServiceID, LoyaltyCardNumber,MID,UserMode,TransactionSummaryID FROM terminalsessions WHERE UBServiceLogin=:serviceUsername";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':serviceUsername', $ServiceUsername);
        $result = $command->queryRow();
        
        return $result;
    }
    
     public function deleteTerminalSessionByTerminalID($terminalID)
    {
        $sql = "DELETE FROM terminalsessions WHERE TerminalID = :terminalID";
        $param = array(':terminalID'=>$terminalID);
        $command = $this->connection->createCommand($sql);
        return $command->execute($param);
    }
    
    public function isTerminalHasActiveUBSession($terminalID){
        $sql = "SELECT COUNT(TerminalID) AS Cnt FROM terminalsessions WHERE TerminalID=:terminal_id";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':terminal_id', $terminalID);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function isCardHasActiveUBSession($cardNumber){
        $sql = "SELECT COUNT(tss.TerminalID) AS Cnt FROM terminalsessions tss INNER JOIN ref_services rs ON tss.ServiceID=rs.ServiceID WHERE tss.LoyaltyCardNumber=:cardNumber AND rs.UserMode=1";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':cardNumber', $cardNumber);
        $result = $command->queryRow();
        
        return $result;
    }
    
}
?>
