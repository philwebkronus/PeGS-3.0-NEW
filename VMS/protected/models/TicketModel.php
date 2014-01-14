<?php

/**
 * Description of TicketModel
 *
 * @author jshernandez
 */
class TicketModel extends CFormModel {

    public $_connection;
    public $_connection2;

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
    public function getAllUsedTicketList($date) {
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
    public function getUsedTicketListBySite($site, $date) {
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
        $result = $command->queryAll();

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
    public function updateTicketData($ticketID, $ticketCode, $siteID, $terminalID, $terminalName, $mid, $amount, $source, $dateUpdated, $updatedByAID, $validFromDate, $validToDate, $trackingID, $status, $stackerBatchID) {
        $beginTrans = $this->_connection->beginTransaction();
        try {
            $query = "UPDATE tickets SET SiteID = :site_id, TerminalID = :terminal_id,
                                             MID = :mid, Amount = :amount,
                                             Source = :source, DateUpdated = :date_updated,
                                             UpdatedByAID = :updated_by_aid, ValidFromDate = :valid_from_date,
                                             ValidToDate = :valid_to_date, TrackingID = :tracking_id, Status = :status
                                             WHERE TicketID = :ticket_id";
            $sql = $this->_connection->createCommand($query);

            $sql->bindValues(array(
                ":site_id" => $siteID,
                ":terminal_id" => $terminalID,
                ":mid" => $mid,
                ":amount" => $amount,
                ":source" => $source,
                ":date_updated" => $dateUpdated,
                ":updated_by_aid" => $updatedByAID,
                ":valid_from_date" => $validFromDate,
                ":valid_to_date" => $validToDate,
                ":tracking_id" => $trackingID,
                ":ticket_id" => $ticketID,
                ":status" => $status
            ));
            $sql->execute();

            try {
                $beginTrans2 = $this->_connection2->beginTransaction();
                $query = "INSERT INTO ticketouts(TerminalName, TicketCode) VALUES(:terminal_name, :ticket_code)";
                $sql = $this->_connection2->createCommand($query);

                $sql->bindValues(array(
                    ":terminal_name" => $terminalName,
                    ":ticket_code" => $ticketCode
                ));
                $sql->execute();
                try {
                    $query = "UPDATE stackersummary SET TicketCode = :ticket_code WHERE StackerSummaryID = :stacker_batch_id";
                    $sql = $this->_connection2->createCommand($query);

                    $sql->bindValues(array(
                        ":ticket_code" => $ticketCode,
                        ":stacker_batch_id" => $stackerBatchID,
                    ));
                    $sql->execute();
                    try {
                        $beginTrans->commit();
                        $beginTrans2->commit();
                        return true;
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

    public function usedTicket($siteID, $terminalID, $mid, $aid, $trackingID, $ticketCode) {
        $sql = "UPDATE tickets SET TISiteID = :siteid, TITerminalID = :terminalid, TIMID = :mid, 
                DateUpdated = now_usec(), UpdatedByAID = :aid, TrackingID2 = :trackingid,
                Status = 3
                WHERE TicketCode = :ticketcode AND Status = 1";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':siteid' => $siteID,
            ':terminalid' => $terminalID,
            ':mid' => $mid,
            ':aid' => $aid,
            ':trackingid' => $trackingID,
            ':ticketcode' => $ticketCode));
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
                                          NOW_USEC(),
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
                        $remainingtickets = (int)$totalticketcount['TicketCount'] - (int)$ticketcount[0]['TicketCount'];

                        return array('TransCode' => 2, 
                                     'TransMsg' => 'Ticket Code already exist. There are '.$remainingtickets.' remaining tickets 
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
        }
        catch(CDbException $e)
        {
            $pdo->rollback();
            return array('TransCode' => 0,
                         'TransMsg' => 'An error occured while regenerating the tickets [0003]');
        }
    }

}

?>
