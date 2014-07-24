<?php

include "PDOLibrary.php";

class redemptionmodel extends PDOLibrary{
    
    public function __construct($sconectionstring) 
    {
        parent::__construct($sconectionstring);          
    } 
    
    
    public function getTerminalID($terminalcode){
          $stmt = "SELECT SiteID, TerminalID FROM terminals WHERE TerminalCode = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $terminalcode);
          $this->execute();
          return $this->fetchData();
     }
     
     public function getLastSessionDetails($terminalid){
          $stmt = "SELECT LoyaltyCardNumber, MID, UserMode, UBServiceLogin, UBServicePassword, 
                ServiceID, UBHashedServicePassword FROM terminalsessions
                WHERE TerminalID = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $terminalid);
          $this->execute();
          return $this->fetchAllData();
     }
     
     public function getServiceDetails($serviceid){
          $stmt = "SELECT ServiceID, Code, UserMode FROM ref_services WHERE ServiceID = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $serviceid);
          $this->execute();
          return $this->fetchData();
     }
     
     public function getTerminalPassword($terminal_id, $service_id){
         $stmt = "SELECT t.ServicePassword, t.HashedServicePassword FROM terminalservices t 
                WHERE t.TerminalID = ? AND ServiceID = ? AND t.Status = 1";
          $this->prepare($stmt);
          $this->bindparameter(1, $terminal_id);
          $this->bindparameter(2, $service_id);
          $this->execute();
          return $this->fetchData();
     }
     
     
     public function getSpyderStatus($siteid){
          $stmt = "SELECT Spyder FROM sites WHERE SiteID = ? AND Status = 1";
          $this->prepare($stmt);
          $this->bindparameter(1, $siteid);
          $this->execute();
          return $this->fetchData();
     }
     
     public function insertSpyderRequest($terminalCode, $commandType){        
        try{
                $this->begintrans();
                $this->prepare('INSERT INTO spyderrequestlogs (TerminalCode, CommandType, DateCreated, 
                Status) VALUES (?, ?, now_usec(), 0)');

                $this->bindparameter(1, $terminalCode);
                $this->bindparameter(2, $commandType);
                
                $this->execute();
                $spyderrequestlogsID = $this->insertedid();
                try {
                    $this->committrans();
                    return $spyderrequestlogsID;
                } catch(Exception $e) {
                    $this->rollbacktrans();
                    return false;
                }
        } catch (Exception $e) {
            $this->rollbacktrans();
            return false;
        }
    }
    
    public function updateSpyderRequest($status, $spyderReqID){
        $sql = "UPDATE spyderrequestlogs SET Status = ?, DateUpdated = now_usec() 
                  WHERE SpyderRequestLogID = ?";
        $this->prepare($sql);
        $this->bindparameter(1, $status);
        $this->bindparameter(2, $spyderReqID);
        $this->execute();
    }
    
    public function isSessionActive($terminal_id) {
        $sql = 'SELECT COUNT(TerminalID) AS Cnt FROM terminalsessions WHERE TerminalID = :terminal_id AND DateEnded = 0';
        $this->prepare($sql);
        $this->bindparameter(1, $terminal_id);
        $this->execute();
        $result = $this->fetchData();
        if(!isset($result['Cnt']))
          return false;
        return $result['Cnt'];
    }
    
    
    public function insertTransactionRequestLogs($trans_ref_id,$amount,$trans_type, $paymentType,$terminal_id,$site_id, $service_id, 
                           $loyalty_card, $mid, $user_mode, $trackingid = '',$voucher_code = '', $mg_ticket_id = '') {
        
        try {
            $this->begintrans();
            $this->prepare('INSERT INTO transactionrequestlogs (TransactionReferenceID,
                                         Amount, StartDate, TransactionType, TerminalID, Status, 
                                         SiteID, ServiceID, LoyaltyCardNumber, MID, UserMode, PaymentType, 
                                         PaymentTrackingID, Option1, ServiceTransactionID)
                                         VALUES (:trans_ref_id, :amount, now(6), :trans_type, 
                                         :terminal_id, \'0\', :site_id, :service_id, :loyalty_card, 
                                         :mid, :user_mode, :paymentType ,:trackingID, :voucher_code, 
                                         :service_trans_id)');
            
            $this->bindparameter(':trans_ref_id', $trans_ref_id);
            $this->bindparameter(':amount', $amount);
            $this->bindparameter(':trans_type', $trans_type);
            $this->bindparameter(':terminal_id', $terminal_id);
            $this->bindparameter(':site_id', $site_id);
            $this->bindparameter(':service_id', $service_id);
            $this->bindparameter(':loyalty_card', $loyalty_card);
            $this->bindparameter(':trackingID', $trackingid);
            $this->bindparameter(':voucher_code', $voucher_code);
            $this->bindparameter(':mid', $mid);
            $this->bindparameter(':user_mode', $user_mode);
            $this->bindparameter(':paymentType', $paymentType);
            $this->bindparameter(':service_trans_id', $mg_ticket_id);
            
            $this->execute();
            $trans_req_log_last_id = $this->insertedid();
            try {
                $this->committrans();
                return $trans_req_log_last_id;
            } catch(Exception $e) {
                $this->rollbacktrans();
                return false;
            }
        } catch (Exception $e) {
            $this->rollbacktrans();
            return false;
        }
      
    }
    
    
    public function redeemTransaction($amount, $trans_summary_id, $trans_ref_id, $site_id,
                                      $terminal_id, $trans_type, $paymentType,$service_id, $acctid,
                                      $trans_status, $loyalty_card, $mid)
    {
        $this->begintrans();
        try {
            $this->prepare('UPDATE transactionsummary SET Withdrawal = :amount, DateEnded = now_usec()  
                                         WHERE TransactionsSummaryID = :trans_summary_id');
            $this->bindparameter(':amount', $amount);
            $this->bindparameter(':trans_summary_id', $trans_summary_id);
            
            if($this->execute())
            {
                $this->prepare('INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, SiteID, 
                                             TerminalID, TransactionType, Amount, DateCreated, ServiceID, CreatedByAID, Status, 
                                             LoyaltyCardNumber, MID, PaymentType) VALUES (:trans_ref_id, :trans_summary_id, :site_id, :terminal_id, 
                                             :trans_type, :amount, now_usec(), :service_id, :acct_id, :trans_status, :loyalty_card, :mid, :paymentType)');
                
                $this->bindparameter(':trans_ref_id',$trans_ref_id);
                $this->bindparameter(':trans_summary_id', $trans_summary_id);
                $this->bindparameter(':site_id', $site_id);
                $this->bindparameter(':terminal_id', $terminal_id);
                $this->bindparameter(':trans_type', $trans_type);
                $this->bindparameter(':amount', $amount);
                $this->bindparameter(':service_id', $service_id);
                $this->bindparameter(':acct_id', $acctid);
                $this->bindparameter(':trans_status', $trans_status);
                $this->bindparameter(':loyalty_card', $loyalty_card);
                $this->bindparameter(':mid', $mid);
                $this->bindparameter(':paymentType', $paymentType);
                
                if($this->execute()) {
                    $trans_details_id = $this->insertedid();
                    
                    $this->prepare('DELETE FROM terminalsessions WHERE TerminalID = :terminal_id');
                    $this->bindparameter(':terminal_id', $terminal_id);
                    
                    if($this->execute()) {
                        $this->committrans();
                        return $trans_details_id;
                    } else {
                        $this->rollbacktrans();
                        return false;
                    }
                } else {
                    $this->rollbacktrans();
                    return false;
                }
            } else {
                $this->rollbacktrans();
                return false;
            }
        } catch(Exception $e) {
            $this->rollbacktrans();
            return false;
        }   
    }
    
    
    public function updateTransLogs($trans_req_log_max_id,$apiresult, $transstatus,$transrefid,$terminal_id) {
        $sql = 'UPDATE transactionrequestlogs SET ServiceStatus = :servicestatus, ServiceTransactionID = :servicetransid, Status = :status, EndDate = now_usec()  WHERE TransactionRequestLogID = :maximumid AND TerminalID = :terminal_id';
        $this->prepare($sql);
        $this->bindparameter(':terminal_id',$terminal_id);
        $this->bindparameter(':maximumid',$trans_req_log_max_id);
        $this->bindparameter(':servicestatus',$apiresult);
        $this->bindparameter(':status',$transstatus);
        $this->bindparameter(':servicetransid',$transrefid);
        
        return $this->execute();
    }
    
    
    public function getLastSessSummaryID($terminalID){
        $sql = 'SELECT TransactionSummaryID FROM terminalsessions WHERE TerminalID = ?';
        $this->prepare($sql);
        $this->bindparameter(1,$terminalID);
        $this->execute();
        $result = $this->fetchData();
        if(!isset($result['TransactionSummaryID']))
            return false;
        return $result['TransactionSummaryID'];
    }
    
    
    public function updateTransReqLogDueZeroBal($terminal_id,$site_id,$trans_type, $transRegLogsId) {
        $sql = "UPDATE transactionrequestlogs SET Status = 1, EndDate = now_usec() 
                WHERE TransactionRequestLogID = :trans_logs_id AND Amount = '0' AND TerminalID = :terminal_id 
                AND SiteID = :site_id AND TransactionType = :transaction_type";
        $this->prepare($sql);
        $this->bindparameter(':terminal_id',$terminal_id);
        $this->bindparameter(':site_id',$site_id);
        $this->bindparameter(':transaction_type',$trans_type);
        $this->bindparameter(':trans_logs_id',$transRegLogsId);
        
        return $this->execute();
    }
     
    
    public function insertLoyaltyReqLogs($mid, $trans_type, $terminal_id, $amount, $trans_details_id, $paymentType, $isCreditable="") {
        try{
                $this->begintrans();
                $this->prepare('INSERT INTO loyaltyrequestlogs (MID,'.
                                'DateCreated, TransactionType, TransactionOrigin, TerminalID, Amount, TransactionDetailsID,'. 
                                'PaymentType, IsCreditable, Status) VALUES (:mid, now_usec(),'.
                                ':trans_type, :trans_org, :terminal_id, :amount, :transdetailsid,'.
                                ':payment_type, :isCreditable, :trans_status)');

                $this->bindparameter(':mid', $mid);
                $this->bindparameter(':trans_type', $trans_type);
                $this->bindparameter(':trans_org', 1);
                $this->bindparameter(':terminal_id', $terminal_id);
                $this->bindparameter(':amount', $amount);
                $this->bindparameter(':transdetailsid', $trans_details_id);
                $this->bindparameter(':payment_type', $paymentType);
                $this->bindparameter(':isCreditable', $isCreditable);
                $this->bindparameter(':trans_status', 0);
                
                $this->execute();
                $loyaltyrequestlogsID = $this->insertedid();
                try {
                    $this->committrans();
                    return $loyaltyrequestlogsID;
                } catch(Exception $e) {
                    $this->dbh->rollbacktrans();
                    return false;
                }
        } catch (Exception $e) {
            $this->dbh->rollbacktrans();
            return false;
        }
        
    }
    
    
    public function updateLoyaltyRequestLogs($loyaltyrequestlogID,$status) {
        $sql = 'UPDATE loyaltyrequestlogs SET Status = :trans_status, ' . 
                'DateUpdated = now_usec() WHERE LoyaltyRequestLogID = :loyaltyrequestlogID';
        $this->prepare($sql);
        $this->bindparameter(':trans_status', $status);
        $this->bindparameter(':loyaltyrequestlogID', $loyaltyrequestlogID);
        return $this->execute();
    }
     
    
}

?>
