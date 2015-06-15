<?php

/**
 * @author fdlsison
 * 
 * @date 6-23-2014
 */

class MemberSessionsModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new MemberSessionsModel();
        return self::$_instance;
    }
    
    //@date 6-23-2014
    //@purpose check for member sessions
    public function checkSession($MID) {
        $sql = 'SELECT COUNT(MemberSessionID)
                FROM membersessions
                WHERE MID = :MID';
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@purpose update existing session
    public function updateSession($sessionID, $MID, $remoteIP) {
        $null = null;
        
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = "UPDATE membersessions SET SessionID = :SessionID, RemoteIP = :RemoteIP, 
                    DateStarted = NOW(6), TransactionDate = NOW(6), DateEnded = :Null WHERE MID = :MID";
            $param = array(':SessionID' => $sessionID,':MID' => $MID, ':RemoteIP' => $remoteIP, ':Null' => $null);
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
    
    //@purpose insert new member session
    public function insertMemberSession($MID, $sessionID, $remoteIP) {
        $null = null;
        
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = "INSERT INTO membersessions(MID, SessionID, RemoteIP, DateStarted, DateEnded, TransactionDate)
                    VALUES(:MID, :SessionID, :RemoteIP, NOW(6), :Null, NOW(6))";
            $param = array(':MID' => $MID, ':SessionID' => $sessionID, ':RemoteIP' => $remoteIP, ':Null' => $null);
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
    
    //@date 6-24-2014
    //@purpose check if there is an existing session for this MID and SessionID
    public function checkIfSessionExist($MID, $sessionID) {
        $sql = 'SELECT COUNT(*) FROM membersessions WHERE MID = :MID AND SessionID = :SessionID';
        $param = array(':MID' => $MID, ':SessionID' => $sessionID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 07-07-2014
    //@purpose get existing member session
    public function getMemberSessions($mid) {
        $sql = 'SELECT *
                FROM membersessions
                WHERE MID = :mid';
        $param = array(':mid' => $mid);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 07-08-2014
    //@purpose update transaction date after every transaction
    public function updateTransactionDate($mid) {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'UPDATE membersessions
                    SET TransactionDate = NOW(6)
                    WHERE MID = :mid';
            $param = array(':mid' => $mid);
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
    
    //@date 07-24-2014
    //@purpose get all member sessions
    public function getAllMemberSessions() {
        $sql = 'SELECT * FROM membersessions';
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
    
    //@purpose delete expired member session
    public function deleteExpiredMemberSession($MID, $sessionID) {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'DELETE FROM membersessions WHERE MID = :MID AND SessionID = :sessionID';
            $param = array(':MID' => $MID, ':sessionID' => $sessionID);
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
    
    //@date 07-25-2014
    //@purpose get MID based on SessionID
    public function getMID($mpSessionID) {
        $sql = 'SELECT *
                FROM membersessions
                WHERE SessionID = :mpSessionID';
        $param = array(':mpSessionID' => $mpSessionID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
        
    }
    
    //@date 08-05-2014
    //@purpose delete session
    public function deleteSession($MID, $mpSessionID) {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'DELETE FROM membersessions WHERE MID = :MID AND SessionID = :sessionID';
            $param = array(':MID' => $MID, ':sessionID' => $mpSessionID);
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
    
    //@date 10-08-2014
    //@purpose get active member session
    public function getActiveSession($mpSessionID) {
        //$sql = 'SELECT COUNT(accs.AID) as Count,accs.AID, accs.SessionID, a.Username, accs.DateCreated FROM accountsessions accs, accounts a WHERE a.AID = accs.AID AND accs.SessionID=:SessionID AND a.Username=:Username AND a.Status=:Status';
        $sql = 'SELECT COUNT(ms.MemberSessionID) as Count, ms.MemberSessionID, ms.MID, ms.SessionID, ms.TransactionDate FROM membersessions ms, members m WHERE m.MID = ms.MID AND ms.SessionID = :mpSessionID AND m.Status = :status';
        $param = array(':mpSessionID'=>$mpSessionID,':Status'=>'1');
        
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }
    
    //@purpose validate MP session ID
    public function validateMPSessionID($mpSessionID) {
        //$sql = 'SELECT COUNT(acs.AID) as Count,a.UserName,acs.AID, acs.DateCreated FROM accounts a, accountsessions acs WHERE acs.SessionID=:TPSessionID AND acs.AID=a.AID LIMIT 1';
        $sql = 'SELECT COUNT(ms.MID) as Count, ms.MemberSessionID, ms.MID, ms.SessionID, ms.TransactionDate FROM membersessions ms, members m WHERE ms.SessionID =:MPSessionID AND ms.MID = m.MID LIMIT 1';
        $param = array(':MPSessionID'=>$mpSessionID);
        
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        //print_r($result);
        return $result;
    }
    
    //@date 10-23-2014
    public function addRemarks($MID, $alterStr) {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'UPDATE membersessions SET Remarks = :alterStr WHERE MID = :MID';
            $param = array(':alterStr' => $alterStr, ':MID' => $MID);
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
    
    //@date 10-24-2014
    //@purpose get active IMEI
    public function getAlterStr($MID) {
        $sql = 'SELECT *
                FROM membersessions
                WHERE MID = :MID';
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
}