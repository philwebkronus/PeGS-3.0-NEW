<?php

/**
 * 
 * @purpose Description of MembersRecentPasswords model
 * @author fdlsison
 * @date 01-12-2015
 */

class MembersRecentPasswordsModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new MembersRecentPasswordsModel();
        return self::$_instance;
    }
    
    public function insertRecentPassword($MID, $password) {
        $startTrans = $this->_connection->beginTransaction();
        $password = md5($password);
        try {
            $sql = 'INSERT INTO membersrecentpasswords(MID, Password, DateCreated)
                    VALUES(:MID, :password, NOW(6))';
            $param = array(':MID' => $MID, ':password' => $password);
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
    
    public function updateRecentPassword($MID, $password, $date) {
        $startTrans = $this->_connection->beginTransaction();
        $password = md5($password);
        try {
            $sql = 'UPDATE membersrecentpasswords SET Password = :password, DateCreated = NOW(6)
                    WHERE MID = :MID AND DateCreated = :date';
            $param = array(':MID' => $MID, ':password' => $password, ':date' => $date);
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
    
    public function countRecentPassword($MID) {
        $sql = 'SELECT COUNT(Password) AS countrecentpassword
                FROM membersrecentpasswords
                WHERE MID = :MID ORDER BY DateCreated DESC LIMIT 5';
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':MID' => $MID));
        $result = $command->queryRow();
        
        return $result; 
    }
    
    public function isDuplicate($MID, $password) {
        $sql = 'SELECT COUNT(Password) AS countpassword
                FROM membersrecentpasswords
                WHERE MID = :MID AND Password = :password';
        
        $param = array(':MID' => $MID, ':password' => $password);
        $command = $this->_connection->createCommand($sql);
        $command->bindValues($param);
        $result = $command->queryRow();
        
        return $result; 
    }
    
    public function getOldestDate($MID) {
        $sql = 'SELECT MIN(DateCreated) AS DateCreated
                FROM membersrecentpasswords
                WHERE MID = :MID';
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':MID' => $MID));
        $result = $command->queryRow();
        
        return $result; 
    }
    
    
   
}