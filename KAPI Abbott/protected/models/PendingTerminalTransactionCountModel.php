<?php

/**
 * Model for PendingTerminalTransactions
 * 
 * @author elperez
 */
class PendingTerminalTransactionCountModel extends CFormModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new PendingTerminalTransactionCountModel();
        return self::$_instance;
    }
    /**
     * Updates count attempts of pending terminal transaction
     * @param int $terminalID
     * @return bool 0|1
     */
    public function updatePendingTerminalCount($terminalID){
        $sql = "UPDATE pendingterminaltransactioncount SET 
                TransactionCount = TransactionCount + 1 WHERE TerminalID = :terminal_id";
        $param = array(':terminal_id'=>$terminalID);
        $command = $this->_connection->createCommand($sql);
        return $command->execute($param);
    }
}

?>
