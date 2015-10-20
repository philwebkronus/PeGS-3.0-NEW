<?php

/**
 * Date Created 04 05, 2013 5:42:58 PM <pre />
 * Description of LoyaltyRequestLogsModel
 * @author aqdepliyan
 */
class LoyaltyRequestLogsModel extends CFormModel {

    /**
     * Insert to loyaltyrequestlogs either UB or TB
     * @param int $mid, char $trans_type, int $terminal_id, 
     *                  int $amount, int $trans_details_id, int $paymentType, 
     *                  int $isCreditable
     * @return obj
     */
    public function insert($mid, $trans_type, $terminal_id, $amount, $trans_details_id, $paymentType, $isCreditable) {
        $startTrans = Yii::app()->db->beginTransaction();
        try {
            $sql = "INSERT INTO loyaltyrequestlogs (MID,
                    DateCreated, TransactionType, TransactionOrigin, TerminalID, Amount, TransactionDetailsID,
                    PaymentType, IsCreditable, Status) VALUES (:mid, now(6),
                    :trans_type, :trans_org, :terminal_id, :amount, :transdetailsid,
                    :payment_type, :isCreditable, :trans_status)";

            $param = array(
                ':mid' => $mid,
                ':trans_type' => $trans_type,
                ':trans_org' => 1,
                ':terminal_id' => $terminal_id,
                ':amount' => $amount,
                ':transdetailsid' => $trans_details_id,
                ':payment_type' => $paymentType,
                ':isCreditable' => $isCreditable,
                ':trans_status' => 0);

            $command = Yii::app()->db->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            $loyaltyrequestlogsID = Yii::app()->db->getLastInsertID();
            try {
                $startTrans->commit();
                return $loyaltyrequestlogsID;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch (Exception $e) {
//            $startTrans->rollback();
            echo $e;
            return false;
        }
    }

    /**
     * Update loyaltyrequestlogs status either UB or TB
     * @param int $trans_details_id, $status
     * @return obj
     */
    public function updateLoyaltyRequestLogs($loyaltyrequestlogID, $status) {
        $startTrans = Yii::app()->db->beginTransaction();
        try {
            $sql = "UPDATE loyaltyrequestlogs SET Status = :trans_status,
                    DateUpdated = now(6) WHERE LoyaltyRequestLogID = :loyaltyrequestlogID";

            $param = array(
                ':trans_status' => $status,
                ':loyaltyrequestlogID' => $loyaltyrequestlogID
            );

            $command = Yii::app()->db->createCommand($sql);
            $command->bindValues($param);
            $result = $command->execute();

            if ($result) {
                $startTrans->commit();
                return true;
            } else {
                $startTrans->rollback();
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

}

?>
