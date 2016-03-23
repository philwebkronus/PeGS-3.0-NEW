<?php

/**
 * Description of CashierMachineCountsModel
 *
 * @author bryan
 */
class CashierMachineCountsModel extends MI_Model{
    /**
    * get the cashier machine count per site
    */
    public function checkCashierMachine($siteID) {
        $sql = 'SELECT CashierMachineCount FROM cashiermachinecounts WHERE SiteID = :siteid';
        $param = array(':siteid'=>$siteID);
        $this->exec($sql, $param);
        return $this->find();
    }    
}

?>
