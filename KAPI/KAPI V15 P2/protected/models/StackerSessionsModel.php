<?php
/**
 * Stacker Sessions Model
 * @author Mark Kenneth Esguerra
 * @date September 23, 2014
 */
class StackerSessionsModel extends CFormModel
{
    private $connection;
    
    public function __construct()
    {
        $this->connection = Yii::app()->db4;
    }
    /**
     * Check if the StackerSummaryID's Stacker Session is already ended.
     * @param int $stackerbatchid Stacker Summary ID
     * @return array 
     */
    public function isEndedStackerSession($stackerbatchid)
    {
        $sql = "SELECT sses.IsEnded 
                FROM stackersessions sses 
                INNER JOIN stackersummary ssum ON ssum.StackerSessionID = sses.StackerSessionID 
                WHERE ssum.StackerSummaryID = :stackerbatchid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":stackerbatchid", $stackerbatchid);
        $result = $command->queryRow();
        
        return $result;
    }
}
?>
