<?php

/**
 * @author Noel Antonio
 * @dateCreated 03-13-2014
 */
class cron {

    public $_connection;
    public $_dbh;

    /**
     * This construct retrieves the connection string.
     * @param type $connString
     */
    public function __construct($connString) {
        $this->_connection = explode(",", $connString);
    }

    /**
     * This function opens the pdo connection.
     * @return boolean
     */
    public function open() {
        $dbh = $this->_connection[0];
        $user = $this->_connection[1];
        $pwd = $this->_connection[2];
        $this->_dbh = new PDO($dbh, $user, $pwd);
        if ($this->_dbh)
            return true;
        else
            return false;
    }

    /**
     * This function retrieves if there are existing pending cron
     * @param type $cronId
     * @return type
     */
    public function selectPendingCron($cronId) {
        $command = $this->_dbh->prepare("SELECT count(*) AS PendingCron FROM crontemp WHERE CronID = :cronId;");
        $command->bindParam(':cronId', $cronId);
        $command->execute();
        $result = $command->fetch(PDO::FETCH_LAZY);
        return $result;
    }

    /**
     * This function retrieves the parameter value based on the given
     * parameter ID.
     * @param type $paramId
     * @return type
     */
    public function selectParamValueById($paramId) {
        $command = $this->_dbh->prepare("SELECT ParamValue FROM ref_parameters WHERE ParamID = :paramId;");
        $command->bindParam(':paramId', $paramId);
        $command->execute();
        $result = $command->fetch(PDO::FETCH_LAZY);
        return $result;
    }

