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
    /**
     * Get the Regular and VIP ID of the absolute Terminal
     * @param type $terminalcode Absolute Terminal code
     * @author Mark Kenneth Esguerra
     * @date 05-14-2015
     */
    public function getRegVIPTerminalID($terminalcode) {
        $terminalcode_vip = $terminalcode."VIP";
        $sql = "SELECT TerminalID 
                FROM terminals 
                WHERE TerminalCode IN (:reg, :vip)";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":reg", $terminalcode);
        $command->bindValue(":vip", $terminalcode_vip);
        $result = $command->queryAll();
        
        return $result;
    }
    public function getTerminalType($terminalID) {
        $query = "SELECT TerminalType FROM terminals 
                  WHERE TerminalID = :terminal";
        $command = $this->connection->createCommand($query);
        $command->bindValue(":terminal", $terminalID);
        $result = $command->queryRow();
        
        return $result['TerminalType'];
    }
    
    public function getTerminalStatus($terminalID) {
        $query = "SELECT Status FROM terminals 
                  WHERE TerminalID = :terminal";
        $command = $this->connection->createCommand($query);
        $command->bindValue(":terminal", $terminalID);
        $result = $command->queryRow();

        return $result['Status'];
    }
}
?>
