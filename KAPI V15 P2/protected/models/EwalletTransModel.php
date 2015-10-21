<?php
/**
 * EwalletTrans Model
 * Created by: Mark Kenneth Esguerra
 * Date: Sep 21, 2015
 */
class EwalletTransModel extends CFormModel {
    
    private $_connection; //kronus
    private $_connection4; //stacker
    private $_connection3; //membership
    
    CONST STATUS_PENDING = 0;
    CONST STATUS_SUCCESSFUL = 1;
    CONST STATUS_FAILED = 2;
    
    CONST SOURCE_CASHIER = 0;
    CONST SOURCE_GENESIS = 1;
    
    public function __construct() {
        $this->_connection = Yii::app()->db; //kronus
        $this->_connection4 = Yii::app()->db4; //stacker
        $this->_connection3 = Yii::app()->db3;
    }
    
    public function insertEWalletTrans($siteID, $mid, $cardnumber, $amount, 
                                   $fromBalance, $serviceID, $usermode, $paymentType, $transType,  
                                   $stackerBatchID, $terminalID, $createdByAID, $transSummaryID, 
                                   $stackerdetailid = null) {
        $pdo = $this->_connection->beginTransaction();
        
        try {
            $sql = "INSERT INTO ewallettrans (
                            Status, 
                            Source, 
                            StackerDetailID, 
                            StartDate, 
                            SiteID, 
                            MID, 
                            LoyaltyCardNumber, 
                            Amount, 
                            FromBalance, 
                            TransType, 
                            ServiceID, 
                            UserMode, 
                            PaymentType, 
                            StackerSummaryID, 
                            TerminalID, 
                            CreatedByAID, 
                            TransactionSummaryID
            ) VALUES (
                            :status, 
                            :source, 
                            :stackerdetailid,
                            NOW(6), 
                            :siteid, 
                            :mid,
                            :cardnumber, 
                            :amount,
                            :frombalance, 
                            :transtype, 
                            :serviceid,
                            :usermode, 
                            :paymenttype, 
                            :stackersummid, 
                            :terminalid,
                            :createdbyaid, 
                            :transsummid
            )";
            $command = $this->_connection->createCommand($sql);
            $command->bindValues(array(
                ':status' => self::STATUS_PENDING, 
                ':source' => self::SOURCE_GENESIS, 
                ':stackerdetailid' => $stackerdetailid, 
                ':siteid' => $siteID, 
                ':mid' => $mid, 
                ':cardnumber' => $cardnumber, 
                ':amount' => $amount, 
                ':frombalance' => $fromBalance, 
                ':transtype' => $transType, 
                ':serviceid' => $serviceID, 
                ':usermode' => $usermode, 
                ':paymenttype' => $paymentType, 
                ':stackersummid' => $stackerBatchID,
                ':terminalid' => $terminalID, 
                ':createdbyaid' => $createdByAID, 
                ':transsummid' => $transSummaryID
            ));
            $result = $command->execute();
            $ewallet_trans_id = $this->_connection->getLastInsertID();
            if ($result > 0) {
                try {
                    $pdo->commit();
                    return $ewallet_trans_id;
                }
                catch (CDbException $e) {
                    $pdo->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
            }
            else {
                $pdo->rollback();
                return false;
            }
        }
        catch (CDbException $e){
            $pdo->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }
    /**
     * Update Ewallet Transaction
     * @param type $eWalletransID
     * @param type $stackerDetailID
     * @param type $status
     * @param type $serviceTransID
     * @param type $serviceTransStat
     * @param type $aid
     * @param type $toBalance
     * @param type $amount
     * @param type $transSummaryID
     * @param type $mid
     * @param type $stackerBatchID
     * @return type
     */
    public function updateEwalletTrans($eWalletransID, $stackerDetailID, $status, $serviceTransID, $serviceTransStat, 
                                       $aid, $serviceID = null, $toBalance = null, $amount = null, $transSummaryID = null, 
                                       $transaction_id = null, $mid = null, $stackerBatchID = null, $ticketCode = '') {
        $pdo = $this->_connection->beginTransaction();
        //get transaction type
        $transtype = $this->getTransType($eWalletransID);
        switch ($transtype) {
            case "D": //DEPOSIT
                if ($status == self::STATUS_FAILED) { //transaction failed
                    try {
                        //update ewallettrans table
                        $sql = "UPDATE npos.ewallettrans SET 
                                    EndDate = NOW(6),
                                    Status = :status, 
                                    ServiceTransactionID = :svctransid, 
                                    ServiceTransactionStatus = :svctransstat, 
                                    UpdatedByAID = :aid 
                                WHERE EwalletTransID = :ewallettransid
                                "; 
                        $command = $this->_connection->createCommand($sql);
                        $command->bindValues(array(":status" => $status, 
                                                   ":svctransid" => $serviceTransID, 
                                                   ":svctransstat" => $serviceTransStat, 
                                                   ":aid" => $aid, 
                                                   ":ewallettransid" => $eWalletransID));
                        $result = $command->execute();
                        if ($result > 0) {
                            try {
                                //update stackermanagement.stackerdetails table
                                $sql = "UPDATE stackermanagement.stackerdetails 
                                        SET EwalletTransID = :ewalletransid 
                                        WHERE StackerDetailID = :stackerdetailid";
                                $command2 = $this->_connection->createCommand($sql);
                                $command2->bindValue(":ewalletransid", $eWalletransID);
                                $command2->bindValue(":stackerdetailid", $stackerDetailID);
                                $result = $command2->execute();
                                if ($result > 0) {
                                    try {
                                        $pdo->commit();
                                        return array('TransCode' => 0, 
                                                     'TransMsg' => 'Transaction successfully updated as failed.');
                                    }
                                    catch (CDbException $e) {
                                        $pdo->rollback();
                                        Utilities::log($e->getMessage());
                                        return array('TransCode' => 1, 
                                                     'TransMsg' => 'An error occured while updating records.');
                                    }
                                }
                                else {
                                    $pdo->rollback();
                                    return array('TransCode' => 1, 
                                                 'TransMsg' => 'Transaction Failed.');
                                }
                            }
                            catch (CDbException $e) {
                                $pdo->rollback();
                                Utilities::log($e->getMessage());
                                return array('TransCode' => 1, 
                                             'TransMsg' => 'An error occured while updating records.'); 
                            }
                        }
                        else {
                            $pdo->rollback();
                            return array('TransCode' => 1, 
                                         'TransMsg' => 'An error occured while updating records.');
                        }
                    }
                    catch (CDbException $e) {
                        $pdo->rollback();
                        Utilities::log($e->getMessage());
                        return array('TransCode' => 1, 
                                     'TransMsg' => 'An error occured while updating records.');
                    }   
                }
                else { //transaction successful
                    $stackerSummaryModel        = new StackerSummaryModel();
                    $transactionSummaryModel    = new TransactionSummaryModel();
                    try {
                        //update ewallettrans tbl
                        $sql = "UPDATE npos.ewallettrans SET 
                                    EndDate = NOW(6),
                                    ToBalance = :tobalance, 
                                    Status = :status, 
                                    ServiceTransactionID = :svctransid, 
                                    ServiceTransactionStatus = :svctransstat, 
                                    UpdatedByAID = :aid 
                                WHERE EwalletTransID = :ewallettransid
                                "; 
                        $command = $this->_connection->createCommand($sql);
                        $command->bindValues(array(":tobalance" => $toBalance, 
                                                   ":status" => $status, 
                                                   ":svctransid" => $serviceTransID, 
                                                   ":svctransstat" => $serviceTransStat, 
                                                   ":aid" => $aid, 
                                                   ":ewallettransid" => $eWalletransID));
                        $result = $command->execute();
                        if ($result > 0) {
                            try {
                                //update stackerdetails table
                                $sql = "UPDATE stackermanagement.stackerdetails 
                                        SET EwalletTransID = :ewalletransid 
                                        WHERE StackerDetailID = :stackerdetailid";
                                $command2 = $this->_connection->createCommand($sql);
                                $command2->bindValue(":ewalletransid", $eWalletransID);
                                $command2->bindValue(":stackerdetailid", $stackerDetailID);
                                $result = $command2->execute();
                                if ($result > 0) {
                                    try {
                                        //get current total walletreloads
                                        $walletReloads = $transactionSummaryModel->getWalletReloads($transSummaryID);
                                        $walletReloaded = $walletReloads + $amount;
                                        //update transactio summary
                                        $sql = "UPDATE npos.transactionsummary 
                                                SET WalletReloads = :walletreloaded 
                                                WHERE TransactionsSummaryID = :transsummid";
                                        $command4 = $this->_connection->createCommand($sql);
                                        $command4->bindValue(":walletreloaded", $walletReloaded);
                                        $command4->bindValue(":transsummid", $transSummaryID);
                                        $result = $command4->execute();

                                        if ($result > 0) {
                                            try {
                                                $sql = "UPDATE membership.memberservices 
                                                        SET CurrentBalance = :currentbalance, 
                                                            LastTransaction = :lasttrans, 
                                                            CurrentBalanceLastUpdate = NOW(6)   
                                                         WHERE MID = :mid AND ServiceID = :serviceid";
                                                $command5 = $this->_connection->createCommand($sql);
                                                $command5->bindValues(array(":currentbalance" => $toBalance, 
                                                                           ":lasttrans" => "Load-".$eWalletransID, 
                                                                           ":mid" => $mid, 
                                                                           ":serviceid" => $serviceID));
                                                $result = $command5->execute();
                                                if ($result > 0) {
                                                    try { //try and catch for PDO commiting
                                                        $pdo->commit();
                                                        return array('TransCode' => 0, 
                                                                     'TransMsg' => 'Transaction successful.');
                                                    }
                                                    catch (CDbException $e) {
                                                        $pdo->rollback();
                                                        Utilities::log($e->getMessage());
                                                        return array('TransCode' => 1, 
                                                                     'TransMsg' => 'An error occured while updating records.');
                                                    }
                                                }
                                                else {
                                                    $pdo->rollback();
                                                    return array('TransCode' => 1, 
                                                                 'TransMsg' => 'Transaction Failed.');
                                                }
                                            }
                                            catch (CDbException $e) {
                                                $pdo->rollback();
                                                Utilities::log($e->getMessage());
                                                return array('TransCode' => 1, 
                                                             'TransMsg' => 'An error occured while updating records.');
                                            }
                                        }
                                        else {
                                            $pdo->rollback();
                                            return array('TransCode' => 1, 
                                                         'TransMsg' => 'Transaction Failed.');
                                        }

                                    }
                                    catch (CDbException $e) {
                                        $pdo->rollback();
                                        Utilities::log($e->getMessage());
                                        return array('TransCode' => 1, 
                                                     'TransMsg' => 'An error occured while updating records.');
                                    }
                                }
                                else {
                                    $pdo->rollback();
                                    return array('TransCode' => 1, 
                                                 'TransMsg' => 'Transaction Failed.');
                                }
                            }
                            catch (CDbException $e) {
                                $pdo->rollback();
                                Utilities::log($e->getMessage());
                                return array('TransCode' => 1, 
                                             'TransMsg' => 'An error occured while updating records.'); 
                            }
                        }
                        else {
                            $pdo->rollback();
                            return array('TransCode' => 1, 
                                         'TransMsg' => 'An error occured while updating records.');
                        }
                    }
                    catch (CDbException $e) {
                        $pdo->rollback();
                        Utilities::log($e->getMessage());
                        return array('TransCode' => 1, 
                                     'TransMsg' => 'An error occured while updating records.');
                    }
                }
                break;
            case "W": //WITHDRAW
                if ($status == self::STATUS_FAILED) {
                    try {
                        //update ewallettrans table
                        $sql = "UPDATE npos.ewallettrans SET 
                                    EndDate = NOW(6),
                                    Status = :status, 
                                    ServiceTransactionID = :svctransid, 
                                    ServiceTransactionStatus = :svctransstat, 
                                    UpdatedByAID = :aid 
                                WHERE EwalletTransID = :ewallettransid
                                "; 
                        $command = $this->_connection->createCommand($sql);
                        $command->bindValues(array(":status" => $status, 
                                                   ":svctransid" => $serviceTransID, 
                                                   ":svctransstat" => $serviceTransStat, 
                                                   ":aid" => $aid, 
                                                   ":ewallettransid" => $eWalletransID));
                        $result = $command->execute();
                        if ($result > 0) {
                            try {
                                $pdo->commit();
                                return array('TransCode' => 0, 
                                             'TransMsg' => 'Transaction successfully updated as failed.');
                            }
                            catch (CDbException $e) {
                                $pdo->rollback();
                                Utilities::log($e->getMessage());
                                return array('TransCode' => 1, 
                                             'TransMsg' => 'An error occurred while updating.');
                            }
                        }
                        else {
                            $pdo->rollback();
                            return array('TransCode' => 1, 
                                         'TransMsg' => 'Transaction Failed.');
                        }
                    }
                    catch (CDbException $e) {
                        $pdo->rollback();
                        Utilities::log($e->getMessage());
                        return array('TransCode' => 0, 
                                     'TransMsg' => 'An error occurred while updating.');
                    }
                }   
                else { //TRANSACTION SUCCESSFUL
                    try {
                        //update ewallettrans table
                        $sql = "UPDATE npos.ewallettrans 
                                SET Status = :status,   
                                    EndDate = NOW(6), 
                                    ToBalance = :tobalance, 
                                    ServiceTransactionID = :svctransid, 
                                    ServiceTransactionStatus = :svctransstat, 
                                    UpdatedByAID = :aid 
                                WHERE EwalletTransID = :ewallettransid";
                        $command = $this->_connection->createCommand($sql);
                        $command->bindValues(array(":status" => $status, 
                                                   ":tobalance" => $toBalance, 
                                                   ":svctransid" => $serviceTransID, 
                                                   ":svctransstat" => $serviceTransStat, 
                                                   ":aid" => $aid, 
                                                   ":ewallettransid" => $eWalletransID));
                        $result = $command->execute();
                        if ($result > 0) {
                            //update stackersummary table
                            try {
                                $sql = "UPDATE stackermanagement.stackersummary 
                                        SET Withdrawal = :amount, 
                                            TicketCode = :ticketcode, 
                                            UpdatedByAID = :aid, 
                                            DateUpdated = NOW(6), 
                                            EwalletTransID = :ewallettransid, 
                                            Status = :status 
                                        WHERE StackerSummaryID = :stackerbatchid";
                                $command = $this->_connection->createCommand($sql);
                                $command->bindValues(array(":amount" => $amount, 
                                                           ":ticketcode" => $ticketCode, 
                                                           ":aid" => $aid, 
                                                           ":status" => 5, 
                                                           ":ewallettransid" => $eWalletransID, 
                                                           ":stackerbatchid" => $stackerBatchID));
                                $result = $command->execute();
                                if ($result > 0) {
                                    try {
                                        //update memberservices
                                        $sql = "UPDATE membership.memberservices 
                                                SET CurrentBalance = :currentbalance, 
                                                    LastTransaction = :lasttrans, 
                                                    CurrentBalanceLastUpdate = NOW(6)   
                                                 WHERE MID = :mid AND ServiceID = :serviceid";
                                        $command = $this->_connection->createCommand($sql);
                                        $command->bindValues(array(":currentbalance" => $toBalance, 
                                                                   ":lasttrans" => "Withdraw-".$eWalletransID, 
                                                                   ":mid" => $mid, 
                                                                   ":serviceid" => $serviceID));
                                        $result = $command->execute();
                                        if ($result > 0) {
                                            try {
                                                $pdo->commit();
                                                return array('TransCode' => 0, 
                                                             'TransMsg' => 'Transaction Successful.');
                                            }
                                            catch (CDbException $e) {
                                                $pdo->rollback();
                                                Utilities::log($e->getMessage());
                                                return array('TransCode' => 1, 
                                                             'TransMsg' => 'An error occurs while updating.');
                                            }
                                        }
                                        else {
                                            $pdo->rollback();
                                            return array('TransCode' => 1, 
                                                         'TransMsg' => 'Transaction Failed.');
                                        }
                                    }
                                    catch(CDbException $e) {
                                        $pdo->rollback();
                                        Utilities::log($e->getMessage());
                                        return array('TransCode' => 1, 
                                                     'TransMsg' => 'An error occurs while updating.');
                                    }
                                }
                                else {
                                    $pdo->rollback();
                                    return array('TransCode' => 1, 
                                                 'TransMsg' => 'Transaction Failed.');
                                }
                            }
                            catch (CDbException $e) {
                                $pdo->rollback();
                                Utilities::log($e->getMessage());
                                return array('TransCode' => 1, 
                                             'TransMsg' => 'An error occurs while updating.');
                            }
                        }
                        else {
                            $pdo->rollback();
                            return array('TransCode' => 1, 
                                         'TransMsg' => 'Transaction Failed.');
                        }
                    }
                    catch (CDbException $e) {
                        $pdo->rollback();
                        Utilities::log($e->getMessage());
                        return array('TransCode' => 1, 
                                     'TransMsg' => 'An error occurs while updating.');
                    }
                }
            break;
            default: 
                break;
        }
    }
    public function getTransactionDate($ewalletTransID){
        $sql = "SELECT StartDate FROM ewallettrans WHERE EwalletTransID = :ewallettransid";
        $param = array(":ewallettransid"=>$ewalletTransID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['StartDate'];
    }
    /**
     * Get Transaction type via EwalletTransID
     * @param type $ewallet_trans_id
     * @return boolean
     */
    function getTransType($ewallet_trans_id) {
        $sql = "SELECT TransType FROM ewallettrans 
                WHERE EwalletTransID = :ewallettransid";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":ewallettransid", $ewallet_trans_id);
        $result = $command->queryRow();
        if (is_array($result)) {
            return $result['TransType'];
        }
        else {
            return false;
        }
    }
}
?>
