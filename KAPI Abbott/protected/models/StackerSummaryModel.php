<?php
/**
 * Stacker Summary Model 
 * @author Mark Kenneth Esguerra
 * @date March 12, 2014
 */
class StackerSummaryModel extends CFormModel
{
    public static $_instance = null;
    public $_connection;   
    
    CONST STATUS_DEPOSIT   = 3;
    CONST STATUS_RELOAD    = 4;
    CONST STATUS_WITHDRAW  = 5;
    
    public function __construct() 
    {
        $this->_connection = Yii::app()->db4;
    }
    /**
     * Update the Stacker Summary Status during Deposit, Reload and Withdraw.
     * @param int $stackersummaryID StackerBatchID or StackerSummaryID
     * @param int $status Status of the Stacker Summary depending on transaction type.
     * @return array Result
     * @author Mark Kenneth Esguerra
     * @date March 13, 2014
     */
    public function updateStackerSummaryStatus($stackersummaryID, $status, $user)
    {
        
        $pdo    = $this->_connection->beginTransaction();
        
        try
        {
            $query = "UPDATE stackersummary SET Status = :status,  
                                                DateUpdated = NOW(6), 
                                                UpdatedByAID = :user
                      WHERE StackerSummaryID = :stackersummaryID";
            $sql = $this->_connection->createCommand($query);
            $sql->bindValue(":stackersummaryID", $stackersummaryID);
            $sql->bindValue(":status", $status);
            $sql->bindValue(":user", $user);
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
                    Utilities::log($e->getMessage());
                    return array('TransCode' => 2, 'An Error occured while updating the status.');
                }
            }
            else
            {
                $pdo->rollback();
                return array('TransCode' => 0, 'Stacker Summary status was not successfully updated.');
            }
        }
        catch (CDbException $e)
        {
            $pdo->rollback();
            Utilities::log($e->getMessage());
            return array('TransCode' => 2, 'An Error occured while updating the status.');
        }
    }
}
?>
