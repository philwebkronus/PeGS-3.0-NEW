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

}

?>
