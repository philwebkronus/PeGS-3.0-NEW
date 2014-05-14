<?php

/**
 * @description of StackerDetailsModel
 * @author Junjun S. Hernandez <jshernandez@philweb.com.ph>
 * @datecreated 11 11, 13 1:11:44 PM
 */
class StackerDetailsModel {

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
     * @param string $trackingID
     * @return int
     */
    public function isTrackingIDExists($trackingID) {
        $query = 'SELECT COUNT(StackerDetailID) ctrtracking FROM stackerdetails WHERE TrackingID = :trackingid';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":trackingid" => $trackingID
        ));

        $result = $sql->queryRow();

        return $result['ctrtracking'];
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param int $stackerSummaryID
     * @param float $amount
     * @param int $transType
     * @param int $paymentType
     * @param string $voucherCode
     * @param datetime $dateCreated
     * @param string $trackingID
     * @return boolean success|failed
     */
    public function insertStackerDetails($stackerSummaryID, $amount, $transType, $paymentType, $voucherCode, $trackingID) {
        $beginTrans = $this->_connection->beginTransaction();

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
        } catch (Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $trackingID
     * @return int
     */
    public function getDetailsCashOnly($stackerSummaryID) {
        $query = 'SELECT COUNT(StackerDetailID) AS Quantity, SUM(Amount) AS Amount FROM stackerdetails WHERE StackerSummaryID = :stacker_summary_id AND PaymentType = 0';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":stacker_summary_id" => $stackerSummaryID
        ));

        $result = $sql->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $trackingID
     * @return int
     */
    public function getDetailsTicketOnly($stackerSummaryID) {
        $query = 'SELECT COUNT(StackerDetailID) AS Quantity, SUM(Amount) AS Amount FROM stackerdetails WHERE StackerSummaryID = :stacker_summary_id AND PaymentType = 2';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":stacker_summary_id" => $stackerSummaryID
        ));

        $result = $sql->queryAll();

        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $trackingID
     * @return int
     */
    public function getDetailsCouponOnly($stackerSummaryID) {
        $query = 'SELECT COUNT(StackerDetailID) AS Quantity, SUM(Amount) AS Amount FROM stackerdetails WHERE StackerSummaryID = :stacker_summary_id AND PaymentType = 1';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":stacker_summary_id" => $stackerSummaryID
        ));

        $result = $sql->queryAll();

        return $result;
    }

}

?>