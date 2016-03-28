<?php

/**
 * Date Created 11 4, 11 5:24:03 PM <pre />
 * Date Modified 11/06/12
 * Description of TerminalServicesModel
 * @author Bryan Salazar
 * @author Edson Perez
 */
class TerminalServicesModel{
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public function getCasinoByTerminal($terminal_id) {
        $sql = 'SELECT ts.ServiceID, rs.Alias, rs.Code FROM terminalservices ts ' . 
                'INNER JOIN ref_services rs ON rs.ServiceID = ts.ServiceID ' . 
                'WHERE ts.Status = 1 AND ts.isCreated = 1 AND rs.Status = 1 ' . 
                'AND ts.TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $command = Yii::app()->db->createCommand($sql);
        return $command->queryAll(true, $param);
        
    }
    
    public function getServiceID($terminal_id){
        $sql = "SELECT ServiceID FROM terminalservices WHERE TerminalID = :terminal_id";
        $param = array(':terminal_id'=>$terminal_id);
        $command = Yii::app()->db->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['ServiceID'];
    }
    
    public function getPasswordsByTerminalAndServiceID($terminal_id, $service_id){
        $sql = 'SELECT ServicePassword, HashedServicePassword
                FROM terminalservices
                WHERE TerminalID = :terminal_id AND ServiceID = :service_id';
        $param = array(':terminal_id'=>$terminal_id, ':service_id'=>$service_id);
        $command = Yii::app()->db->createCommand($sql);
        return $command->queryAll(true, $param);
    }
    
    public function getMatchedTerminalAndServiceID($TerminalID, $ServiceID){
        $sql = "SELECT TerminalID FROM terminalservices WHERE TerminalID = :terminal_id AND ServiceID = :service_id";
        $param = array(':terminal_id'=>$TerminalID, ':service_id'=>$ServiceID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        if($result['TerminalID']=="")
            return 0;
        else
            return count($result['TerminalID']);
    }
    
    /**
     * Check if a terminal has mapped casino
     * @param int $terminalID ID of the terminal
     * @param int $serviceID CasinoID
     * @return array Count
     * @author Mark Kenneth Esguerra [02-13-14]
     */
    public function checkHasMappedCasino($terminalID, $serviceID = null)
    {
        if (!is_null($serviceID)) 
        {
            $query = "SELECT COUNT(TerminalID) AS cnt FROM terminalservices 
                      WHERE TerminalID = :terminalID AND ServiceID = :serviceID AND Status = 1";

            $command = $this->_connection->createCommand($query);
            $command->bindParam(":terminalID", $terminalID);
            $command->bindParam(":serviceID",  $serviceID);
            $result = $command->queryRow();
        }
        else
        {
            $query = "SELECT COUNT(TerminalID) AS cnt FROM terminalservices 
                      WHERE TerminalID = :terminalID AND Status = 1";

            $command = $this->_connection->createCommand($query);
            $command->bindParam(":terminalID", $terminalID);
            $result = $command->queryRow();
        }
        
        return $result;
    }
}

