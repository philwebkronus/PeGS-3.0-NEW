<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BankTransactionLogsModel
 *
 * @author jdlachica
 */
class BankTransactionLogsModel extends MI_Model {
    
    public function insertBankTransaction($trlID, $traceNumber, $referenceNumber, $paymentType){
       
        
        try {
            $this->beginTransaction();
            $stmt = $this->dbh->prepare('INSERT INTO banktransactionlogs (TransactionRequestLogID,PaymentType, TraceNumber, 
                ReferenceNumber) VALUES (:trlID,:paymentType,:traceNumber, :referenceNumber)');
            
            $stmt->bindValue(':trlID', $trlID);
            $stmt->bindValue(':traceNumber', $traceNumber);
            $stmt->bindValue(':referenceNumber', $referenceNumber);
            $stmt->bindValue(':paymentType', $paymentType);
            $stmt->execute();
            
            //$btlID = $this->getLastInsertId();
            try {
                $this->dbh->commit();
                return true;
            } catch(Exception $e) {
                $this->dbh->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->dbh->rollBack();
            return false;
        }
    }
}
