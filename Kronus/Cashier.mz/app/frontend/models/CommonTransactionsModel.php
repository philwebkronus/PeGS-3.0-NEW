<?php
/**
 * Database calls for deposit, reload, withdraw transactions
 * @date 05/07/12
 * @date 03/14/13
 * @author elperez
 */
class CommonTransactionsModel extends MI_Model{
    /**
     * Call this method on common Start Session
     * This will insert records in transactionsummary, transactiondetails and terminalsessions
     * @version Kronus UB
     * @author elperez
     * @param int $site_id
     * @param int $terminal_id
     * @param int $amount
     * @param int $acctid
     * @param int $trans_ref_id
     * @param string $trans_type
     * @param int $service_id
     * @param int $trans_status
     * @return int | boolean  $trans_summary_max_id
     */
    public function startTransaction($site_id,$terminal_id,$amount,$acctid, $trans_ref_id, $trans_type, $paymentType,
             $service_id,$trans_status, $loyalty_card, $mid)
             //$service_id,$trans_status, $loyalty_card, $mid, $viptype = 0) // CCT added viptype VIP
    {
        $beginTrans = $this->beginTransaction();
        
        try
        {
            //$stmt = $this->dbh->prepare('INSERT INTO transactionsummary (SiteID, TerminalID, Deposit, DateStarted, DateEnded, 
            //                            CreatedByAID, LoyaltyCardNumber,MID, OptionID1) 
            //                            VALUES (:site_id, :terminal_id, :amount, now(6), \'0\', 
            //                            :acctid, :loyalty_card, :mid, :viptype)'); // CCT added OptionID1, viptype VIP

            $stmt = $this->dbh->prepare('INSERT INTO transactionsummary (SiteID, TerminalID, Deposit, DateStarted, DateEnded, 
                                        CreatedByAID, LoyaltyCardNumber,MID) 
                                        VALUES (:site_id, :terminal_id, :amount, now(6), \'0\', 
                                        :acctid, :loyalty_card, :mid)');
            
            $stmt->bindValue(':site_id', $site_id);
            $stmt->bindValue(':terminal_id', $terminal_id);
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':acctid', $acctid);
            $stmt->bindValue(':loyalty_card', $loyalty_card);
            $stmt->bindValue(':mid', $mid);
            // CCT BEGIN added VIP
            //$stmt->bindValue(':viptype', $viptype);
            // CCT END added VIP
                    
            if($stmt->execute()) 
            {
                $trans_summary_max_id = $this->getLastInsertId();
                
                $stmt = $this->dbh->prepare('INSERT INTO transactiondetails (TransactionReferenceID, 
                        TransactionSummaryID, SiteID, TerminalID, TransactionType, Amount, DateCreated, 
                        ServiceID, CreatedByAID, Status, LoyaltyCardNumber, MID, PaymentType) VALUES (:trans_ref_id, 
                        :trans_summary_id, :site_id, :terminal_id, :trans_type, :amount, 
                        now(6), :service_id, :acct_id, :trans_status, :loyalty_card, :mid, :paymentType)');

                $stmt->bindValue(':trans_ref_id', $trans_ref_id);
                $stmt->bindValue(':trans_summary_id', $trans_summary_max_id);
                $stmt->bindValue(':site_id', $site_id);
                $stmt->bindValue(':terminal_id', $terminal_id);
                $stmt->bindValue(':trans_type', $trans_type);
                $stmt->bindValue(':amount', $amount);
                $stmt->bindValue(':service_id', $service_id);
                $stmt->bindValue(':acct_id', $acctid);
                $stmt->bindValue(':trans_status', $trans_status);
                $stmt->bindValue(':loyalty_card', $loyalty_card);
                $stmt->bindValue(':mid', $mid);
                $stmt->bindValue(':paymentType', $paymentType);
                
                if($stmt->execute()) 
                {
                    $trans_details_id = $this->getLastInsertId();
                    
                    $stmt = $this->dbh->prepare('UPDATE terminalsessions SET TransactionSummaryID = :trans_summary_id 
                                                 WHERE TerminalID = :terminal_id');
                    $stmt->bindValue(':trans_summary_id', $trans_summary_max_id);
                    $stmt->bindValue(':terminal_id', $terminal_id);
                    
                    if($stmt->execute()) 
                    {
                        $this->dbh->commit();
                        return array('trans_summary_max_id' =>$trans_summary_max_id, 'transdetails_max_id' =>$trans_details_id);
                    } 
                    else 
                    {
                        $this->dbh->rollBack();
                        return false;
                    }
                } 
                else 
                {
                    $this->dbh->rollBack();
                    return false;
                }
            } 
            else 
            {
                $this->dbh->rollBack();
                return false;
            }
        } 
        catch (Exception $e) 
        {
            $this->dbh->rollBack();
            return false;
        }
    }
    
