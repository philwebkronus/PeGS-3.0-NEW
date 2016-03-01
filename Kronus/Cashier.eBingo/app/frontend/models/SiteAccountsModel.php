<?php

/**
 * Date Created 11 11, 11 6:36:28 PM <pre />
 * Description of SiteAccountsModel
 * @author Bryan Salazar
 */
class SiteAccountsModel extends MI_Model {
    public function getSiteCodeByAccId($user_id) {
        $sql = 'SELECT s.SiteCode FROM siteaccounts AS sa INNER JOIN sites AS s ON s.SiteID = sa.SiteID WHERE sa.AID = :user_id';
        $param = array(':user_id'=>$user_id);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['SiteCode'];
    }
    
    public function getSiteID($aid) {
        $sql = 'SELECT SiteID FROM siteaccounts s where Status = 1 AND AID = :aid';
        $param = array(':aid'=>$aid);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['SiteID'];
    }    
    
    /**
     * Get Site Details
     * @author elperez
     * @param type $siteid
     * @return array sitedetails
     */
    public function getSiteDetails($siteid)
    {
        $sql = "SELECT SiteName, SiteCode, if(isnull(POSAccountNo), '0000000000', POSAccountNo) as POS FROM sites WHERE SiteID = :site_id";
        $param = array(':site_id'=>$siteid);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result;
    }
    
    public function getSiteGroup($siteid, $bgiOwner)
    {
        $sql = "SELECT COUNT(SiteID) as ctrbgi FROM sites WHERE OwnerAID = :owner_id AND SiteID = :site_id";
        $param = array(':owner_id'=>$bgiOwner,':site_id'=>$siteid);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result;
    }
}