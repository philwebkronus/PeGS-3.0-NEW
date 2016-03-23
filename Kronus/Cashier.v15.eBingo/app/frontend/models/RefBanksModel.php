<?php
/**
 * Description of RefBanksModel
 *
 * @author jdlachica
 */
class RefBanksModel extends MI_Model {
    public function getBanks() {
        $sql = 'SELECT BankID,BankName FROM ref_banks WHERE IsAccredited=1 AND Status=1 ORDER BY BankName';
        $this->exec($sql);
        return $this->findAll();
    }
    
}
