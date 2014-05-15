<?php
/**
 * Ticket Status History Model
 * @author Mark Kenneth Esguerra [02-27-14]
 */
class VoucherStatusHistoryModel extends CFormModel
{
    /**
     * Insert Change Status History upon changing status
     * @param mixed $ticketcode Unique Ticket Code
     * @param int $currentstat Current status of the Ticket
     * @param int $status Status to be entered
     * @param int $user AID of the User
     * @return array The result
     * @author Mark Kenneth Esguerra [02-27-14]
     */
    public function insertStatusHistory($ticketcode, $currentstat, $status, $user)
    {
        $connection = Yii::app()->db;
        $pdo        = $connection->beginTransaction();
        
        $tickets = new TicketModel();
        
        //Get TicketID
        $ticketdtls = $tickets->getTicketDetails($ticketcode); 
        $ticketID = $ticketdtls['TicketID'];
        try 
        {
            $query = "INSERT INTO ticketstatushistory (
                        TicketID, 
                        TicketCode, 
                        OriginalStatus, 
                        NewStatus, 
                        DateCreated, 
                        CreatedByAID
                     ) VALUES (
                        :ticketID, 
                        :ticketcode, 
                        :originalstat, 
                        :newstat, 
                        NOW(6), 
                        :aid
                     )";
            $sql = $connection->createCommand($query);
            $sql->bindValues(array(
                ':ticketID' => $ticketID, 
                ':ticketcode' => $ticketcode, 
                ':originalstat' => $currentstat, 
                ':newstat' => $status, 
                ':aid' => $user
            ));
            $result = $sql->execute();
            //Check if there a record inserted
            if ($result > 0)
            {
                try
                {
                    $pdo->commit();
                    return array('TransCode' => 1);
                }
                catch (CDbException $e)
                {
                    $pdo->rollback();
                    return array('TransCode' => 2, 
                                 'TransMsg' => 'An error occured while inserting records in database.');
                }
            }
            else
            {
                $pdo->rollback();
                return array('TransCode' => 2, 
                                 'TransMsg' => 'Failed to insert records.');
            }
        }
        catch (CDbException $e)
        {
            $pdo->rollback();
            return array('TransCode' => 2, 
                         'TransMsg' => 'An error occured while inserting records in database.');
        }
    }
    public function getChangeStatusHistory($vouchercode, $datefrom, $dateto)
    {
        $connection = Yii::app()->db;
        
        if ($vouchercode != "")
        {
            $query = "SELECT TicketStatusID, TicketCode, OriginalStatus, NewStatus, CreatedByAID, DateCreated
                      FROM ticketstatushistory WHERE TicketCode = :ticketcode AND
                      DateCreated >= '$datefrom 00:00:00' AND DateCreated <= '$dateto 23:59:59' 
                      ORDER BY TicketStatusID DESC";
        }
        else
        {
            $query = "SELECT TicketStatusID, TicketCode, OriginalStatus, NewStatus, CreatedByAID, DateCreated
                      FROM ticketstatushistory WHERE DateCreated >= '$datefrom 00:00:00' 
                      AND DateCreated <= '$dateto 23:59:59' ORDER BY TicketStatusID DESC";
        }
        $command = $connection->createCommand($query);
        $command->bindParam(":ticketcode", $vouchercode);
        $result = $command->queryAll();
        
        return $result;
    }
}
?>
