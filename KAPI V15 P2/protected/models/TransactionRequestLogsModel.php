<?php

/**
 * Date Created 11 7, 11 1:34:44 PM <pre />
 * Date Modifie 10/12/12
 * Description of TransactionRequestLogsModel
 * @author Bryan Salazar
 * @author Edson Perez
 */
class TransactionRequestLogsModel{
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new TransactionRequestLogsModel();
        return self::$_instance;
    }
    
    public function updateTransReqLogDueZeroBal($terminal_id,$site_id,$trans_type, $trans_req_log_last_id) {
        $sql = "UPDATE transactionrequestlogs SET Status = '1', " . 
                "EndDate = NOW(6) WHERE " . 
                "Amount = '0' AND TerminalID = :terminal_id AND " . 
                "SiteID = :site_id AND TransactionType = :transaction_type AND TransactionRequestLogID = :trlID";
        $param = array(':terminal_id'=>$terminal_id,':site_id'=>$site_id,
            ':transaction_type'=>$trans_type, ':trlID' => $trans_req_log_last_id);
        $command = $this->_connection->createCommand($sql);
        return $command->execute($param);
    }
    
    public function getTransReqLogStatus($site_id,$terminal_id) {
        $sql = 'SELECT Status FROM transactionrequestlogs WHERE TransactionRequestLogID = ' . 
                '(SELECT MAX(TransactionRequestLogID) FROM transactionrequestlogs WHERE SiteID = :site_id AND TerminalID = :terminal_id)';
        $param = array(':site_id'=>$site_id,':terminal_id'=>$terminal_id);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(!isset($result['Status']))
            return false;
        return $result['Status'];
    }
    
    public function insert($trans_ref_id,$amount,$trans_type,$terminal_id,$site_id,$service_id){
        $beginTrans = $this->_connection->beginTransaction();
        try {
            $stmt = $this->_connection->createCommand('INSERT INTO transactionrequestlogs (TransactionReferenceID, Amount, StartDate, TransactionType, TerminalID, Status, SiteID, ServiceID) ' . 
                                'VALUES (:trans_ref_id, :amount, NOW(6), :trans_type, :terminal_id, \'0\', :site_id, :service_id)');
            $stmt->bindValue(':trans_ref_id', $trans_ref_id);
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':trans_type', $trans_type);
            $stmt->bindValue(':terminal_id', $terminal_id);
            $stmt->bindValue(':site_id', $site_id);
            $stmt->bindValue(':service_id', $service_id);
            $stmt->execute();
            $trans_req_log_last_id = $this->_connection->getLastInsertID();
            try {
                $beginTrans->commit();
                return $trans_req_log_last_id;
            } catch(PDOException $e) {
                $beginTrans->rollback();
                return false;
            }
        } catch (CDbException $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }
    
    
    public function insert2($trans_ref_id,$amount,$trans_type,$terminal_id,$site_id,$service_id,$paymentType,
            $loyalty_card, $mid, $user_mode, $stackerbatchid, $trackingid = '',$voucher_code = '', $mg_ticket_id = ''){
        $beginTrans = $this->_connection->beginTransaction();
        try {
            $stmt = $this->_connection->createCommand('INSERT INTO transactionrequestlogs (TransactionReferenceID, StackerSummaryID, 
                                         Amount, StartDate, TransactionType, TerminalID, Status, 
                                         SiteID, ServiceID, LoyaltyCardNumber, MID, UserMode, PaymentType, 
                                         PaymentTrackingID, Option1, ServiceTransactionID)
                                         VALUES (:trans_ref_id, :stackerbatchid, :amount, NOW(6), :trans_type, 
                                         :terminal_id, \'0\', :site_id, :service_id, :loyalty_card, 
                                         :mid, :user_mode, :paymentType ,:trackingID, :voucher_code, 
                                         :service_trans_id)');
            $stmt->bindValue(':trans_ref_id', $trans_ref_id);
            $stmt->bindValue(':stackerbatchid', $stackerbatchid);
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':trans_type', $trans_type);
            $stmt->bindValue(':terminal_id', $terminal_id);
            $stmt->bindValue(':site_id', $site_id);
            $stmt->bindValue(':service_id', $service_id);
            $stmt->bindValue(':loyalty_card', $loyalty_card);
            $stmt->bindValue(':trackingID', $trackingid);
            $stmt->bindValue(':voucher_code', $voucher_code);
            $stmt->bindValue(':mid', $mid);
            $stmt->bindValue(':user_mode', $user_mode);
            $stmt->bindValue(':paymentType', $paymentType);
            $stmt->bindValue(':service_trans_id', $mg_ticket_id);
            
            $stmt->execute();
            $trans_req_log_last_id = $this->_connection->getLastInsertID();
            try {
                $beginTrans->commit();
                return $trans_req_log_last_id;
            } catch(PDOException $e) {
                $beginTrans->rollback();
                return false;
            }
        } catch (CDbException $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }
    
    public function insertDueToZeroBalance($trans_ref_id,$amount,$trans_type,$terminal_id,$site_id,$service_id) {
        $sql = 'INSERT INTO transactionrequestlogs (EndDate,TransactionReferenceID, Amount, StartDate, TransactionType, TerminalID, Status, SiteID, ServiceID) ' . 
                'VALUES (NOW(6), :trans_ref_id, :amount, NOW(6), :trans_type, :terminal_id, \'1\', :site_id, :service_id)';
        $param = array(':trans_ref_id'=>$trans_ref_id,':amount'=>$amount,':trans_type'=>$trans_type,
            ':terminal_id'=>$terminal_id,':site_id'=>$site_id,':service_id'=>$service_id);  
        $command = $this->_connection->createCommand($sql);
        return $command->execute($param);
    }
    
    public function update($trans_req_log_max_id,$apiresult, $transstatus,$transrefid,$terminal_id) {
        $sql = 'UPDATE transactionrequestlogs SET ServiceStatus = :servicestatus, ServiceTransactionID = :servicetransid, ' . 
                'Status = :status, EndDate = NOW(6)  WHERE TransactionRequestLogID = :maximumid AND TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id,':maximumid'=>$trans_req_log_max_id,':servicestatus'=>$apiresult,':status'=>$transstatus,':servicetransid'=>$transrefid);
        $command = $this->_connection->createCommand($sql);
        return $command->execute($param);
    }
}

