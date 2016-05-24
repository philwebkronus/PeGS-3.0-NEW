<?php
/**
 * Description of TransactionSummaryLogsModel
 *
 * @author fdlsison
 */
class TransactionSummaryLogsModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new TransactionSummaryLogsModel();
        return self::$_instance;
    }
    
    /**
     * Insert Genesis Withdrawal in TransactionSummaryLogs
     * @date January 15, 2016
     */
    public function insertGenesisWithdrawal($transSumID, $genWith, $mswWith) {
        $query = "INSERT INTO transactionsummarylogs (TransactionSummaryID, GenesisWithdrawal, MswWithdrawal)
                  VALUES (:tranSumID, :genWith, :mswWith)";
        $smt = $this->_connection->createCommand($query);
        $param = array(
            ':tranSumID'=> $transSumID,
            ':genWith'=> $genWith,
            ':mswWith' => $mswWith);

        $result = $smt->execute($param);
        //$transactionID = $this->_connection->getLastInsertID();
        return $result;
    }
    /**
     * Check if Transaction SummaryID already exist
     * @date May 11, 2016
     */
    
        public function checkTransactionSummaryID($transactionSummaryID) {
        $sql = 'SELECT Count(TransactionSummaryLogID) as Count FROM transactionsummarylogs WHERE TransactionSummaryID = :TransactionSummaryID';
        $param = array('TransactionSummaryID' => $transactionSummaryID);

        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }
        public function updateGenesisWithdrawal($transactionSummaryID, $amount) {

        $startTrans = $this->_connection->beginTransaction();

        try {
            $sql = 'UPDATE transactionsummarylogs SET GenesisWithdrawal = :Amount WHERE TransactionSummaryID = :TransactionSummaryID';
            $param = array(':Amount' => $amount, ':TransactionSummaryID' => $transactionSummaryID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
}
