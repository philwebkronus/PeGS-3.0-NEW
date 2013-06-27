<?php

/*
 * @author : owliber
 * @date : 2013-06-27
 */

class Vouchers extends CFormModel
{
    public function checkExpiredVouchers()
    {
        $query = "SELECT
                    VoucherCode
                  FROM vouchers
                  WHERE Status IN (1, 2)
                  AND NOW() > DateExpiry";
        
        $sql = Yii::app()->db->createCommand($query);
        return $sql->queryAll();
        
    }
    
    public function updateExpiredVouchers($voucherCode,$status)
    {
        $query = "UPDATE vouchers
                  SET Status =:status
                  WHERE VoucherCode =:voucherCode";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":status", $status);
        $sql->bindParam(":voucherCode", $voucherCode);
        $sql->execute();        
    }
    
}
?>
