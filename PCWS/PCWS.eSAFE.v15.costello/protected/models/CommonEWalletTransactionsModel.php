<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CommonEWalletTransactionsModel
 *
 * @author jdlachica
 */
class CommonEWalletTransactionsModel extends CFormModel  {
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
    public function insert($mid, $terminalID, $serviceID, $loyaltyCardNumber, $userMode,
            $ubServiceLogin, $ubServicePassword, $ubHashedServicePassword,
            $transactionReferenceID, $amount, $transactionType, $siteID, $trackingID,
            $voucherCode, $paymentType, $serviceTransactionID, $deposit, $AID, $balance){
        
        $startTrans = $this->connection->beginTransaction();
   
        try {

            $sql2 = "INSERT INTO transactionrequestlogs (TransactionReferenceID, Amount, StartDate, TransactionType, TerminalID, Status, SiteID, ServiceID, LoyaltyCardNumber, MID, UserMode, PaymentType, PaymentTrackingID, Option1, ServiceTransactionID) "
                ."VALUES (:trans_ref_id, :amount, NOW(6), :trans_type, :terminal_id, 0, :site_id, :service_id, :loyalty_card, :mid, :user_mode, :paymentType ,:trackingID, :voucher_code, :service_trans_id)";
            $param2 = array(
                ':trans_ref_id'=>$transactionReferenceID,':amount'=>$amount,
                ':trans_type'=>$transactionType, ':terminal_id'=>$terminalID,
                ':site_id'=>$siteID, ':service_id'=>$serviceID,
                ':loyalty_card'=>$loyaltyCardNumber, ':mid'=>$mid, ':user_mode'=>$userMode,
                ':trackingID'=>$trackingID, ':voucher_code'=>$voucherCode,
                ':paymentType'=>$paymentType,':service_trans_id'=>$serviceTransactionID);

            $command2 = $this->connection->createCommand($sql2);
            $command2->bindValues($param2);
            $command2->execute();

            try {
                $transactionRequestLogID = $this->connection->getLastInsertID();

                $sql3 = "INSERT INTO transactionsummary (SiteID, TerminalID, Deposit, DateStarted, DateEnded, CreatedByAID, StartBalance, LoyaltyCardNumber, MID) "
                        . "VALUES (:siteID, :terminalID, :deposit, now(6), 0, :createdByAID, :startBalance, :loyaltyCardNumber, :mid)";

                $param3 = array(':siteID'=>$siteID, ':terminalID'=>$terminalID, ':deposit'=>$deposit, ':createdByAID'=>$AID, ':loyaltyCardNumber'=>$loyaltyCardNumber, ':mid'=>$mid, ':startBalance'=>$balance);

                $command3 = $this->connection->createCommand($sql3);
                $command3->bindValues($param3);
                $command3->execute();

                try {
                $transactionSummaryID = $this->connection->getLastInsertID();

                $sql4 = "INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, SiteID, TerminalID, TransactionType, Amount, DateCreated, ServiceID, CreatedByAID, Status, LoyaltyCardNumber, MID, PaymentType) "
                        . "VALUES (:transactionReferenceID, :transactionSummaryID, :siteID, :terminalID, :transactionType, :amount, now(6), :serviceID, :createdByAID, :status, :loyaltyCardNumber, :mid, :paymentType)";

                $param4 = array(':transactionReferenceID'=>$transactionReferenceID, ':transactionSummaryID'=>$transactionSummaryID, ':siteID'=>$siteID, ':terminalID'=>$terminalID, 
                    ':transactionType'=>$transactionType, ':amount'=>$amount, ':serviceID'=>$serviceID, ':createdByAID'=>$AID, ':status'=>1, 
                    ':loyaltyCardNumber'=>$loyaltyCardNumber, ':mid'=>$mid, ':paymentType'=>$paymentType);

                $command4 = $this->connection->createCommand($sql4);
                $command4->bindValues($param4);
                $command4->execute();

                    try {

                    $sql5 = "UPDATE terminalsessions SET TransactionSummaryID = :transactionSummaryID WHERE TerminalID=:terminalID";
                    $param5 = array(':transactionSummaryID'=>$transactionSummaryID,':terminalID'=>$terminalID);

                    $command5 = $this->connection->createCommand($sql5);
                    $command5->bindValues($param5);
                    $command5->execute();

                        try {

                        $sql6 = "UPDATE transactionrequestlogs SET Status = :status, EndDate=now(6) WHERE TransactionRequestLogID=:trlID";
                        $param6 = array(':status'=>1,':trlID'=>$transactionRequestLogID);

                        $command6 = $this->connection->createCommand($sql6);
                        $command6->bindValues($param6);
                        $command6->execute();

                            try {
                                $startTrans->commit();

                                return $transactionSummaryID;
                            } catch (PDOException $e) {
                                $startTrans->rollback();
                                Utilities::log($e->getMessage());
                                return 0;
                            }

                        } catch (PDOException $e) {
                            $startTrans->rollback();
                            Utilities::log($e->getMessage());
                            return 0;
                        }

                    } catch (PDOException $e) {
                        $startTrans->rollback();
                        Utilities::log($e->getMessage());
                        return 0;
                    }

                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }

            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }

        } catch (PDOException $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
             
    }
    
    
    public function insert2($mid, $terminalID, $serviceID, $loyaltyCardNumber, $userMode,
            $ubServiceLogin, $ubServicePassword, $ubHashedServicePassword, $balance){
        
        
        $startTrans = $this->connection->beginTransaction();
        
            
            try {
                $sql = "INSERT INTO terminalsessions (TerminalID, ServiceID, LoyaltyCardNumber, MID,
                        UserMode, UBServiceLogin, UBServicePassword, UBHashedServicePassword, DateStarted, 
                        LastBalance, LastTransactionDate, Status) 
                        VALUES (:terminalID,:serviceID, :loyaltyCardNumber, :mid, :userMode, 
                        :ubServiceLogin, :ubServicePassword, :ubHashedServicePassword, now(6), :lastBalance, now(6), 0)";
                $param = array(
                    ':terminalID'=>$terminalID,':serviceID'=>$serviceID,
                    ':loyaltyCardNumber'=>$loyaltyCardNumber,':mid'=>$mid,
                    ':userMode'=>$userMode,':ubServiceLogin'=>$ubServiceLogin,
                    ':ubServicePassword'=>$ubServicePassword, 
                    ':ubHashedServicePassword'=>$ubHashedServicePassword, 
                    ':lastBalance'=>$balance
                    );
                $command2 = $this->connection->createCommand($sql);
                $command2->bindValues($param);
                $command2->execute();
                
                try {

                    $startTrans->commit();
                    return 1;
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }
                
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        
    }
    
    
    public function forceLogout($mid, $terminalID, $serviceID, $loyaltyCardNumber, $userMode,
            $transactionReferenceID, $amount, $transactionType, $siteID, $trackingID,
            $voucherCode, $paymentType, $serviceTransactionID, $AID, $transactionSummaryID, $withdrawal, $balance){
        
        $startTrans = $this->connection->beginTransaction();
        
        try {

            $sql2 = "INSERT INTO transactionrequestlogs (TransactionReferenceID, Amount, StartDate, TransactionType, TerminalID, Status, SiteID, ServiceID, LoyaltyCardNumber, MID, UserMode, PaymentType, PaymentTrackingID, Option1, ServiceTransactionID) "
                ."VALUES (:trans_ref_id, :amount, NOW(6), :trans_type, :terminal_id, 0, :site_id, :service_id, :loyalty_card, :mid, :user_mode, :paymentType ,:trackingID, :voucher_code, :service_trans_id)";
            $param2 = array(
                ':trans_ref_id'=>$transactionReferenceID,':amount'=>$amount,
                ':trans_type'=>$transactionType, ':terminal_id'=>$terminalID,
                ':site_id'=>$siteID, ':service_id'=>$serviceID,
                ':loyalty_card'=>$loyaltyCardNumber, ':mid'=>$mid, ':user_mode'=>$userMode,
                ':trackingID'=>$trackingID, ':voucher_code'=>$voucherCode,
                ':paymentType'=>$paymentType,':service_trans_id'=>$serviceTransactionID);

            $command2 = $this->connection->createCommand($sql2);
            $command2->bindValues($param2);
            $command2->execute();




            try {
                $transactionRequestLogID = $this->connection->getLastInsertID();

                $sql3 = "UPDATE transactionsummary SET Withdrawal=:withdrawal,EndBalance=:endBalance, DateEnded=NOW(6) WHERE TransactionsSummaryID=:transactionSummaryID";

                $param3 = array(':withdrawal'=>$withdrawal, 'transactionSummaryID'=>$transactionSummaryID, ':endBalance'=>$balance);

                $command3 = $this->connection->createCommand($sql3);
                $command3->bindValues($param3);
                $command3->execute();

                try {

                $sql4 = "INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, SiteID, TerminalID, TransactionType, Amount, DateCreated, ServiceID, CreatedByAID, Status, LoyaltyCardNumber, MID, PaymentType) "
                        . "VALUES (:transactionReferenceID, :transactionSummaryID, :siteID, :terminalID, :transactionType, :amount, now(6), :serviceID, :createdByAID, :status, :loyaltyCardNumber, :mid, :paymentType)";

                $param4 = array(':transactionReferenceID'=>$transactionReferenceID, ':transactionSummaryID'=>$transactionSummaryID, ':siteID'=>$siteID, ':terminalID'=>$terminalID, 
                    ':transactionType'=>$transactionType, ':amount'=>$amount, ':serviceID'=>$serviceID, ':createdByAID'=>$AID, ':status'=>1, 
                    ':loyaltyCardNumber'=>$loyaltyCardNumber, ':mid'=>$mid, ':paymentType'=>$paymentType);

                $command4 = $this->connection->createCommand($sql4);
                $command4->bindValues($param4);
                $command4->execute();

                    try {

                    $sql5 = "DELETE FROM terminalsessions WHERE TerminalID = :terminalID AND LoyaltyCardNumber=:loyaltyCardNumber AND ServiceID=:serviceID";
                    $param5 = array(':terminalID'=>$terminalID, ':loyaltyCardNumber'=>$loyaltyCardNumber,':serviceID'=>$serviceID);

                    $command5 = $this->connection->createCommand($sql5);
                    $command5->bindValues($param5);
                    $command5->execute();

                        try {

                        $sql6 = "UPDATE transactionrequestlogs SET Status = :status, EndDate=NOW(6) WHERE TransactionRequestLogID=:trlID AND Amount=0 AND TerminalID=:terminalID AND SiteID=:siteID AND TransactionType=:transactionType";
                        $param6 = array(':status'=>1,':trlID'=>$transactionRequestLogID, 'trlID'=>$transactionRequestLogID,':terminalID'=>$terminalID, ':siteID'=>$siteID, ':transactionType'=>$transactionType);

                        $command6 = $this->connection->createCommand($sql6);
                        $command6->bindValues($param6);
                        $command6->execute();

                            try {
                                $startTrans->commit();

                                return 1;
                            } catch (PDOException $e) {
                                $startTrans->rollback();
                                Utilities::log($e->getMessage());
                                return 0;
                            }

                        } catch (PDOException $e) {
                            $startTrans->rollback();
                            Utilities::log($e->getMessage());
                            return 0;
                        }

                    } catch (PDOException $e) {
                        $startTrans->rollback();
                        Utilities::log($e->getMessage());
                        return 0;
                    }

                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }

            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }

        } catch (PDOException $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
      
    }
    
    public function getTransSumDate($transsumid){
        $sql = "SELECT DateStarted FROM transactionsummary WHERE TransactionsSummaryID = :transsumid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":transsumid", $transsumid);
        $result = $command->queryRow();
        
        return $result;
    }
    
    public function insertWithTerminalSession($mid, $terminalID, $serviceID, $loyaltyCardNumber, $userMode,
            $ubServiceLogin, $ubServicePassword, $ubHashedServicePassword,
            $transactionReferenceID, $amount, $transactionType, $siteID, $trackingID,
            $voucherCode, $paymentType, $serviceTransactionID, $deposit, $AID, $balance){
        
        $startTrans = $this->connection->beginTransaction();
        
        try {
            $sql = "INSERT INTO terminalsessions (TerminalID, ServiceID, LoyaltyCardNumber, MID, UserMode, UBServiceLogin, UBServicePassword, UBHashedServicePassword, DateStarted, LastBalance, LastTransactionDate, Status) 
                VALUES (:terminalID,:serviceID, :loyaltyCardNumber, :mid, :userMode, :ubServiceLogin, :ubServicePassword, :ubHashedServicePassword, now(6), :lastBalance, now(6), 0)";
            $param = array(
                ':terminalID'=>$terminalID,':serviceID'=>$serviceID,
                ':loyaltyCardNumber'=>$loyaltyCardNumber,':mid'=>$mid,
                ':userMode'=>$userMode,':ubServiceLogin'=>$ubServiceLogin,
                ':ubServicePassword'=>$ubServicePassword, 
                ':ubHashedServicePassword'=>$ubHashedServicePassword,
                ':lastBalance'=>$balance
                );
            $command = $this->connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
            
            try {
                
                $sql2 = "INSERT INTO transactionrequestlogs (TransactionReferenceID, Amount, StartDate, TransactionType, TerminalID, Status, SiteID, ServiceID, LoyaltyCardNumber, MID, UserMode, PaymentType, PaymentTrackingID, Option1, ServiceTransactionID) "
                    ."VALUES (:trans_ref_id, :amount, NOW(6), :trans_type, :terminal_id, 0, :site_id, :service_id, :loyalty_card, :mid, :user_mode, :paymentType ,:trackingID, :voucher_code, :service_trans_id)";
                $param2 = array(
                    ':trans_ref_id'=>$transactionReferenceID,':amount'=>$amount,
                    ':trans_type'=>$transactionType, ':terminal_id'=>$terminalID,
                    ':site_id'=>$siteID, ':service_id'=>$serviceID,
                    ':loyalty_card'=>$loyaltyCardNumber, ':mid'=>$mid, ':user_mode'=>$userMode,
                    ':trackingID'=>$trackingID, ':voucher_code'=>$voucherCode,
                    ':paymentType'=>$paymentType,':service_trans_id'=>$serviceTransactionID);
                
                $command2 = $this->connection->createCommand($sql2);
                $command2->bindValues($param2);
                $command2->execute();
                
                try {
                    $transactionRequestLogID = $this->connection->getLastInsertID();
                    
                    $sql3 = "INSERT INTO transactionsummary (SiteID, TerminalID, Deposit, DateStarted, DateEnded, CreatedByAID, StartBalance, LoyaltyCardNumber, MID) "
                            . "VALUES (:siteID, :terminalID, :deposit, now(6), 0, :createdByAID, :startBalance, :loyaltyCardNumber, :mid)";
                    
                    $param3 = array(':siteID'=>$siteID, ':terminalID'=>$terminalID, ':deposit'=>$deposit, ':createdByAID'=>$AID, ':loyaltyCardNumber'=>$loyaltyCardNumber, ':mid'=>$mid, ':startBalance'=>$balance);
                    
                    $command3 = $this->connection->createCommand($sql3);
                    $command3->bindValues($param3);
                    $command3->execute();
                    
                    try {
                    $transactionSummaryID = $this->connection->getLastInsertID();
                    
                    $sql4 = "INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, SiteID, TerminalID, TransactionType, Amount, DateCreated, ServiceID, CreatedByAID, Status, LoyaltyCardNumber, MID, PaymentType) "
                            . "VALUES (:transactionReferenceID, :transactionSummaryID, :siteID, :terminalID, :transactionType, :amount, now(6), :serviceID, :createdByAID, :status, :loyaltyCardNumber, :mid, :paymentType)";
                    
                    $param4 = array(':transactionReferenceID'=>$transactionReferenceID, ':transactionSummaryID'=>$transactionSummaryID, ':siteID'=>$siteID, ':terminalID'=>$terminalID, 
                        ':transactionType'=>$transactionType, ':amount'=>$amount, ':serviceID'=>$serviceID, ':createdByAID'=>$AID, ':status'=>1, 
                        ':loyaltyCardNumber'=>$loyaltyCardNumber, ':mid'=>$mid, ':paymentType'=>$paymentType);
                    
                    $command4 = $this->connection->createCommand($sql4);
                    $command4->bindValues($param4);
                    $command4->execute();
                    
                        try {

                        $sql5 = "UPDATE terminalsessions SET TransactionSummaryID = :transactionSummaryID WHERE TerminalID=:terminalID";
                        $param5 = array(':transactionSummaryID'=>$transactionSummaryID,':terminalID'=>$terminalID);

                        $command5 = $this->connection->createCommand($sql5);
                        $command5->bindValues($param5);
                        $command5->execute();
                        
                            try {

                            $sql6 = "UPDATE transactionrequestlogs SET Status = :status, EndDate=now(6) WHERE TransactionRequestLogID=:trlID";
                            $param6 = array(':status'=>1,':trlID'=>$transactionRequestLogID);

                            $command6 = $this->connection->createCommand($sql6);
                            $command6->bindValues($param6);
                            $command6->execute();
                            
                                try {
                                    $startTrans->commit();
                                    
                                    return 1;
                                } catch (PDOException $e) {
                                    $startTrans->rollback();
                                    Utilities::log($e->getMessage());
                                    return 0;
                                }

                            } catch (PDOException $e) {
                                $startTrans->rollback();
                                Utilities::log($e->getMessage());
                                return 0;
                            }

                        } catch (PDOException $e) {
                            $startTrans->rollback();
                            Utilities::log($e->getMessage());
                            return 0;
                        }
                        
                    } catch (PDOException $e) {
                        $startTrans->rollback();
                        Utilities::log($e->getMessage());
                        return 0;
                    }
                    
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }
                
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
             
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
}
