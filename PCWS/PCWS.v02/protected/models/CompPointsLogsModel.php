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
}
?>
