<?php

/**
 * VoucherBatchInfoModel
 * @datecreated 09/19/13
 * @author elperez
 */
class VoucherBatchInfoModel {
    
    /**
     * Get Voucher BatchID
     * @param int $voucherTypeID
     * @return int voucher type
     */
    public function getActiveBatchNo($voucherTypeID){
        $queryBatch = "SELECT VoucherBatchID FROM voucherbatchinfo WHERE Status = 1 AND VoucherTypeID = :vouchertype";
        $sqlBatch = Yii::app()->db->createCommand($queryBatch);
        $sqlBatch->bindValues(array(
                ":vouchertype"=>$voucherTypeID
            ));
        $resultBatch = $sqlBatch->queryRow();

        return $resultBatch['VoucherBatchID'];
    }
    
    /**
     * Gets active voucher batch info
     * @param int $voucherTypeID
     * @return obj batch info
     */
    public function getVoucherBatchInfo($voucherTypeID){
        $query = "SELECT ExpiryDate FROM voucherbatchinfo WHERE VoucherTypeID = :vouchertypeid 
                  AND Status = 1";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValues(array(
                ":vouchertypeid"=>$voucherTypeID
        ));
        
        return $sql->queryRow();
    }
   
    /**
     * Checks pending batch info
     * @param int $voucherTypeID
     * @return obj batch info count
     */
    public function checkpendingVouchers(){
        $query = "SELECT COUNT(VoucherBatchInfoID) AS Count FROM voucherbatchinfo WHERE Status = 0";
        $sql = Yii::app()->db->createCommand($query);
        
        $result = $sql->queryRow();
        
        foreach ($result as $value) {
            $vouchercount = $value['Count'];
        }
        
        return $vouchercount;
    }
    
    public function getMaxVoucherBatchID($vouchertypeid){
        $query = "SELECT MAX(VoucherBatchID) AS MAX FROM voucherbatchinfo WHERE VoucherTypeID = $vouchertypeid";
        $sql = Yii::app()->db->createCommand($query);
        
        $result = $sql->queryRow();
        
        foreach ($result as $value) {
            $vouchercount = $value['MAX'];
        }
        
        return $vouchercount;
    }
    
    
    public function getLastActiveVoucherBatchID($vouchertypeid){
        $query = "SELECT MAX(VoucherBatchID) AS MAX FROM voucherbatchinfo 
            WHERE VoucherTypeID = $vouchertypeid AND Status = 1";
        $sql = Yii::app()->db->createCommand($query);
        
        $result = $sql->queryRow();
        if(!empty($result)){
            foreach ($result as $value) {
            $vouchercount = $value['MAX'];
            }
        }
        else{
            $vouchercount = 0;
        }
        
        return $vouchercount;
    }
    
    
    public function insertVoucherBatchInfo($vouchertype, $amount, $vouchercount, $voucherbatchid){
        $query = "INSERT INTO voucherbatchinfo (VoucherTypeID, VoucherBatchID, 
            Amount, VoucherCount, DateCreated, Status) 
            VALUES ($vouchertype, $voucherbatchid, $amount, $vouchercount, NOW(), 0)";
        $sql = Yii::app()->db->createCommand($query);
        return $sql->execute();
    }
    
    
    public function setVoucherBatchUsed($voucherbatchid, $vouchertype){
        $query = "UPDATE voucherbatchinfo SET Status = 2
            WHERE VoucherBatchID = $voucherbatchid AND VoucherTypeID = $vouchertype";
        $sql = Yii::app()->db->createCommand($query);
        return $sql->execute();
    }
    
    public function activateVoucherBatchInfo($expirydate, $voucherbatchid, $vouchertype){
        $query = "UPDATE voucherbatchinfo SET ExpiryDate = '$expirydate', Status = 1 
            WHERE VoucherBatchID = $voucherbatchid AND VoucherTypeID = $vouchertype";
        $sql = Yii::app()->db->createCommand($query);
        return $sql->execute();
    }
    
    
    public function getVoucherBatchDetails($vouchertypeid, $voucherbatchid){
        $query = "SELECT Amount, VoucherCount FROM voucherbatchinfo 
            WHERE VoucherTypeID = $vouchertypeid AND Status = 1 AND VoucherBatchID = $voucherbatchid";
        $sql = Yii::app()->db->createCommand($query);
        
        $result = $sql->queryRow();
        
        return $result;
    }
}

?>
