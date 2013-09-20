<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CouponBatchModel
 *
 * @author elperez
 */
class CouponBatchModel {
   
    public function getActiveCouponBatch($couponBatchTable, $voucherCode){
        
        $query = "SELECT Status, Amount, DateCreated, LoyaltyCreditable FROM $couponBatchTable WHERE CouponCode = :couponcode";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValues(array(
                ":couponcode"=>$voucherCode
            ));
        return $sql->queryRow();
    }
}

?>
