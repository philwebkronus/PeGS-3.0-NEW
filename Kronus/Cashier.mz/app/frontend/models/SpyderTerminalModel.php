<?php

/**
 * Check Terminal if Activated or Not
 *
 * @author John Aaron Vida
 * @datecreated 12/19/2017
 * 
 */
class SpyderTerminalModel extends MI_Model {

    public function checkTerminalStatus($terminalname) {
        $sql = 'SELECT status FROM terminals WHERE terminalname = :terminalname';
        $param = array(':terminalname' => $terminalname);

        $this->exec5($sql, $param);
        $result = $this->find5();

        $terminal_status = $result['status'];

        return $terminal_status;
    }

}

?>
