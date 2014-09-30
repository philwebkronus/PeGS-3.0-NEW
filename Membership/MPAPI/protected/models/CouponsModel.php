<?php

/**
 * @author fdlsison
 * 
 * @date 09-17-2014
 */

class CouponsModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db6;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new CouponsModel();
        return self::$_instance;
    }
    
    public function getCoupon($couponBatchID) {
        $query = "SELECT *
                  FROM coupons c
                  INNER JOIN couponbatch cb ON c.CouponBatchID = cb.CouponBatchID 
                  WHERE c.CouponBatchID = :couponBatchID AND c.Status = 1 AND c.Option1 is null 
                  LIMIT 1";
        $param = array(':couponBatchID' => $couponBatchID);
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 09-18-2014
    public function updateCouponStatus($couponBatchID, $couponNumber, $MID) {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $query = "UPDATE coupons
                      SET Option1 = :MID
                      WHERE CouponBatchID = :couponBatchID AND CouponCode = :couponNumber AND Status = 1 AND Option1 is null ";
            $param = array(':couponBatchID' => $couponBatchID, ':couponNumber' => $couponNumber, ':MID' => $MID);
            $command = $this->_connection->createCommand($query);
            $command->bindValues($param);
            $command->execute();
            
            try {
                $startTrans->commit();
                return true;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
            
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
        
    }
    
    
//    public function getRaffleCouponSuffix() {
//        $sql = 'SELECT *
//                FROM couponbatches
//                WHERE Status = 1
//                LIMIT 1';
//        $command = $this->_connection->createCommand($sql);
//        $result = $command->queryRow();
//        
//        
//        return $result;
//    }
            
}

