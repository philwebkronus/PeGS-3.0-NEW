<?php

class TerminalServices extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
    public function getTerminalDetails($temrinalid,$serviceid)
    {
        $sql = "SELECT * FROM terminalservices WHERE TerminalID = :terminalid AND ServiceID = :serviceid;";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":terminalid", $temrinalid);
        $command->bindValue(":serviceid", $serviceid);
        $result = $command->queryRow();
        
        return $result;
        
    }
   
}
?>
