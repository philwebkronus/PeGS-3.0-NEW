<?php

/**
 * Description of EGMRequestLogs
 *
 * @author jshernandez
 */

class EGMSessionsModel extends CFormModel{
    
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db2;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 03/21/14
     * @param int $terminalID
     * @return int
     */
    public function isEGMSessionExistsByTerminalID($terminalID){
        $sql = "SELECT COUNT(EGMSessionID) ctrEGMSessionID FROM egmsessions WHERE TerminalID = :terminal_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":terminal_id", $terminalID);
        $result = $command->queryRow();
        return $result['ctrEGMSessionID'];
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 03/21/14
     * @param int $stackerBatchID
     * @return int
     */
    public function isEGMSessionExistsByBatchID($stackerBatchID){
        $sql = "SELECT COUNT(EGMSessionID) ctrEGMSessionID FROM egmsessions WHERE StackerBatchID = :stacker_batch_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":stacker_batch_id", $stackerBatchID);
        $result = $command->queryRow();
        return $result['ctrEGMSessionID'];
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 03/21/14
     * @param int $terminalID
     * @param int $stackerBatchID
     * @return int
     */
    public function isTerminalAndBatchIDMatched($terminalID, $stackerBatchID){
        $sql = "SELECT COUNT(EGMSessionID) ctrEGMSessionID FROM egmsessions WHERE TerminalID = :terminal_id AND StackerBatchID = :stacker_batch_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":terminal_id", $terminalID);
        $command->bindValue(":stacker_batch_id", $stackerBatchID);
        $result = $command->queryRow();
        return $result['ctrEGMSessionID'];
    }
    
    /**
     * @author Noel Antonio
     * @dateCreated 03-21-2014
     */
    public function isTerminalAndMIDMatched($terminalID, $mid){
        $sql = "SELECT COUNT(EGMSessionID) ctrEGMSessionID FROM egmsessions WHERE TerminalID = :terminal_id AND MID = :mid";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":terminal_id", $terminalID);
        $command->bindValue(":mid", $mid);
        $result = $command->queryRow();
        return $result['ctrEGMSessionID'];
    }
    
}

?>
