<?php

/**
 * Model for pending user transaction count
 *
 * @author its-edson
 */
class PendingUserTransactionCountModel extends MI_Model{
    
    /**
     * Updates count attempts of pending user transaction
     * @param int $terminalID
     * @return bool 0|1
     */
    public function updatePendingUserCount($loyaltyCardNo){
        $sql = "UPDATE pendingusertransactioncount SET 
                TransactionCount = TransactionCount + 1 WHERE LoyaltyCardNumber = :loyalty_card";
        $param = array(':loyalty_card'=>$loyaltyCardNo);
        return $this->exec($sql,$param);
    }
    
}

?>
