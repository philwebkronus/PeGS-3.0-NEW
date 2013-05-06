<?php

/**
 * Date Created 04 05, 2013 5:42:58 PM <pre />
 * Description of LoyaltyRequestLogsModel
 * @author aqdepliyan
 */
class LoyaltyRequestLogsModel extends MI_Model {
    
    /**
     * Insert to loyaltyrequestlogs either UB or TB
     * @param int $mid, char $trans_type, int $terminal_id, 
     *                  int $amount, int $trans_details_id, int $paymentType, 
     *                  int $isCreditable
     * @return obj
     */
    public function insert($mid, $trans_type, $terminal_id, $amount, $trans_details_id, $paymentType, $isCreditable="") {
        try{
                $this->beginTransaction();
                $stmt = $this->dbh->prepare('INSERT INTO loyaltyrequestlogs (MID,'.
                                'DateCreated, TransactionType, TransactionOrigin, TerminalID, Amount, TransactionDetailsID,'. 
                                'PaymentType, IsCreditable, Status) VALUES (:mid, now_usec(),'.
                                ':trans_type, :trans_org, :terminal_id, :amount, :transdetailsid,'.
                                ':payment_type, :isCreditable, :trans_status)');

                $stmt->bindValue(':mid', $mid);
                $stmt->bindValue(':trans_type', $trans_type);
                $stmt->bindValue(':trans_org', 1);
                $stmt->bindValue(':terminal_id', $terminal_id);
                $stmt->bindValue(':amount', $amount);
                $stmt->bindValue(':transdetailsid', $trans_details_id);
                $stmt->bindValue(':payment_type', $paymentType);
                $stmt->bindValue(':isCreditable', $isCreditable);
                $stmt->bindValue(':trans_status', 0);
                
                $stmt->execute();
                $loyaltyrequestlogsID = $this->getLastInsertId();
                try {
                    $this->dbh->commit();
                    return $loyaltyrequestlogsID;
                } catch(Exception $e) {
                    $this->dbh->rollBack();
                    return false;
                }
        } catch (Exception $e) {
            $this->dbh->rollBack();
            return false;
        }
        
    }
     
    /**
     * Update loyaltyrequestlogs status either UB or TB
     * @param int $trans_details_id, $status
     * @return obj
     */
    public function updateLoyaltyRequestLogs($loyaltyrequestlogID,$status) {
        $sql = 'UPDATE loyaltyrequestlogs SET Status = :trans_status, ' . 
                'DateUpdated = now_usec() WHERE LoyaltyRequestLogID = :loyaltyrequestlogID';
        $param = array(
            ':trans_status'=> $status,
            ':loyaltyrequestlogID'=>$loyaltyrequestlogID);
        return $this->exec($sql,$param);
    }
    
}

?>
