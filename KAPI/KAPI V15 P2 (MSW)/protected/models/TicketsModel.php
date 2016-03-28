<?php

/**
 * @description of TicketsModel
 * @author jshernandez <jshernandez@philweb.com.ph>
 * @datecreated 11 11, 13 1:11:44 PM
 */
class TicketsModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db5;
    }

    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new TicketsModel();
        return self::$_instance;
    }
    
    
     /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param int $terminalID
     * @param int $AID
     * @param int $status
     * @return array
     */
    public function generateTicketCode() {
        $sql = "SELECT generate_ticket() TicketCode";
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow();
        
        return $result['TicketCode'];
    }
    
    public function checkIfTrackingIDExist($trackingID)
    {
        $sql = "SELECT COUNT(TrackingID) as cntTrackingID FROM tickets 
                WHERE TrackingID = :trackingID";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":trackingID", $trackingID);
        $result = $command->queryRow();
        
        if ($result['cntTrackingID'] > 0)
        {
            return true;
        }
        else
        {
            return false; 
        }
    }
}
