<?php
/**
 * Ticket Status History Model
 * @author Mark Kenneth Esguerra [02-27-14]
 */
class TicketStatusHistoryModel extends CFormModel
{
    /**
     * Insert Ticket Status History
     * @param mixed $ticketcode Ticket Code
     * @param int $currentstat Current Status
     * @param int $status Entered status
     * @param int $user ID of the User
     * @return array The array
     * @author Mark Kenneth Esguerra
     * @date January 27, 2014
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
}
?>