<?php

/**
 * Model for PendingTerminalTransactions
 * 
 * @author elperez
 */
class PendingTerminalTransactionCountModel extends MI_Model {
    
    /**
     * Updates count attempts of pending terminal transaction
     * @param int $terminalID
     * @return bool 0|1
     */
    public function updatePendingTerminalCount($terminalID){
        $sql = "UPDATE pendingterminaltransactioncount SET 
                TransactionCount = TransactionCount + 1 WHERE TerminalID = :terminal_id";
        $param = array(':terminal_id'=>$terminalID);
        return $this->exec($sql,$param);
    }
}

?>
