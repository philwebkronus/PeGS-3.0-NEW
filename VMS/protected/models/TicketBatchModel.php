<?php
/**
 * Ticket Batch Model
 * @author Mark Kenneth Esguerra
 * @date October 30, 2013
 */
class TicketBatchModel extends CFormModel
{
    /**
     * Insert Tickets. Generate Ticket Codes and insert in DB
     * with other info.
     * @param int $count Number of tickets to generate
     * @param int $iscreditable Yes | No
     * @param int $user AID of the user
     * @return array TransCode and TransMsg
     * @author Mark Kenneth Esguerra
     * @date November 4, 2013
     */
    public function insertTickets($count, $iscreditable, $user)
    {
        $model = new GenerationToolModel();
        
        $connection = Yii::app()->db;
        
        $pdo = $connection->beginTransaction();
        
        $firstquery = "INSERT INTO ticketbatch (TicketCount,
                                                Status,
                                                CreatedByAID,
                                                DateCreated
                            ) VALUES (
                                :count,
                                1,
                                :AID,
                                NOW_USEC()
                        )";
        $command = $connection->createCommand($firstquery);
        $command->bindParam(":count", $count);
        $command->bindParam(":AID", $user);
        $firstresult = $command->execute();
        //Get Last Insert TicketBatchID
        $ticketbatch = $connection->getLastInsertID();
        if ($firstresult > 0)
        {
            for ($i = 0; $count > $i; $i++)
            {
                //Generate Coupon Code
                $code = "";
//                for ($x = 0; 6 > $x; $x++)
//                {
//                    $num = mt_rand(1, 36);
//                    $code .= $model->getRandValue($num);
//                }
                $code = $model->mt_rand_str(6);
                $ticketcode = "T".$code;
//                if ($i != 500)
//                    $ticketcode = "T".$code;
//                else
//                    $ticketcode = "TQISK2J";
                try
                {
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
                    //Check if successfully inserted
                    if ($secondresult > 0)
                    {
                        continue;
                    }
                    else
                    {
                        $pdo->rollback();
                        return array('TransCode' => 0,
                                     'TransMsg' => 'An error occured while generating the tickets [0001]');
                    }
                }
                catch (CDbException $e)
                {
                    //Check if error is 'Duplicate Key constraints violation'
                    $errcode = $e->getCode();
                    if ($errcode == 23000)
                    {
                        try
                        {
                            $pdo->commit();

                            $querycount = "SELECT COUNT(TicketID) as TicketCount FROM tickets
                                           WHERE TicketBatchID = :ticketbatch";

                            $sql = $connection->createCommand($querycount);
                            $sql->bindParam(":ticketbatch", $ticketbatch);
                            $ticketcount = $sql->queryAll(); 
                            $remainingtickets = $count - $ticketcount[0]['TicketCount'];

                            return array('TransCode' => 2, 
                                         'TransMsg' => 'Ticket Code already exist. There are '.$remainingtickets.' remaining tickets 
                                                        to generate. Click Retry to continue',
                                         'TicketBatchID' => $ticketbatch,
                                         'RemainingTickets' => $remainingtickets,
                                         'IsCreditable' => $iscreditable);
                        }
                        catch (CDbException $e)
                        {
                            $pdo->rollback();
                            return array('TransCode' => 0,
                                         'TransMsg' => 'An error occured while generating the tickets [0002]');
                        }
 
                    }
                    else
                    {
                        $pdo->rollback();
                        return array('TransCode' => 0, 'TransMsg' => 'An error occured while updating the status');
                    }
                }
            }
            try
            {
                $pdo->commit();
                
                AuditLog::logTransactions(31, " - Generate Tickets");
                return array('TransCode' => 1, 
                             'TransMsg' => 'Tickets successfully generated');
            }
            catch(CDbException $e)
            {
                $pdo->rollback();
                return array('TransCode' => 0,
                             'TransMsg' => $e->getMessage());
            }
        }
        else
        {
            $pdo->rollback();
            return array('TransCode' => 0,
                         'TransMsg' => 'An error occured while generating the tickets [0003]');
        }
    }
    /**
     * Select Ticket Batches
     * @author Mark Kenneth Esguerra
     * @date November 4, 2013
     * @return array Array of ticket batches
     */
    public function getTicketBatch()
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT TicketBatchID FROM ticketbatch";
        $command = $connection->createCommand($query);
        $result = $command->queryAll();
        
        return $result;
    }
    /**
     * Get Batch Status
     * @param int $batch Selected BatchID
     * @return array Status
     * @author Mark Kenneth Esguerra
     * @date November 4, 2013
     */
    public function getBatchStatus($batch)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT Status FROM ticketbatch WHERE TicketBatchID = :batch";
        $command = $connection->createCommand($query);
        $command->bindParam(":batch", $batch);
        $result = $command->queryRow();
        
        return $result;
    }
    /**
     * Change Ticket Status
     * @param int $batch BatchID of the Ticket
     * @param int $status Selected status
     * @param int $user AID of the user
     * @return array TransCode and TransMsg
     * @author Mark Kenneth Esguerra
     * @date November 4, 2013
     */
    public function changeStatus ($batch, $status, $user)
    {
        $connection = Yii::app()->db;
        
        $pdo = $connection->beginTransaction();
        
        //Get Current Status of the TicketBatch
        $getstat = "SELECT Status FROM ticketbatch WHERE TicketBatchID = :batch";
        $sql = $connection->createCommand($getstat);
        $sql->bindParam(":batch", $batch);
        $stat = $sql->queryRow();
        
        if ($stat['Status'] == $status)
        {
            return array('TransCode' => 2,
                         'TransMsg' => 'Ticket status unchanged');
        }
        else
        {
            $firstquery = "UPDATE ticketbatch SET Status = :status, 
                                                  DateUpdated = NOW_USEC(),
                                                  UpdatedByAID = :AID 
                           WHERE TicketBatchID = :batch";
            $command = $connection->createCommand($firstquery);
            $command->bindParam(":status", $status);
            $command->bindParam(":batch", $batch);
            $command->bindParam(":AID", $user);
            $firstresult = $command->execute();
            if ($firstresult > 0)
            {
                try
                {
                    $secondquery = "UPDATE tickets SET Status = :status, 
                                                       DateUpdated = NOW_USEC(),
                                                       UpdatedByAID = :AID
                                    WHERE TicketBatchID = :batch AND Status <> 3";
                    $command = $connection->createCommand($secondquery);
                    $command->bindParam(":status", $status);
                    $command->bindParam(":batch", $batch);
                    $command->bindParam(":AID", $user);
                    $secondresult = $command->execute();
                    if ($secondresult > 0)
                    {
                        try
                        {
                            $pdo->commit();

                            AuditLog::logTransactions(34, "Update Ticket Status Batch ".$batch);
                            return array('TransCode' => 1,
                                         'TransMsg' => 'Ticket status successfully updated');
                        }
                        catch (CDbException $e)
                        {
                            $pdo->rollback();
                            return array('TransCode' => 0,
                                         'TransMsg' => $e->getMessage());
                        }
                    }
                    else
                    {
                        return array('TransCode' => 2,
                                     'TransMsg' => 'There are no tickets in batch');
                    }
                }
                catch(CDbException $e)
                {
                    $pdo->rollback();
                    return array('TransCode' => 0,
                                 'TransMsg' => $e->getMessage());
                }
            }
            else
            {
                return array('TransCode' => 2,
                             'TransMsg' => 'Ticket status unchanged');
            }
        }
    }
}
?>
