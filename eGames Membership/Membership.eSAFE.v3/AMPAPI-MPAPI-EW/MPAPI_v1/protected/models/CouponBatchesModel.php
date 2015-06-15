<?php

/**
 * @author fdlsison
 * 
 * @date 07-01-2014
 */

class CouponBatchesModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db4;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new CouponBatchesModel();
        return self::$_instance;
    }
    
    
    public function getRaffleCouponSuffix() {
        $sql = 'SELECT *
                FROM couponbatches
                WHERE Status = 1
                LIMIT 1';
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow();
        
        
        return $result;
    }
            
}

