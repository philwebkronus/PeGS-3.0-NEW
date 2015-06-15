<?php

/*
 * @author fdlsison
 * @date : 2014-10-20
 */

class MobileIdentityModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new MobileIdentityModel();
        return self::$_instance;
    }
    
    //@purpose checking for IMEI
    public function validateAlterStr($alterStr, $MID) {
        $sql = "SELECT COUNT(*) as COUNT
                FROM mobileidentity 
                WHERE IMEI = :alterStr AND MID = :MID AND Status = :Status";
        $param = array(':alterStr' => $alterStr, ':MID' => $MID, ':Status' => 1);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 10-21-2014
    //@purpose get active IMEI
    public function getAlterStr($MID) {
        $sql = 'SELECT *
                FROM mobileidentity
                WHERE MID = :MID AND Status = :Status';
        $param = array(':MID' => $MID, ':Status' => 1);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 10-27-2014
    public function insertAlterStr($MID, $alterStr) {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'INSERT INTO mobileidentity(MID, IMEI, Status, DateCreated)
                    VALUES(:MID, :IMEI, :Status, NOW(6))';
            $param = array(':MID' => $MID, 
                           ':IMEI' => $alterStr,
                           ':Status' => 1);
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

?>