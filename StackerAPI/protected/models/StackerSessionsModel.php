<?php

/**
 * @description of StackerSessionsModel
 * @author JunJun S. Hernandez <jshernandez@philweb.com.ph>
 * @datecreated 11 11, 13 1:11:44 PM
 */
class StackerSessionsModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }

    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new TerminalsModel();
        return self::$_instance;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param int $terminalName
     * @param datetime $dateStarted
     * @param int $isEnded
     * @return boolean success | failed transaction
     */
    public function insertStacker($terminalName, $stackerInfoID, $isEnded) {

        $beginTrans = $this->_connection->beginTransaction();
        try {
            $query = "INSERT INTO stackersessions(TerminalName, StackerInfoID, DateStarted, IsEnded) VALUES(:terminal_name, :stacker_info_id, NOW(6), :is_ended)";
            $sql = $this->_connection->createCommand($query);

            $sql->bindValues(array(
                ":terminal_name" => $terminalName,
                ":stacker_info_id" => $stackerInfoID,
                ":is_ended" => $isEnded
            ));
            $sql->execute();
            $stackerSessionID = $this->_connection->getLastInsertID();
            try {
                $beginTrans->commit();
                return $stackerSessionID;
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
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param datetime $dateEnded
     * @param int $isEnded
     * @return boolean success | failed transaction
     */
    public function removeStacker($stackerSessionID, $isEnded, $collectedBy) {

        $beginTrans = $this->_connection->beginTransaction();

        try {

            $sql = "UPDATE stackersessions SET DateEnded = NOW(6), IsEnded = :is_ended, DateCollected = NOW(6), CollectedBy = :collected_by WHERE StackerSessionID = :stacker_session_id";

            $param = array(':stacker_session_id' => $stackerSessionID, ':is_ended' => $isEnded, ':collected_by' => $collectedBy);

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
        } catch (Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $terminalName
     * @return boolean success | failed transaction
     */
    public function getStackerSessionIDByTerminalName($terminalName) {
        $sql = "SELECT StackerSessionID FROM stackersessions WHERE TerminalName = :terminal_name and IsEnded = 0";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':terminal_name' => $terminalName));
        $stacker = $command->queryRow();

        return $stacker['StackerSessionID'];
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $terminalName
     * @return boolean success | failed transaction
     */
    public function getStackerSessionDetails($stackerSessionID) {
        $sql = "SELECT Quantity, CashAmount, TotalAmount, CashCount, TicketCount FROM stackersessions WHERE StackerSessionID = :stacker_session_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':stacker_session_id' => $stackerSessionID));
        $stacker = $command->queryRow();

        return $stacker;
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param int $quantity
     * @param float $amount
     * @param float $totalAmount
     * @return boolean success|failed
     */
    public function updateStackerSessionsDetails($quantity, $amount, $totalAmount, $stackerSessionID, $transType, $paymentType, $voucherCode, $trackingID, $stackerBatchID, $finalTotalDeposit, $finalTotalReload, $AID) {
        $beginTrans = $this->_connection->beginTransaction();

        try {

            $sql = "UPDATE stackersessions SET Quantity = :quantity, CashAmount = :cash_amount, TotalAmount = :total_amount WHERE StackerSessionID = :stacker_session_id";

            $param = array(':quantity' => $quantity, ':cash_amount' => $amount, ':total_amount' => $totalAmount, ':stacker_session_id' => $stackerSessionID);

            $command = $this->_connection->createCommand($sql);

            $command->bindValues($param);

            $isupdated = $command->execute();

            try {
                if ($transType == CommonController::TRANS_TYPE_DEPOSIT) {
                    if ($quantity <= 1) {
                        $sql2 = "UPDATE stackersummary SET Deposit = :amount WHERE StackerSummaryID = :stacker_summary_id";
                        $param2 = array(':stacker_summary_id' => $stackerBatchID, ':amount' => $finalTotalDeposit);
                    } else {
                        $sql2 = "UPDATE stackersummary SET Deposit = :amount, DateUpdated = NOW(6), UpdatedByAID = :updated_by_aid WHERE StackerSummaryID = :stacker_summary_id";
                        $param2 = array(':stacker_summary_id' => $stackerBatchID, ':amount' => $finalTotalDeposit, ':updated_by_aid' => $AID);
                    }
                } else {
                    $sql2 = "UPDATE stackersummary SET Reload = :amount, DateUpdated = NOW(6), UpdatedByAID = :updated_by_aid, TicketCode = :voucher_code WHERE StackerSummaryID = :stacker_summary_id";
                    $param2 = array(':stacker_summary_id' => $stackerBatchID, ':amount' => $finalTotalReload, ':voucher_code' => $voucherCode, ':updated_by_aid' => $AID);
                }
                $command2 = $this->_connection->createCommand($sql2);
                $command2->bindValues($param2);
                $command2->execute();
                try {
                    if ($paymentType == CommonController::PAYMENT_TYPE_CASH) {
                        $sql3 = "INSERT INTO stackerdetails(StackerSummaryID, Amount, TransactionType, PaymentType, DateCreated, TrackingID, CreatedByAID)
                                        VALUES(:stacker_summary_id, :amount, :trans_type, :payment_type, NOW(6), :tracking_id, :created_by_aid)";
                        $param3 = array(':stacker_summary_id' => $stackerBatchID, ':amount' => $amount, ':trans_type' => $transType,
                            ':payment_type' => $paymentType, ':tracking_id' => $trackingID, ':created_by_aid' => $AID);
                    } else {
                        $sql3 = "INSERT INTO stackerdetails(StackerSummaryID, Amount, TransactionType, PaymentType, VoucherCode, DateCreated, TrackingID, CreatedByAID)
                                        VALUES(:stacker_summary_id, :amount, :trans_type, :payment_type, :voucher_code, NOW(6), :tracking_id, :created_by_aid)";
                        $param3 = array(':stacker_summary_id' => $stackerBatchID, ':amount' => $amount, ':trans_type' => $transType,
                            ':payment_type' => $paymentType, ':voucher_code' => $voucherCode, ':tracking_id' => $trackingID, ':created_by_aid' => $AID);
                    }

                    $command3 = $this->_connection->createCommand($sql3);

                    $command3->bindValues($param3);

                    $command3->execute();
                    try {
                        $beginTrans->commit();
                        return $isupdated;
                    } catch (PDOException $e) {
                        $beginTrans->rollback();
                        Utilities::log($e->getMessage());
                        return 0;
                    }
                } catch (PDOException $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }
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
     * @DateCreated 11/11/13
     * @param int $quantity
     * @param float $amount
     * @param float $totalAmount
     * @return boolean success|failed
     */
    public function updateStackerSessionsDetailsTickets($quantity, $amount, $totalAmount, $stackerSessionID, $transType, $paymentType, $voucherCode, $trackingID, $stackerBatchID, $finalTotalDeposit, $finalTotalReload, $AID, $ticketCount) {
        $dateCreated = "NOW(6)";
        $beginTrans = $this->_connection->beginTransaction();

        try {

            $sql = "UPDATE stackersessions SET Quantity = :quantity, TotalAmount = :total_amount, TicketCOunt = :ticket_count WHERE StackerSessionID = :stacker_session_id";

            $param = array(':quantity' => $quantity, ':total_amount' => $totalAmount, ':ticket_count' => $ticketCount, ':stacker_session_id' => $stackerSessionID);

            $command = $this->_connection->createCommand($sql);

            $command->bindValues($param);

            $isupdated = $command->execute();

            try {
                if ($transType == CommonController::TRANS_TYPE_DEPOSIT) {
                    if ($quantity <= 1) {
                        $sql2 = "UPDATE stackersummary SET Deposit = :amount, DateUpdated = NOW(6) WHERE StackerSummaryID = :stacker_summary_id";
                        $param2 = array(':amount' => $finalTotalDeposit, ':stacker_summary_id' => $stackerBatchID);
                    } else {
                        $sql2 = "UPDATE stackersummary SET Deposit = :amount, DateUpdated = NOW(6), UpdatedByAID = :updated_by_aid WHERE StackerSummaryID = :stacker_summary_id";
                        $param2 = array(':amount' => $finalTotalDeposit, ':stacker_summary_id' => $stackerBatchID, ':updated_by_aid' => $AID);
                    }
                } else {
                    $sql2 = "UPDATE stackersummary SET Reload = :amount, DateUpdated = NOW(6), Status = 4, UpdatedByAID = :updated_by_aid WHERE StackerSummaryID = :stacker_summary_id";
                    $param2 = array(':amount' => $finalTotalReload, ':stacker_summary_id' => $stackerBatchID, ':updated_by_aid' => $AID);
                }
                $command2 = $this->_connection->createCommand($sql2);
                $command2->bindValues($param2);
                $command2->execute();
                try {
                    if ($paymentType == CommonController::PAYMENT_TYPE_CASH) {
                        $sql3 = "INSERT INTO stackerdetails(StackerSummaryID, Amount, TransactionType, PaymentType, DateCreated, TrackingID, CreatedByAID)
                                        VALUES(:stacker_summary_id, :amount, :trans_type, :payment_type, NOW(6), :tracking_id, :created_by_aid)";
                        $param3 = array(':stacker_summary_id' => $stackerBatchID, ':amount' => $amount, ':trans_type' => $transType,
                            ':payment_type' => $paymentType, ':tracking_id' => $trackingID, ':created_by_aid' => $AID);
                    } else {
                        $sql3 = "INSERT INTO stackerdetails(StackerSummaryID, Amount, TransactionType, PaymentType, VoucherCode, DateCreated, TrackingID, CreatedByAID)
                                        VALUES(:stacker_summary_id, :amount, :trans_type, :payment_type, :voucher_code, NOW(6), :tracking_id, :created_by_aid)";
                        $param3 = array(':stacker_summary_id' => $stackerBatchID, ':amount' => $amount, ':trans_type' => $transType,
                            ':payment_type' => $paymentType, ':voucher_code' => $voucherCode, ':tracking_id' => $trackingID, ':created_by_aid' => $AID);
                    }

                    $command3 = $this->_connection->createCommand($sql3);

                    $command3->bindValues($param3);

                    $command3->execute();
                    try {
                        $beginTrans->commit();
                        return $isupdated;
                    } catch (PDOException $e) {
                        $beginTrans->rollback();
                        Utilities::log($e->getMessage());
                        return 0;
                    }
                } catch (PDOException $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }
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
     * @DateCreated 11/11/13
     * @param int $quantity
     * @param float $amount
     * @param float $totalAmount
     * @return boolean success|failed
     */
    public function updateStackerSessionsData($cashCount, $ticketCount, $quantity, $cashAmount, $totalAmount, $stackerSessionID, $denominationID, $denominationCount, $stackerSummaryID, $amount, $transType, $paymentType, $voucherCode, $trackingID, $finalTotalReload, $AID) {
//        var_dump($amount, $finalTotalReload); exit;
        $dateCreated = "NOW(6)";
        $beginTrans = $this->_connection->beginTransaction();

        try {
            $sql = "UPDATE stackersessions SET Quantity = :quantity, CashAmount = :cash_amount, TotalAmount = :total_amount, CashCount = :cash_count, TicketCount = :ticket_count WHERE StackerSessionID = :stacker_session_id";
            $param = array(':quantity' => $quantity, ':cash_amount' => $cashAmount, ':total_amount' => $totalAmount, ':cash_count' => $cashCount, ':ticket_count' => $ticketCount, ':stacker_session_id' => $stackerSessionID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            try {
                $sql1 = "UPDATE stackersessioncashdenom SET DenominationCount = :denomination_count WHERE StackerSessionID = :stacker_session_id AND DenominationID = :denominationID";
                $param1 = array(':stacker_session_id' => $stackerSessionID, ':denominationID' => $denominationID, ':denomination_count' => $denominationCount);
                $command1 = $this->_connection->createCommand($sql1);
                $command1->bindValues($param1);
                $command1->execute();

                try {

                    if ($transType == CommonController::TRANS_TYPE_DEPOSIT) {
                        $sql2 = "UPDATE stackersummary SET Deposit = :amount, DateUpdated = NOW(6) WHERE StackerSummaryID = :stacker_summary_id";
                        $param2 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $amount);
                    } else {
                        $sql2 = "UPDATE stackersummary SET Reload = :amount, DateUpdated = NOW(6), TicketCode = :voucher_code, Status = 4 WHERE StackerSummaryID = :stacker_summary_id";
                        $param2 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $finalTotalReload);
                    }
                    $command2 = $this->_connection->createCommand($sql2);
                    $command2->bindValues($param2);
                    $command2->execute();

                    try {
                        if ($paymentType == CommonController::PAYMENT_TYPE_CASH) {
                            $sql3 = "INSERT INTO stackerdetails(StackerSummaryID, Amount, TransactionType, PaymentType, DateCreated, TrackingID, CreatedByAID)
                                        VALUES(:stacker_summary_id, :amount, :trans_type, :payment_type, NOW(6), :tracking_id, :created_by_aid)";
                            $param3 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $amount, ':trans_type' => $transType,
                                ':payment_type' => $paymentType, ':tracking_id' => $trackingID, ':created_by_aid' => $AID);
                        } else {
                            $sql3 = "INSERT INTO stackerdetails(StackerSummaryID, Amount, TransactionType, PaymentType, VoucherCode, DateCreated, TrackingID)
                                        VALUES(:stacker_summary_id, :amount, :trans_type, :payment_type, :voucher_code, NOW(6), :tracking_id, :created_by_aid)";
                            $param3 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $amount, ':trans_type' => $transType,
                                ':payment_type' => $paymentType, ':voucher_code' => $voucherCode, ':tracking_id' => $trackingID, ':created_by_aid' => $AID);
                        }

                        $command3 = $this->_connection->createCommand($sql3);

                        $command3->bindValues($param3);

                        $command3->execute();

                        try {
                            $beginTrans->commit();
                            return 1;
                        } catch (PDOException $e) {
                            $beginTrans->rollback();
                            Utilities::log($e->getMessage());
                            return 0;
                        }
                    } catch (PDOException $e) {
                        $beginTrans->rollback();
                        Utilities::log($e->getMessage());
                        return 0;
                    }
                } catch (PDOException $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }
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

    public function updateStackerSessionsDataCashDenom($cashCount, $ticketCount, $quantity, $cashAmount, $totalAmount, $stackerSessionID, $denominationID, $denominationCount, $stackerSummaryID, $amount, $transType, $paymentType, $voucherCode, $trackingID, $finalTotalDeposit, $finalTotalReload, $AID) {
        $beginTrans = $this->_connection->beginTransaction();

        try {
            $sql = "UPDATE stackersessions SET Quantity = :quantity, CashAmount = :cash_amount, TotalAmount = :total_amount, CashCount = :cash_count, TicketCount = :ticket_count WHERE StackerSessionID = :stacker_session_id";
            $param = array(':quantity' => $quantity, ':cash_amount' => $cashAmount, ':total_amount' => $totalAmount, ':cash_count' => $cashCount, ':ticket_count' => $ticketCount, ':stacker_session_id' => $stackerSessionID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            try {
                $sql1 = "UPDATE stackersessioncashdenom SET DenominationCount = :denomination_count WHERE StackerSessionID = :stacker_session_id AND DenominationID = :denominationID";
                $param1 = array(':stacker_session_id' => $stackerSessionID, ':denominationID' => $denominationID, ':denomination_count' => $denominationCount);
                $command1 = $this->_connection->createCommand($sql1);
                $command1->bindValues($param1);
                $command1->execute();

                try {

                    if ($transType == CommonController::TRANS_TYPE_DEPOSIT) {
                        if ($quantity <= 1) {
                            $sql2 = "UPDATE stackersummary SET Deposit = :amount WHERE StackerSummaryID = :stacker_summary_id";
                            $param2 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $finalTotalDeposit);
                        } else {
                            $sql2 = "UPDATE stackersummary SET Deposit = :amount, DateUpdated = NOW(6), UpdatedByAID = :updated_by_aid WHERE StackerSummaryID = :stacker_summary_id";
                            $param2 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $finalTotalDeposit, ':updated_by_aid' => $AID);
                        }
                    } else {
                        $sql2 = "UPDATE stackersummary SET Reload = :amount, DateUpdated = NOW(6), TicketCode = :voucher_code, Status = 4, UpdatedByAID = :updated_by_aid WHERE StackerSummaryID = :stacker_summary_id";
                        $param2 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $finalTotalReload, ':voucher_code' => $voucherCode, ':updated_by_aid' => $AID);
                    }
                    $command2 = $this->_connection->createCommand($sql2);
                    $command2->bindValues($param2);
                    $command2->execute();

                    try {
                        if ($paymentType == CommonController::PAYMENT_TYPE_CASH) {
                            $sql3 = "INSERT INTO stackerdetails(StackerSummaryID, Amount, TransactionType, PaymentType, DateCreated, TrackingID, CreatedByAID)
                                        VALUES(:stacker_summary_id, :amount, :trans_type, :payment_type, NOW(6), :tracking_id, :created_by_aid)";
                            $param3 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $amount, ':trans_type' => $transType,
                                ':payment_type' => $paymentType, ':tracking_id' => $trackingID, ':created_by_aid' => $AID);
                        } else {
                            $sql3 = "INSERT INTO stackerdetails(StackerSummaryID, Amount, TransactionType, PaymentType, VoucherCode, DateCreated, TrackingID, CreatedByAID)
                                        VALUES(:stacker_summary_id, :amount, :trans_type, :payment_type, :voucher_code, NOW(6), :tracking_id, :created_by_aid)";
                            $param3 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $amount, ':trans_type' => $transType,
                                ':payment_type' => $paymentType, ':voucher_code' => $voucherCode, ':tracking_id' => $trackingID, ':created_by_aid' => $AID);
                        }

                        $command3 = $this->_connection->createCommand($sql3);

                        $command3->bindValues($param3);

                        $command3->execute();

                        try {
                            $beginTrans->commit();
                            return 1;
                        } catch (PDOException $e) {
                            $beginTrans->rollback();
                            Utilities::log($e->getMessage());
                            return 0;
                        }
                    } catch (PDOException $e) {
                        $beginTrans->rollback();
                        Utilities::log($e->getMessage());
                        return 0;
                    }
                } catch (PDOException $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }
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

    public function updateStackerSessionsInsertDataCashDenom($cashCount, $ticketCount, $quantity, $cashAmount, $totalAmount, $stackerSessionID, $denominationID, $denominationCount, $stackerSummaryID, $amount, $transType, $paymentType, $voucherCode, $trackingID, $finalTotalDeposit, $finalTotalReload, $AID) {
        $beginTrans = $this->_connection->beginTransaction();
        try {
            $sql = "UPDATE stackersessions SET Quantity = :quantity, CashAmount = :cash_amount, TotalAmount = :total_amount, CashCount = :cash_count, TicketCount = :ticket_count WHERE StackerSessionID = :stacker_session_id";
            $param = array(':quantity' => $quantity, ':cash_amount' => $cashAmount, ':total_amount' => $totalAmount, ':cash_count' => $cashCount, ':ticket_count' => $ticketCount, ':stacker_session_id' => $stackerSessionID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            try {
                $sql1 = "INSERT INTO stackersessioncashdenom(StackerSessionID, DenominationID, DenominationCount) VALUES(:stacker_session_id, :denominationID, :denomination_count)";
                $param1 = array(':stacker_session_id' => $stackerSessionID, ':denominationID' => $denominationID, ':denomination_count' => $denominationCount);
                $command1 = $this->_connection->createCommand($sql1);
                $command1->bindValues($param1);
                $command1->execute();

                try {

                    if ($transType == CommonController::TRANS_TYPE_DEPOSIT) {
                        if ($quantity <= 1) {
                            $sql2 = "UPDATE stackersummary SET Deposit = :amount WHERE StackerSummaryID = :stacker_summary_id";
                            $param2 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $finalTotalDeposit);
                        } else {
                            $sql2 = "UPDATE stackersummary SET Deposit = :amount, DateUpdated = NOW(6), UpdatedByAID = :updated_by_aid WHERE StackerSummaryID = :stacker_summary_id";
                            $param2 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $finalTotalDeposit, ':updated_by_aid' => $AID);
                        }
                    } else {
                        $sql2 = "UPDATE stackersummary SET Reload = :amount,DateUpdated = NOW(6), Status = 4, TicketCode = :voucher_code WHERE StackerSummaryID = :stacker_summary_id";
                        $param2 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $finalTotalReload, ':voucher_code' => $voucherCode);
                    }
                    $command2 = $this->_connection->createCommand($sql2);
                    $command2->bindValues($param2);
                    $command2->execute();

                    try {
                        if ($paymentType == CommonController::PAYMENT_TYPE_CASH) {
                            $sql3 = "INSERT INTO stackerdetails(StackerSummaryID, Amount, TransactionType, PaymentType, DateCreated, TrackingID, CreatedByAID)
                                        VALUES(:stacker_summary_id, :amount, :trans_type, :payment_type, NOW(6), :tracking_id, :created_by_aid)";
                            $param3 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $amount, ':trans_type' => $transType,
                                ':payment_type' => $paymentType, ':tracking_id' => $trackingID, ':created_by_aid' => $AID);
                        } else {
                            $sql3 = "INSERT INTO stackerdetails(StackerSummaryID, Amount, TransactionType, PaymentType, VoucherCode, DateCreated, TrackingID, CreatedByAID)
                                        VALUES(:stacker_summary_id, :amount, :trans_type, :payment_type, :voucher_code, NOW(6), :tracking_id, :created_by_aid)";
                            $param3 = array(':stacker_summary_id' => $stackerSummaryID, ':amount' => $amount, ':trans_type' => $transType,
                                ':payment_type' => $paymentType, ':voucher_code' => $voucherCode, ':tracking_id' => $trackingID, ':created_by_aid' => $AID);
                        }

                        $command3 = $this->_connection->createCommand($sql3);

                        $command3->bindValues($param3);

                        $command3->execute();

                        try {
                            $beginTrans->commit();
                            return 1;
                        } catch (PDOException $e) {
                            $beginTrans->rollback();
                            Utilities::log($e->getMessage());
                            return 0;
                        }
                    } catch (PDOException $e) {
                        $beginTrans->rollback();
                        Utilities::log($e->getMessage());
                        return 0;
                    }
                } catch (PDOException $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return 0;
                }
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
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $terminalName
     * @return object
     */
    public function isTerminalExists($terminalName) {
        $sql = "SELECT COUNT(TerminalName) ctrTerminal FROM stackersessions WHERE TerminalName = :terminal_name";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':terminal_name' => $terminalName));
        $result = $command->queryRow();

        if (isset($result['ctrTerminal'])) {
            return $result['ctrTerminal'];
        } else {
            return 0;
        }
    }

    public function isTerminalSessionUnendedExists($terminalCode) {
        $terminalCodeVip = $terminalCode . "VIP";
        $query = 'SELECT COUNT(StackerSessionID) ctrSession FROM stackersessions WHERE TerminalName IN (:terminal_code, :terminal_code_vip) AND IsEnded = 0';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":terminal_code" => $terminalCode, ":terminal_code_vip" => $terminalCodeVip
        ));

        $result = $sql->queryRow();

        return $result['ctrSession'];
    }

    public function isTerminalIDExists($terminalCode) {
        $terminalCodeVip = $terminalCode . "VIP";
        $query = 'SELECT COUNT(TerminalID) ctrterminal FROM terminals WHERE TerminalCode IN (:terminal_code, :terminal_code_vip)';
        $sql = $this->_connection2->createCommand($query);
        $sql->bindValues(array(
            ":terminal_code" => $terminalCode, ":terminal_code_vip" => $terminalCodeVip
        ));

        $result = $sql->queryRow();

        return $result['ctrterminal'];
    }

    public function isTerminalSessionExists($terminalCode) {
        $terminalCodeVip = $terminalCode . "VIP";
        $query = 'SELECT COUNT(StackerSessionID) ctrSession FROM stackersessions WHERE TerminalName IN (:terminal_code, :terminal_code_vip)';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":terminal_code" => $terminalCode, ":terminal_code_vip" => $terminalCodeVip
        ));

        $result = $sql->queryRow();

        return $result['ctrSession'];
    }

    public function isTerminalStatusNotValid($terminalCode) {
        $terminalCodeVip = $terminalCode . "VIP";
        $query = 'SELECT COUNT(StackerSessionID) ctrSession FROM stackersessions WHERE TerminalName IN (:terminal_code, :terminal_code_vip) AND Status = 0';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":terminal_code" => $terminalCode, ":terminal_code_vip" => $terminalCodeVip
        ));

        $result = $sql->queryRow();

        return $result['ctrSession'];
    }

    public function isTerminalStatusNotYetValidated($stackerInfoID) {
        $query = 'SELECT COUNT(StackerInfoID) ctrStackerInfoID FROM stackersessions WHERE StackerInfoID = :stacker_info_id AND Status IN (0,2)';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(':stacker_info_id' => $stackerInfoID));

        $result = $sql->queryRow();

        if (isset($result['ctrStackerInfoID'])) {
            return $result['ctrStackerInfoID'];
        } else {
            return 0;
        }
    }

}
