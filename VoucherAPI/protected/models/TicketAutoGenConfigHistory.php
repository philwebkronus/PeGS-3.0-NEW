<?php
/**
 * Ticket Auto-Generation Configuration History Model
 * Access Table: ticketautogenconfighistory
 * @author Mark Kenneth Esguerra
 * @date March 5, 2014
 */
class TicketAutoGenConfigHistory extends CFormModel
{
    /**
     * Insert Configuration History
     * @param int $autogenjob 1 -ON | 2 - OFF
     * @param int $threshold Threshold Limit
     * @param int $ticketcount Ticket Count
     * @param int $user ID of the User
     * @return array Result
     * @author Mark Kenneth Esguerra
     * @date March 5, 2014
     */
    public function insertConfigHistory($autogenjob, $threshold, $ticketcount, $user)
    {
        $connection = Yii::app()->db;
        $pdo        = $connection->beginTransaction();
        
        try
        {
            $query = "INSERT INTO ticketautogenconfighistory (
                                            AutoGenerate, 
                                            TicketThresholdLimit, 
                                            TicketCount, 
                                            DateCreated, 
                                            CreatedByAID
                    ) VALUES (
                                            :autogenjob, 
                                            :threshold, 
                                            :ticketcount, 
                                            NOW(6), 
                                            :createdByAID
                    )";
            $sql = $connection->createCommand($query);
            $sql->bindValues(array(
                ':autogenjob' => $autogenjob,
                ':threshold' => $threshold, 
                ':ticketcount' => $ticketcount, 
                ':createdByAID' => $user
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
                    return array('TransCode' => 2);
                }
            }
            else
            {
                $pdo->rollback();
                return array('TransCode' => 0);
            }
        }
        catch (CDbException $e)
        {
            $pdo->rollback();
            return array('TransCode' => 2);
        }
    }
    public function selectConfigurationHistory($datefrom, $dateto)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT * FROM ticketautogenconfighistory 
                  WHERE DateCreated >= '$datefrom 00:00:00' AND DateCreated <= '$dateto 23:59:59'
                  ORDER BY DateCreated DESC";
        $command = $connection->createCommand($query);
        $result = $command->queryAll();
        
        return $result;
    }
}
?>
