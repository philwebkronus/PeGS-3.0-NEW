<?php

class TransactionSummaryModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
    public function getTotalReload($transsumid){
        $sql = "SELECT WalletReloads FROM transactionsummary WHERE TransactionsSummaryID = :transsumid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":transsumid", $transsumid);
        $result = $command->queryRow();
        
        return $result;
    }


    public function updateTransSummary($amount, $transsumid){
        $startTrans = $this->connection->beginTransaction();
        
        try {
            $sql = "UPDATE transactionsummary SET WalletReloads = :amount WHERE TransactionsSummaryID = :transsumid";
            $param = array(':amount' => $amount, ':transsumid' => $transsumid);
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
    
    public function getDateEnded($MID, $terminalID){
        $sql = "SELECT DateEnded FROM transactionsummary WHERE MID = :MID AND TerminalID=:terminalID ORDER BY TransactionsSummaryID DESC LIMIT 1";
        $command = $this->connection->createCommand($sql);
        $command->bindValues(array(":MID"=>$MID, ':terminalID'=>$terminalID));
        $result = $command->queryRow();
        
        return $result['DateEnded'];
    }

}
?>
