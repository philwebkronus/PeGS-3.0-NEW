<?php

/**
 * Description of TicketModel
 *
 * @author jshernandez
 */
class TicketModel extends CFormModel {

    public $_connection;
    public $_connection2;

    CONST ACCOUNTTYPE_ID_SITE_OPERATOR = 2;
    CONST ACCOUNTTYPE_ID_SITE_SUPERVISOR = 3;
    CONST ACCOUNTTYPE_ID_SITE_CASHIER = 4;

    CONST ACTIVE = 1;
    CONST VOID = 2;
    CONST USED = 3;
    CONST ENCASHED = 4;
    
    public function __construct() {
        $this->_connection = Yii::app()->db;
        $this->_connection2 = Yii::app()->db4;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param str $date
     * @return array
     */
    public function getAllUsedTicketList($date, $ticket_stat = null) {
        //get kronus database name
        $kronusConnString = Yii::app()->db2->connectionString;
        $dbnameresult = explode(";", $kronusConnString);
        $dbname = str_replace("dbname=", "", $dbnameresult[1]);

        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        
        switch ($ticket_stat)
        {
            case self::ACTIVE:
                $status = "AND t.Status IN (1, 2)";
                break;
            case 2:
                $status = "AND t.Status IN (1, 2)";
                break;
            case self::USED: 
                $status = "AND t.Status = 3";
                break;
            case self::ENCASHED:
                $status = "AND t.Status = 4";
                break;
            default: 
                $status = " ";
                break;
        }
        if (($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_OPERATOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_SUPERVISOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_CASHIER)) {
            $sql = "SELECT DISTINCT(t.TicketID) AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable, SUM(t.Amount) AS TotalAmount FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                INNER JOIN $dbname.siteaccounts sa ON sa.SiteID = st.SiteID
                INNER JOIN $dbname.accounts a ON a.AID = sa.AID
                WHERE t.DateUpdated >= :transdate AND  t.DateUpdated < :vtransdate AND ".$status."
                AND a.AccountTypeID = :account_type_id AND a.AID = :aid
                GROUP BY t.TicketCode
                ORDER BY t.DateUpdated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":transdate", $date);
            $command->bindValue(":vtransdate", $vdate);
            $command->bindValue(":account_type_id", $_SESSION['AccountType']);
            $command->bindValue(":aid", $_SESSION['AID']);
        } else {
            $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE t.DateUpdated >= :transdate AND  t.DateUpdated < :vtransdate ".$status."
                GROUP BY t.TicketCode
                ORDER BY t.DateUpdated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":transdate", $date);
            $command->bindValue(":vtransdate", $vdate);
        }
        $result = $command->queryAll();
        return $result;
    }

        public function getAllActiveTicketList($date) {
        //get kronus database name
        $kronusConnString = Yii::app()->db2->connectionString;
        $dbnameresult = explode(";", $kronusConnString);
        $dbname = str_replace("dbname=", "", $dbnameresult[1]);

        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        
        if (($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_OPERATOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_SUPERVISOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_CASHIER)) {
            $sql = "SELECT DISTINCT(t.TicketID) AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable, SUM(t.Amount) AS TotalAmount FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                INNER JOIN $dbname.siteaccounts sa ON sa.SiteID = st.SiteID
                INNER JOIN $dbname.accounts a ON a.AID = sa.AID
                WHERE t.DateCreated >= :transdate AND  t.DateCreated < :vtransdate AND t.Status IN (1, 2)
                AND a.AccountTypeID = :account_type_id AND a.AID = :aid
                GROUP BY t.TicketCode
                ORDER BY t.DateUpdated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":transdate", $date);
            $command->bindValue(":vtransdate", $vdate);
            $command->bindValue(":account_type_id", $_SESSION['AccountType']);
            $command->bindValue(":aid", $_SESSION['AID']);
        } else {
            $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE t.DateCreated >= :transdate AND  t.DateCreated < :vtransdate AND t.Status IN (1, 2)
                GROUP BY t.TicketCode
                ORDER BY t.DateUpdated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":transdate", $date);
            $command->bindValue(":vtransdate", $vdate);
        }
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
    public function getUsedTicketListBySite($site, $date) {
        //get kronus database name
        $kronusConnString = Yii::app()->db2->connectionString;
        $dbnameresult = explode(";", $kronusConnString);
        $dbname = str_replace("dbname=", "", $dbnameresult[1]);

        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE st.SiteID = $site AND t.DateUpdated >= :transdate AND  t.DateUpdated < :vtransdate AND t.Status IN (1, 2, 3, 4)
                GROUP BY t.TicketCode
                ORDER BY t.DateUpdated DESC";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":transdate", $date);
        $command->bindValue(":vtransdate", $vdate);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param str $date
     * @return array
     */
    public function getAllUsedTicketListTrans($date) {
        //get kronus database name
        $kronusConnString = Yii::app()->db2->connectionString;
        $dbnameresult = explode(";", $kronusConnString);
        $dbname = str_replace("dbname=", "", $dbnameresult[1]);

        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');

        if (($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_OPERATOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_SUPERVISOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_CASHIER)) {
            $sql = "SELECT DISTINCT(t.TicketID) AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable, SUM(t.Amount) AS TotalAmount FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                INNER JOIN $dbname.siteaccounts sa ON sa.SiteID = st.SiteID
                INNER JOIN $dbname.accounts a ON a.AID = sa.AID
                WHERE t.DateUpdated >= :transdate AND  t.DateUpdated < :vtransdate AND t.Status IN (1, 2, 3, 4)
                AND a.AccountTypeID = :account_type_id AND a.AID = :aid
                GROUP BY t.TicketCode
                ORDER BY t.DateUpdated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":transdate", $date);
            $command->bindValue(":vtransdate", $vdate);
            $command->bindValue(":account_type_id", $_SESSION['AccountType']);
            $command->bindValue(":aid", $_SESSION['AID']);
        } else {
            $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE t.DateUpdated >= :transdate AND  t.DateUpdated < :vtransdate AND t.Status IN (1, 2, 3, 4)
                GROUP BY t.TicketCode
                ORDER BY t.DateUpdated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":transdate", $date);
            $command->bindValue(":vtransdate", $vdate);
        }
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
    public function getUsedTicketListBySiteTrans($site, $date) {
        //get kronus database name
        $kronusConnString = Yii::app()->db2->connectionString;
        $dbnameresult = explode(";", $kronusConnString);
        $dbname = str_replace("dbname=", "", $dbnameresult[1]);

        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE st.SiteID = $site AND t.DateUpdated >= :transdate AND  t.DateUpdated < :vtransdate AND t.Status IN (1, 2, 3, 4)
                GROUP BY t.TicketCode
                ORDER BY t.DateUpdated DESC";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":transdate", $date);
        $command->bindValue(":vtransdate", $vdate);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param str $date
     * @return array
     */
    public function getAllTicketTransactionList($dateFrom, $dateTo) {
        //get kronus database name
        $kronusConnString = Yii::app()->db2->connectionString;
        $dbnameresult = explode(";", $kronusConnString);
        $dbname = str_replace("dbname=", "", $dbnameresult[1]);

        $datetime = new DateTime($dateTo);
        $datetime->modify('+1 day');
        $dateTo = $datetime->format('Y-m-d H:i:s');

        if (($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_OPERATOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_SUPERVISOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_CASHIER)) {
            $sql = "SELECT DISTINCT(t.TicketID) AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable, SUM(t.Amount) AS TotalAmount FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                INNER JOIN $dbname.siteaccounts sa ON sa.SiteID = st.SiteID
                INNER JOIN $dbname.accounts a ON a.AID = sa.AID
                WHERE t.DateUpdated >= :dateFrom AND  t.DateUpdated < :dateTo AND t.Status IN (3, 4, 7)
                AND a.AccountTypeID = :account_type_id AND a.AID = :aid
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":dateFrom", $dateFrom);
            $command->bindValue(":dateTo", $dateTo);
            $command->bindValue(":account_type_id", $_SESSION['AccountType']);
            $command->bindValue(":aid", $_SESSION['AID']);
        } else {
            $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE t.DateUpdated >= :dateFrom AND  t.DateUpdated < :dateTo AND t.Status IN (3, 4, 7)
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":dateFrom", $dateFrom);
            $command->bindValue(":dateTo", $dateTo);
        }
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
    public function getTicketTransactionListBySite($site, $dateFrom, $dateTo) {
        //get kronus database name
        $kronusConnString = Yii::app()->db2->connectionString;
        $dbnameresult = explode(";", $kronusConnString);
        $dbname = str_replace("dbname=", "", $dbnameresult[1]);

        $datetime = new DateTime($dateTo);
        $datetime->modify('+1 day');
        $dateTo = $datetime->format('Y-m-d H:i:s');

        $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE st.SiteID = :site AND t.DateUpdated >= :dateFrom AND  t.DateUpdated < :dateTo AND t.Status IN (3, 4, 7)
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":site", $site);
        $command->bindValue(":dateFrom", $dateFrom);
        $command->bindValue(":dateTo", $dateTo);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param str $date
     * @return array
     */
    public function getAllTicketTransactionListWithStatus($status, $dateFrom, $dateTo) {
        //get kronus database name
        $kronusConnString = Yii::app()->db2->connectionString;
        $dbnameresult = explode(";", $kronusConnString);
        $dbname = str_replace("dbname=", "", $dbnameresult[1]);
        $status = (int)$status;
        $datetime = new DateTime($dateTo);
        $datetime->modify('+1 day');
        $dateTo = $datetime->format('Y-m-d H:i:s');

        if (($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_OPERATOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_SUPERVISOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_CASHIER)) {
            
            if ($status == 2) {
                $sql = "SELECT DISTINCT(t.TicketID) AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable, SUM(t.Amount) AS TotalAmount FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                INNER JOIN $dbname.siteaccounts sa ON sa.SiteID = st.SiteID
                INNER JOIN $dbname.accounts a ON a.AID = sa.AID
                WHERE t.DateCreated >= :dateFrom AND  t.DateCreated < :dateTo AND t.Status = :status
                AND t.ValidToDate > NOW(6)
                AND a.AccountTypeID = :account_type_id AND a.AID = :aid
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            } else if ($status == 3) {
                $sql = "SELECT DISTINCT(t.TicketID) AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable, SUM(t.Amount) AS TotalAmount FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                INNER JOIN $dbname.siteaccounts sa ON sa.SiteID = st.SiteID
                INNER JOIN $dbname.accounts a ON a.AID = sa.AID
                WHERE t.DateUpdated >= :dateFrom AND  t.DateUpdated < :dateTo AND t.Status = :status
                AND a.AccountTypeID = :account_type_id AND a.AID = :aid
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            } else if ($status == 4) {
                $sql = "SELECT DISTINCT(t.TicketID) AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable, SUM(t.Amount) AS TotalAmount FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                INNER JOIN $dbname.siteaccounts sa ON sa.SiteID = st.SiteID
                INNER JOIN $dbname.accounts a ON a.AID = sa.AID
                WHERE t.DateEncashed >= :dateFrom AND  t.DateEncashed < :dateTo AND t.Status = :status
                AND a.AccountTypeID = :account_type_id AND a.AID = :aid
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            } else if ($status == 7) {
                $sql = "SELECT DISTINCT(t.TicketID) AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable, SUM(t.Amount) AS TotalAmount FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                INNER JOIN $dbname.siteaccounts sa ON sa.SiteID = st.SiteID
                INNER JOIN $dbname.accounts a ON a.AID = sa.AID
                WHERE t.ValidToDate >= :dateFrom AND t.ValidToDate < :dateTo AND t.Status = :status 
                AND a.AccountTypeID = :account_type_id AND a.AID = :aid
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            } else {
                $sql = "SELECT DISTINCT(t.TicketID) AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable, SUM(t.Amount) AS TotalAmount FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                INNER JOIN $dbname.siteaccounts sa ON sa.SiteID = st.SiteID
                INNER JOIN $dbname.accounts a ON a.AID = sa.AID
                WHERE t.DateUpdated >= :dateFrom AND  t.DateUpdated < :dateTo AND t.Status = :status
                AND a.AccountTypeID = :account_type_id AND a.AID = :aid
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            }
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":dateFrom", $dateFrom);
            $command->bindValue(":dateTo", $dateTo);
            $command->bindValue(":account_type_id", $_SESSION['AccountType']);
            $command->bindValue(":aid", $_SESSION['AID']);
            $command->bindValue(":status", $status);
        } else {
            if ($status == 2) {
                $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE t.DateCreated >= :dateFrom AND  t.DateCreated < :dateTo AND t.ValidToDate > NOW(6) AND t.Status = :status
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":dateFrom", $dateFrom);
            $command->bindValue(":dateTo", $dateTo);
            $command->bindValue(":status", $status);
            } else if ($status == 3) {
                $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE t.DateUpdated >= :dateFrom AND  t.DateUpdated < :dateTo AND t.Status = :status
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":dateFrom", $dateFrom);
            $command->bindValue(":dateTo", $dateTo);
            $command->bindValue(":status", $status);
            } else if ($status == 4) {
                $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE t.DateEncashed >= :dateFrom AND  t.DateEncashed < :dateTo AND t.Status = :status
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":dateFrom", $dateFrom);
            $command->bindValue(":dateTo", $dateTo);
            $command->bindValue(":status", $status);
            } else if ($status == 7) {
                $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE t.ValidToDate >= :dateFrom AND t.ValidToDate < :dateTo  AND t.Status = :status 
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":dateFrom", $dateFrom);
                $command->bindValue(":status", $status);
            $command->bindValue(":dateTo", $dateTo);
            } else {
                $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE t.DateUpdated >= :dateFrom AND  t.DateUpdated < :dateTo AND t.Status = :status
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":dateFrom", $dateFrom);
            $command->bindValue(":dateTo", $dateTo);
            $command->bindValue(":status", $status);
            }
        }
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
    public function getTicketTransactionListBySiteWithStatus($status, $site, $dateFrom, $dateTo) {
        
        //get kronus database name
        $kronusConnString = Yii::app()->db2->connectionString;
        $dbnameresult = explode(";", $kronusConnString);
        $dbname = str_replace("dbname=", "", $dbnameresult[1]);

        $datetime = new DateTime($dateTo);
        $datetime->modify('+1 day');
        $dateTo = $datetime->format('Y-m-d H:i:s');

        if ($status == 2) {
            $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE st.SiteID = :site AND t.DateCreated >= :dateFrom AND  t.DateCreated < :dateTo AND t.Status = :status AND t.ValidToDate > NOW(6)
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":status", $status);
        } else if ($status == 3) {
            $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE st.SiteID = :site AND t.DateUpdated >= :dateFrom AND  t.DateUpdated < :dateTo AND t.Status = :status
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":status", $status);
        } else if ($status == 4) {
            $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE st.SiteID = :site AND t.DateEncashed >= :dateFrom AND  t.DateEncashed < :dateTo AND t.Status = :status
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":status", $status);
        } else if ($status == 7) {
            $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE st.SiteID = :site AND t.ValidToDate > :dateFrom AND t.ValidToDate < :dateTo 
                AND t.Status = :status 
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":status", $status);
        } else {
            $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE st.SiteID = :site AND t.DateUpdated >= :dateFrom AND  t.DateUpdated < :dateTo AND t.Status = :status
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":status", $status);
        }

        $command->bindValue(":site", $site);
        $command->bindValue(":dateFrom", $dateFrom);
        $command->bindValue(":dateTo", $dateTo);

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
    public function getTicketTransactionByTicketCode($ticketcode) {
        //get kronus database name
        $kronusConnString = Yii::app()->db2->connectionString;
        $dbnameresult = explode(";", $kronusConnString);
        $dbname = str_replace("dbname=", "", $dbnameresult[1]);

        if (($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_OPERATOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_SUPERVISOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_CASHIER)) {
            $sql = "SELECT DISTINCT(t.TicketID) AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable, SUM(t.Amount) AS TotalAmount FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                INNER JOIN $dbname.siteaccounts sa ON sa.SiteID = st.SiteID
                INNER JOIN $dbname.accounts a ON a.AID = sa.AID
                WHERE TicketCode = :ticketcode AND t.Status IN (3, 4, 7)
                AND a.AccountTypeID = :account_type_id AND a.AID = :aid
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":ticketcode", $ticketcode);
            $command->bindValue(":account_type_id", $_SESSION['AccountType']);
            $command->bindValue(":aid", $_SESSION['AID']);
        } else {
            $sql = "SELECT t.TicketID AS VoucherID, '1' AS VoucherTypeID, st.SiteName, st.SiteCode, t.TicketCode AS VoucherCode, 
                t.Status, t.TerminalID, t.Amount, SUM(t.Amount) AS TotalAmount, t.DateCreated, t.CreatedByAID, t.DateUpdated, t.UpdatedByAID, t.DateEncashed, t.ValidToDate, 
                t.Source, t.IsCreditable FROM tickets t 
		INNER JOIN $dbname.terminals tr ON tr.TerminalID=t.TerminalID
		INNER JOIN $dbname.sites st ON st.SiteID = tr.SiteID
                WHERE TicketCode = :ticketcode AND t.Status IN (3, 4, 7)
                GROUP BY t.TicketCode
                ORDER BY t.DateCreated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":ticketcode", $ticketcode);
        }
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $voucherTicketBarcode
     * @return array
     */
    public function getTicketDataByCode($voucherTicketBarcode) {
        $sql = "SELECT TicketID, TicketCode, TerminalID, TrackingID, Amount, DateCreated, CreatedByAID, Source, IsCreditable, ValidToDate, Status FROM tickets WHERE TicketCode = :voucherTicketBarcode";
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
    public function getTicketIDByValues($trackingID, $voucherTicketBarcode, $source) {
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
    public function getTicketIDByValuesWithoutAID($trackingID, $voucherTicketBarcode, $source) {
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
    public function getTerminalIDByTicketCode($voucherTicketBarcode) {
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
    public function getAIDByTicketCode($voucherTicketBarcode) {
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
    public function getTicketIDByCodeAndSource($terminalID, $amount, $ticketStatus, $aid) {
        $sql = "SELECT TicketID FROM tickets WHERE TerminalID = :terminal_id
                AND Amount = :amount AND Status = :ticket_status AND CreatedByAID = :aid";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":terminal_id", $terminalID);
        $command->bindValue(":amount", $amount);
        $command->bindValue(":ticket_status", $ticketStatus);
        $command->bindValue(":aid", $aid);
        $result = $command->queryAll();

        if (isset($result[0]['TicketID'])) {
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
    public function updateTicketStatus($ticketID, $statusUsed) {
        $beginTrans = $this->_connection->beginTransaction();
        try {
            $query = "UPDATE tickets SET Status = $statusUsed WHERE TicketID = $ticketID";
            $sql = $this->_connection->createCommand($query);
            $sql->execute();

            try {
                $beginTrans->commit();
                return true;
            } catch (Exception $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param string $voucherTicketBarcode
     * @param int $siteID
     * @return array
     */
    public function getTicketDataByCodeAndSiteID($voucherTicketBarcode, $siteID) {
        $sql = "SELECT TicketCode, TerminalID, TrackingID, ValidFromDate, Amount, DateCreated, CreatedByAID, IsCreditable, ValidToDate, Status
                FROM tickets
                WHERE TicketCode = :voucherTicketBarcode AND SiteID = :site_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindValue(":site_id", $siteID);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $trackingID
     * @return object
     */
    public function getTicketDataByTrackingId($trackingID) {
        $sql = "SELECT TicketID, TicketCode, TrackingID, ValidFromDate, ValidToDate, Status FROM tickets WHERE TrackingID = :tracking_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":tracking_id", $trackingID);
        $result = $command->queryRow();

        if ($result != '') {
            return $result['TicketCode'];
        } else {
            return 0;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $trackingID
     * @return array
     */
    public function getTicketDetailsByTrackingId($trackingID) {
        $sql = "SELECT TicketID, TicketCode, Amount, DateCreated, TrackingID, IsCreditable, ValidFromDate, ValidToDate, Status FROM tickets WHERE TrackingID = :tracking_id OR TrackingID2 = :tracking_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(":tracking_id" => $trackingID));
        $result = $command->queryRow();

        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $trackingID
     * @return array
     */
    public function getTicketDataByTrackingIdAndSiteID($trackingID, $siteID) {
        $sql = "SELECT TicketID, TicketCode, Amount, DateCreated, TrackingID, IsCreditable, ValidFromDate, ValidToDate, Status FROM tickets WHERE TrackingID = :tracking_id AND SiteID = :site_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(":tracking_id" => $trackingID, ":site_id" => $siteID));
        $result = $command->queryAll();

        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param int $terminalID
     * @param int $AID
     * @param int $status
     * @return array
     */
    public function getTicketDataByStatus($status) {
        $sql = "SELECT TicketID, TicketCode, DateCreated, ValidFromDate, ValidToDate
                FROM tickets
                WHERE Status = :status AND Amount IS NULL LIMIT 1";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(":status" => $status));
        $result = $command->queryAll();
        return $result;
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
     * @datecreated 10/22/13
     * @param int $ticketID
     * @param int $siteID
     * @param int $terminalID
     * @param int $mid
     * @param float $amount
     * @param string $dateUpdated
     * @param int $updatedByAID
     * @param string $validFromDate
     * @param string $validToDate
     * @param int $trackingID
     * @return boolean success | failed transaction
     */
    public function insertTicketData($ticketCode, $siteID, $terminalID, $terminalName, $MID, $amount, $source, $dateUpdated, $updatedByAID, $validFromDate, $validToDate, $trackingID, $status, $stackerBatchID) {
        $beginTrans = $this->_connection->beginTransaction();
        try {
            $query = "INSERT INTO tickets(TicketCode, SiteID, TerminalID, MID, Amount, Source, CreatedByAID, ValidFromDate, ValidToDate, TrackingID, Status) VALUES(:ticket_code, :site_id, :terminal_id, :mid, :amount, :source, :created_by_aid, :valid_from_date, :valid_to_date, :tracking_id, :status)";
            $sql = $this->_connection->createCommand($query);

            $sql->bindValues(array(
                ":ticket_code" => $ticketCode,
                ":site_id" => $siteID,
                ":terminal_id" => $terminalID,
                ":mid" => $MID,
                ":amount" => $amount,
                ":source" => $source,
                ":created_by_aid" => $updatedByAID,
                ":valid_from_date" => $validFromDate,
                ":valid_to_date" => $validToDate,
                ":tracking_id" => $trackingID,
                ":status" => $status
            ));
            $sql->execute();
            $lastInsertedID = $this->_connection->getLastInsertID();

            try {
                $beginTrans2 = $this->_connection2->beginTransaction();
                if ($status == 1) {
                    $ticketOutStatus = 1;
                } else if ($status == 2) {
                    $ticketOutStatus = 2;
                } else {
                    $ticketOutStatus = 2;
                }
                $query = "INSERT INTO ticketouts(StackerSummaryID, TerminalName, TicketCode, Amount, DateCreated, CreatedByAID, Status) VALUES(:stacker_batch_id, :terminal_name, :ticket_code, :amount, :date_created, :createdbyAID, :ticket_out_status)";
                $sql = $this->_connection2->createCommand($query);

                $sql->bindValues(array(
                    "stacker_batch_id" => $stackerBatchID,
                    ":terminal_name" => $terminalName,
                    ":ticket_code" => $ticketCode,
                    ":amount" => $amount,
                    ":date_created" => $dateUpdated,
                    ":createdbyAID" => $updatedByAID,
                    ":ticket_out_status" => $ticketOutStatus,
                ));
                $sql->execute();

                try {
                    $query = "UPDATE stackersummary SET TicketCode = :ticket_code, Withdrawal = :amount WHERE StackerSummaryID = :stacker_batch_id";
                    $sql = $this->_connection2->createCommand($query);

                    $sql->bindValues(array(
                        ":ticket_code" => $ticketCode,
                        ":amount" => $amount,
                        ":stacker_batch_id" => $stackerBatchID,
                    ));
                    $sql->execute();
                    try {
                        $beginTrans->commit();
                        $beginTrans2->commit();
                        return $lastInsertedID;
                    } catch (Exception $e) {
                        $beginTrans->rollback();
                        $beginTrans2->rollback();
                        Utilities::log($e->getMessage());
                        return false;
                    }
                    return true;
                } catch (Exception $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
            } catch (Exception $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }

    public function isTrackingIDExists($trackingID) {
        $query = 'SELECT COUNT(TicketID) ctrtracking FROM tickets WHERE TrackingID = :trackingid OR TrackingID2 = :trackingid';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":trackingid" => $trackingID
        ));

        $result = $sql->queryRow();

        return $result['ctrtracking'];
    }

    public function chkTicketAvailable($ticketCode) {
        $sql = "SELECT Status, Amount, IsCreditable, ValidFromDate, ValidToDate, SiteID,
                DateCreated FROM tickets WHERE TicketCode = :ticketCode";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':ticketCode' => $ticketCode));
        return $command->queryRow();
    }

    public function usedTicket($siteID, $terminalID, $mid, $aid, $trackingID, $ticketCode, $ticketStatus) {
        $sql = "UPDATE tickets SET TISiteID = :siteid, TITerminalID = :terminalid, TIMID = :mid, 
                DateUpdated = NOW(6), UpdatedByAID = :aid, TrackingID2 = :trackingid,
                Status = 3, OldStatus = :status
                WHERE TicketCode = :ticketcode AND Status = :status";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':siteid' => $siteID,
            ':terminalid' => $terminalID,
            ':mid' => $mid,
            ':aid' => $aid,
            ':trackingid' => $trackingID,
            ':ticketcode' => $ticketCode,
            ':status' => $ticketStatus));
        return $command->execute();
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $voucherTicketBarcode
     * @return array
     */
    public function getTicketDataByCodeAndMID($voucherTicketBarcode, $MID) {
        $sql = "SELECT TicketID, TicketCode, TerminalID, TrackingID, Amount, DateCreated, ValidFromDate, ValidToDate, CreatedByAID, Source, IsCreditable, Status FROM tickets WHERE TicketCode = :voucherTicketBarcode
                AND MID = :mid";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindValue(":mid", $MID);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param string $voucherTicketBarcode
     * @param int $siteID
     * @return array
     */
    public function getTicketDataByCodeSiteIDAndMID($voucherTicketBarcode, $siteID, $MID) {
        $sql = "SELECT TicketID, TicketCode, TerminalID, TrackingID, Amount, DateCreated, 
                ValidFromDate, ValidToDate, CreatedByAID, IsCreditable, Status
                FROM tickets
                WHERE TicketCode = :voucherTicketBarcode AND SiteID = :site_id AND MID = :mid;";
        $command = $this->_connection->createCommand($sql);
        $command->bindParam(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindParam(":site_id", $siteID);
        $command->bindParam(":mid", $MID);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param string $voucherTicketBarcode
     * @param int $siteID
     * @return array
     */
    public function getTicketDataByCodeSiteIDMIDAndTerminalID($voucherTicketBarcode, $siteID, $MID, $terminalID) {
        $sql = "SELECT TicketID, TicketCode, TerminalID, TrackingID, Amount, DateCreated, 
                ValidFromDate, ValidToDate, CreatedByAID, IsCreditable, Status
                FROM tickets
                WHERE TicketCode = :voucherTicketBarcode AND SiteID = :site_id AND MID = :mid AND TerminalID = :terminal_id;";
        $command = $this->_connection->createCommand($sql);
        $command->bindParam(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindParam(":site_id", $siteID);
        $command->bindParam(":mid", $MID);
        $command->bindParam(":terminal_id", $terminalID);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param string $voucherTicketBarcode
     * @param int $siteID
     * @return array
     */
    public function getTicketDataByCodeAndTerminalID($voucherTicketBarcode, $terminalID) {
        $sql = "SELECT TicketID, TicketCode, TerminalID, TrackingID, Amount, DateCreated, 
                ValidFromDate, ValidToDate, CreatedByAID, IsCreditable, Status
                FROM tickets
                WHERE TicketCode = :voucherTicketBarcode AND TerminalID = :terminal_id;";
        $command = $this->_connection->createCommand($sql);
        $command->bindParam(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindParam(":terminal_id", $terminalID);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param string $voucherTicketBarcode
     * @param int $siteID
     * @return array
     */
    public function getTicketDataBySiteID($voucherTicketBarcode, $siteID) {
        $sql = "SELECT TicketID, TicketCode, TerminalID, TrackingID, Amount, DateCreated, 
                ValidFromDate, ValidToDate, CreatedByAID, IsCreditable, Status
                FROM tickets
                WHERE TicketCode = :voucherTicketBarcode AND SiteID = :site_id;";
        $command = $this->_connection->createCommand($sql);
        $command->bindParam(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindParam(":site_id", $siteID);
        $result = $command->queryRow();

        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param string $voucherTicketBarcode
     * @param int $siteID
     * @return array
     */
    public function getTicketDataByTerminalID($voucherTicketBarcode, $terminalID) {
        $sql = "SELECT TicketID, TicketCode, TerminalID, TrackingID, Amount, DateCreated, 
                ValidFromDate, ValidToDate, CreatedByAID, IsCreditable, Status
                FROM tickets
                WHERE TicketCode = :voucherTicketBarcode AND TerminalID = :terminal_id;";
        $command = $this->_connection->createCommand($sql);
        $command->bindParam(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindParam(":terminal_id", $terminalID);
        $result = $command->queryRow();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param string $voucherTicketBarcode
     * @param int $siteID
     * @return array
     */
    public function getTicketDataByMID($voucherTicketBarcode, $MID) {
        $sql = "SELECT TicketID, TicketCode, TerminalID, TrackingID, Amount, DateCreated, 
                ValidFromDate, ValidToDate, CreatedByAID, IsCreditable, Status
                FROM tickets
                WHERE TicketCode = :voucherTicketBarcode AND MID = :mid;";
        $command = $this->_connection->createCommand($sql);
        $command->bindParam(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindParam(":mid", $MID);
        $result = $command->queryRow();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param string $voucherTicketBarcode
     * @param int $siteID
     * @return array
     */
    public function getTicketDataByExpiredStatus($voucherTicketBarcode, $status) {
        $sql = "SELECT TicketID, TicketCode, TerminalID, TrackingID, Amount, DateCreated, 
                ValidFromDate, ValidToDate, CreatedByAID, IsCreditable, Status
                FROM tickets
                WHERE TicketCode = :voucherTicketBarcode AND Status = :status;";
        $command = $this->_connection->createCommand($sql);
        $command->bindParam(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindParam(":status", $status);
        $result = $command->queryRow();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param string $voucherTicketBarcode
     * @param int $siteID
     * @return array
     */
    public function getTicketDataByExpirationDate($voucherTicketBarcode, $validToDate) {
        $sql = "SELECT TicketID, TicketCode, TerminalID, TrackingID, Amount, DateCreated, 
                ValidFromDate, ValidToDate, CreatedByAID, IsCreditable, Status
                FROM tickets
                WHERE TicketCode = :voucherTicketBarcode AND ValidToDate >= :valid_to_date;";
        $command = $this->_connection->createCommand($sql);
        $command->bindParam(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindParam(":valid_to_date", $validToDate);
        $result = $command->queryRow();
        return $result;
    }

    /**
     * Regenerate Ticket Codes
     * @param int $count Remaining number of tickets to generate
     * @param int $iscreditable Yes | No
     * @param int $ticketbatch Ticket BatchID
     * @param type $user AID of the user
     * @return array TransCode and TransMsg
     * @author Mark Kenneth Esguerra
     * @date November 5, 2013
     */
    public function regenerateTickets($count, $iscreditable, $ticketbatch, $user) {
        $model = new GenerationToolModel();

        $connection = Yii::app()->db;

        $pdo = $connection->beginTransaction();
        //Generate Tickets
        for ($i = 0; $count > $i; $i++) {
            //Generate Coupon Code
            $code = "";
//            for ($x = 0; 6 > $x; $x++)
//            {
//                $num = mt_rand(1, 36);
//                $code .= $model->getRandValue($num);
//            }
            $code = $model->mt_rand_str(6);
            $ticketcode = "T" . $code;

            try {
                $secondquery = "INSERT INTO tickets (TicketBatchID,
                                                     TicketCode,
                                                     Status,
                                                     DateCreated,
                                                     CreatedByAID,
                                                     IsCreditable
                                ) VALUES (:ticketbatch,
                                          :ticketcode,
                                          1,
                                          NOW(6),
                                          :AID,
                                          :iscreditable)";
                $command = $connection->createCommand($secondquery);
                $command->bindParam(":ticketbatch", $ticketbatch);
                $command->bindParam(":ticketcode", $ticketcode);
                $command->bindParam(":AID", $user);
                $command->bindParam(":iscreditable", $iscreditable);
                $secondresult = $command->execute();
                if ($secondresult > 0) {
                    continue;
                } else {
                    $pdo->rollback();
                    return array('TransCode' => 0,
                        'TransMsg' => 'An error occured while regenerating the tickets [0001]');
                }
            } catch (CDbException $e) {
                //Check if error is 'Duplicate Key constraints violation'
                $errcode = $e->getCode();
                if ($errcode == 23000) {
                    try {
                        $pdo->commit();
                        //get total ticket count
                        $totalticket = "SELECT TicketCount FROM ticketbatch WHERE TicketBatchID = :ticketbatchID";
                        $sql = $connection->createCommand($totalticket);
                        $sql->bindParam(":ticketbatchID", $ticketbatch);
                        $totalticketcount = $sql->queryRow();
                        //get generated tickets after duplication
                        $querycount = "SELECT COUNT(TicketID) as TicketCount FROM tickets
                                       WHERE TicketBatchID = :ticketbatch";

                        $sql = $connection->createCommand($querycount);
                        $sql->bindParam(":ticketbatch", $ticketbatch);
                        $ticketcount = $sql->queryAll();
                        $remainingtickets = (int) $totalticketcount['TicketCount'] - (int) $ticketcount[0]['TicketCount'];

                        return array('TransCode' => 2,
                            'TransMsg' => 'Ticket Code already exist. There are ' . $remainingtickets . ' remaining tickets 
                                         to generate. Click Retry to continue',
                            'TicketBatchID' => $ticketbatch,
                            'RemainingTickets' => $remainingtickets,
                            'IsCreditable' => $iscreditable);
                    } catch (CDbException $e) {
                        $pdo->rollback();
                        return array('TransCode' => 0,
                            'TransMsg' => 'An error occured while regenerating the tickets [0001]');
                    }
                } else {
                    $pdo->rollback();
                    return array('TransCode' => 0, 'TransMsg' => 'An error occured while updating the status');
                }
            }
        }
        try {
            $pdo->commit();

            AuditLog::logTransactions(31, "Generate Tickets");
            return array('TransCode' => 1,
                'TransMsg' => 'Tickets successfully generated');
        } catch (CDbException $e) {
            $pdo->rollback();
            return array('TransCode' => 0,
                'TransMsg' => 'An error occured while regenerating the tickets [0003]');
        }
    }

    /**
     * Get Ticket Details. For checking if ticket is already exist
     * @param mixed $ticketcode Ticket Code
     * @return array Status and Ticket Code
     * @author Mark Kenneth Esguerra [02-25-14]
     */
    public function getTicketDetails($ticketcode) {
        $connection = Yii::app()->db;
        //Check if entered ticket code is existing in database
        $query = "SELECT TicketID, TicketCode, Status, Amount FROM tickets 
                  WHERE TicketCode = :ticketcode";
        $command = $connection->createCommand($query);
        $command->bindParam(":ticketcode", $ticketcode);
        $result = $command->queryRow();

        if (is_array($result)) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Change the Ticket Status after checking inputs validity
     * @param int $user AID of the user
     * @param mixed $ticketcode Unique code of the Ticket
     * @param int $previoustat The current status of the ticket that will going to be the previous after process
     * @param type $status The entered status for the ticket
     * @return array The result
     * @author Mark Kenneth Esguerra [02-26-2014]
     */
    public function changeTicketStatus($user, $ticketcode, $previoustat, $status) {
        $connection = Yii::app()->db;

        $pdo = $connection->beginTransaction();

        try {
            //Update the status and store the previous status in OldStatus field
            $statupdate = "UPDATE tickets SET Status = :newstatus,       
                                              OldStatus = :previoustat, 
                                              DateUpdated = NOW(6), 
                                              UpdatedByAID = :aid
                           WHERE TicketCode = :ticketcode
                          ";
            $sql = $connection->createCommand($statupdate);
            $sql->bindValues(array(
                ':newstatus' => $status,
                ':previoustat' => $previoustat,
                ':aid' => $user,
                ':ticketcode' => $ticketcode
            ));
            $result = $sql->execute();
            if ($result > 0) {
                try {
                    $pdo->commit();

                    AuditLog::logTransactions(34, " - TicketCode: " . $ticketcode . "; Status: " . $this->nameStatus($status) . "");
                    return array('TransCode' => 1,
                        'TransMsg' => 'The Ticket ' . $ticketcode . ' change status is successful.');
                } catch (CDbException $e) {
                    $pdo->rollback();
                    return array('TransCode' => 0,
                        'TransMsg' => 'An error occured while updating the status.');
                }
            } else {
                $pdo->rollback();
                return array('TransCode' => 0,
                    'TransMsg' => 'Ticket ' . $ticketcode . ' status is not successfully changed.');
            }
        } catch (CDbException $e) {
            $pdo->rollback();
            return array('TransCode' => 0,
                'TransMsg' => 'An error occured while updating the status.');
        }
    }

    /**
     * Convert numeric status into string status
     * @param int $status Numeric status
     * @return string Status
     * @author Mark Kenneth Esguerra [02-27-14]
     */
    public static function nameStatus($status) {
        switch ($status) {
            case 1:
                $stat = "Active";
                break;
            case 2:
                $stat = "Void";
                break;
            case 3:
                $stat = "Used";
                break;
            case 4:
                $stat = "Encashment";
                break;
            case 5:
                $stat = "Cancelled";
                break;
            case 6:
                $stat = "Reimbursed";
                break;
            case 7:
                $stat = "Expired";
                break;
            default:
                $stat = "Invalid Status";
                break;
        }
        return $stat;
    }

    /**
     * Get Old Status. This is used to identify the previous status of the cancelled ticket
     * @param mixed $ticketcode Unique code of the ticket
     * @return string Old Status
     * @author Mark Kenneth Esguerra [02-27-14]
     */
    public function getOldStatus($ticketcode) {
        $connection = Yii::app()->db;

        $query = "SELECT OldStatus FROM tickets 
                 WHERE TicketCode = :ticketcode";
        $command = $connection->createCommand($query);
        $command->bindParam(":ticketcode", $ticketcode);
        $result = $command->queryRow();
        //Check if has result
        if (is_array($result)) {
            return $result['OldStatus'];
        } else {
            return "";
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 03/19/14
     * @param str $ticketCode
     * @return int
     */
    public function getAmountByTicketCode($ticketCode) {
        $sql = 'SELECT Amount FROM tickets WHERE TicketCode = :ticket_code';
        $command = $this->_connection->createCommand($sql);
        $command->bindParam(":ticket_code", $ticketCode);
        $result = $command->queryRow();

        if (!isset($result)) {
            return $result['Amount'];
        } else {
            return 0;
        }
    }

    /**
     * Get TerminalCode only, useful for ticket padding
     * @param mixed $ticketcode Ticket Code
     * @return array Status and Ticket Code
     * @author JunJun S. Hernandez [02-25-14]
     */
    public function getTerminalCodeByTicketCode($ticketcode) {
        $connection = Yii::app()->db;
        //Check if entered ticket code is existing in database
        $query = "SELECT tm.TerminalCode FROM vouchermanagement.tickets tk
                    INNER JOIN npos.terminals tm ON tm.TerminalID = tk.TerminalID WHERE tk.TicketCode = :ticketcode";
        $command = $connection->createCommand($query);
        $command->bindParam(":ticketcode", $ticketcode);
        $result = $command->queryRow();

        return $result['TerminalCode'];
    }

    /**
     * Get Active Tickets Details
     * @param mixed $sitecode Site code
     * @return array Details of Active Tickets
     * @author Mark Kenneth Esguerra
     */
    public function getActiveTicketsDetails($sitecode, $start = null, $limit = null) {
        if (is_null($start) && is_null($limit))
        {
            $query = "SELECT t.TicketID, t.TicketCode, s.SiteCode, t.DateCreated, t.Amount, 
                        t.ValidFromDate, t.ValidToDate 
                        FROM tickets t
                        INNER JOIN npos.terminals tmnl ON t.TerminalID = tmnl.TerminalID 
                        INNER JOIN npos.sites s ON t.SiteID = s.SiteID 
                        WHERE t.Status IN (1, 2) AND t.SiteID = :siteID  
                        ORDER BY t.DateCreated DESC 
                        ";
        }
        else
        {
            $query = "SELECT t.TicketID, t.TicketCode, s.SiteCode, t.DateCreated, t.Amount, 
                        t.ValidFromDate, t.ValidToDate 
                        FROM tickets t
                        INNER JOIN npos.terminals tmnl ON t.TerminalID = tmnl.TerminalID 
                        INNER JOIN npos.sites s ON t.SiteID = s.SiteID 
                        WHERE t.Status IN (1, 2) AND t.SiteID = :siteID  
                        ORDER BY t.DateCreated DESC 
                        LIMIT $start, $limit 
                        ";
        }
        $command = $this->_connection->createCommand($query);
        $command->bindValue(":siteID", $sitecode);
        $result = $command->queryAll();

        return $result;
    }
    /**
     * Get printed tickets within the cut off
     * @param int $sitecode Converted SiteID
     * @param type $tdate Transaction date
     * @author Mark Kenneth Esguerra
     * @date May 22, 2014
     */
    public function getNumberOfPrintedTickets($tdatefrom, $tdateto, $sitecode)
    {
        if ($sitecode == 'All')
        {
            $sql = "SELECT COUNT(TicketID) as PrintedTickets, SUM(Amount) as Value FROM tickets 
                    WHERE DateCreated >= :tdateFrom AND DateCreated < :tdateTo";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
            $command->bindValue(":tdateTo", $tdateto." 06:00:00");
        }
        else if (is_array($sitecode))
        {
            $sitecode = implode(",", $sitecode);
            
            $sql = "SELECT COUNT(TicketID) as PrintedTickets, SUM(Amount) as Value FROM tickets 
                    WHERE DateCreated >= :tdateFrom AND DateCreated < :tdateTo 
                    AND SiteID IN ($sitecode)";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
            $command->bindValue(":tdateTo", $tdateto." 06:00:00");
        }
        else
        {
            $sql = "SELECT COUNT(TicketID) as PrintedTickets, SUM(Amount) as Value FROM tickets 
                    WHERE DateCreated >= :tdateFrom AND DateCreated < :tdateTo 
                    AND SiteID = :siteid";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
            $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            $command->bindValue(":siteid", $sitecode);
        } 
        $result = $command->queryRow();

        return $result;
    }
    /**
     * Get total number of Used tickets within the cut off
     * @param date $tdatefrom The date from
     * @param date $tdateto The date to
     * @param type $sitecode Site ID
     * @param int if for running tickets, regardless of date created
     * @return array Result
     * @author Mark Kenneth Esguerra
     * @date May 23, 2013
     */
    public function getNumberOfUsedTickets($tdatefrom, $tdateto, $sitecode, $isrunning = null)
    {
        if (is_null($isrunning))
        {
            if ($sitecode == 'All')
            {
                $sql = "SELECT COUNT(TicketID) as UsedTickets, SUM(Amount) as Value FROM tickets 
                        WHERE DateUpdated >= :tdateFrom AND DateUpdated < :tdateTo 
                        AND DateCreated >= :tdateFrom AND DateCreated < :tdateTo 
                        AND Status = 3";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
                $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            }
            else if (is_array($sitecode))
            {
                $sitecode = implode(",", $sitecode);
                
                $sql = "SELECT COUNT(TicketID) as UsedTickets, SUM(Amount) as Value FROM tickets 
                        WHERE DateUpdated >= :tdateFrom AND DateUpdated < :tdateTo 
                        AND DateCreated >= :tdateFrom AND DateCreated < :tdateTo 
                        AND Status = 3 AND SiteID IN ($sitecode)";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
                $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            }
            else
            {
                $sql = "SELECT COUNT(TicketID) as UsedTickets, SUM(Amount) as Value FROM tickets 
                        WHERE DateUpdated >= :tdateFrom AND DateUpdated < :tdateTo 
                        AND DateCreated >= :tdateFrom AND DateCreated < :tdateTo 
                        AND Status = 3 
                        AND SiteID = :siteid";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
                $command->bindValue(":tdateTo", $tdateto." 06:00:00");
                $command->bindValue(":siteid", $sitecode);
            } 
        }
        else
        {
            if ($sitecode == 'All')
            {
                $sql = "SELECT COUNT(TicketID) as UsedTickets, SUM(Amount) as Value FROM tickets 
                        WHERE DateUpdated >= :tdateFrom AND DateUpdated < :tdateTo 
                        AND Status = 3";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
                $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            }
            else if (is_array($sitecode))
            {
                $sitecode = implode(",", $sitecode);
                
                $sql = "SELECT COUNT(TicketID) as UsedTickets, SUM(Amount) as Value FROM tickets 
                        WHERE DateUpdated >= :tdateFrom AND DateUpdated < :tdateTo 
                        AND Status = 3 AND SiteID IN ($sitecode)";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
                $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            }
            else
            {
                $sql = "SELECT COUNT(TicketID) as UsedTickets, SUM(Amount) as Value FROM tickets 
                        WHERE DateUpdated >= :tdateFrom AND DateUpdated < :tdateTo 
                        AND Status = 3 
                        AND SiteID = :siteid";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
                $command->bindValue(":tdateTo", $tdateto." 06:00:00");
                $command->bindValue(":siteid", $sitecode);
            } 
        }
        $result = $command->queryRow();

        return $result;
    }
    /**
     * Get total number of Encashed tickets within the cut off
     * @param date $tdatefrom The date from
     * @param date $tdateto The date to
     * @param type $sitecode Site ID
     * @param int if for running tickets, regardless of date created
     * @return array Result
     * @author Mark Kenneth Esguerra
     * @date May 23, 2013
     */
    public function getNumberOfEncashedTickets($tdatefrom, $tdateto, $sitecode, $isrunning = null)
    {
        if (is_null($isrunning))
        {
            if ($sitecode == 'All')
            {
                $sql = "SELECT COUNT(TicketID) as EncashedTickets, SUM(Amount) as Value FROM tickets 
                        WHERE DateEncashed >= :tdateFrom AND DateEncashed < :tdateTo 
                        AND DateCreated >= :tdateFrom AND DateCreated < :tdateTo 
                        AND Status = 4";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
                $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            }
            else if (is_array($sitecode))
            {
                $sitecode = implode(",", $sitecode);
                
                $sql = "SELECT COUNT(TicketID) as EncashedTickets, SUM(Amount) as Value FROM tickets 
                        WHERE DateEncashed >= :tdateFrom AND DateEncashed < :tdateTo 
                        AND DateCreated >= :tdateFrom AND DateCreated < :tdateTo 
                        AND Status = 4 
                        AND SiteID IN ($sitecode)";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
                $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            }
            else
            {
                
                $sql = "SELECT COUNT(TicketID) as EncashedTickets, SUM(Amount) as Value FROM tickets 
                        WHERE DateEncashed >= :tdateFrom AND DateEncashed < :tdateTo 
                        AND DateCreated >= :tdateFrom AND DateCreated < :tdateTo 
                        AND Status = 4 
                        AND SiteID = :siteid";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
                $command->bindValue(":tdateTo", $tdateto." 06:00:00");
                $command->bindValue(":siteid", $sitecode);
            } 
        }
        else
        {
            if ($sitecode == 'All')
            {
                $sql = "SELECT COUNT(TicketID) as EncashedTickets, SUM(Amount) as Value FROM tickets 
                        WHERE DateEncashed >= :tdateFrom AND DateEncashed < :tdateTo 
                        AND Status = 4";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
                $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            }
            else if (is_array($sitecode))
            {
                $sitecode = implode(",", $sitecode);
                
                $sql = "SELECT COUNT(TicketID) as EncashedTickets, SUM(Amount) as Value FROM tickets 
                        WHERE DateEncashed >= :tdateFrom AND DateEncashed < :tdateTo 
                        AND Status = 4 
                        AND SiteID IN ($sitecode)";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
                $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            }
            else
            {
                $sql = "SELECT COUNT(TicketID) as EncashedTickets, SUM(Amount) as Value FROM tickets 
                        WHERE DateEncashed >= :tdateFrom AND DateEncashed < :tdateTo 
                        AND Status = 4 
                        AND SiteID = :siteid";
                $command = $this->_connection->createCommand($sql);
                $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
                $command->bindValue(":tdateTo", $tdateto." 06:00:00");
                $command->bindValue(":siteid", $sitecode);
            } 
        }
        $result = $command->queryRow();

        return $result;
    }
    /**
     * Get Expired Ticket
     * @param type $tdatefrom
     * @param type $tdateto
     * @param type $sitecode
     * @param type $isrunning
     * @return type
     * @author Mark Kenneth Esguerra
     * @date July 14, 2014
     */
    public function getNumberOfExpiredTickets($tdatefrom, $tdateto, $sitecode, $isrunning = null)
    {
        if ($sitecode == 'All')
        {
            $sql = "SELECT COUNT(TicketID) as ExpiredTickets, SUM(Amount) as Value FROM tickets 
                    WHERE ValidToDate = :validtodate AND Status IN (1, 2)";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":validtodate", $tdatefrom." 23:59:59.000000");
        }
        else if (is_array($sitecode))
        {
            $sitecode = implode(",", $sitecode);

            $sql = "SELECT COUNT(TicketID) as ExpiredTickets, SUM(Amount) as Value FROM tickets 
                    WHERE ValidToDate = :validtodate AND Status IN (1, 2) 
                    AND SiteID IN ($sitecode)";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":validtodate", $tdatefrom." 23:59:59.000000");
        }
        else
        {

            $sql = "SELECT COUNT(TicketID) as ExpiredTickets, SUM(Amount) as Value FROM tickets 
                    WHERE ValidToDate = :validtodate AND Status IN (1, 2) 
                    AND SiteID = :siteid";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":siteid", $sitecode);
            $command->bindValue(":validtodate", $tdatefrom." 23:59:59.000000");
        }
        $result = $command->queryRow();

        return $result;
    }
    /**
     * Get the Used and Encashed Tickets with its details to 
     * be displayed in transaction details
     * @param type $tdatefrom
     * @param type $tdateto
     * @param type $sitecode
     */
    public function getTicketRedemptions($tdatefrom, $tdateto, $sitecode)
    {
        
        if ($sitecode == 'All')
        {
            $sql = "SELECT t.TicketID, 
                       trml.TerminalName, 
                       s.SiteCode, 
                       t.TicketCode, 
                       t.DateCreated, 
                       t.Amount, 
                       t.ValidToDate, 
                       t.Status, 
                       t.DateUpdated, 
                       t.DateEncashed
                    FROM tickets t 
                    INNER JOIN npos.sites s ON t.SiteID = s.SiteID 
                    INNER JOIN npos.terminals trml ON t.TerminalID = trml.TerminalID 
                    WHERE t.DateUpdated >= :tdateFrom AND t.DateUpdated < :tdateTo 
                    AND t.DateCreated >= :tdateFrom AND t.DateCreated < :tdateTo 
                    AND t.Status = 3 

                    UNION ALL 

                    SELECT t.TicketID, 
                           trml.TerminalName, 
                           s.SiteCode, 
                           t.TicketCode, 
                           t.DateCreated, 
                           t.Amount, 
                           t.ValidToDate, 
                           t.Status, 
                           t.DateUpdated, 
                           t.DateEncashed
                    FROM tickets t 
                    INNER JOIN npos.sites s ON t.SiteID = s.SiteID 
                    INNER JOIN npos.terminals trml ON t.TerminalID = trml.TerminalID 
                    WHERE t.DateEncashed >= :tdateFrom AND t.DateEncashed < :tdateTo 
                    AND t.DateCreated >= :tdateFrom AND t.DateCreated < :tdateTo 
                    AND t.Status = 4
               ";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
            $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            $result = $command->queryAll();
        }
        else if (is_array($sitecode))
        {
            $sitecode = implode(",", $sitecode);
            
            $sql = "SELECT t.TicketID, 
                       trml.TerminalName, 
                       s.SiteCode, 
                       t.TicketCode, 
                       t.DateCreated, 
                       t.Amount, 
                       t.ValidToDate, 
                       t.Status, 
                       t.DateUpdated, 
                       t.DateEncashed
                    FROM tickets t 
                    INNER JOIN npos.sites s ON t.SiteID = s.SiteID 
                    INNER JOIN npos.terminals trml ON t.TerminalID = trml.TerminalID 
                    WHERE t.DateUpdated >= :tdateFrom AND t.DateUpdated < :tdateTo 
                    AND t.DateCreated >= :tdateFrom AND t.DateCreated < :tdateTo 
                    AND t.Status = 3 AND t.SiteID IN ($sitecode)

                    UNION ALL 

                    SELECT t.TicketID, 
                           trml.TerminalName, 
                           s.SiteCode, 
                           t.TicketCode, 
                           t.DateCreated, 
                           t.Amount, 
                           t.ValidToDate, 
                           t.Status, 
                           t.DateUpdated, 
                           t.DateEncashed
                    FROM tickets t 
                    INNER JOIN npos.sites s ON t.SiteID = s.SiteID 
                    INNER JOIN npos.terminals trml ON t.TerminalID = trml.TerminalID 
                    WHERE t.DateEncashed >= :tdateFrom AND t.DateEncashed < :tdateTo 
                    AND t.DateCreated >= :tdateFrom AND t.DateCreated < :tdateTo 
                    AND t.Status = 4 AND t.SiteID IN ($sitecode)
               ";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
            $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            $result = $command->queryAll();        
        }
        else
        {
            $sql = "SELECT t.TicketID, 
                        trml.TerminalName, 
                        s.SiteCode, 
                        t.TicketCode, 
                        t.DateCreated, 
                        t.Amount, 
                        t.ValidToDate, 
                        t.Status, 
                        t.DateUpdated, 
                        t.DateEncashed
                    FROM tickets t 
                    INNER JOIN npos.sites s ON t.SiteID = s.SiteID 
                    INNER JOIN npos.terminals trml ON t.TerminalID = trml.TerminalID 
                    WHERE t.DateUpdated >= :tdateFrom AND t.DateUpdated < :tdateTo 
                    AND t.DateCreated >= :tdateFrom AND t.DateCreated < :tdateTo 
                    AND t.Status = 3 AND t.SiteID = :siteID

                    UNION ALL 

                    SELECT t.TicketID, 
                           trml.TerminalName, 
                           s.SiteCode, 
                           t.TicketCode, 
                           t.DateCreated, 
                           t.Amount, 
                           t.ValidToDate, 
                           t.Status, 
                           t.DateUpdated, 
                           t.DateEncashed
                    FROM tickets t 
                    INNER JOIN npos.sites s ON t.SiteID = s.SiteID 
                    INNER JOIN npos.terminals trml ON t.TerminalID = trml.TerminalID 
                    WHERE t.DateEncashed >= :tdateFrom AND t.DateEncashed < :tdateTo 
                    AND t.DateCreated >= :tdateFrom AND t.DateCreated < :tdateTo 
                    AND t.Status = 4 AND t.SiteID = :siteID
                ";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
            $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            $command->bindValue(":siteID", $sitecode);
            $result = $command->queryAll();
        }
        return $result;
    }
    /**
     * Get Unused Tickets. Tickets that are not used or encashed on the day it was printed
     * @param type $tdatefrom
     * @param type $tdateto
     * @param type $sitecode
     * @return type
     */
    public function getUnusedTickets($tdatefrom, $tdateto, $sitecode)
    {
        
        if ($sitecode == 'All')
        {
            $sql = "SELECT COUNT(TicketID) as UnusedTickets, SUM(Amount) as Value FROM tickets 
                    WHERE DateCreated >= :tdateFrom AND DateCreated < :tdateTo 
                    AND DateUpdated IS NULL AND DateEncashed IS NULL";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
            $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            $result = $command->queryRow();
        }
        else if (is_array($sitecode))
        {
            $sitecode = implode(",", $sitecode);
            
            $sql = "SELECT COUNT(TicketID) as UnusedTickets, SUM(Amount) as Value FROM tickets 
                    WHERE DateCreated >= :tdateFrom AND DateCreated < :tdateTo 
                    AND DateUpdated IS NULL AND DateEncashed IS NULL 
                    AND SiteID IN ($sitecode)";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
            $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            $result = $command->queryRow();
        }
        else
        {
            $sql = "SELECT COUNT(TicketID) as UnusedTickets, SUM(Amount) as Value FROM tickets 
                    WHERE DateCreated >= :tdateFrom AND DateCreated < :tdateTo 
                    AND DateUpdated IS NULL AND DateEncashed IS NULL AND 
                    SiteID = :siteID";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":tdateFrom", $tdatefrom." 06:00:00");
            $command->bindValue(":tdateTo", $tdateto." 06:00:00");
            $command->bindValue(":siteID", $sitecode);
            $result = $command->queryRow();
        }
                
        return $result;
    }
    public function getRunningActiveTickets($dateFrom, $dateTo, $sitecode)
    {
        if ($sitecode == "All")
        {
            $sql = "SELECT COUNT(TicketID) as RunningActive, SUM(Amount) as Value 
                    FROM tickets 
                    WHERE DateCreated >= :datefrom AND DateCreated < :dateto AND 
                    Status IN (1, 2)";   
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":datefrom", $dateFrom);
            $command->bindValue(":dateto", $dateTo);
            $result = $command->queryRow();
        } 
        else if (is_array($sitecode))
        {
            $sitecode = implode(",", $sitecode);
            
            $sql = "SELECT COUNT(TicketID) as RunningActive, SUM(Amount) as Value 
                    FROM tickets 
                    WHERE DateCreated >= :datefrom AND DateCreated < :dateto AND 
                    Status IN (1, 2) AND SiteID IN ($sitecode)";   
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":datefrom", $dateFrom);
            $command->bindValue(":dateto", $dateTo);
            $result = $command->queryRow();
        } 
        else
        {
            $sql = "SELECT COUNT(TicketID) as RunningActive, SUM(Amount) as Value 
                    FROM tickets 
                    WHERE DateCreated >= :datefrom AND DateCreated < :dateto AND 
                    SiteID = :siteID 
                    AND Status IN (1, 2)";   
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":datefrom", $dateFrom);
            $command->bindValue(":dateto", $dateTo);
            $command->bindValue(":siteID", $sitecode);
            $result = $command->queryRow();
        }
        
        return $result;
    }
}

?>