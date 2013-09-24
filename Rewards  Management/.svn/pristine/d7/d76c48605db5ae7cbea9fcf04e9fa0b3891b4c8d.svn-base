<?php

class RaffleCouponModel extends CFormModel
{
    public function getRaffleCoupons(){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT CouponNumber, RewardItemID FROM rafflecoupons WHERE Status = 1 ORDER BY CouponNumber ASC;";
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        
        $partners = array('-1'=>'- Please Select -');
        foreach($result as $row)
        {
            $partners[$row['RewardItemID']] = $row['CouponNumber'];
        }
        
        return $partners;
        
    }
}

?>
