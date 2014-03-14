<?php

/**
 * Date Created 11 7, 11 4:06:50 PM <pre />
 * Description of TransactionDetailsModel
 * @author Bryan Salazar
 */
class TransactionDetailsModel extends MI_Model{
    
    public function insert($trans_ref_id,$trans_summary_max_id,$site_id,$terminal_id,
            $trans_type,$amount,$service_id,$acct_id, $trans_status,$loyaltyCardNo, $mid) {
        
        $sql = 'INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, 
                SiteID, TerminalID, TransactionType, Amount, DateCreated, ServiceID, 
                CreatedByAID, Status, LoyaltyCardNumber, MID) VALUES (:trans_ref_id, 
                :trans_summary_id, :site_id, :terminal_id, :trans_type, :amount, 
                now_usec(), :service_id, :acct_id, :trans_status, :loyalty_card, :mid)';
        
        $param = array(
            ':trans_ref_id'=>$trans_ref_id,
            ':trans_summary_id'=>$trans_summary_max_id,
            ':site_id'=>$site_id,
            ':terminal_id'=>$terminal_id,
            ':trans_type'=>$trans_type,
            ':amount'=>$amount,
            ':service_id'=>$service_id,
            ':acct_id'=>$acct_id,
            ':trans_status'=>$trans_status,
            ':loyalty_card'=>$loyaltyCardNo,
            ':mid'=>$mid);
        
        return $this->exec($sql,$param);
    }
    
    public function getSessionDetails($trans_summary_id) {
        // DATE_FORMAT(B.DateStarted,'%m-%d-%y %h:%i:%s %p') DateStarted FROM transactiondetails A 
        $sql = "SELECT CASE A.TransactionType WHEN 'D' THEN 'Deposit' WHEN 'R' THEN 'Reload' ELSE 'Withdraw' END TransType,
            A.Amount,DATE_FORMAT(A.DateCreated,'%Y-%m-%d %h:%i:%s %p') DateCreated,C.TerminalCode,
            DATE_FORMAT(B.DateStarted,'%Y-%m-%d %h:%i:%s %p') DateStarted FROM transactiondetails A 
            INNER JOIN transactionsummary B ON A.TransactionSummaryID = B.TransactionsSummaryID 
            INNER JOIN terminals C ON C.TerminalID = A.TerminalID " . 
            "WHERE B.TransactionsSummaryID = :trans_summary_id AND A.Status IN (1,4)";
        $param = array(':trans_summary_id'=>$trans_summary_id);
        $this->exec($sql, $param);
        return $this->findAll();
    }
    
    public function getTotalDetails($trans_summary_id) {
        $sql = 'SELECT SUM(AMOUNT) as total_amount ,TransactionType,DATE_FORMAT(DateCreated,\'%Y-%m-%d %h:%i:%s %p\') DateCreated FROM `transactiondetails` ' . 
                'WHERE Status IN (1,4) AND TransactionSummaryID = :trans_summary_id  GROUP BY TransactionType';
        $param = array(':trans_summary_id'=>$trans_summary_id);
        $this->exec($sql, $param);
        return $this->findAll();
    }
    
    public function getTransactionDetails($createdBy,$limit,$start_date,$end_date) {
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $sql = 'SELECT td.DateCreated, td.TransactionType, td.Amount,t.TerminalCode, td.Option2 FROM transactiondetails td ' . 
            'INNER JOIN terminals t ON td.TerminalID = t.TerminalID ' . 
            'WHERE td.CreatedByAID = :createdBy AND td.Status IN (1,4) AND td.DateCreated >= :start_date AND td.DateCreated < :end_date '.
            'ORDER BY td.DateCreated DESC LIMIT 0,'.$limit;
        $param = array(
            ':createdBy'=>$createdBy,
            ':start_date'=>$start_date . ' ' .$cutoff_time,
            ':end_date'=>$end_date . ' ' .$cutoff_time,            
        );
        $this->exec($sql,$param);
        return $this->findAll();
    }
}

