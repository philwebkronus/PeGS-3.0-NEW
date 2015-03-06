<?php

/**
 * Date Created 11 7, 11 8:18:09 AM <pre />
 * Description of SiteBalanceModel
 * @author Bryan Salazar
 */
class SiteBalanceModel extends MI_Model{

    public function getSiteBalance($site_id) {
        $sql = 'SELECT Balance FROM sitebalance WHERE SiteID = :siteid';
        $param = array(':siteid'=>$site_id);
        $this->exec($sql,$param);
        return $this->find();
    }
    
    //update bcf
    public function updateBcf($newbal, $site_id, $transdtl) {
        $sql = 'UPDATE sitebalance SET Balance = :newbal, LastTransactionDate = now(6), ' . 
                'LastTransactionDescription = :transdtl WHERE SiteID = :siteid';
        $param = array(':newbal'=>$newbal,':transdtl'=>$transdtl,':siteid'=>$site_id);
        return $this->exec($sql,$param);
    }
}

