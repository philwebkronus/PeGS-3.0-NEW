<?php

class EGMSessionModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    public function checkIfCasinoExist($terminalID, $casinoID)
    {
        
        
        $sql = "SELECT Count(EGMSessionID) as Count FROM egmsessions 
                WHERE TerminalID = :terminalID AND ServiceID = :serviceID";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":terminalID", $terminalID);
        $command->bindValue(":serviceID", $casinoID);
        $result = $command->queryRow();
        
        return $result;
    }
}
?>
