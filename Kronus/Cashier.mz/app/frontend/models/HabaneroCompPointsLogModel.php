<?php

/**
 * Date Created 04 05, 2013 5:42:58 PM <pre />
 * Description of LoyaltyRequestLogsModel
 * @author aqdepliyan
 */
class HabaneroCompPointsLogModel extends MI_Model {

    /**
     * Insert to loyaltyrequestlogs either UB or TB
     * @param int $mid, char $trans_type, int $terminal_id, 
     *                  int $amount, int $trans_details_id, int $paymentType, 
     *                  int $isCreditable
     * @return obj
     */
    public function insert($mid, $loyalty_card, $terminal_id, $siteid, $transtype, $tracking1, $status) {
        try {
            $this->beginTransaction4();

            $sql = 'INSERT INTO habanerocomppointslog (MID,LoyaltyCardNumber,StartDate,TerminalID,SiteID,TransactionType,TransactionRequestsLogsID,Status)'
                    . ' VALUES (:mid,:loyalty_card,NOW(6),:terminal_id,:siteid,:transtype,:tracking1,:status)';

            $param = array(
                ':mid' => $mid,
                ':loyalty_card' => $loyalty_card,
                ':terminal_id' => $terminal_id,
                ':siteid' => $siteid,
                ':transtype' => $transtype,
                ':tracking1' => $tracking1,
                ':status' => $status);

            $this->exec4($sql, $param);
            $habaneroCompPointsLogID = $this->getLoyaltyLastInsertId();

            try {
                $this->dbh4->commit();
                return $habaneroCompPointsLogID;
            } catch (Exception $e) {
                $this->dbh4->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->dbh4->rollBack();
            return false;
        }
    }

    public function updateHabaneroCompPointsLog($HabaneroCompPointsLogID, $remarks, $PointsWithdrawn, $status) {

        $sql = 'UPDATE habanerocomppointslog SET EndDate = NOW(6), Remarks = :remarks, Points = :points, Status = :trans_status'
                . ' WHERE HabaneroCompPointsLogID = :habcomppointlogid';
        $param = array(
            ':remarks' => $remarks,
            ':points' => $PointsWithdrawn,
            ':trans_status' => $status,
            ':habcomppointlogid' => $HabaneroCompPointsLogID
        );
        return $this->exec4($sql, $param);
    }

}

?>
