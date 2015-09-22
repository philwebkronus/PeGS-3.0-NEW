<?php

class CompPointsLogsModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
    public function logEvent($mid, $cardNumber, $terminalID, $siteID, $serviceID, $amount, $transType) {
        $sql = "INSERT INTO comppointslogs(MID, LoyaltyCardNumber, TerminalID, SiteID, ServiceID, Amount, TransactionDate, TransactionType)
                VALUES(:mid, :cardNumber, :terminalID, :siteID, :serviceID, :amount, NOW(6), :transType)";
        $param = array(':mid'=>$mid, ':cardNumber'=>$cardNumber,':terminalID'=>$terminalID,':siteID'=>$siteID,':serviceID'=>$serviceID,  ':amount'=>$amount, ':transType'=>$transType);
        $command = $this->connection->createCommand($sql);
        return $command->execute($param);
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
    
   public function checkUserMode($serviceid)
    {
        $sql = "SELECT UserMode FROM ref_services WHERE ServiceID = :serviceid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":serviceid", $serviceid);
        $result = $command->queryRow();
        
        foreach($result as $row){
            $mode = $row['UserMode']; 
        }
        return $mode;
    }

    public function insert($mid, $card_number, $terminal_id, $site_id, $service_id, $amount, $trans_date, $trans_type) {
        $startTrans = $this->connection->beginTransaction();
        try {
            $sql = "INSERT INTO comppointslogs (MID,
                    LoyaltyCardNumber, TerminalID, SiteID, ServiceID, Amount, TransactionDate,
                    TransactionType) VALUES (:mid,
                    :card_number, :terminal_id, :site_id,
                    :service_id, :amount, :trans_date, :trans_type)";

            $param = array(
                ':mid' => $mid,
                ':card_number' => $card_number,
                ':terminal_id' => $terminal_id,
                ':site_id' => $site_id,
                ':service_id' => $service_id,
                ':amount' => $amount,
                ':trans_date' => $trans_date,
                ':trans_type' => $trans_type);

            $command = $this->connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            $test = $this->connection->getLastInsertID();
            try {
                $startTrans->commit();
                return $test;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            $startTrans->rollback();
            return false;
        }
    }

}

?>
