<?php

/**
 * Date Created 11 21, 11 4:14:52 PM <pre />
 * Date Modified 10/12/12
 * Description of SitesModel
 * @author Bryan Salazar
 * @author Edson Perez
 */
class SitesModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new SitesModel();
        return self::$_instance;
    }
    
    
    public function getPosAccountNo($site_id) {
        $sql = 'SELECT POSAccountNo FROM sites WHERE SiteID = :site_id';
        $param = array(':site_id'=>$site_id);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['POSAccountNo'];
    }
    
    //check if site is active
    public function checkIfActiveSite($siteid) {
        $sql = 'SELECT SiteID FROM sites WHERE SiteID = :siteid and Status = 1';
        $param = array(':siteid'=>$siteid);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(isset($result['SiteID']) && $result['SiteID'] != '')
            return true;
        
        return false;
    }
    
    public function getSiteCode($site_id){
        $sql = "SELECT SiteCode FROM sites WHERE SiteID = :site_id";
        $param = array(':site_id'=>$site_id);
        $command = Yii::app()->db->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['SiteCode'];
    }
    
    public function getSpyderStatus($siteid){
        $sql = 'SELECT Spyder FROM sites WHERE SiteID = :site_id AND Status = 1';
        $param = array(':site_id'=>$siteid);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['Spyder'];
    }
    /**
     * Check Status of the Site by Terminal
     * @param int $terminalID Terminal ID
     * @return array Status
     * @author Mark Kenneth Esguerra [02-13-14]
     */
    public function checkStatusByTerminal($terminalID)
    {
        $query = "SELECT s.Status FROM sites s  
                  INNER JOIN terminals t ON s.SiteID = t.SiteID 
                  WHERE t.TerminalID = :terminalID";
        $command = $this->_connection->createCommand($query);
        $command->bindParam(":terminalID", $terminalID);
        $result = $command->queryRow();
        
        return $result;
    }
    /**
     * Check Status of the Site by Terminal
     * @param int $terminalID
     * @return array SiteID
     * @author JunJun S. Hernandez
     */
    public function getSiteNameByTerminalID($siteID) {
        $sql = 'SELECT SiteName FROM sites WHERE SiteID = :site_id';
        $param = array(':site_id'=>$siteID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['SiteName'];
    }
    /**
     * Check if site exist using Site code
     * @param string $sitecode Site Code
     * @return boolean <b>TRUE</b> if do exist, <b>FALSE</b> if not.
     * @author kenken
     * @date 03-04-15
     */
    public function isSiteExist($sitecode)
    {
        $sql = "SELECT COUNT(SiteID) AS Count 
                FROM sites 
                WHERE SiteCode = :sitecode";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":sitecode", $sitecode);
        $result = $command->queryRow();
        
        if ($result['Count'] > 0)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }
    /**
     * Get Site ID by Site Code
     * @param type $sitecode SiteCode
     * @return array SiteID
     */
    public function getSiteIDBySiteCode($sitecode)
    {
        $sql = "SELECT SiteID 
                FROM sites 
                WHERE SiteCode = :sitecode";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":sitecode", $sitecode);
        $result = $command->queryRow();
        
        return $result['SiteID'];
    }
    /**
     * 
     * @param type $siteID
     * @return type
     */
    public function getSiteClassfication($siteID) {
        $sql = "SELECT SiteClassificationID 
                FROM sites 
                WHERE SiteID = :siteID";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":siteID", $siteID);
        $result  = $command->queryRow();
        
        return $result['SiteClassificationID'];
    }
    /**
     * Get Site's Classification by Terminal
     * @param type $terminalID
     * @return boolean
     */
    public function getSiteClassificationByTerminal($terminalID) {
        $sql = "SELECT s.SiteClassificationID FROM sites s 
                 INNER JOIN terminals t ON s.SiteID = t.SiteID 
                 WHERE t.TerminalID = :terminalid";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":terminalid", $terminalID);
        $result  = $command->queryRow();
        
        if (is_array($result)) {
            return $result['SiteClassificationID'];
        }
        else {
            return false;
        }
    }
}