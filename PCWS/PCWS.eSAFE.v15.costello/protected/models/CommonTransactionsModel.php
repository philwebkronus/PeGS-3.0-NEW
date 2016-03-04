<?php
/*
class CommonTransactionsModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
    public function insert($mid, $serviceid, $loyaltycardnumber, $terminalid, $usermode, $ubservicelogin, $ubservicepassword, $ubhashedservicepassword, $hasloyalty, $status, $siteid, $aid){
        $startTrans = $this->connection->beginTransaction();
        
        try {
            $sql = "INSERT INTO playertransactionsummary (MID, ServiceID, LoyaltyCardNumber, SiteID, TerminalID, DateStarted, CreatedByAID) VALUES (:mid, :serviceid, :loyaltycardnumber, :siteid, :terminalid, NOW(6), :aid)";
            $param = array(':mid' => $mid, ':serviceid' => $serviceid, ':loyaltycardnumber' => $loyaltycardnumber, ':siteid' => $siteid, ':terminalid' => $terminalid, ':aid' => $aid);
            $command = $this->connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
            
            try {
                $lastinserted = $this->connection->getLastInsertID();
                
                $sql2 = "INSERT INTO playersessions (MID, ServiceID, LoyaltyCardNumber, TerminalID, UserMode, UBServiceLogin, UBServicePassword, UBHashedServicePassword, DateStarted, LastTransactionDate, PlayerTransactionSummaryID, HasLoyalty, Status) 
                    VALUES (:mid, :serviceid, :loyaltycardnumber, :terminalid, :usermode, :ubservicelogin, :ubservicepassword, :ubhashedservicepassword, NOW(6), NOW(6), :playertransid, :hasloyalty, :status)";
                $param2 = array(':mid' => $mid, ':serviceid' => $serviceid, ':loyaltycardnumber' => $loyaltycardnumber, ':terminalid' => $terminalid, ':usermode' => $usermode, ':ubservicelogin' => $ubservicelogin, ':ubservicepassword' => $ubservicepassword, 
                    ':ubhashedservicepassword' => $ubhashedservicepassword, ':playertransid' => $lastinserted, ':hasloyalty' => $hasloyalty, ':status' => $status);
                $command2 = $this->connection->createCommand($sql2);
                $command2->bindValues($param2);
                $command2->execute();
                
                try {

                    $startTrans->commit();
                    return $lastinserted;
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }
                
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
    
    
    public function deleteSession($loyaltycardnumber, $playertransid, $terminalid, $aid){
        $startTrans = $this->connection->beginTransaction();
        
        try {
            $sql = "DELETE FROM playersessions WHERE LoyaltyCardNumber = :cardnumber AND PlayerTransactionSummaryID = :playertransid AND TerminalID = :tertminalid";
            $param = array(':loyaltycardnumber' => $loyaltycardnumber, ':playertransid' => $playertransid, ':tertminalid' => $terminalid);
            $command = $this->connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
            
            try {
                $lastinserted = $this->connection->getLastInsertID();
                
                $sql2 = "UPDATE PlayerTansactionSummary SET DateEnded = NOW(6) AND UpdatedByAID = :aid";
                $param2 = array(':aid' => $aid);
                $command2 = $this->connection->createCommand($sql2);
                $command2->bindValues($param2);
                $command2->execute();
                
                try {

                    $startTrans->commit();
                    return $lastinserted;
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }
                
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
