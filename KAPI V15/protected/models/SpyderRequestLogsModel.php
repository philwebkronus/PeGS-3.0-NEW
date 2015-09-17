<?php

/**
 * Log every call of spyder API
 *
 * @author elperez
 * @datecreated 04/26/13
 * 
 */
class SpyderRequestLogsModel extends CFormModel{
    
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new SpyderRequestLogsModel();
        return self::$_instance;
    }
    
    /**
     * insert records as pending
     * @param str $terminalCode
     * @param int $commandType
     * @return bool success | failed
     */
    public function insert($terminalCode, $commandType){        
        
            $sql = 'INSERT INTO spyderrequestlogs (TerminalCode, CommandType, DateCreated, 
                    Status) VALUES (:terminal_code, :command_type, NOW(6), 0)';
            $smt = $this->_connection->createCommand($sql);
            $param = array(
                ':terminal_code'=> $terminalCode,
                ':command_type'=>$commandType);
            
            $smt->execute($param);
            $transaction_id = $this->_connection->getLastInsertID();
            return $transaction_id;
                
    }
    
    /**
     * update spyder record as success | failed
     * @param int $status
     * @param int $spyderReqID
     * @return bool success | failed
     */
    public function update($status, $spyderReqID){
        $sql = "UPDATE spyderrequestlogs SET Status = :status, DateUpdated = NOW(6) 
                WHERE SpyderRequestLogID = :spyder_req_id";
        $smt = $this->_connection->createCommand($sql);
        $param = array(
            ':status'=> $status,
            ':spyder_req_id'=>$spyderReqID);
        $result = $smt->execute($param);
    
        return $result;
    }
    
}

?>
