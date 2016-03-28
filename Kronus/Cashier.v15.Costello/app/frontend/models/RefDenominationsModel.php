<?php

/**
 * Date Created 11 4, 11 1:56:40 PM <pre />
 * Description of RefDenominationsModel
 * @author Bryan Salazar
 */
class RefDenominationsModel extends MI_Model{
    
    
    public function getAllDenomination() {
        $sql = 'SELECT Amount FROM ref_denominations';
        $this->exec($sql);
        return $this->findAll();
    }
    
    
    /**
     * Description: convert to singler array and sort denomination
     * @return array 
     */
    public function getAllDenominationInterval() {
        $denominations = $this->getAllDenomination();
        $deno = array();
        foreach($denominations as $val) {
            array_push($deno, $val['Amount']);
        }
        sort($deno);
        return $deno;
    }
}

