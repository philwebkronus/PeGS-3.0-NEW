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
    /**
     * Get Deposited Amount by StackerBatchID
     * @param type $stackerBatchID
     * @return array Amount
     * @author Mark Kenneth Esguerra
     * @date December 10, 2014
     */
    public function getDepositedAmount($stackerBatchID)
    {
        $sql = "SELECT StackerSummaryID, Deposit 
                FROM stackersummary 
                WHERE StackerSummaryID = :stacker_batch_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":stacker_batch_id", $stackerBatchID);
        $result = $command->queryRow();
        
        return $result['Deposit'];
    }
    /**
     * Get Total Reload Amount
     * @param type $stackerBatchID
     * @return boolean
     * @date September 28, 2015
     * @author MGE
     */
    public function getTotalReloadAmount($stackerBatchID) {
        $sql = "SELECT Reload FROM stackersummary 
                WHERE StackerSummaryID = :stackersummaryid";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":stackersummaryid", $stackerBatchID);
        $result = $command->queryRow();
        
        if (is_array($result)) {
            return $result['Reload'];
        }
        else {
            return false;
        }
    }
    /**
     * Update stackersummary and memberservices if amount to withdraw is zero.
     * @param type $stackerBatchID
     * @param type $acct_id
     * @param type $balance
     * @param type $mid
     * @param type $serviceID
     * @return type
     */
    public function updateESAFETransWithZero($stackerBatchID, $acct_id, $balance, $mid, $serviceID) {
        $pdo = $this->_connection->beginTransaction();
        
        try {
            $sql = "UPDATE stackermanagement.stackersummary 
                                    SET UpdatedByAID = :aid, 
                                        DateUpdated = NOW(6), 
                                        TicketCode = NULL, 
                                        Status = :status 
                                    WHERE StackerSummaryID = :stackerbatchid";
            $command = $this->_connection->createCommand($sql);
            $command->bindValues(array(":aid" => $acct_id, 
                                       ":status" => 5, 
                                       ":stackerbatchid" => $stackerBatchID));
            $result = $command->execute();
            if ($result > 0) {
                if (!is_string($result)) { //if result is not string "can't get balance"
                    try {
                        //update memberservices
                        $sql = "UPDATE membership.memberservices 
                                SET CurrentBalance = :currentbalance, 
                                    CurrentBalanceLastUpdate = NOW(6), 
                                    LastTransaction = '' 
                                 WHERE MID = :mid AND ServiceID = :serviceid";
                        $command = $this->_connection->createCommand($sql);
                        $command->bindValues(array(":currentbalance" => $balance, 
                                                   ":mid" => $mid, 
                                                   ":serviceid" => $serviceID));
                        $result = $command->execute();
                        if ($result > 0) {
                            try {
                                $pdo->commit();
                                return array('TransCode' => 0, 
                                             'TransMsg' => 'Transaction Successful.');
                            }
                            catch (CDbException $e) {
                                $pdo->rollback();
                                Utilities::log($e->getMessage());
                                return array('TransCode' => 1, 
                                             'TransMsg' => 'An error occurs while updating.');
                            }  
                        }
                        else {
                            $pdo->rollback();
                            Utilities::log($e->getMessage());
                            return array('TransCode' => 1, 
                                         'TransMsg' => 'An error occurs while updating.');
                        }
                    }
                    catch (CDbException $e) {
                        $pdo->rollback();
                        Utilities::log($e->getMessage());
                        return array('TransCode' => 1, 
                                     'TransMsg' => 'An error occurs while updating.');
                    }
                }
                else {
                    //commit
                    try {
                        $pdo->commit();
                        return array('TransCode' => 0, 
                                     'TransMsg' => 'Transaction Successful.');
                    }
                    catch (CDbException $e) {
                        $pdo->rollback();
                        Utilities::log($e->getMessage());
                        return array('TransCode' => 1, 
                                     'TransMsg' => 'An error occurs while updating.');
                    }   
                }
            }
            else {
                $pdo->rollback();
                Utilities::log($e->getMessage());
                return array('TransCode' => 1, 
                             'TransMsg' => 'Transaction Failed.');
            }
        }
        catch (CDbException $e) {
            $pdo->rollback();
            Utilities::log($e->getMessage());
            return array('TransCode' => 1, 
                         'TransMsg' => 'An error occurs while updating.');
        }
    }
}
?>