    /**
     * This function gets the total count of tickets on queue.
     * @return type
     */
    public function selectQueuedTickets() {
        $command = $this->_dbh->prepare("SELECT count(TicketID) AS QueuedTickets FROM tickets 
            WHERE Status = 1 AND SiteID IS NULL AND TerminalID IS NULL AND MID IS NULL AND Amount IS NULL;");
        $command->execute();
        $result = $command->fetch(PDO::FETCH_LAZY);
        return $result;
    }

    /**
     * This function is used to shuffle or randomized set of string
     * and returns in a particular length needed.
     * @param type $length
     * @return type
     */
    public function mt_rand_str($length) {
        $c = str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $s = '';
        $cl = strlen($c) - 1;
        for ($cl = strlen($c) - 1, $i = 0; $i < $length; $s .= $c[mt_rand(0, $cl)], ++$i)
            ;
        return $s;
    }

    /**
     * This function is used to insert pending cron to temp table.
     * @param type $cronId
     */
    public function createCronSession($cronId) {
        $pdo = $this->_dbh;

        try {
            $pdo->beginTransaction();

            $sql = "INSERT INTO crontemp (CronID, RunDate) VALUES (:cronId, NOW(6))";
            $command = $pdo->prepare($sql);
            $command->bindParam(":cronId", $cronId);
            $result = $command->execute();
            if ($result) {
                $sql2 = "INSERT INTO cronlogs (CronID, RunDate, Status) VALUES (:cronId, NOW(6), 0)";
                $command2 = $pdo->prepare($sql2);
                $command2->bindParam(":cronId", $cronId);
                $result2 = $command2->execute();
                if ($result2) {
                    $pdo->commit();
                    return true;
                } else {
                    $pdo->rollback();
                    print "Error inserting in cronlogs table.";
                    return false;
                }
            } else {
                $pdo->rollback();
                print "Error inserting in crontemp table.";
                return false;
            }
        } catch (PDOException $e) {
            $pdo->rollback();
            print "Error in creating cron session.";
            return false;
        }
    }

    /**
     * This function is used to generate tickets according to given count.
     * @param type $count
     * @param type $isCreditable
     * @param type $user
     */
    public function generateTickets($count, $isCreditable = 1, $user = 1) {
        $pdo = $this->_dbh;

        $pdo->beginTransaction();

        // Insert into ticketbatch
        $firstquery = "INSERT INTO ticketbatch (TicketCount, Status, CreatedByAID, DateCreated) 
                        VALUES (:count, 1, :AID, NOW(6))";
        $command = $pdo->prepare($firstquery);
        $command->bindParam(":count", $count);
        $command->bindParam(":AID", $user);
        $firstresult = $command->execute();

        // Get Last Insert TicketBatchID
        $ticketbatch = $pdo->lastInsertId();
        if ($firstresult) {
            for ($i = 0; $count > $i; $i++) {
                // Generate Coupon Code
                $code = "";
                $code = $this->mt_rand_str(6);
                $ticketcode = "T" . $code;
                try {
                    $secondquery = "INSERT INTO tickets (TicketBatchID, TicketCode, Status, DateCreated, CreatedByAID, IsCreditable) 
                                    VALUES (:ticketbatch, :ticketcode, 1, NOW(6), :AID, :iscreditable)";
                    $command2 = $pdo->prepare($secondquery);
                    $command2->bindParam(":ticketbatch", $ticketbatch);
                    $command2->bindParam(":ticketcode", $ticketcode);
                    $command2->bindParam(":AID", $user);
                    $command2->bindParam(":iscreditable", $isCreditable);
                    $secondresult = $command2->execute();

                    // Check if successfully inserted
                    if ($secondresult) {
                        continue;
                    } else {
                        $pdo->rollback();
                        print "Generation Error 1: Error inserting in tickets.";
                    }
                } catch (PDOException $e) {
                    // Check if error is 'Duplicate Key constraints violation'
                    $errcode = $e->getCode();
                    if ($errcode == 23000) {
                        try {
                            $pdo->commit();

                            $querycount = "SELECT COUNT(TicketID) as TicketCount FROM tickets
                                           WHERE TicketBatchID = :ticketbatch";
                            $sql = $pdo->prepare($querycount);
                            $sql->bindParam(":ticketbatch", $ticketbatch);
                            $sql->execute();
                            $ticketcount = $sql->fetch(PDO::FETCH_LAZY);
                            $remainingtickets = $count - $ticketcount['TicketCount'];

                            if ($remainingtickets > 0) {
                                $this->regenerateTickets($remainingtickets, $isCreditable, $ticketbatch, $user);
                            }
                        } catch (PDOException $e) {
                            $pdo->rollback();
                            print "Generation Error 2: Error on commit() tickets and ticket regeneration.";
                        }
                    } else {
                        $pdo->rollback();
                        print "Generation Error 3: Different error code received:" . $errcode;
                    }
                }
            }

            try {
                $pdo->commit();

                $result = $this->deleteCronSession(2);
                if ($result) {
                    $this->logTransactions(31, "CRON: Auto-generation of tickets.");
                    print "Ticket generation cron successfully processed!";
                } else {
                    print "Failed in deleting cron session.";
                }
            } catch (PDOException $e) {
                $pdo->rollback();
                print "Generation Error 4: Error inserting in log transactions.";
            }
        } else {
            $pdo->rollback();
            print "Generation Error 5: Error inserting in ticketbatch.";
        }
    }

    /**
     * This function is used to regenerate the remaining tickets failed to be
     * inserted on the first trial (insertTickets()).
     * @param type $count
     * @param type $isCreditable
     * @param type $ticketBatch
     * @param type $user
     */
    public function regenerateTickets($count, $isCreditable, $ticketbatch, $user) {
        $pdo = $this->_dbh;

        $pdo->beginTransaction();

        // Generate Tickets
        for ($i = 0; $count > $i; $i++) {
            // Generate Coupon Code
            $code = "";
            $code = $this->mt_rand_str(6);
            $ticketcode = "T" . $code;

            try {
                $secondquery = "INSERT INTO tickets (TicketBatchID, TicketCode, Status, DateCreated, CreatedByAID, IsCreditable) 
                                VALUES (:ticketbatch, :ticketcode, 1, NOW(6), :AID, :iscreditable)";
                $command = $pdo->prepare($secondquery);
                $command->bindParam(":ticketbatch", $ticketbatch);
                $command->bindParam(":ticketcode", $ticketcode);
                $command->bindParam(":AID", $user);
                $command->bindParam(":iscreditable", $isCreditable);
                $secondresult = $command->execute();
                if ($secondresult > 0) {
                    continue;
                } else {
                    $pdo->rollback();
                    print "Regeneration Error 1: Error inserting in tickets.";
                }
            } catch (PDOException $e) {
                // Check if error is 'Duplicate Key constraints violation'
                $errcode = $e->getCode();
                if ($errcode == 23000) {
                    try {
                        $pdo->commit();

                        // get total ticket count
                        $totalticket = "SELECT TicketCount FROM ticketbatch WHERE TicketBatchID = :ticketbatchID";
                        $sql = $pdo->prepare($totalticket);
                        $sql->bindParam(":ticketbatchID", $ticketbatch);
                        $totalticketcount = $sql->fetch(PDO::FETCH_LAZY);

                        // get generated tickets after duplication
                        $querycount = "SELECT COUNT(TicketID) as TicketCount FROM tickets
                                       WHERE TicketBatchID = :ticketbatch";
                        $sql2 = $pdo->createCommand($querycount);
                        $sql2->bindParam(":ticketbatch", $ticketbatch);
                        $ticketcount = $sql2->fetch(PDO::FETCH_LAZY);

                        $remainingtickets = (int) $totalticketcount['TicketCount'] - (int) $ticketcount['TicketCount'];

                        if ($remainingtickets > 0) {
                            $this->regenerateTickets($remainingtickets, $isCreditable, $ticketbatch, $user);
                        }
                    } catch (PDOException $e) {
                        $pdo->rollback();
                        print "Regeneration Error 2: Error on commit() tickets and ticket regeneration.";
                    }
                } else {
                    $pdo->rollback();
                    print "Regeneration Error 3: Different error code received:" . $errcode;
                }
            }
        }

        try {
            $pdo->commit();

            $result = $this->deleteCronSession(2);
            if ($result) {
                $this->logTransactions(31, "CRON: Auto-generation of tickets.");
                print "Ticket generation cron successfully processed!";
            } else {
                print "Failed in deleting cron session.";
            }
        } catch (PDOException $e) {
            $pdo->rollback();
            print "Regeneration Error 4: Error inserting in log transactions.";
        }
    }

    /**
     * This function is used to retrieve the last run date of cron.
     * @author JunJun S. Hernandez
     * @date 03-14-2014
     * @return boolean true|false
     */
    public function getLastRunDate() {
        $lastLogDate = '0000-00-00 00:00:00';
        
        $connection = $this->_dbh;
        $sql = "SELECT RunDate FROM vouchermanagement.cronlogs WHERE CronID = 3 ORDER BY RunDate DESC LIMIT 1";
        $command = $connection->prepare($sql);
        $command->execute();
        $result = $command->fetch();

        if (!empty($result)) 
        {
            $lastLogDate = $result['RunDate'];
        }
        
        return $lastLogDate;
    }

    /**
     * This function is used to retrieve all the reprinted tickets.
     * @author JunJun S. Hernandez
     * @date 03-14-2014
     * @return boolean true|false
     */
    public function getPrintedTicketsFromSpyder($lastLogDate) {
        $connection = $this->_dbh;

        $sql = "SELECT TerminalLogID, LogDate, Remarks FROM spyder.terminallogs t WHERE EventID = 4 AND PeripheralID = 7 AND LogDate > :logDate AND LogDate < NOW(6)";
        $command = $connection->prepare($sql);
        $command->bindParam(":logDate", $lastLogDate);
        $command->execute();
        $result = $command->fetchAll();

        return $result;
    }

    /**
     * This function is used to retrieve all the reprinted tickets.
     * @author JunJun S. Hernandez
     * @date 01-07-2013
     * @params string $ticketCode, $dateReprinted, $reprintedBy
     * @return boolean true|false
     */
    public function updateTicketReprint($ticketCode, $dateReprinted, $reprintedBy) {
        $pdo = $this->_dbh;
        $cronId = 3;
        $pdo->beginTransaction();

        try {
            $query = "UPDATE tickets SET DateTimeReprinted = :dateReprinted, ReprintedBy = :reprintedBy WHERE TicketCode = :ticketCode";
            $command = $pdo->prepare($query);
            $command->bindParam(':dateReprinted', $dateReprinted);
            $command->bindParam(':reprintedBy', $reprintedBy);
            $command->bindParam(':ticketCode', $ticketCode);
            $result = $command->execute();
            
            if ($result == true) {
                try {
                    $sql = "INSERT INTO cronlogs (CronID, RunDate, Status) VALUES (:cronId, NOW(6), 1)";
                    $command = $pdo->prepare($sql);
                    $command->bindParam(":cronId", $cronId);
                    $result = $command->execute();
                    try {
                        $pdo->commit();
                        return true;
                    } catch (Exception $e) {
                        $pdo->rollback();
                        return $e->getMessage();
                    }
                } catch (Exception $e) {
                    $pdo->rollback();
                    return $e->getMessage();
                }
            } else {
                $pdo->rollback();
                print "Error deleting crontemp table.";
                return false;
            }
        } catch (Exception $e) {
            $pdo->rollback();
            return $e->getMessage();
        }
    }

    /**
     * This function is used to log audit trail transactions.
     * @param type $auditFunctionID
     * @param type $transDetails
     */
    public function logTransactions($auditFunctionID, $transDetails = NULL) {
        $pdo = $this->_dbh;
        $AID = 1;
        try {
            $pdo->beginTransaction();

            $remoteIP = '127.0.0.1';//$_SERVER['REMOTE_ADDR'];

            $transMsg = $transDetails;
            $query = "INSERT INTO audittrail (AuditTrailFunctionID, TransDetails, TransDateTime, RemoteIP)
                      VALUE (:auditFunctionID, :transMsg, NOW(6), :remoteIP)";

            $sql = $pdo->prepare($query);            
            $sql->bindParam(":auditFunctionID", $auditFunctionID);
            $sql->bindParam(":transMsg", $transMsg);
            $sql->bindParam(":remoteIP", $remoteIP);
            $result = $sql->execute();
            if ($result > 0) {
                $pdo->commit();
            } else {
                $pdo->rollback();
                print "Error inserting in audittrail.";
            }
        } catch (PDOException $e) {
            $pdo->rollback();
            print "PDOException: Error in log transactions.";
        }
    }

    /**
     * This function is used to end or delete the existing pending cron.
     * @param type $cronId
     */
    public function deleteCronSession($cronId) {
        $pdo = $this->_dbh;

        try {
            $pdo->beginTransaction();

            $sql = "INSERT INTO cronlogs (CronID, RunDate, Status) VALUES (:cronId, NOW(6), 1)";
            $command = $pdo->prepare($sql);
            $command->bindParam(":cronId", $cronId);
            $result = $command->execute();
            if ($result > 0) {
                $sql2 = "DELETE FROM crontemp WHERE CronID = :cronId";
                $command2 = $pdo->prepare($sql2);
                $command2->bindParam(":cronId", $cronId);
                $result2 = $command2->execute();
                if ($result2 > 0) {
                    $pdo->commit();
                    return true;
                } else {
                    $pdo->rollback();
                    print "Error deleting crontemp table.";
                    return false;
                }
            } else {
                $pdo->rollback();
                print "Error inserting in cronlogs table.";
                return false;
            }
        } catch (PDOException $e) {
            $pdo->rollback();
            print "Error in deleting the cron session.";
            return false;
        }
    }

    /**
     * This function uses cURL to execute another php script (thru URL)
     * separately from the cron job.
     * @param type $uri
     * @return string output response
     */
    public function cURL($uri) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        $response = curl_exec($curl);

        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        echo $http_status . "-" . $response;
    }

