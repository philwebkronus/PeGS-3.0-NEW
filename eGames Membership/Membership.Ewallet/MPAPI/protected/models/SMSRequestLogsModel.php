<?php

/**
 * @author fdlsison
 * 
 * @date 07-11-2014
 */

class SMSRequestLogsModel {   
    const COUPON_REDEMPTION = 1;
    const ITEM_REDEMPTION = 2;
    const PLAYER_REGISTRATION = 3;
    
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db4;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new SMSRequestLogsModel();
        return self::$_instance;
    }
    
    //@date 07-11-2014
    //@purpose inserting SMS request logs to database
    public function insertSMSRequestLogs($methodID, $mobileNumber, $dateCreated, $couponSeries = '', $refNumber = '', $itemCount = '', $trackingID = '') {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'INSERT INTO smsrequestlogs(SMSMethodID, CouponNo, ReferenceNo, RedeemedNo, TrackingID, MobileNo, DateCreated)
                    VALUES(:methodID, :couponSeries, :refNumber, :itemCount, :trackingID, :mobileNumber, :dateCreated)';
            $param = array(':methodID' => $methodID, ':couponSeries' => $couponSeries, ':refNumber' => $refNumber, ':itemCount' => $itemCount,
                           ':trackingID' => $trackingID, ':mobileNumber' => $mobileNumber, ':dateCreated' => $dateCreated);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
            $lastInsertedID = $this->_connection->getLastInsertID();
            
            try {
                $startTrans->commit();
                return $lastInsertedID;
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