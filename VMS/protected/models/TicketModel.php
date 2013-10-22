<?php
/**
 * Description of TicketModel
 *
 * @author jshernandez
 */

class TicketModel extends CFormModel{
    
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param str $date
     * @return object
     */
    public function getAllUsedTicketList($date){
        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID,  t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.DateExpiry, 
                t.Source, t.LoyaltyCreditable FROM tickets t 
                WHERE t.DateCreated >= :transdate AND  t.DateCreated < :vtransdate AND t.Status = 3";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":transdate", $date);
        $command->bindValue(":vtransdate", $vdate);
        $result = $command->queryAll();
        return $result;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param int $site
     * @param str $date
     * @return object
     */
    public function getUsedTicketListBySite($site, $date){
        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.DateExpiry, 
                t.Source, t.LoyaltyCreditable FROM tickets t INNER JOIN terminals tr ON tr.TerminalID = t.TerminalID
                INNER JOIN sites st ON st.SiteID = tr.SiteID
                WHERE tr.SiteID = '$site' AND t.DateCreated >= :transdate AND t.DateCreated < :vtransdate AND t.Status = 3";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":transdate", $date);
        $command->bindValue(":vtransdate", $vdate);
        $result = $command->queryAll();
        return $result;
    }
}
?>
