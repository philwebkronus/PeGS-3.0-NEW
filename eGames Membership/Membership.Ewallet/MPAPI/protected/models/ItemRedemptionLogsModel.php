<?php

/**
 * @author fdlsison
 * 
 * @date 07-14-2014
 */

class ItemRedemptionLogsModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db4;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new ItemRedemptionLogsModel();
        return self::$_instance;
    }
    
    //$purpose for inserting redemption logs on database
    public function insertItemLogs($redeemedDate, $MID, $rewardItemID, $itemCount, $siteID = '', $serviceID = '') {
            $sql = 'INSERT INTO itemredemptionlogs (MID, SiteID, ServiceID, RewardItemID, ItemCount, Source, DateCreated, CreatedByAID)
                    VALUES(:MID, :siteID, :serviceID, :rewardItemID, :itemCount, 1, :redeemedDate, :MID)';
            $param = array(':MID' => $MID, ':rewardItemID' => $rewardItemID, ':itemCount' => $itemCount, ':serviceID' => $serviceID,
                           ':redeemedDate' => $redeemedDate, ':siteID' => $siteID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
            $lastInsertedID = $this->_connection->getLastInsertID();
            
            
            if($lastInsertedID != '') {
                return $lastInsertedID;
            }
            else {
                return 0;
            }
    }
    
    //@date 07-15-2014
    //@purpose updating of redemption log status(0-pending, 1-successful, 2-failed)
    public function updateLogsStatus($itemRedemptionLogID, $status, $MID = '', $totalItemPoints = 0, $serialCode = '', $securityCode = '', $validFrom = null, $validTo = null ) {
        $startTrans = $this->_connection->beginTransaction();
        
        $updatedByAID = $MID;
        try {
            $sql = 'UPDATE itemredemptionlogs
                    SET Status = :status, DateUpdated = NOW(6), UpdatedByAID = :updatedByAID,
                        SerialCode = :serialCode, SecurityCode = :securityCode, ValidFrom = :validFrom,
                        ValidTo = :validTo, RedeemedPoints = :totalItemPoints
                    WHERE ItemRedemptionLogID = :itemRedemptionLogID';
            $param = array(':status' => $status, ':updatedByAID' => $updatedByAID, ':serialCode' => $serialCode,
                           ':securityCode' => $securityCode, ':validFrom' => $validFrom, ':validTo' => $validTo, ':totalItemPoints' => $totalItemPoints, ':itemRedemptionLogID' => $itemRedemptionLogID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
            
            try {
                $startTrans->commit();
                return 1;
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