    /**
     * Call this method on Common Reload
     * This will update records in transactionsummary, terminalsessions and insert in transactiondetails
     * @param int $amount
     * @param int $trans_summary_id
     * @param int $trans_ref_id
     * @param int $site_id
     * @param int $terminal_id
     * @param string $trans_type
     * @param int $service_id
     * @param int $acctid
     * @param int $trans_status
     * @param int $terminal_balance
     * @return boolean 
     */
    public function reloadTransaction($amount, $trans_summary_id, $trans_ref_id, $site_id, $terminal_id, $trans_type, 
                                    $paymentType, $service_id, $acctid, $trans_status, $terminal_balance, 
                                    $total_terminal_balance, $loyalty_card, $mid)    
    {
        $beginTrans = $this->beginTransaction();
        
        try
        {
            $stmt = $this->dbh->prepare('UPDATE transactionsummary SET Reload = :amount WHERE TransactionsSummaryID = :trans_summary_id');
            $stmt->bindValue(':amount', $terminal_balance);
            $stmt->bindValue(':trans_summary_id', $trans_summary_id);
            
            if($stmt->execute()) 
            {
                $stmt = $this->dbh->prepare('INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, 
                                            SiteID, TerminalID, TransactionType, Amount, DateCreated, ServiceID, CreatedByAID, 
                                            Status, LoyaltyCardNumber, MID, PaymentType) 
                                            VALUES (:trans_ref_id, :trans_summary_id, :site_id, :terminal_id, :trans_type, 
                                            :amount, now(6), :service_id, :acct_id, :trans_status, :loyalty_card, :mid, :paymentType)');
                
                $stmt->bindValue(':trans_ref_id',$trans_ref_id);
                $stmt->bindValue(':trans_summary_id', $trans_summary_id);
                $stmt->bindValue(':site_id', $site_id);
                $stmt->bindValue(':terminal_id', $terminal_id);
                $stmt->bindValue(':trans_type', $trans_type);
                $stmt->bindValue(':amount', $amount);
                $stmt->bindValue(':service_id', $service_id);
                $stmt->bindValue(':acct_id', $acctid);
                $stmt->bindValue(':trans_status', $trans_status);
                $stmt->bindValue(':loyalty_card', $loyalty_card);
                $stmt->bindValue(':mid', $mid);
                $stmt->bindValue(':paymentType', $paymentType);
                
                if($stmt->execute()) 
                {
                    $trans_details_id = $this->getLastInsertId();
                    $stmt = $this->dbh->prepare('UPDATE terminalsessions SET ServiceID = :service_id, 
                                                 LastBalance = :terminal_balance, LastTransactionDate = now(6) 
                                                 WHERE TerminalID = :terminal_id');
                    $stmt->bindValue(':service_id', $service_id);
                    $stmt->bindValue(':terminal_balance', $total_terminal_balance);
                    $stmt->bindValue(':terminal_id', $terminal_id);
                    $isupdated = $stmt->rowCount();
                    
                    if($stmt->execute()) 
                    {
                        $this->dbh->commit();
                        return $trans_details_id;
                    } 
                    else 
                    {
                        $this->dbh->rollBack();
                        return false;
                    }
                } 
                else 
                {
                    $this->dbh->rollBack();
                    return false;
                }
            } 
            else 
            {
                $this->dbh->rollBack();
                return false;
            }
        } 
        catch (Exception $e) 
        {
            $this->dbh->rollBack();
            return false;
        }
    }
    
    public function redeemTransaction($amount, $trans_summary_id, $trans_ref_id, $site_id, $terminal_id, $trans_type, 
                                        $paymentType,$service_id, $acctid, $trans_status, $loyalty_card, $mid)
    {
        $beginTrans = $this->beginTransaction();
        
        try 
        {
            $stmt = $this->dbh->prepare('UPDATE transactionsummary SET Withdrawal = :amount, DateEnded = now(6)  
                                         WHERE TransactionsSummaryID = :trans_summary_id');
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':trans_summary_id', $trans_summary_id);
            
            if($stmt->execute())
            {
                $stmt = $this->dbh->prepare('INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, SiteID, 
                                             TerminalID, TransactionType, Amount, DateCreated, ServiceID, CreatedByAID, Status, 
                                             LoyaltyCardNumber, MID, PaymentType) VALUES (:trans_ref_id, :trans_summary_id, :site_id, :terminal_id, 
                                             :trans_type, :amount, now(6), :service_id, :acct_id, :trans_status, :loyalty_card, :mid, :paymentType)');
                
                $stmt->bindValue(':trans_ref_id',$trans_ref_id);
                $stmt->bindValue(':trans_summary_id', $trans_summary_id);
                $stmt->bindValue(':site_id', $site_id);
                $stmt->bindValue(':terminal_id', $terminal_id);
                $stmt->bindValue(':trans_type', $trans_type);
                $stmt->bindValue(':amount', $amount);
                $stmt->bindValue(':service_id', $service_id);
                $stmt->bindValue(':acct_id', $acctid);
                $stmt->bindValue(':trans_status', $trans_status);
                $stmt->bindValue(':loyalty_card', $loyalty_card);
                $stmt->bindValue(':mid', $mid);
                $stmt->bindValue(':paymentType', $paymentType);
                
                if($stmt->execute()) 
                {
                    $trans_details_id = $this->getLastInsertId();
                    
                    $stmt = $this->dbh->prepare('DELETE FROM terminalsessions WHERE TerminalID = :terminal_id');
                    $stmt->bindValue(':terminal_id', $terminal_id);
                    
                    if($stmt->execute()) 
                    {
                        $this->dbh->commit();
                        return $trans_details_id;
                    } 
                    else 
                    {   
                        $this->dbh->rollBack();
                        return false;
                    }
                } 
                else 
                {
                    $this->dbh->rollBack();
                    return false;
                }
            } 
            else 
            {
                $this->dbh->rollBack();
                return false;
            }
        } 
        catch(Exception $e) 
        {
            $this->dbh->rollBack();
            return false;
        }   
    }
}
?>