<?php

/**
 * @description of StackerSummaryModel
 * @author JunJun S. Hernandez <jshernandez@philweb.com.ph>
 * @datecreated 11/11/13
 */
class StackerSummaryModels {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
        $this->_connection2 = Yii::app()->db2;
    }

    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new TerminalsModel();
        return self::$_instance;
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param int $MID
     * @param int $stackerSessionID
     * @return boolean success|failed
     */
    public function insertStackerSummaryDetails($MID, $stackerSessionID, $AID) {
        $beginTrans = $this->_connection->beginTransaction();

        try {

            $sql = "INSERT INTO stackersummary(StackerSessionID, MID, DateCreated, CreatedByAID) VALUES(:stacker_session_id, :mid, NOW(6), :aid)";

            $param = array(':stacker_session_id' => $stackerSessionID, ':mid' => $MID, ':aid' => $AID);

            $command = $this->_connection->createCommand($sql);

            $command->bindValues($param);

            $command->execute();

            $stackerBatchID = $this->_connection->getLastInsertID();

            try {
                $beginTrans->commit();
                return $stackerBatchID;
            } catch (PDOException $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        } catch (Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 01/07/14
     * @param int $egmSessionID
     * @return boolean success|failed
     */
    public function updateEGMStackerBatchID($egmSessionID, $MID, $stackerSessionID, $AID) {

        $beginTrans = $this->_connection->beginTransaction();

        try {

            $sql = "INSERT INTO stackersummary(StackerSessionID, MID, DateCreated, CreatedByAID) VALUES(:stacker_session_id, :mid, NOW(6), :aid)";

            $param = array(':stacker_session_id' => $stackerSessionID, ':mid' => $MID, ':aid' => $AID);

            $command = $this->_connection->createCommand($sql);

            $command->bindValues($param);

            $command->execute();

            $stackerBatchID = $this->_connection->getLastInsertID();

            try {
                $beginTrans2 = $this->_connection2->beginTransaction();
                $sql = "UPDATE egmsessions SET StackerBatchID = :stacker_batch_id WHERE EGMSessionID = :egm_session_id";

                $param = array(':stacker_batch_id' => $stackerBatchID, ':egm_session_id' => $egmSessionID);

                $command = $this->_connection2->createCommand($sql);

                $command->bindValues($param);

                $command->execute();

                try {
                    $beginTrans->commit();
                    $beginTrans2->commit();
                    return $stackerBatchID;
                } catch (PDOException $e) {
                    $beginTrans->rollback();
                    $beginTrans2->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }
            } catch (PDOException $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        } catch (Exception $e) {
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param int $stackerSessionID
     * @param int $amount
     * @return boolean success|failed
     */
    public function updateStackerSummaryDeposit($stackerSessionID, $amount, $stackerSummaryID, $transType, $paymentType, $voucherCode, $trackingID, $MID) {
        
        $beginTrans = $this->_connection->beginTransaction();

        try {

            $sql = "UPDATE stackersummary SET Deposit = :amount, DateUpdated = NOW(6) WHERE StackerSessionID = :stacker_session_id";

            $param = array(':stacker_session_id' => $stackerSessionID, ':amount' => $amount);

            $command = $this->_connection->createCommand($sql);

            $command->bindValues($param);

            $command->execute();

            try {
                $sql = "INSERT INTO stackerdetails(StackerSummaryID, Amount, TransactionType, PaymentType, VoucherCode, DateCreated, TrackingID)
                                        VALUES(:stacker_summary_id, :amount, :trans_type, :payment_type, :voucher_code, NOW(6), :tracking_id)";

                $param = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $amount, ':trans_type' => $transType,
                    ':payment_type' => $paymentType, ':voucher_code' => $voucherCode, ':tracking_id' => $trackingID);

                $command = $this->_connection->createCommand($sql);

                $command->bindValues($param);

                $command->execute();
                try {
                    $beginTrans->commit();
                    return true;
                } catch (PDOException $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
            } catch (PDOException $e) {
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

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param int $stackerSessionID
     * @param int $amount
     * @return boolean success|failed
     */
    public function updateStackerSummaryReload($stackerSessionID, $amount, $stackerSummaryID, $transType, $paymentType, $voucherCode, $trackingID, $MID) {
        $beginTrans = $this->_connection->beginTransaction();

        try {

            $sql = "UPDATE stackersummary SET Reload = :amount, DateUpdated = NOW(6) WHERE StackerSessionID = :stacker_session_id";

            $param = array(':stacker_session_id' => $stackerSessionID, ':amount' => $amount);

            $command = $this->_connection->createCommand($sql);

            $command->bindValues($param);

            $command->execute();

            try {
                $sql = "INSERT INTO stackerdetails(StackerSummaryID, Amount, TransactionType, PaymentType, VoucherCode, DateCreated, TrackingID)
                                        VALUES(:stacker_summary_id, :amount, :trans_type, :payment_type, :voucher_code, NOW(6), :tracking_id)";

                $param = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $amount, ':trans_type' => $transType,
                    ':payment_type' => $paymentType, ':voucher_code' => $voucherCode, ':tracking_id' => $trackingID);

                $command = $this->_connection->createCommand($sql);

                $command->bindValues($param);

                $command->execute();
                try {
                    $beginTrans->commit();
                    return true;
                } catch (PDOException $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
            } catch (PDOException $e) {
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

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param int $stackerSessionID
     * @return obj
     */
    public function getSummaryIDByStackerSessionId($stackerSessionID) {
        $query = 'SELECT StackerSummaryID FROM stackersummary WHERE StackerSessionID = :stacker_session_id';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":stacker_session_id" => $stackerSessionID
        ));

        $result = $sql->queryRow();

        return $result['StackerSummaryID'];
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param int $stackerSessionID
     * @return int
     */
    public function isStackerBatchIdExists($stackerSummaryID) {
        $query = 'SELECT COUNT(StackerSummaryID) AS ctrStackerSummaryID FROM stackersummary WHERE StackerSummaryID = :stacker_summary_id';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":stacker_summary_id" => $stackerSummaryID
        ));

        $result = $sql->queryRow();

        return $result['ctrStackerSummaryID'];
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param int $stackerSessionID
     * @return obj
     */
    public function getMIDByStackerSummaryID($stackerSummaryID) {
        $query = 'SELECT MID FROM stackersummary WHERE StackerSummaryID = :stacker_summary_id';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":stacker_summary_id" => $stackerSummaryID
        ));

        $result = $sql->queryRow();

        return $result['MID'];
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param int $stackerSessionID
     * @return obj
     */
    public function getTotalDepositByID($stackerSummaryID) {
        $query = 'SELECT SUM(Deposit) TotalDeposit FROM stackersummary WHERE StackerSummaryID = :stacker_summary_id';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":stacker_summary_id" => $stackerSummaryID
        ));

        $result = $sql->queryRow();
        return $result['TotalDeposit'];
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param int $stackerSessionID
     * @return obj
     */
    public function getTotalReloadByID($stackerSummaryID) {
        $query = 'SELECT SUM(Reload) TotalReload FROM stackersummary WHERE StackerSummaryID = :stacker_summary_id';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":stacker_summary_id" => $stackerSummaryID
        ));

        $result = $sql->queryRow();
        return $result['TotalReload'];
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 03/11/14
     * @param int $stackerSessionID
     * @return obj
     */
    public function updateSummaryStatus($mid, $terminalID, $terminalName, $casinoID, $aid) {
        $beginTrans = $this->_connection->beginTransaction();

        try {

            $sql = "UPDATE stackersummary SET Status = 3 WHERE TerminalName = :terminal_name AND Status = 0";

            $param = array(':terminal_name' => $terminalName);

            $command = $this->_connection->createCommand($sql);

            $command->bindValues($param);

            $command->execute();

            $stackerBatchID = $this->_connection->getLastInsertID();

            try {
                $beginTrans2 = $this->_connection2->beginTransaction();
                $sql = "INSERT INTO egmsessions (MID, TerminalID, ServiceID, DateCreated, CreatedByAID) VALUES (:mid, :terminal_id, :casino_id, NOW(6), :aid)";

                $param = array(':mid'=>$mid, ':terminal_id'=>$terminalID, ':casino_id'=>$casinoID, ':aid'=>$aid);

                $command = $this->_connection2->createCommand($sql);

                $command->bindValues($param);

                $command->execute();

                try {
                    $beginTrans->commit();
                    $beginTrans2->commit();
                    return $stackerBatchID;
                } catch (PDOException $e) {
                    $beginTrans->rollback();
                    $beginTrans2->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }
            } catch (PDOException $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        } catch (Exception $e) {
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    public function isStackerBatchIDAndTerminalMatched($stackerBatchID, $stackerSessionID) {
        $query = 'SELECT COUNT(StackerSummaryID) ctrStackerSummaryID FROM stackersummary WHERE StackerSummaryID = :stacker_batch_id AND StackerSessionID = :stacker_session_id';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(':stacker_batch_id' => $stackerBatchID, ':stacker_session_id' => $stackerSessionID));

        $result = $sql->queryRow();

        if (isset($result['ctrStackerSummaryID'])) {
            return $result['ctrStackerSummaryID'];
        } else {
            return 0;
        }
    }
    
    public function getDeposit($stackerBatchID, $stackerSessionID) {
        $query = 'SELECT Deposit FROM stackersummary WHERE StackerSummaryID = :stacker_batch_id AND StackerSessionID = :stacker_session_id';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(':stacker_batch_id' => $stackerBatchID, ':stacker_session_id' => $stackerSessionID));

        $result = $sql->queryRow();

        if (isset($result['Deposit'])) {
            return $result['Deposit'];
        } else {
            return 0;
        }
    }
    
    public function getCardNumber($stackerBatchID, $stackerSessionID, $MID) {
        $sql = 'SELECT mc.CardNumber FROM stackermanagement.stackersummary ss
                INNER JOIN loyaltydb.membercards mc
                WHERE ss.StackerSummaryID = :stacker_batch_id AND ss.StackerSessionID = :stacker_session_id AND ss.Status = 0 AND mc.Status = 1 AND mc.MID = :mid';
        $param = array(':stacker_batch_id'=>$stackerBatchID, ':stacker_session_id'=>$stackerSessionID, ':mid'=>$MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(!isset($result['CardNumber']))
            return false;
        return $result['CardNumber'];
    }
    
}

?>