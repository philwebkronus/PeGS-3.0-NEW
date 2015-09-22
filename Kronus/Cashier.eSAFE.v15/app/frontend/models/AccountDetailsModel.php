<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AccountDetailsModel
 *
 * @author bryan
 */
class AccountDetailsModel extends MI_Model{
    
    public function getDesignation($aid) {
        $sql = 'SELECT a.DesignationID, b.DesignationName FROM accountdetails a ' . 
            'INNER JOIN ref_designations b ON a.DesignationID = b.DesignationID WHERE AID = :aid';
        $param = array(':aid'=>$aid);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['DesignationName'];
    }
}
