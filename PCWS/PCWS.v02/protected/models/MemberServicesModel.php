<?php

class MemberServicesModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db2;
    }
    
    public function getCasinoCredentials($mid)
    {
        $sql = "SELECT ServiceUsername, ServicePassword FROM memberservices WHERE MID = :mid AND ServiceID = 20";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    
    public function getCasinoCredentialsAbbott($mid)
    {
        $sql = "SELECT ServiceUsername, ServicePassword, HashedServicePassword FROM memberservices WHERE MID = :mid AND ServiceID = 19";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getCasinoCredentialsCostelloAbbott($mid)
    {
        $sql = "SELECT ServiceUsername, ServicePassword, HashedServicePassword FROM memberservices WHERE MID = :mid AND (ServiceID = 18 OR ServiceID=19)";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function UpdateBalances($currentbalance, $lasttransaction, $mid, $serviceid){
        $startTrans = $this->connection->beginTransaction();
        
        try {
            $sql = "UPDATE memberservices SET CurrentBalance = :curbalance, LastTransaction = :lasttransaction, CurrentBalanceLastUpdate = NOW(6) WHERE MID = :mid AND ServiceID = :serviceid";
            $param = array(':curbalance' => $currentbalance, ':lasttransaction' => $lasttransaction, ':mid' => $mid, ':serviceid' => $serviceid);
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
