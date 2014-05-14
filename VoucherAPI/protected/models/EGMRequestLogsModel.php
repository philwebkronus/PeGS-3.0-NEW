<?php

/**
 * Description of EGMRequestLogs
 *
 * @author jshernandez
 */

class EGMRequestLogsModel extends CFormModel{
    
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $date
     * @return array
     */
    public function getStatusByTrackingId($trackingid){
        $sql = "SELECT TrackingID, Status FROM egmrequestlogs WHERE TrackingID = :tracking_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":tracking_id", $trackingid);
        $result = $command->queryAll();
        return $result;
    }
    
}

?>
