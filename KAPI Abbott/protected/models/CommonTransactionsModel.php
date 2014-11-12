<?php

/**
 * Database calls for deposit, reload, withdraw transactions
 * date created 05/07/12
 * date modified 10/12/12
 * For EGM Webservice
 * @author elperez
 */

class CommonTransactionsModel{
    
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new CommonTransactionsModel();
        return self::$_instance;
    }
    
    /**
     * Call this method on common Start Session
     * This will insert records in transactionsummary, transactiondetails and terminalsessions
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
    public function startTransaction($site_id,$terminal_id,$amount,$acctid, $trans_ref_id,
                                      $trans_type,$service_id,$trans_status)
    {
        $beginTrans = $this->_connection->beginTransaction();
        
        try{
            
            $stmt = $this->_connection->createCommand('INSERT INTO transactionsummary 
                                        (SiteID, TerminalID, Deposit, DateStarted, DateEnded, CreatedByAID) 
                                        VALUES (:site_id, :terminal_id, :amount, NOW(6), \'0\', :acctid)');
            
            $stmt->bindValues(array(':site_id'=>$site_id, ':terminal_id'=> $terminal_id,
                                    ':amount'=>$amount, ':acctid'=>$acctid));
            
            $stmt->execute();
            
            $trans_summary_max_id = $this->_connection->getLastInsertID();
            
            try{
                
                $stmt = $this->_connection->createCommand('INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, SiteID, TerminalID, TransactionType, Amount, ' . 
                        'DateCreated, ServiceID, CreatedByAID, Status, Option1) VALUES (:trans_ref_id, :trans_summary_id, :site_id, :terminal_id, ' . 
                        ':trans_type, :amount, NOW(6), :service_id, :acct_id, :trans_status, :trans_tag)');
                
                $stmt->bindValues(array(':trans_ref_id'=>$trans_ref_id, ':trans_summary_id'=>$trans_summary_max_id,
                                        ':site_id'=>$site_id, ':terminal_id'=>$terminal_id,':trans_type'=>$trans_type,
                                        ':amount'=>$amount, ':service_id'=>$service_id, ':acct_id'=>$acctid,
                                        ':trans_status'=>$trans_status,':trans_tag'=> Yii::app()->params['trans_details_tag']));
                
                $stmt->execute();
                
                $trans_details_id = $this->_connection->getLastInsertID();
                
                try{
                    
                    $stmt = $this->_connection->createCommand('UPDATE terminalsessions SET TransactionSummaryID = :trans_summary_id 
                                                 WHERE TerminalID = :terminal_id');
                    
                    $stmt->bindValues(array(':trans_summary_id'=>$trans_summary_max_id,':terminal_id'=>$terminal_id));
                    
                    $stmt->execute();
                    
                    try {
                            
                        $beginTrans->commit();

                        return array($trans_summary_max_id, $trans_details_id);

                    } catch (Exception $e) {
                        $beginTrans->rollback();
                        return false;
                    }
                    
                } catch (Exception $e) {
                    $beginTrans->rollback();
                    return false;
                }
            } catch(Exception $e) {
                $beginTrans->rollback();
                return false;
            }
        } catch (Exception $e) {
            $beginTrans->rollback();
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
    public function reloadTransaction($amount, $trans_summary_id, $trans_ref_id, $site_id,
                                      $terminal_id, $trans_type, $service_id, $acctid, 
                                      $trans_status, $terminal_balance, $total_terminal_balance)               
    {
        $beginTrans = $this->_connection->beginTransaction();
        
        try{
            
            $stmt = $this->_connection->createCommand('UPDATE transactionsummary SET Reload = :amount 
                                                       WHERE TransactionsSummaryID = :trans_summary_id');
            
            $stmt->bindValues(array(':amount'=>$terminal_balance, ':trans_summary_id'=>$trans_summary_id));
            
            $stmt->execute();
            
            try {
                
                $stmt = $this->_connection->createCommand('INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, SiteID, 
                                             TerminalID, TransactionType, Amount, DateCreated, ServiceID, CreatedByAID, Status, Option1) 
                                             VALUES (:trans_ref_id, :trans_summary_id, :site_id, :terminal_id, :trans_type, 
                                             :amount, NOW(6), :service_id, :acct_id, :trans_status, :trans_tag)');
                
                $stmt->bindValues(array(':trans_ref_id'=>$trans_ref_id, ':trans_summary_id'=>$trans_summary_id,
                                        ':site_id'=>$site_id, ':terminal_id'=>$terminal_id, ':trans_type'=>$trans_type,
                                        ':amount'=>$amount, ':service_id'=>$service_id,':acct_id'=>$acctid,':trans_status'=>$trans_status,
                                        ':trans_tag'=> Yii::app()->params['trans_details_tag']));
                
                $stmt->execute();
                
                $trans_details_id = $this->_connection->getLastInsertID();
                
                try{
                    
                    $stmt = $this->_connection->createCommand('UPDATE terminalsessions SET ServiceID = :service_id, LastBalance = :terminal_balance, ' . 
                           'LastTransactionDate = NOW(6) WHERE TerminalID = :terminal_id');
                    
                    $stmt->bindValues(array(':service_id'=>$service_id, ':terminal_balance'=>$total_terminal_balance,
                                            ':terminal_id'=>$terminal_id));
                    
                    $stmt->execute();        
                    
                    try {
                        
                        $beginTrans->commit();

                        return $trans_details_id;

                    } catch(Exception $e) {
                        $beginTrans->rollback();
                        Utilities::log($e->getMessage());
                        return false;
                    }
                } catch (Exception $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
            }catch (Exception $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }
    
    public function redeemTransaction($amount, $trans_summary_id, $trans_ref_id, $site_id,
                                      $terminal_id, $trans_type, $service_id, $acctid, $trans_status)
    {
        $beginTrans = $this->_connection->beginTransaction();
        try {
            
            $stmt = $this->_connection->createCommand('UPDATE transactionsummary SET Withdrawal = :amount, DateEnded = NOW(6)  
                                         WHERE TransactionsSummaryID = :trans_summary_id');
            
            $stmt->bindValues(array(':amount'=>$amount, ':trans_summary_id'=>$trans_summary_id));
            
            $stmt->execute();
            
            try {
                $stmt = $this->_connection->createCommand('INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, SiteID, TerminalID, TransactionType, Amount, ' . 
                            'DateCreated, ServiceID, CreatedByAID, Status, Option1) VALUES (:trans_ref_id, :trans_summary_id, :site_id, :terminal_id, ' . 
                            ':trans_type, :amount, NOW(6), :service_id, :acct_id, :trans_status, :trans_tag )');
                
                $stmt->bindValues(array(':trans_ref_id'=>$trans_ref_id, ':trans_summary_id'=>$trans_summary_id,
                                        ':site_id'=>$site_id, ':terminal_id'=>$terminal_id, ':trans_type'=>$trans_type,
                                        ':amount'=>$amount, ':service_id'=>$service_id, ':acct_id'=>$acctid,
                                        ':trans_status'=>$trans_status,':trans_tag'=>Yii::app()->params['trans_details_tag']));
                $stmt->execute();
                
                $trans_details_id = $this->_connection->getLastInsertID();
                
                try {
                    
                    $stmt = $this->_connection->createCommand('DELETE FROM terminalsessions WHERE TerminalID = :terminal_id');
                    
                    $stmt->bindValue(':terminal_id', $terminal_id);
                    
                    $stmt->execute();
                        
                    try {
                        
                        $beginTrans->commit();

                        return $trans_details_id;

                    } catch (Exception $e) {
                        $beginTrans->rollback();
                        Utilities::log($e->getMessage());
                        return false;
                    }
                } catch (Exception $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
            } catch (Exception $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch(Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }   
    }
    
    
    
    /**
     * Call this method on common Start Session
     * This will insert records in transactionsummary, transactiondetails and terminalsessions
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
    public function startSessionTransaction($site_id,$terminal_id,$amount,$acctid, $trans_ref_id,
                                      $trans_type,$service_id,$trans_status,$loyalty_card, $mid, $paymentType, $stackerbatchid)
    {
        
        $beginTrans = $this->_connection->beginTransaction();
        
        try{
            
            $stmt = $this->_connection->createCommand('INSERT INTO transactionsummary 
                                        (SiteID, TerminalID, Deposit, DateStarted, DateEnded, CreatedByAID, LoyaltyCardNumber, MID) 
                                        VALUES (:site_id, :terminal_id, :amount, NOW(6), \'0\', :acctid, :loyalty_card, :mid)');
            
            $stmt->bindValues(array(':site_id'=>$site_id, ':terminal_id'=> $terminal_id,
                                    ':amount'=>$amount, ':acctid'=>$acctid, ':loyalty_card'=> $loyalty_card, ':mid'=> $mid));
            
            $stmt->execute();
            
            $trans_summary_max_id = $this->_connection->getLastInsertID();
            
            try{
                
                $stmt2 = $this->_connection->createCommand('INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, SiteID, TerminalID, TransactionType, Amount, ' . 
                        'DateCreated, ServiceID, CreatedByAID, Status, LoyaltyCardNumber, MID, PaymentType, StackerSummaryID) VALUES (:trans_ref_id, :trans_summary_id, :site_id, :terminal_id, ' . 
                        ':trans_type, :amount, NOW(6), :service_id, :acct_id, :trans_status, :loyalty_card, :mid, :paymentType, :stackerbatchID)');
                
                $stmt2->bindValues(array(':trans_ref_id'=>$trans_ref_id, ':trans_summary_id'=>$trans_summary_max_id,
                                        ':site_id'=>$site_id, ':terminal_id'=>$terminal_id,':trans_type'=>$trans_type,
                                        ':amount'=>$amount, ':service_id'=>$service_id, ':acct_id'=>$acctid,
                                        ':trans_status'=>$trans_status,':loyalty_card'=> $loyalty_card,
                                        ':mid'=> $mid,':paymentType'=> $paymentType, ":stackerbatchID" => $stackerbatchid));
                
                $stmt2->execute();
                
                $trans_details_id = $this->_connection->getLastInsertID();
                
                try{
                    
                    $stmt3 = $this->_connection->createCommand('UPDATE terminalsessions SET TransactionSummaryID = :trans_summary_id 
                                                 WHERE TerminalID = :terminal_id');
                    
                    $stmt3->bindValues(array(':trans_summary_id'=>$trans_summary_max_id,':terminal_id'=>$terminal_id));
                    
                    $stmt3->execute();
                    
                    try {
                            
                        $beginTrans->commit();

                        return array($trans_summary_max_id, $trans_details_id);

                    } catch (Exception $e) {
                        $beginTrans->rollback();
                        return false;
                    }
                    
                } catch (Exception $e) {
                    $beginTrans->rollback();
                    return false;
                }
            } catch(Exception $e) {
                $beginTrans->rollback();
                return false;
            }
        } catch (Exception $e) {
            $beginTrans->rollback();
            return false;
        }
    }



public function reloadSessionTransaction($amount, $trans_summary_id, $trans_ref_id, $site_id,
                                      $terminal_id, $trans_type, $paymentType, $service_id, $acctid, 
                                      $trans_status, $terminal_balance, $total_terminal_balance, 
                                      $loyalty_card, $mid, $stackerbatchid)               
    {
        $beginTrans = $this->_connection->beginTransaction();
        
        try{
            
            $stmt = $this->_connection->createCommand('UPDATE transactionsummary SET Reload = :amount 
                                                       WHERE TransactionsSummaryID = :trans_summary_id');
            
            $stmt->bindValues(array(':amount'=>$amount, ':trans_summary_id'=>$trans_summary_id));
            
            $stmt->execute();
            
            try {
                
                $stmt = $this->_connection->createCommand('INSERT INTO transactiondetails (TransactionReferenceID, 
                                             TransactionSummaryID, SiteID, TerminalID, TransactionType, 
                                             Amount, DateCreated, ServiceID, CreatedByAID, Status, LoyaltyCardNumber, MID, PaymentType, StackerSummaryID) 
                                             VALUES (:trans_ref_id, :trans_summary_id, :site_id, :terminal_id, :trans_type, 
                                             :amount, NOW(6), :service_id, :acct_id, :trans_status, :loyalty_card, :mid, :paymentType, :stackerbatchID)');
                
                $stmt->bindValues(array(':trans_ref_id'=>$trans_ref_id, ':trans_summary_id'=>$trans_summary_id,
                                        ':site_id'=>$site_id, ':terminal_id'=>$terminal_id, ':trans_type'=>$trans_type,
                                        ':amount'=>$amount, ':service_id'=>$service_id,':acct_id'=>$acctid,':trans_status'=>$trans_status,
                                        ':loyalty_card'=> $loyalty_card, ':mid'=>$mid, ':paymentType'=>$paymentType, ":stackerbatchID" => $stackerbatchid));
                
                $stmt->execute();
                
                $trans_details_id = $this->_connection->getLastInsertID();
                
                try{
                    
                    $stmt = $this->_connection->createCommand('UPDATE terminalsessions SET ServiceID = :service_id, LastBalance = :terminal_balance, ' . 
                           'LastTransactionDate = NOW(6) WHERE TerminalID = :terminal_id');
                    
                    $stmt->bindValues(array(':service_id'=>$service_id, ':terminal_balance'=>$total_terminal_balance,
                                            ':terminal_id'=>$terminal_id));
                    
                    $stmt->execute();        
                    
                    try {
                        
                        $beginTrans->commit();

                        return array($trans_summary_id, $trans_details_id);

                    } catch(Exception $e) {
                        $beginTrans->rollback();
                        Utilities::log($e->getMessage());
                        return false;
                    }
                } catch (Exception $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
            }catch (Exception $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }
    
    
    
    public function redeemSessionTransaction($amount, $trans_summary_id, $trans_ref_id, $site_id,
                                      $terminal_id, $trans_type, $paymentType,$service_id, $acctid,
                                      $trans_status, $loyalty_card, $mid, $stackerbatchid)
    {
        $beginTrans = $this->_connection->beginTransaction();
        try {
            
            $stmt = $this->_connection->createCommand('UPDATE transactionsummary SET Withdrawal = :amount, DateEnded = NOW(6)  
                                         WHERE TransactionsSummaryID = :trans_summary_id');
            
            $stmt->bindValues(array(':amount'=>$amount, ':trans_summary_id'=>$trans_summary_id));
            
            $stmt->execute();
            
            try {
                $stmt = $this->_connection->createCommand('INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, SiteID, 
                                             TerminalID, TransactionType, Amount, DateCreated, ServiceID, CreatedByAID, Status, 
                                             LoyaltyCardNumber, MID, PaymentType, StackerSummaryID) VALUES (:trans_ref_id, :trans_summary_id, :site_id, :terminal_id, 
                                             :trans_type, :amount, NOW(6), :service_id, :acct_id, :trans_status, :loyalty_card, :mid, :paymentType, :stackerbatchID)');
                
                $stmt->bindValues(array(':trans_ref_id'=>$trans_ref_id, ':trans_summary_id'=>$trans_summary_id,
                                        ':site_id'=>$site_id, ':terminal_id'=>$terminal_id, ':trans_type'=>$trans_type,
                                        ':amount'=>$amount, ':service_id'=>$service_id, ':acct_id'=>$acctid,
                                        ':trans_status'=>$trans_status,':loyalty_card'=> $loyalty_card, ':mid'=>$mid, ':paymentType'=>$paymentType, 
                                        ':stackerbatchID' => $stackerbatchid));
                $stmt->execute();
                
                $trans_details_id = $this->_connection->getLastInsertID();
                
                try {
                    
                    $stmt = $this->_connection->createCommand('DELETE FROM terminalsessions WHERE TerminalID = :terminal_id');
                    
                    $stmt->bindValue(':terminal_id', $terminal_id);
                    
                    $stmt->execute();
                        
                    try {
                        
                        $beginTrans->commit();

                        return $trans_details_id;

                    } catch (Exception $e) {
                        $beginTrans->rollback();
                        Utilities::log($e->getMessage());
                        return false;
                    }
                } catch (Exception $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
            } catch (Exception $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch(Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }   
    }
}

?>
