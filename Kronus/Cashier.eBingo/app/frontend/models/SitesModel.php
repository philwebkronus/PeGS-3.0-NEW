<?php

/**
 * Date Created 11 21, 11 4:14:52 PM <pre />
 * Description of SitesModel
 * @author Bryan Salazar
 */
class SitesModel extends MI_Model {
    public function getPosAccountNo($site_id) {
        $sql = 'SELECT POSAccountNo FROM sites WHERE SiteID = :site_id';
        $param = array(':site_id'=>$site_id);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['POSAccountNo'];
    }
    
    public function getPosAccntAndAccntName($site_id) {
        $sql = 'SELECT s.POSAccountNo, ad.Name FROM sites s INNER JOIN accountdetails ad ON s.OwnerAID = ad.AID WHERE s.SiteID = :site_id';
        $param = array(':site_id'=>$site_id);
        $this->exec($sql,$param);
        return $this->find();
    }
    
    //check if site is active
    public function checkIfActiveSite($siteid) {
        $sql = 'SELECT SiteID FROM sites WHERE SiteID = :siteid and Status = 1';
        $param = array(':siteid'=>$siteid);
        $this->exec($sql, $param);
        $result = $this->find();
        if(isset($result['SiteID']) && $result['SiteID'] != '')
            return true;
        
        return false;
    }    
    
    /**
     * Get Spyder status on per site basis OFF (0), ON (1)
     * @author Edson Perez <elperez@philweb.com.ph>
     * @param int $siteid
     * @return obj spyder
     */
    public function getSpyderStatus($siteid){
        $sql = 'SELECT Spyder FROM sites WHERE SiteID = :site_id AND Status = 1';
        $param = array(':site_id'=>$siteid);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['Spyder'];
    }
    
    /**
     * Get cashier version used per site
     * @author Edson Perez
     * @date 06-28-13
     * @param int $siteid
     * @return int cashier version
     */
    public function getCashierVersion($siteid){
        $sql = 'SELECT CashierVersion FROM sites WHERE SiteID = :site_id';
        $param = array(':site_id'=>$siteid);
        $this->exec($sql,$param);
        $result = $this->find();
        return $result['CashierVersion'];
    }
    
    public function getMenu($siteid){
        $sql = 'SELECT TMTab, SRRTab, ESafeTab FROM sites WHERE SiteID = :site_id';
        $param = array(':site_id'=>$siteid);
        $this->exec($sql,$param);
        $result = $this->find();
        return $result;
    }
    
    /**
     * Get site classification if Platinum or Non-Platinum
     * @author aqdepliyan
     * @date 09-11-15
     * @param int $siteid
     * @return int site classification id
     */
    public function getSiteClassification($siteid){
        $sql = 'SELECT SiteClassificationID FROM sites WHERE SiteID = :site_id';
        $param = array(':site_id'=>$siteid);
        $this->exec($sql,$param);
        $result = $this->find();
        return $result['SiteClassificationID'];
    }
}