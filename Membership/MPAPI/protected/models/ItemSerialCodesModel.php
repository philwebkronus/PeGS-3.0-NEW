<?php

/**
 * @author fdlsison
 * 
 * @date 07-14-2014
 */

class ItemSerialCodesModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db4;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new ItemSerialCodesModel();
        return self::$_instance;
    }
    
    //@purpose get available serial code count
    public function getAvailableSerialCodeCount($rewardItemID, $quantity) {
        $sql = 'SELECT ItemSerialCodeID
                FROM itemserialcodes
                WHERE Status = 1 AND RewardItemID = :rewardItemID
                LIMIT '.$quantity;
        $param = array(':rewardItemID' => $rewardItemID);
        $command = $this->_connection->createCommand($sql);
        $command->bindValues($param);
        $result = $command->queryAll();
        
        return $result;
    }
    
    //@date 07-15-2014
    //@purpose updating of pre-generated item serial code status item serial code id
    public function updateSerialCodeStatus($updatedByAID, $rewardItemID) {
        $unlockedMsg = '';
        $resultMsg = '';
        $returningArray = array();
        
        //Status Code 1-Error in locking, 2-Error in unlocking, 3-Error in updating, 4-serialcode unavailable
        
        $startTrans = $this->_connection->beginTransaction();
        
        $lockSQL = 'LOCK TABLES itemserialcodes WRITE, rewarditems READ;';
        $command = $this->_connection->createCommand($lockSQL);
        $isLocked = $command->execute();
        
        try {
            $itemSerialCodeArray = $this->getSerialCodeForRedemptionCopy($rewardItemID);
            $itemSerialCodeID = $itemSerialCodeArray['ItemSerialCodeID'];
            $serialCode = $itemSerialCodeArray['SerialCode'];
            
            if(isset($itemSerialCodeArray['ItemSerialCodeID']) && $itemSerialCodeArray['ItemSerialCodeID'] != '') {
                //proceed with the update query if the table is already locked.
                
                $updateSQL = 'UPDATE itemserialcodes
                              SET Status = 2, UpdatedByAID = :updatedByAID, DateUpdated = NOW(6)
                              WHERE Status = 1 AND ItemSerialCodeID = :itemSerialCodeID';
                $param = array(':updatedByAID' => $updatedByAID, ':itemSerialCodeID' => $itemSerialCodeID);
                $command = $this->_connection->createCommand($updateSQL);
                $command->bindValues($param);
                $result = $command->execute();
                
                
                
                if($result == FALSE || $result == 0) {
                    $resultMsg = 'Failed to update itemserialcodes table.';
                    
                    //Unlock the table if the update query failed.
                    $unlockSQL = "UNLOCK TABLES;";
                    $command = $this->_connection->createCommand($unlockSQL);
                    $result = $command->execute();
                    
                    try {
                        $result == 0 ? $returnvalue = 3: $returnvalue = 2;
                        $returningarray["IsSuccess"] = false;
                        $returningarray["StatusCode"] = $returnvalue;
                        return $returningarray;
                    } catch (PDOException $e) {
                        
                        $result == 0 ? $returnvalue = 3: $returnvalue = 2;
                        $unlockedMsg = 'Failed to unlock tables.';
                        $returningarray["IsSuccess"] = false;
                        $returningarray["StatusCode"] = $returnvalue;
                        $startTrans->rollback();
                        return $returningarray;
                    }
                    
                    
                    
                }
                
                //unlock table if update query succeeds
                $unlockSQL = 'UNLOCK TABLES';
                $command = $this->_connection->createCommand($unlockSQL);
                $isUnLocked = $command->execute();
                
                try {
                    //return results value if lock, update and unlock query all succeeds
                    $returningarray["IsSuccess"] = true;
                    
                    $returningarray["StatusCode"] = $itemSerialCodeArray['SerialCode'];
                    $startTrans->commit();
                    return $returningarray;
                } catch (PDOException $e) {
                    
                    $unlockedMsg = 'Failed to unlock tables.';
                    $returningarray["IsSuccess"] = $isUnLocked;
                    $returningarray["StatusCode"] = 2;
                    $startTrans->rollback();
                    return $returningarray;
                }
//                try {
//                    //unlock the table if the update query succeeded.
//                    $unlockSQL = 'UNLOCK TABLES';
//                    $command2 = $this->_connection->createCommand($unlockSQL);
//                    $result2 = $command2->execute();
//                    if($result2 == false || !$result2){
//                        $unlockedMsg = $resultMsg;
//                        $returningArray["IsSuccess"] = $result2;
//                        $returningArray["StatusCode"] = 2;
//                        return $returningArray;
//                    }
//
//                    //return results value if the lock, update and unlock query all succeed.
//                    $returningArray["IsSuccess"] = true;
//                    $returningArray["StatusCode"] = $itemSerialCodeID['SerialCode'];
//                    return $returningArray;
//                } catch (PDOException $e) {
//                    $startTrans->rollback();
//                    Utilities::log($e->getMessage());
//
//                    $resultMsg = "Updating of itemserialcodes table failed.";
//                    //Unlock table if the update query failed.
//                    $unlockSQL = 'UNLOCK TABLES';
//                    $command = $this->_connection->createCommand($unlockSQL);
//                    $isUnlocked = $command->execute();
//                    if($isUnlocked == false || !$isUnlocked) {
//                        $unlockedMsg = $resultMsg;
//                    }
//                    $result == 0 ? $returnValue = 3: $returnValue = 2;
//                    $returningArray["IsSuccess"] = false;
//                    $returningArray["StatusCode"] = $returnValue;
//                    return $returningArray;
//                    //return $resultMsg;
//                }
//                } catch (Exception $e) {
//                    $startTrans->rollback();
//                    Utilities::log($e->getMessage());
//                    return 0;
//                }
            }
            else {
                $unlockSQL = 'UNLOCK TABLES';
                $command = $this->_connection->createCommand($unlockSQL);
                $isUnLocked = $command->execute();
                
                try {
                   
                    $returningarray["IsSuccess"] = false;
                    $returningarray["StatusCode"] = 4;
                     $startTrans->rollback();
                    return $returningarray;
                } catch (PDOException $e) {
                    $unlockedMsg = $resultMsg;
                    $returningarray["IsSuccess"] = $isUnLocked;
                    $returningarray["StatusCode"] = 2;
                    return $returningarray;
                }
                
//                if($isUnlocked == false || !$isUnlocked) {
//                    $unlockedMsg = $resultMsg;
//                    $returningarray["IsSuccess"] = $isunlocked;
//                    $returningarray["StatusCode"] = 2;
//                    return $returningarray;
//                }
                
            }
//            $startTrans->commit();
//            return 1;
        }
        catch (PDOException $e) {
            
            $returningArray['IsSuccess'] = $isLocked;
            $returningArray['StatusCode'] = 1;
            $startTrans->rollback();
            return $returningArray;
            
        }
        
//        if($isLocked == false || !$isLocked) {
//            $returningArray['IsSuccess'] = $isLocked;
//            $returningArray['StatusCode'] = 1;
//            return $returningArray;
//        }
//        else {
//            $itemSerialCodeID = $this->getSerialCodeForRedemptionCopy($rewardItemID);
//            if(isset($itemSerialCodeID['ItemSerialCodeID']) && $itemSerialCodeID['ItemSerialCodeID'] != '') {
//                //proceed with the update query if the table is already locked.
//                try {
//                    $updateSQL = 'UPDATE itemserialcodes
//                                  SET Status = 2, UpdatedByAID = :updatedByAID, DateUpdated = NOW(6)
//                                  WHERE Status = 1 AND ItemSerialCodeID = :itemSerialCodeID';
//                    $param = array(':updatedByAID' => $updatedByAID, ':itemSerialCodeID' => $itemSerialCodeID);
//                    $command = $this->_connection->createCommand($updateSQL);
//                    $command->bindValues($param);
//                    $result = $command->execute();
//                    
//                    try {
//                        //unlock the table if the update query succeeded.
//                        $unlockSQL = 'UNLOCK TABLES';
//                        $command2 = $this->_connection->createCommand($unlockSQL);
//                        $result2 = $command2->execute();
//                        if($result2 == false || !$result2){
//                            $unlockedMsg = $resultMsg;
//                            $returningArray["IsSuccess"] = $result2;
//                            $returningArray["StatusCode"] = 2;
//                            return $returningArray;
//                        }
//                        
//                        //return results value if the lock, update and unlock query all succeed.
//                        $returningArray["IsSuccess"] = true;
//                        $returningArray["StatusCode"] = $itemSerialCodeID['SerialCode'];
//                        return $returningArray;
//                    } catch (PDOException $e) {
//                        $startTrans->rollback();
//                        Utilities::log($e->getMessage());
//                        
//                        $resultMsg = "Updating of itemserialcodes table failed.";
//                        //Unlock table if the update query failed.
//                        $unlockSQL = 'UNLOCK TABLES';
//                        $command = $this->_connection->createCommand($unlockSQL);
//                        $isUnlocked = $command->execute();
//                        if($isUnlocked == false || !$isUnlocked) {
//                            $unlockedMsg = $resultMsg;
//                        }
//                        $result == 0 ? $returnValue = 3: $returnValue = 2;
//                        $returningArray["IsSuccess"] = false;
//                        $returningArray["StatusCode"] = $returnValue;
//                        return $returningArray;
//                        //return $resultMsg;
//                    }
//                } catch (Exception $e) {
//                    $startTrans->rollback();
//                    Utilities::log($e->getMessage());
//                    return 0;
//                }
//            }  
        
        
    }
    
    //@purpose fetching of pre-generated item serial code from db using reward item ID
    public function getSerialCodeForRedemptionCopy($rewardItemID) {
     
        $sql = 'SELECT min(SerialCode) AS SerialCode, ItemSerialCodeID
                FROM itemserialcodes
                WHERE Status = 1 AND RewardItemID = :rewardItemID';
        $param = array(':rewardItemID' => $rewardItemID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    
            
}

