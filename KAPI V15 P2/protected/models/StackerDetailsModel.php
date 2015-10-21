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
        $query = "SELECT Amount, StackerDetailID, PaymentType FROM stackerdetails 
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
    /**
     * Get Total Stacker Amount.
     * @param type $stackerBatchID
     * @return float TotalStackerAmount
     * @author Mark Kenneth Esguerra
     * @date August 08, 2014
     */
    public function getTotalStackerAmount($stackerBatchID)
    {
        $sql = "SELECT IFNULL(SUM(Amount), 0) AS TotalStackerAmount 
                FROM stackerdetails 
                WHERE StackerSummaryID IN
                    (SELECT StackerSummaryID 
                     FROM stackersummary 
                     WHERE StackerSummaryID = :stackerbatchID  
                     AND Status = 0)";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":stackerbatchID", $stackerBatchID);
        $result = $command->queryRow();
        
        return $result['TotalStackerAmount'];
        
    }
    /**
     * Get Last Stacker Amount. Get the last entered cash/ticket amount
     * @param type $stackerbatchID
     * @return float Amount
     * @author Mark Kenneth Esguerra
     * @date December 11, 2014
     */
    public function getLastStackerAmount($stackerbatchID)
    {
        $sql = "SELECT Amount 
                FROM stackerdetails 
                WHERE StackerSummaryID = :stacker_batch_id 
                ORDER BY DateCreated DESC 
                LIMIT 0, 1";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":stacker_batch_id", $stackerbatchID);
        $result = $command->queryRow();
        
        return $result['Amount'];
    }
    /**
     * Get the last or untransacted stacker detail id
     * @param type $stackerBatchID
     * @return boolean
     */
    public function getLastStackerDetailID($stackerBatchID) {
        $sql = "SELECT StackerDetailID 
                FROM stackerdetails 
                WHERE StackerSummaryID = :stackerbatchid AND 
                EwalletTransID IS NULL 
                ORDER BY StackerDetailID DESC 
                LIMIT 0, 1";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":stackerbatchid", $stackerBatchID);
        $result = $command->queryRow();
        
        if (is_array($result)) {
            return $result['StackerDetailID'];
        }
        else {
            return false;
        }
    }
}
?>

