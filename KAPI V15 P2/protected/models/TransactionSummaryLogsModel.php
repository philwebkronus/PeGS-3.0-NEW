<?php
/**
 * Description of TransactionSummaryLogsModel
 * Added Insert of Genesis Withdrawal
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
}
