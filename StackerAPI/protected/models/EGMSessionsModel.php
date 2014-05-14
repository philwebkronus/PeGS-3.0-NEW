<?php

/**
 * @description of EGMSessionsModel
 * @author jshernandez <jshernandez@philweb.com.ph>
 * @datecreated 01 07, 14 10:18:44 PM
 */
class EGMSessionsModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db2;
        $this->_connection2 = Yii::app()->db;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 01/07/14
     * @param string $terminalID
     * @return int
     */
    public function checkSessionIfExists($terminalID) {
        $query = 'SELECT COUNT(EGMSessionID) ctrEGMSessionID FROM egmsessions WHERE TerminalID = :terminal_id';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":terminal_id" => $terminalID
        ));

        $result = $sql->queryRow();

        return $result['ctrEGMSessionID'];
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 01/07/14
     * @param string $terminalID
     * @return int
     */
    public function getEGMSessionIDByTerminalID($terminalID) {
        $query = 'SELECT EGMSessionID FROM egmsessions WHERE TerminalID = :terminal_id';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":terminal_id" => $terminalID
        ));

        $result = $sql->queryRow();

        return $result['EGMSessionID'];
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param string $voucherTicketBarcode
     * @param int $siteID
     * @return array
     */
    public function getUpdated($stackerSummaryID) {
        $query = "SELECT DateUpdated, UpdatedByAID FROM stackersummary WHERE StackerSummaryID = :stacker_summary_id";
                            $command = $this->_connection2->createCommand($query);
                            $command->bindValues(array(
                                ":stacker_summary_id" => $stackerSummaryID
                            ));
                            $result = $command->queryRow();
        return $result;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 01/07/14
     * @param string $terminalID
     * @param int $stackerBatchID
     * @return int 0 - Success but no affected, 1 - Success with affected, 2 - Failed
     */
    public function cancelDeposit($terminalID, $stackerBatchID, $AID) {
        $beginTrans = $this->_connection->beginTransaction();
        $sql = "DELETE FROM egmsessions WHERE TerminalID = :terminal_id AND StackerBatchID = :stacker_batch_id";
        $param = array(':terminal_id' => $terminalID, ':stacker_batch_id' => $stackerBatchID);
        $command = $this->_connection->createCommand($sql);
        $command->bindValues($param);
        $rowCount = $command->execute();
        try {
            $cancel = $this->getUpdated($stackerBatchID);
            $dateCancelledOn = $cancel['DateUpdated'];
            $beginTrans2 = $this->_connection2->beginTransaction();
            $sql = "UPDATE stackersummary SET Status = 1, CancelledByAID = :cancelled_by_aid, DateCancelledOn = :date_cancelled_on WHERE StackerSummaryID = :stacker_batch_id AND Status = 0;";
            $param = array(':stacker_batch_id' => $stackerBatchID, ':cancelled_by_aid' => $AID, ':date_cancelled_on' => $dateCancelledOn);
            $command = $this->_connection2->createCommand($sql);
            $command->bindValues($param);
            $rowCount2 = $command->execute();
            try {
                if ($rowCount > 0) {
                    if ($rowCount2 > 0) {
                        $beginTrans->commit();
                        $beginTrans2->commit();
                        return 1;
                    } else {
                        $beginTrans->commit();
                        return 1;
                    }
                } else {
                    if ($rowCount2 > 0) {
                        $beginTrans2->commit();
                        return 1;
                    } else {
                        return 0;
                    }
                }
            } catch (PDOException $e) {
                $beginTrans->rollback();
                $beginTrans2->rollback();
                Utilities::log($e->getMessage());
                return 2;
            }
        } catch (PDOException $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 03/05/14
     * @param string $terminalID
     * @param int $stackerBatchID
     * @return int
     */
    public function isTerminalMIDMatched($terminalID, $MID) {
        $sql = "SELECT COUNT(EGMSessionID) ctrEGMSessionID FROM egmsessions
                       WHERE TerminalID = :terminal_id AND MID = :mid";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':terminal_id' => $terminalID, ':mid' => $MID));
        $result = $command->queryRow();

        if (isset($result['ctrEGMSessionID'])) {
            return $result['ctrEGMSessionID'];
        } else {
            return 0;
        }
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 03/05/14
     * @param string $terminalID
     * @param int $stackerBatchID
     * @return int
     */
    public function isTerminalStackerBatchIDMatched($terminalID, $stackerBatchID) {
        $sql = "SELECT COUNT(EGMSessionID) ctrEGMSessionID FROM egmsessions
                       WHERE TerminalID = :terminal_id AND StackerBatchID = :stacker_batch_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':terminal_id' => $terminalID, ':stacker_batch_id' => $stackerBatchID));
        $result = $command->queryRow();

        if (isset($result['ctrEGMSessionID'])) {
            return $result['ctrEGMSessionID'];
        } else {
            return 0;
        }
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 03/05/14
     * @param string $terminalID
     * @return int
     */
    public function getMID($egmSessionID) {
        $sql = "SELECT MID FROM egmsessions WHERE EGMSessionID = :egm_session_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':egm_session_id' => $egmSessionID));
        $result = $command->queryRow();

        if (isset($result['MID'])) {
            return $result['MID'];
        } else {
            return 0;
        }
    }

}