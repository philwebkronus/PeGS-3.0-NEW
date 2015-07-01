<?php

class TerminalsModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
    public function getSiteID($terminalid)
    {
        $sql = "SELECT SiteID FROM terminals WHERE TerminalID = :terminalid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":terminalid", $terminalid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function getTerminalID($terminalname) {
        $prefix = Yii::app()->params['prefix'];
        $terminalcode = $prefix.$terminalname;
        $sql = "SELECT TerminalID FROM terminals WHERE TerminalCode = :terminalcode";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":terminalcode", $terminalcode);
        $result = $command->queryRow();
        
        return $result;
    }
}
?>
