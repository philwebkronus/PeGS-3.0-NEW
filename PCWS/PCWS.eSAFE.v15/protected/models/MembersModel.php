<?php

class MembersModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db2;
    }
    
    public function checkPinCode($pin)
    {  
        $sql = "SELECT MID FROM members WHERE PIN = :pin";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":pin", $pin);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getPIN($MID) {
        $sql = "SELECT sha1(PIN) AS PIN FROM members WHERE MID = :mid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $MID);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getPIN2($MID) {
        $sql = "SELECT PIN AS PIN FROM members WHERE MID = :mid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $MID);
        $result = $command->queryRow();
        
        return $result;
    }
    
    
    
    public function updatePIN($MID, $PIN) {
        $startTrans = $this->connection->beginTransaction();
        $PIN = sha1($PIN);
        try {
            $sql = "UPDATE members SET DatePINLastChange = NOW(6), PIN = :PIN WHERE MID = :MID";
            $param = array(':PIN' => $PIN, ':MID' => $MID);
            $command = $this->connection->createCommand($sql);
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
    
    public function updatePINReset($MID, $PIN) {
        $startTrans = $this->connection->beginTransaction();
        $PIN = sha1($PIN);
        try {
            $sql = "UPDATE members SET DatePINLastChange = null, PINLoginAttemps = 0, PIN = :PIN WHERE MID = :MID";
            $param = array(':PIN' => $PIN, ':MID' => $MID);
            $command = $this->connection->createCommand($sql);
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
    
    public function getIsWalletByMID($mid){
        $sql = "SELECT IsEwallet FROM members WHERE MID = :mid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getIsVIPByMID($mid){
        $sql='SELECT IsVIP FROM members WHERE MID = :mid';
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function checkPINLoginAttempts($mid){
        $sql='SELECT PINLoginAttemps FROM members WHERE MID = :mid';
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    
    public function incrementLoginAttempts($MID){
        $startTrans = $this->connection->beginTransaction();
        
        try {
            $sql = "UPDATE members SET PINLoginAttemps = PINLoginAttemps + 1 WHERE MID = :MID";
            $param = array(':MID' => $MID);
            $command = $this->connection->createCommand($sql);
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
            return 1000;
        }
    }
    
    public function resetPinLoginAttempts($MID){
        $startTrans = $this->connection->beginTransaction();
        try {
            $sql = "UPDATE members SET PINLoginAttemps = 0 WHERE MID = :MID";
            $param = array(':MID' => $MID);
            $command = $this->connection->createCommand($sql);
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
            return 1000;
        }
    }
    
    public function getIsEWallet($MID) {
        $sql = "SELECT IsEwallet FROM members WHERE MID = :mid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $MID);
        $result = $command->queryRow();
        
        return isset($result['IsEwallet'])?$result['IsEwallet']:false;
    }
    
    /**
     * Check if password is valid
     * @param type $password, $mid
     * @author Ralph Sison
     * @date 06-24-2015
     */
    public function checkIfPWIsValid($mid, $password) {
        $sql = "SELECT COUNT(*) AS isValid FROM members WHERE MID = :mid AND Password = :password";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $command->bindValue(":password", $password);
        $result = $command->queryRow();
        
        return $result;
    }
    
    //convert account to e-SAFE
    public function convertToESAFE($mid) {
        $startTrans = $this->connection->beginTransaction();
  
        try {
            $sql = "UPDATE members SET IsEwallet = 1, DateMigrated = NOW(6) WHERE MID = :mid";
            $param = array(':mid' => $mid);
            $command = $this->connection->createCommand($sql);
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
?>
