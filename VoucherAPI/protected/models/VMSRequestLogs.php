<?php

/*
 * @author : owliber
 * @date : 2013-05-16
 */

class VMSRequestLogs extends OcActiveRecord
{    
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function tableName()
    {
            return 'vmsrequestlogs';
    }
    
    public function getFailedRequests()
    {
        $query = "SELECT
                    v.VMSRequestLogID,
                    v.VoucherCode,
                    v.AID,
                    v.DateCreated,
                    v.Status
                  FROM vmsrequestlogs v
                    WHERE v.Status = 3";
        
         $sql = Yii::app()->db2->createCommand($query);
         return $sql->queryAll();
         
    }
    
    public function processVouchers($voucherCode, $AID, $dateused)
    {
        $conn = Yii::app()->db;
        
        $trx = $conn->beginTransaction();
        
        $query = "UPDATE vouchers 
                    SET DateUsed =:dateused, 
                        ProcessedByAID =:AID, 
                        Status = 3
                    WHERE VoucherCode =:vouchercode
                        AND Status = 1";
        
        $sql = $conn->createCommand($query);
        $sql->bindValues(array(':AID'=>$AID, ':dateused'=>$dateused, ':vouchercode'=>$voucherCode));
        $sql->execute();
        
        try
        {
            $trx->commit();
        }
        catch(Exception $e)
        {
            $trx->rollback();
        }
    }
    
    public function logJob($status)
    {
        $conn = Yii::app()->db;
        $trx = $conn->beginTransaction();
        
        $query = "INSERT INTO cronlogs (RunDate, Status) VALUES (NOW(6), :status)";
        $sql = $conn->createCommand($query);
        $sql->bindValue(":status", $status);
        $sql->execute();
        
        try
        {
            $trx->commit();
        }
        catch(Exception $e)
        {
            $trx->rollback();
        }
    }
    
}
?>
