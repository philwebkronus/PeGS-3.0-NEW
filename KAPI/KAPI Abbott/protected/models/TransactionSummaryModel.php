<?php

/**
 * Date Created 11 7, 11 3:55:18 PM <pre />
 * Date Modified 10/12/12
 * Description of TransactionSummaryModel
 * @author Bryan Salazar
 * @author Edson Perez
 */
class TransactionSummaryModel{
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new TransactionSummaryModel();
        return self::$_instance;
    }
    
    /**
     * 
     * Description: get last
     * @param type $site_id
     * @param type $terminal_id
     * @return type 
     */
    public function getTransactionSummaryDetail($site_id,$terminal_id) {
        $sql = 'SELECT TransactionsSummaryID, Reload, Withdrawal FROM transactionsummary WHERE SiteID = :site_id AND TerminalID = :terminal_id AND DateEnded = \'0\' ORDER BY TransactionsSummaryID DESC LIMIT 1';
        $param = array(':site_id'=>$site_id,':terminal_id'=>$terminal_id);
        $command = $this->_connection->createCommand($sql);
        return $command->queryRow(true, $param);
    }
    
    public function updateRedeem($trans_summary_id,$amount) {
        $sql = 'UPDATE transactionsummary SET Withdrawal = :amount,DateEnded = NOW(6)  WHERE TransactionsSummaryID = :trans_summary_id';
        $param = array(':amount'=>$amount,':trans_summary_id'=>$trans_summary_id);
        $command = $this->_connection->createCommand($sql);
        return $command->execute($param);
    }
   
    //EGM
    public function getLastTransSummaryId($terminal_id,$site_id) {
        $sql = 'SELECT TransactionsSummaryID FROM transactionsummary WHERE SiteID = :site_id AND TerminalID = :terminal_id ORDER BY DateStarted DESC LIMIT 1';
        $param = array(':terminal_id'=>$terminal_id,':site_id'=>$site_id);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['TransactionsSummaryID'];
    }
}