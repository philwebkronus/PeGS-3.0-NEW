<?php
/**
 * Ref_Parameters Model
 */
class RefParametersModel extends CFormModel
{
    CONST TICKET_AUTOGEN_JOB = 10;
    CONST TICKET_THRESHHOLD_LIST = 11;
    CONST TICKET_COUNT_LIST = 12;
    CONST TICKET_THRESHHOLD = 13;
    CONST TICKET_COUNT = 14;
    
    /**
     * Get Param Values By ParamID
     * @param int $paramID Parameter ID of the Parameter
     * @return string Param value
     * @author Mark Kenneth Esguerra
     * @date March 4, 2014
     */
    public function getTicketsAutoGenParams($paramID)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT ParamName, ParamValue FROM ref_parameters
                  WHERE ParamID = :paramID";
        $command = $connection->createCommand($query);
        $command->bindValue(":paramID", $paramID);
        $result = $command->queryRow();
        if (is_array($result))
        {
            return $result['ParamValue'];
        }
        else
        {
            return "";
        }
    }
    /**
     * Set Ticket Auto-Generation Configuration.
     * @param int $autogenjob 1 - ON | 2 - OFF
     * @param int $threshold Threshold Limit
     * @param int $ticketcount Ticket Count
     * @return array Result
     * @author Mark Kenneth Esguerra
     * @date March 4, 2014
     */
    public function setTicketAutoGenConfig($autogenjob, $threshold, $ticketcount)
    {
        $connection = Yii::app()->db;
        $pdo        = $connection->beginTransaction();
        
        //Update the autogenjob
        try
        {
            $updateautojob = "UPDATE ref_parameters SET ParamValue = :autogenjob
                              WHERE ParamID = :paramID";
            $sql = $connection->createCommand($updateautojob);
            $sql->bindValue(":autogenjob", $autogenjob);
            $sql->bindValue(":paramID", self::TICKET_AUTOGEN_JOB);
            $result_autogen = $sql->execute();
            
            if ($result_autogen > 0)
            {
                //Update the threshold limit
                try
                {
                    $updatethreshold = "UPDATE ref_parameters SET ParamValue = :threshold
                              WHERE ParamID = :paramID";
                    $sql = $connection->createCommand($updatethreshold);
                    $sql->bindValue(":threshold", $threshold);
                    $sql->bindValue(":paramID", self::TICKET_THRESHHOLD);
                    $sql->execute();

                        //Update ticket count
                        try
                        {
                            $updateticketcount = "UPDATE ref_parameters SET ParamValue = :ticketcount
                                                WHERE ParamID = :paramID";
                            $sql = $connection->createCommand($updateticketcount);
                            $sql->bindValue(":ticketcount", $ticketcount);
                            $sql->bindValue(":paramID", self::TICKET_COUNT);
                            $sql->execute();

                            try
                            {
                                //1 - ON, 2 - OFF
                                if ($autogenjob == 1){
                                    $job = "ON";
                                }
                                else{
                                    $job = "OFF";
                                }
                                AuditLog::logTransactions(36, "AutoGenerate: ".$job."; Threshold: ".$threshold."; TicketCount: ".$ticketcount."");
                                $pdo->commit();
                                return array('TransCode' => 1, 
                                             'TransMsg' => 'You have successfully configured the ticket auto-generation.');
                            }
                            catch (CDbException $e)
                            {
                                $pdo->rollback();
                                return array('TransCode' => 2, 
                                             'TransMsg' => 'An error occured while updating the records.[0001].');
                            }
                        }
                        catch (CDbException $e)
                        {
                            return array('TransCode' => 2, 
                                         'TransMsg' => 'An error occured while updating the records.[0002].');
                        }
                }
                catch (CDbException $e)
                {
                    $pdo->rollback();
                    return array('TransCode' => 2, 
                                 'TransMsg' => 'An error occured while updating the records.[0003].');
                }
            }
            else
            {
                return array('TransCode' => 0, 
                             'TransMsg' => 'Ticket Auto-Generation Configuration not changed.');
            }
        }
        catch (CDbException $e)
        {
            $pdo->rollback();
            return array('TransCode' => 2, 
                         'TransMsg' => 'An error occured while updating the records.[0004].');
        }
    }
}
?>
