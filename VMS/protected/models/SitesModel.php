<?php

/**
 * Description of SitesModel
 *
 * @author jshernandez
 */
class SitesModel extends CFormModel {

    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db2;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param int $siteid
     * @return object
     */
    public function getSiteName($siteid) {
        $sql = "SELECT SiteID, SiteName FROM sites
                WHERE SiteID = :siteid";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":siteid", $siteid);
        $result = $command->queryAll();
        return $result;
    }
    
    /**
     * @author Edson Perez
     * @date 10/25/13
     * @purpose Get ALL active sites
     * @return array sites
     */
    public function fetchAllActiveSites(){
        $sql = "SELECT SiteID, SiteName FROM sites WHERE Status = 1 AND SiteID <> 1
                ORDER BY SiteName ASC";
        
        $command = $this->_connection->createCommand($sql);
        
        $result = $command->queryAll();
        
        $site = array('All'=>'All');
        foreach($result as $row)
        {
            $site[$row['SiteID']] = $row['SiteName'];
        }
        
        return $site;
    }

}

?>
