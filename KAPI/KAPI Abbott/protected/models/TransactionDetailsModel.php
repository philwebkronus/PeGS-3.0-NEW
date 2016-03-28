<?php

/**
 * Date Created 11 7, 11 4:06:50 PM <pre />
 * Date Modified 10/12/12 
 * Modified for EGM-Kronus Integration
 * @author Bryan Salazar
 * @author Edson Perez
 */
class TransactionDetailsModel{
    
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new TransactionDetailsModel();
        return self::$_instance;
    }
    
    public function insert($trans_ref_id,$trans_summary_max_id,$site_id,$terminal_id,$trans_type,$amount,$service_id,$acct_id,$trans_status) {
        $sql = 'INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, SiteID, TerminalID, TransactionType, Amount, ' . 
                'DateCreated, ServiceID, CreatedByAID, Status) VALUES (:trans_ref_id, :trans_summary_id, :site_id, :terminal_id, ' . 
                ':trans_type, :amount, NOW(6), :service_id, :acct_id, :trans_status)';
        $param = array(
            ':trans_ref_id'=>$trans_ref_id,
            ':trans_summary_id'=>$trans_summary_max_id,
            ':site_id'=>$site_id,
            ':terminal_id'=>$terminal_id,
            ':trans_type'=>$trans_type,
            ':amount'=>$amount,
            ':service_id'=>$service_id,
            ':acct_id'=>$acct_id,
            ':trans_status'=>$trans_status);
        $command = $this->_connection->createCommand($sql);
        return $command->execute($param);
    }

    public function getTransactionDate($transDetailsID){
        $sql = "SELECT DateCreated FROM transactiondetails WHERE TransactionDetailsID = :trans_details_id";
        $param = array(":trans_details_id"=>$transDetailsID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['DateCreated'];
    }
    
    public function getDetailsByReferenceID($transRefID){
        $sql = "SELECT Amount, DateCreated FROM transactiondetails WHERE TransactionReferenceID = :trans_ref_id";
        $param = array(':trans_ref_id'=>$transRefID);
        $command = $this->_connection->createCommand($sql);
        return $command->queryRow(true, $param);
    }
}

