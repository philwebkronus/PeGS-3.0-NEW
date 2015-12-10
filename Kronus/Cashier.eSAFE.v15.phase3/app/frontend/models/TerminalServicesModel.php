<?php

/**
 * Date Created 11 4, 11 5:24:03 PM <pre />
 * Description of TerminalServicesModel
 * @author Bryan Salazar
 */
class TerminalServicesModel extends MI_Model{
    public function getCasinoByTerminal($terminal_id) {
        $sql = 'SELECT ts.ServiceID, rs.Alias, rs.ServiceName FROM terminalservices ts ' . 
                'INNER JOIN ref_services rs ON rs.ServiceID = ts.ServiceID ' . 
                'WHERE ts.Status = 1 AND ts.isCreated = 1 AND rs.Status = 1 ' . 
                'AND ts.TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql,$param);
        $casinos =  $this->findAll();
        $services = array();
        foreach($casinos as $casino) {
            $services = array_merge($services, array($casino['Alias']=>$casino['ServiceID']));
        }
        $c = array();
        foreach($services as $k => $v) {
            $c[$v] = $k;
        }
        return $c;
    }
    
    public function getCasinosCodeByTerminalId($terminal_id) {
        $sql = 'SELECT rs.Code FROM terminalservices ts ' . 
                'INNER JOIN ref_services rs ON rs.ServiceID = ts.ServiceID ' . 
                'WHERE ts.Status = 1 AND ts.isCreated = 1 AND rs.Status = 1 ' . 
                'AND ts.TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql,$param);
        $casinos =  $this->findAll();
        $code = '';
        foreach($casinos as $casino) {
            $code.=', '.$casino['Code'];
        }
        return substr($code,2);
    }
    
    public function getServiceIDByTerminalID($terminalID){
        $sql = 'SELECT ServiceID FROM terminalservices WHERE TerminalID=:terminalID AND Status=1 LIMIT 1';
        $param = array(':terminalID'=>$terminalID);
        $this->exec($sql,$param);
        $result =  $this->find();
        
        return $result['ServiceID'];
    }
    
    public function getAndCountCasinoServicesByTerminalID($terminalID){
        $sql = 'SELECT COUNT(ServiceID) as ServicesCount, ServiceID FROM terminalservices WHERE TerminalID=:terminalID AND Status=1';
        $param = array(':terminalID'=>$terminalID);
        $this->exec($sql,$param);
        $result =  $this->find();
        
        return $result;
    }
    
}