    /**
     * This function is used to retrieve the total count of active tickets.
     * @param type $current_date
     * @return type
     */
    public function selectActiveTicketCount($current_date)
    {
        $command = $this->_dbh->prepare("SELECT count(TicketID) AS ActiveTickets FROM tickets 
            WHERE Status IN (1,2) AND ValidToDate < :current_date;");
        $command->bindParam(":current_date", $current_date);
        $command->execute();
        $result = $command->fetch(PDO::FETCH_LAZY);
        
        return $result;
    }
    
    /**
     * This function is used to update the status of tickets to expire.
     * @param type $current_date
     * @param type $limit
     */
    public function updateTicketsToExpiration($current_date, $limit)
    {
        $pdo = $this->_dbh;
        
        $pdo->beginTransaction();
        
        try
        {
            $sql = "UPDATE tickets SET Status = 7, DateUpdated = NOW(6), Remarks = 'Updated via cron' WHERE Status IN (1,2) AND ValidToDate < '$current_date' LIMIT " . $limit;            
            $command = $pdo->prepare($sql);
            //$command->bindParam(":current_date", $current_date);
            $result = $command->execute();
            if ($result > 0 || ($result))
            { 
                $pdo->commit();                
                
                $result2 = $this->deleteCronSession(1);
                if ($result2)
                {
                    $this->logTransactions(37, "CRON: Ticket Expiration. No. of Affected Tickets: ".$result);
                    print "Ticket expiration cron successfully processed!";
                }
                else
                {
                    print "Failed in deleting cron session.";
                }
            }
            else
            {
                $pdo->rollback();
                print "Failed in updating tickets to expire.";
            }
        }
        catch (PDOException $e)
        {
            $pdo->rollback();
            print "PDO Error";
        }
    }
    
    /**
     * This function is used to close the PDO connection.
     */
    public function close() {
        if ($this->_dbh)
            $this->_dbh = NULL;
    }

}

?>