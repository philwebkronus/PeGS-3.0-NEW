<?php

/**
 * Date Created 11 10, 11 11:33:11 AM <pre />
 * Description of ServiceTerminalsModel
 * @author Bryan Salazar
 */
class ServiceTerminalsModel extends MI_Model{
    
    public function getAgentSessionByTerminalId($terminal_id) {
        $sql = 'SELECT C.ServiceAgentSessionID FROM serviceterminals A INNER JOIn terminalmapping B ON A.ServiceTerminalID = B.ServiceTerminalID ' . 
                'INNER JOIN serviceagentsessions C ON A.ServiceAgentID = C.ServiceAgentID WHERE B.TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['ServiceAgentSessionID'];
    }
    
    public function getTerminalMapByTerminalId($terminal_id) {
        $sql = 'SELECT ServiceTerminalAccount FROM serviceterminals A INNER JOIN terminalmapping B ON A.ServiceTerminalID = B.ServiceTerminalID ' . 
                'WHERE B.TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['ServiceTerminalAccount'];
    }
}

