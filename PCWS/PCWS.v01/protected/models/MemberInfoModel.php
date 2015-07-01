<?php

class MemberInfoModel{
    
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db2;
    }
    public function getPlayerInfo($mid)
    {
        $sql = "SELECT FirstName, MiddleName, LastName, Email, MobileNumber FROM memberinfo WHERE MID = :mid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $result = $command->queryRow();
        
        return $result;
    }
}
?>
