<?php

class SiteBalanceModel extends CFormModel{
    
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
    
    public function updateBcf($newbal, $site_id, $transdtl) {
        $startTrans = $this->connection->beginTransaction();
        try {
            $sql = "UPDATE sitebalance SET Balance = :newbal, LastTransactionDate = NOW(6), LastTransactionDescription = :transdtl WHERE SiteID = :siteid";
            $param = array(':newbal'=>$newbal,':transdtl'=>$transdtl,':siteid'=>$site_id);
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
    
    public function getSiteBalance($siteid)
    {
        $sql = "SELECT Balance FROM sitebalance WHERE SiteID = :siteid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":siteid", $siteid);
        $result = $command->queryRow();
        
        return $result;
    }
    
}
?>
