<?php

/**
 * Date Created 11 7, 11 1:34:44 PM <pre />
 * Description of TransactionRequestLogsModel
 * @author Bryan Salazar
 */
class TransactionRequestLogsModel extends MI_Model{
    
    public function updateTransReqLogDueZeroBal($terminal_id,$site_id,$trans_type, $transRegLogsId) {
        $sql = "UPDATE transactionrequestlogs SET Status = 1, EndDate = now(6) 
                WHERE TransactionRequestLogID = :trans_logs_id AND Amount = '0' AND TerminalID = :terminal_id 
                AND SiteID = :site_id AND TransactionType = :transaction_type";
        $param = array(':terminal_id'=>$terminal_id,':site_id'=>$site_id,
            ':transaction_type'=>$trans_type,':trans_logs_id'=>$transRegLogsId);
        return $this->exec($sql,$param);
    }
     
    /**
     * Inserts records in transactionrequestlogs, Initial Status is pending
     * @param int $trans_ref_id
     * @param str $amount
     * @param str $trans_type
     * @param int $terminal_id
     * @param int $site_id
     * @param int $service_id
     * @param str $loyalty_card
     * @param int $trackingid
     * @param str $voucher_code
     * @return boolean
     */
    public function insert($trans_ref_id,$amount,$trans_type, $paymentType,$terminal_id,$site_id, $service_id, 
                           $loyalty_card, $mid, $user_mode, $trackingid = '',$voucher_code = '', $mg_ticket_id = '', $login = '') {
        
        try {
            $this->beginTransaction();
            $stmt = $this->dbh->prepare('INSERT INTO transactionrequestlogs (TransactionReferenceID,
                                         Amount, StartDate, TransactionType, TerminalID, Status, 
                                         SiteID, ServiceID, LoyaltyCardNumber, MID, UserMode, PaymentType, 
                                         PaymentTrackingID, Option1, ServiceTransactionID, Option2)
                                         VALUES (:trans_ref_id, :amount, now(6), :trans_type, 
                                         :terminal_id, \'0\', :site_id, :service_id, :loyalty_card, 
                                         :mid, :user_mode, :paymentType ,:trackingID, :voucher_code, 
                                         :service_trans_id,:login)');
            
            $stmt->bindValue(':trans_ref_id', $trans_ref_id);
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
            $stmt->bindValue(':service_trans_id', $trackingid);
            $stmt->bindValue(':login', $login);
            
            $stmt->execute();
            $trans_req_log_last_id = $this->getLastInsertId();
            try {
                $this->dbh->commit();
                return $trans_req_log_last_id;
            } catch(Exception $e) {
                $this->dbh->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->dbh->rollBack();
            return false;
        }
    }
    
    public function update($trans_req_log_max_id,$apiresult, $transstatus,$transrefid,$terminal_id) {
        $sql = 'UPDATE transactionrequestlogs SET ServiceStatus = :servicestatus, ServiceTransactionID = :servicetransid, ' . 
                'Status = :status, EndDate = now(6)  WHERE TransactionRequestLogID = :maximumid AND TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id,':maximumid'=>$trans_req_log_max_id,':servicestatus'=>$apiresult,':status'=>$transstatus,':servicetransid'=>$transrefid);
        return $this->exec($sql,$param);
    }
}

