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
    
    

}

?>
