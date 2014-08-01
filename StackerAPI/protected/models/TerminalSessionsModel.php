<?php
/**
 * Terminal Session Model 
 * @author Mark Kenneth Esguerra
 * @date July 09, 2014
 */
class TerminalSessionsModel extends CFormModel
{
    private $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db2;
    }
    /**
     * Check if has Active Terminal Session
     * @param type $terminalID
     * @param type $MID
     * @date July 9, 2014
     */
    public function checkIfHasActiveSession($terminalID, $MID)
    {
        $sql = "SELECT COUNT(*) as Count from terminalsessions 
                WHERE TerminalID = :terminalID AND MID = :mid";
        $command = $this->connection->createCommand($sql); 
        $command->bindValue(":terminalID", $terminalID);
        $command->bindValue(":mid", $MID);
        $result = $command->queryRow();
        
        return $result['Count'];
    }
}
?>
