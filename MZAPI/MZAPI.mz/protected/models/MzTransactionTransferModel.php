<?php

class MzTransactionTransferModel extends CFormModel {

    public $connection;

    public function __construct() {
        $this->connection = Yii::app()->db;
    }

    public function updateFromMzTransactionTransfer($FromServiceTransID, $FromServiceStatus, $FromStatus, $TrasferStatus, $TransferID, $TransactionSummaryID) {

        $startTrans = $this->connection->beginTransaction();
        try {
            $sql = "UPDATE mztransactiontransfer SET 
                FromServiceTransID =:FromServiceTransID,
                FromServiceStatus = :FromServiceStatus,
                FromEndTransDate = NOW(6),
                FromStatus= :FromStatus,
                TransferStatus = :TrasferStatus
                WHERE TransferID = :TransferID AND TransactionSummaryID = :TransactionSummaryID";
            $command = $this->connection->createCommand($sql);
            $command->bindValue(':FromServiceTransID', $FromServiceTransID);
            $command->bindValue(":FromServiceStatus", $FromServiceStatus);
            $command->bindValue(":FromStatus", $FromStatus);
            $command->bindValue(':TrasferStatus', $TrasferStatus);
            $command->bindValue(":TransferID", $TransferID);
            $command->bindValue(":TransactionSummaryID", $TransactionSummaryID);


            try {
                $command->execute();
                $startTrans->commit();
                return true;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch (Exception $ex) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }

    public function updateToMzTransactionTransfer($ToServiceTransID, $ToAmount, $ToTransactionType, $ToServiceStatus, $ToStatus, $TrasferStatus, $TransferID, $TransactionSummaryID, $identifier) {

        $startTrans = $this->connection->beginTransaction();
        try {

            if ($identifier == 0) {
                $sql = "UPDATE mztransactiontransfer SET 
                ToTransactionType =:ToTransactionType,
                ToAmount = :ToAmount,
                ToStartTransDate = NOW(6),
                ToStatus= :ToStatus,
                TransferStatus = :TrasferStatus
                WHERE TransferID = :TransferID AND TransactionSummaryID = :TransactionSummaryID";

                $command = $this->connection->createCommand($sql);
                $command->bindValue(':ToTransactionType', $ToTransactionType);
                $command->bindValue(":ToAmount", $ToAmount);
                $command->bindValue(":ToStatus", $ToStatus);
                $command->bindValue(':TrasferStatus', $TrasferStatus);
                $command->bindValue(":TransferID", $TransferID);
                $command->bindValue(":TransactionSummaryID", $TransactionSummaryID);

                try {
                    $command->execute();
                    $startTrans->commit();
                    return true;
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
            } else {
                $sql = "UPDATE mztransactiontransfer SET
                ToServiceTransID =:ToServiceTransID,
                ToServiceStatus = :ToServiceStatus,
                ToEndTransDate = NOW(6),
                 ToAmount = :ToAmount,
                ToStatus= :ToStatus,
                TransferStatus = :TrasferStatus
                WHERE TransferID = :TransferID AND TransactionSummaryID = :TransactionSummaryID";

                $command = $this->connection->createCommand($sql);
                $command->bindValue(":ToServiceTransID", $ToServiceTransID);
                $command->bindValue(":ToServiceStatus", $ToServiceStatus);
                $command->bindValue(":ToAmount", $ToAmount);
                $command->bindValue(":ToStatus", $ToStatus);
                $command->bindValue(":TrasferStatus", $TrasferStatus);
                $command->bindValue(":TransferID", $TransferID);
                $command->bindValue(":TransactionSummaryID", $TransactionSummaryID);

                try {
                    $startTrans->commit();
                    $command->execute();
                    return true;
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
            }
        } catch (Exception $ex) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }

    public function updateStatusMzTransactionTransfer($TransferStatus, $TransferID, $TransactionSummaryID) {

        $startTrans = $this->connection->beginTransaction();
        try {
            $sql = "UPDATE mztransactiontransfer SET 
                    TransferStatus = :TransferStatus
                    WHERE TransferID = :TransferID AND TransactionSummaryID = :TransactionSummaryID";

            $command = $this->connection->createCommand($sql);
            $command->bindValue(':TransferStatus', $TransferStatus);
            $command->bindValue(':TransferID', $TransferID);
            $command->bindValue(':TransactionSummaryID', $TransactionSummaryID);


            try {
                $command->execute();
                $startTrans->commit();
                return true;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch (Exception $ex) {
            $startTrans->rollback();
            Utilities::log($ex->getMessage());
            return false;
        }
    }

    public function getMaxTransferID($TransactionSummaryID) {
        $sql = "SELECT MAX(TransferID) as MaxTransferID FROM mztransactiontransfer WHERE TransactionSummaryID = $TransactionSummaryID";
        $command = $this->connection->createCommand($sql);
        $result = $command->queryRow();

        return $result;
    }

    public function insert($TransactionSummaryID, $SiteID, $TerminalID, $MID, $CardNumber, $FromTransactionType, $FromAmount, $ToAmount, $FromServiceID, $FromStatus, $ToStatus, $ToTransactionType, $ToServiceID, $TransferStatus, $FromServiceStatus, $identifier) {

        if ($identifier == 0) {
            $startTrans = $this->connection->beginTransaction();
            try {
                $sql = "INSERT INTO mztransactiontransfer (
                        TransactionSummaryID,
                        SiteID, 
                        TerminalID, 
                        MID, 
                        LoyaltyCardNumber, 
                        FromTransactionType, 
                        FromAmount,
                        FromServiceID, 
                        FromStartTransDate, 
                        FromEndTransDate,
                        FromStatus,
                        ToTransactionType,
                        ToAmount,
                        ToServiceID,
                        ToStartTransDate,
                        ToEndTransDate,
                        ToStatus,
                        TransferStatus
                    ) 
                    VALUES (
                        :TransactionSummaryID, 
                        :SiteID,
                        :TerminalID, 
                        :MID, 
                        :LoyaltyCardNumber, 
                        :FromTransactionType, 
                        :FromAmount, 
                        :FromServiceID,
                        NOW(6),
                        NOW(6), 
                        :FromStatus,
                        :ToTransactionType,
                        :ToAmount,
                        :ToServiceID,
                        NOW(6),
                        NOW(6),
                        :ToStatus ,
                        :TransferStatus
                    )";

                $param = array(
                    ':TransactionSummaryID' => $TransactionSummaryID,
                    ':SiteID' => $SiteID,
                    ':TerminalID' => $TerminalID,
                    ':MID' => $MID,
                    ':LoyaltyCardNumber' => $CardNumber,
                    ':FromTransactionType' => $FromTransactionType,
                    ':FromAmount' => $FromAmount,
                    ':ToAmount' => $ToAmount,
                    ':FromServiceID' => $FromServiceID,
                    ':FromStatus' => $FromStatus,
                    ':ToStatus' => $ToStatus,
                    ':ToTransactionType' => $ToTransactionType,
                    ':TransferStatus' => $TransferStatus,
                    ':ToServiceID' => $ToServiceID
                );

                $command = $this->connection->createCommand($sql);
                $command->bindValues($param);
                $command->execute();

                $MzTransactionTransferID = $this->connection->getLastInsertID();
                try {
                    $startTrans->commit();
                    return $MzTransactionTransferID;
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
            } catch (Exception $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } else {
            $startTrans = $this->connection->beginTransaction();
            try {
                $sql = "INSERT INTO mztransactiontransfer (
                        TransactionSummaryID,
                        SiteID, 
                        TerminalID, 
                        MID, 
                        LoyaltyCardNumber, 
                        FromTransactionType, 
                        FromAmount,
                        FromServiceID, 
                        FromServiceStatus,
                        FromStartTransDate, 
                        FromEndTransDate,
                        FromStatus,
                        ToTransactionType,
                        ToAmount,
                        ToServiceID,
                        ToStartTransDate,
                        ToEndTransDate,
                        ToStatus,
                        TransferStatus
                    ) 
                    VALUES (
                        :TransactionSummaryID,
                        :SiteID, 
                        :TerminalID, 
                        :MID, 
                        :LoyaltyCardNumber, 
                        :FromTransactionType, 
                        :FromAmount,
                        :FromServiceID, 
                        :FromServiceStatus,
                        NOW(6),
                        NOW(6),
                        :FromStatus,
                        :ToTransactionType,
                        :ToAmount,
                        :ToServiceID,
                        NOW(6),
                        NOW(6),
                        :ToStatus,
                        :TransferStatus
                    )";


                $param = array(
                    ':TransactionSummaryID' => $TransactionSummaryID,
                    ':SiteID' => $SiteID,
                    ':TerminalID' => $TerminalID,
                    ':MID' => $MID,
                    ':LoyaltyCardNumber' => $CardNumber,
                    ':FromTransactionType' => $FromTransactionType,
                    ':FromAmount' => $FromAmount,
                    ':ToAmount' => $ToAmount,
                    ':FromServiceID' => $FromServiceID,
                    ':FromStatus' => $FromStatus,
                    ':ToStatus' => $ToStatus,
                    ':ToTransactionType' => $ToTransactionType,
                    ':TransferStatus' => $TransferStatus,
                    ':ToServiceID' => $ToServiceID,
                    ':FromServiceStatus' => $FromServiceStatus
                );

                $command = $this->connection->createCommand($sql);
                $command->bindValues($param);
                $command->execute();

                $MzTransactionTransferID = $this->connection->getLastInsertID();
                try {
                    $startTrans->commit();
                    return $MzTransactionTransferID;
                } catch (PDOException $e) {
                    $startTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false2;
                }
            } catch (Exception $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return $e;
            }
        }
    }

}

?>

