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
        $this->_connection = Yii::app()->db4;
    }

    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new TicketsModel();
        return self::$_instance;
    }
    
    /**
     * This function is used to retrieve data of the specific
     * ticket code to be encashed.
     * @param string $ticketCode Ticket Code
     * @return array resultset
     */
    public function checkTicketCode($ticketCode)
    {
        $query = "SELECT TicketCode FROM tickets WHERE
                    Amount IS NOT NULL AND TicketCode = :ticket_code;";
        $command = $this->_connection->createCommand($query);
        $command->bindValue(":ticket_code", $ticketCode);
        $result = $command->queryRow();
        
        if (isset($result)) {
            return $result['TicketCode'];
        } else {
            return 0;
        }
    }

    public function getAmountByTicketCode($ticketCode) {
        $sql = 'SELECT Amount FROM tickets WHERE TicketCode = :ticket_code';
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":ticket_code", $ticketCode);
        $result = $command->queryRow();
  
        if (isset($result)) {
            return $result['Amount'];
        } else {
            return 0;
        }
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
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $trackingID
     * @return int
     */
    public function isTrackingIDExists($trackingID) {
        $query = 'SELECT COUNT(TicketID) ctrticket FROM tickets WHERE TrackingID = :trackingid OR TrackingID2 = :trackingid';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":trackingid" => $trackingID
        ));

        $result = $sql->queryRow();

        return $result['ctrticket'];
    }

}
