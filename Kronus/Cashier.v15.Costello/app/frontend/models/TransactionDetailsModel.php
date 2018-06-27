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
                now(6), :service_id, :acct_id, :trans_status, :loyalty_card, :mid)';
        
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
            DATE_FORMAT(B.DateStarted,'%Y-%m-%d %h:%i:%s %p') DateStarted,
            CASE C.TerminalType WHEN 0 THEN 'Regular' WHEN 1 THEN 'Genesis' WHEN 2 THEN 'e-SAFE' END TerminalType, 
            CASE E.AccountTypeID WHEN 4 THEN 'Cashier' WHEN 15 THEN 'EGM' WHEN 17 THEN 'e-SAFE Virtual Cashier' END Name
            FROM transactiondetails A 
            INNER JOIN transactionsummary B ON A.TransactionSummaryID = B.TransactionsSummaryID 
            INNER JOIN terminals C ON C.TerminalID = A.TerminalID 
            INNER JOIN accounts D ON A.CreatedByAID = D.AID
            INNER JOIN ref_accounttypes E ON D.AccountTypeID = E.AccountTypeID  
            WHERE B.TransactionsSummaryID = :trans_summary_id AND A.Status IN (1,4)";
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
        
        $sql = 'SELECT td.DateCreated, td.TransactionType, td.Amount,t.TerminalCode, td.Option2, t.TerminalType, trl.StackerSummaryID FROM transactiondetails td ' . 
            'INNER JOIN terminals t ON td.TerminalID = t.TerminalID ' . 
            'INNER JOIN transactionrequestlogs trl ON td.TransactionReferenceID = trl.TransactionReferenceID ' . 
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

 /*
     * Added : 06 22 2018
     * JAVIDA
     */

    public function getTransactionDetailsPerCutOff($start_date, $end_date, $siteID) {
        $cutoff_time = Mirage::app()->param['cut_off'];

        $sql = 'SELECT td.DateCreated, td.TransactionType, td.Amount, ad.Name as CreatedBy, REPLACE(t.TerminalCode, "ICSA-", "") as TerminalCode 
                FROM transactiondetails td FORCE INDEX(IX_transactiondetails_DateCreated)
                INNER JOIN terminals t ON td.TerminalID = t.TerminalID 
		INNER JOIN accountdetails ad ON td.CreatedByAID = ad.AID
                INNER JOIN ref_services s ON td.ServiceID = s.ServiceID
                WHERE td.DateCreated >= :start_date AND td.DateCreated < :end_date AND  td.Status IN (1,4) 
                AND td.SiteID = :site_id
                ORDER BY td.DateCreated ASC';

        $param = array(
            ':start_date' => $start_date . ' ' . $cutoff_time,
            ':end_date' => $end_date . ' ' . '05:59:59',
            ':site_id' => $siteID,
        );
        $this->exec($sql, $param);
        return $this->findAll();
    }

    /*
     * Added : 06 22 2018
     * JAVIDA
     */

    public function getManualRedemptionsPerCutOff($start_date, $end_date, $siteID) {
        $cutoff_time = Mirage::app()->param['cut_off'];

        $sql = 'SELECT  mr.TransactionDate as DateCreated,mr.ActualAmount as Amount, ad.Name
                FROM manualredemptions mr FORCE INDEX(IX_manualredemptions_TransactionDate)
                INNER JOIN accountdetails ad ON mr.ProcessedByAID = ad.AID
		INNER JOIN ref_services s ON mr.ServiceID = s.ServiceID
                WHERE mr.TransactionDate >= :start_date AND mr.TransactionDate < :end_date AND mr.Status = 1 
                AND mr.SiteID = :site_id
                ORDER BY mr.TransactionDate ASC';

        $param = array(
            ':start_date' => $start_date . ' ' . $cutoff_time,
            ':end_date' => $end_date . ' ' . '05:59:59',
            ':site_id' => $siteID,
        );
        $this->exec($sql, $param);

        $arr = array();
        foreach ($this->findAll() as $rows) {
            $arr['DateCreated'] = $rows['DateCreated'];
            $arr['TransactionType'] = 'MR';
            $arr['Amount'] = $rows['Amount'];
            $arr['CreatedBy'] = $rows['Name'];

            $arrTotal[] = $arr;
        }
        return $arrTotal;
    }


}

