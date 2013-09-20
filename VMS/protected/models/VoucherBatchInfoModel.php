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
   
}

?>
