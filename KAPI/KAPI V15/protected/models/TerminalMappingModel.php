<?php
/**
 * Terminal Mapping Model
 * @author Mark Kenneth Esguerra [02-13-14]
 */
class TerminalMappingModel extends CFormModel
{
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    /**
     * Check if a terminal has mapped casino
     * @param int $terminalID ID of the terminal
     * @return array Count
     * @author Mark Kenneth Esguerra [02-13-14]
     */
    public function checkHasMappedCasino($terminalID)
    {
        $query = "SELECT COUNT(TerminalID) AS cnt FROM terminalmapping 
                  WHERE TerminalID = :terminalID";
        
        $command = $this->_connection->createCommand($query);
        $command->bindParam(":terminalID", $terminalID);
        $result = $command->queryRow();
        
        return $result;
    }
}

