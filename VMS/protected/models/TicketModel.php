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
     * @return array
     */
    public function getAllUsedTicketList($date){
         //get kronus database name
        $kronusConnString = Yii::app()->db2->connectionString;
        $dbnameresult = explode(";", $kronusConnString);
        $dbname = str_replace("dbname=", "", $dbnameresult[1]);
        
        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.DateUpdated, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE t.DateUpdated >= :transdate AND  t.DateUpdated < :vtransdate AND t.Status = 3
                ORDER BY st.SiteID, t.TerminalID, t.DateUpdated";
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
     * @return array
     */
    public function getUsedTicketListBySite($site, $date){
         //get kronus database name
        $kronusConnString = Yii::app()->db2->connectionString;
        $dbnameresult = explode(";", $kronusConnString);
        $dbname = str_replace("dbname=", "", $dbnameresult[1]);
        
        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.DateUpdated, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE st.SiteID = $site AND t.DateUpdated >= :transdate AND  t.DateUpdated < :vtransdate AND t.Status = 3
                ORDER BY st.SiteID, t.TerminalID, t.DateUpdated";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":transdate", $date);
        $command->bindValue(":vtransdate", $vdate);
        $result = $command->queryAll();
        return $result;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $voucherTicketBarcode
     * @return array
     */
    public function getTicketDataByCode($voucherTicketBarcode){
        $sql = "SELECT TicketCode, TerminalID, TrackingID, Amount, DateCreated, CreatedByAID, Source, IsCreditable FROM tickets WHERE TicketCode = :voucherTicketBarcode";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":voucherTicketBarcode", $voucherTicketBarcode);
        $result = $command->queryAll();
        return $result;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param int $trackingID
     * @param int $terminalID
     * @param str $voucherTicketBarcode
     * @param int $source
     * @param int $aid
     * @return array
     */
    public function getTicketIDByValuesWithAID($trackingID, $voucherTicketBarcode, $source){
        $sql = "SELECT TicketID FROM tickets WHERE TrackingID = :trackingid AND TicketCode = :voucherTicketBarcode AND Source = :source";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":trackingid", $trackingID);
        $command->bindValue(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindValue(":source", $source);
        $result = $command->queryAll();
        return $result;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param int $trackingID
     * @param int $terminalID
     * @param str $voucherTicketBarcode
     * @param int $source
     * @return array
     */
    public function getTicketIDByValuesWithoutAID($trackingID, $voucherTicketBarcode, $source){
        $sql = "SELECT TicketID FROM tickets WHERE TrackingID = :trackingid AND TicketCode = :voucherTicketBarcode AND Source = :source";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":trackingid", $trackingID);
        $command->bindValue(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindValue(":source", $source);
        $result = $command->queryAll();
        return $result;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/23/13
     * @param str $voucherTicketBarcode
     * @return object
     */
    public function getTerminalIDByTicketCode($voucherTicketBarcode){
        $sql = "SELECT TerminalID FROM tickets WHERE TicketID = :voucherTicketBarcode";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":voucherTicketBarcode", $voucherTicketBarcode);
        $result = $command->queryRow();
        
        return $result;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/23/13
     * @param int $aid
     * @return object
     */
    public function getAIDByTicketCode($voucherTicketBarcode){
        $sql = "SELECT CreatedByAID FROM tickets WHERE CouponCode = :voucherTicketBarcode";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":voucherTicketBarcode", $voucherTicketBarcode);
        $result = $command->queryAll();
        return $result;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param int $terminalID
     * @param int $source
     * @param int $amount
     * @param int $aid
     * @return object
     */
    public function getTicketIDByCodeAndSource($terminalID, $amount, $ticketStatus, $aid){
        $sql = "SELECT TicketID FROM tickets WHERE TerminalID = :terminal_id
                AND Amount = :amount AND Status = :ticket_status AND CreatedByAID = :aid";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":terminal_id", $terminalID);
        $command->bindValue(":amount", $amount);
        $command->bindValue(":ticket_status", $ticketStatus);
        $command->bindValue(":aid", $aid);
        $result = $command->queryAll();
        
        if(isset($result[0]['TicketID'])){
            return $result[0]['TicketID'];
        } else {
            return 0;
        }
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param int $couponID
     * @param int $statusUsed
     * @return object
     */
    public function updateTicketStatus($ticketID, $statusUsed){
        $beginTrans = $this->_connection->beginTransaction();
        try{
                $query = "UPDATE tickets SET Status = $statusUsed WHERE TicketID = $ticketID";
                $sql = $this->_connection->createCommand($query);
                $sql->execute();
                
                try {
                    $beginTrans->commit();
                    return true;
                } catch(Exception $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
            }catch(Exception $e){
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
    }
}
?>
