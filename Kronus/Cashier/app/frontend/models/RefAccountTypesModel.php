<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RefAccountTypesModel
 *
 * @author bryan
 */
class RefAccountTypesModel extends MI_Model{
    public function getAccTypeName($acctype) {
        $sql = 'SELECT Name FROM ref_accounttypes WHERE AccountTypeID = :acctype';
        $param = array(':acctype'=>$acctype);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['Name'];
    }    
}

?>
