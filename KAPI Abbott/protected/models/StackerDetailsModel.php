<?php
/**
 * Stacker Summary Model 
 * @author Mark Kenneth Esguerra
 * @date March 12, 2014
 */
class StackerDetailsModel extends CFormModel
{
    public static $_instance = null;
    public $_connection;


    public function __construct() 
    {
        $this->_connection = Yii::app()->db4;
    }
    /**
     * Get Stacker's Total Amount
     * @param int $stackerBatchID StackerBatchID/StackerSummaryID
     * @return array Total Amount
     * @author Mark Kenneth Esguerra
     * March 12, 2014
     */
    public function getStackerTotalAmount($stackerBatchID)
    {
        $query = "SELECT SUM(Amount) AS TotalAmount FROM stackerdetails 
                  WHERE StackerSummaryID = :stackerBatchID AND 
                  TransactionType = 1";
        $command = $this->_connection->createCommand($query);
        $command->bindValue(":stackerBatchID", $stackerBatchID);
        $result = $command->queryRow();
        
        return $result;
        
    }
    /**
     * Get the last Reload transaction
     * @param int $stackerBatchID StackerBatchID/StackerSummaryID
     * @return array Amount
     * @author Mark Kenneth Esguerra
     * @date March 13, 2014
     */
    public function getLastReloadTrans($stackerBatchID)
    {
        $query = "SELECT Amount, StackerDetailID FROM stackerdetails 
                  WHERE StackerSummaryID = :stackerBatchID AND 
                  TransactionType = 2 ORDER BY DateCreated DESC 
                  LIMIT 0, 1";
        $command = $this->_connection->createCommand($query);
        $command->bindValue(":stackerBatchID", $stackerBatchID);
        $result = $command->queryRow();
        
        return $result;
    }
    /**
     * Update Transaction Details ID
     * @param int $transDetailsID TransactionDetailID
     * @param int $stackerdetailID Stacker Details ID
     * @return array result
     * @date May 5, 2014
     * @author Mark Kenneth Esguerra
     */
    public function updateTransactionDetailsID($transDetailsID, $stackerdetailID)
    {
        $pdo = $this->_connection->beginTransaction();
        
        try
        {
            $sql = "UPDATE stackerdetails SET TransactionDetailsID = :transdetailID 
                    WHERE StackerDetailID = :stackerdetailID";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":transdetailID", $transDetailsID);
            $command->bindValue(":stackerdetailID", $stackerdetailID);
            $result = $command->execute();
            if ($result > 0)
            {
                try
                {
                    $pdo->commit();
                    return array("TransCode" => 1, "TransMsg" => "Successfully updated");
                }
                catch (CDbException $e)
                {
                    $pdo->rollback();
                    return array("TransCode" => 0, "TransMsg" => "Transaction Failed");
                }
            }
            else
            {
                $pdo->rollback();
                return array("TransCode" => 0, "TransMsg" => "Transaction Failed");
            }
            
        }
        catch (CDbException $e)
        {
            $pdo->rollback();
            return array("TransCode" => 0, "TransMsg" => "Transaction Failed");
        }
        
    }
}
?>

