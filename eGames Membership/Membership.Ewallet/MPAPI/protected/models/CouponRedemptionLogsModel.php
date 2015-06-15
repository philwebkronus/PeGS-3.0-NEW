<?php

/**
 * @author fdlsison
 * 
 * @date 07-01-2014
 */

class CouponRedemptionLogsModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db4;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new CouponRedemptionLogsModel();
        return self::$_instance;
    }
        
    //@purpose insert new record in coupon redemption logs
    public function insertCouponLogs($MID, $rewardItemID, $couponCount, $redeemedDate = '', $siteID = '', $serviceID = '') {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'INSERT INTO couponredemptionlogs(MID, RewardItemID, CouponCount, ServiceID, Source, DateCreated, CreatedByAID, SiteID)
                    VALUES(:MID, :RewardItemID, :CouponCount, :ServiceID, 1, :RedeemedDate, :MID, :SiteID)';
            $param = array(':MID' => $MID, ':RewardItemID' => $rewardItemID, ':CouponCount' => $couponCount, ':ServiceID' => $serviceID, ':RedeemedDate' => $redeemedDate, ':SiteID' => $siteID);
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
    
    //@date 07-03-2014
    //@purpose update coupon redemption details and status as (1-success, 2-failed)
    public function updateLogsStatus($couponRedemptionLogID, $status, $MID = '', $totalItemPoints = '', $serialCode = '', $securityCode = '', $validFrom = null, $validTo = null) {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $updatedByAID = $MID;
            $sql = 'UPDATE couponredemptionlogs
                    SET Status = :status, DateUpdated = NOW(6), UpdatedByAID = :updatedByAID,
                        SerialCode = :serialCode, SecurityCode = :securityCode, ValidFrom = :validFrom,
                        ValidTo = :validTo, RedeemedPoints = :totalItemPoints
                    WHERE CouponRedemptionLogID = :couponRedemptionLogID';
            $param = array(':status' => $status, ':updatedByAID' => $updatedByAID, ':serialCode' => $serialCode,
                           ':securityCode' => $securityCode, ':validFrom' => $validFrom,
                           ':validTo' => $validTo, ':totalItemPoints' => $totalItemPoints, ':couponRedemptionLogID' => $couponRedemptionLogID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $result = $command->execute();
            try {
                $startTrans->commit();
                return $MID;
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