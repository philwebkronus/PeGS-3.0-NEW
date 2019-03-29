<?php

/**
 * Date Created March 20, 2019 10:41:29 <pre />
 * Description of PendingMzTransactionsModel
 * @author John Aaron Vida
 */
class PendingMzTransactionsModel extends MI_Model {

    public function checkPendingMzTransactions($MID, $ServiceID) {
        $sql = 'SELECT COUNT(LoyaltyCardNumber) as PendingCount FROM pendingmztransactions WHERE MID = :mid AND ServiceID = :service_id';
        $param = array(':mid' => $MID, ':service_id' => $ServiceID,);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['PendingCount'];
    }

}

