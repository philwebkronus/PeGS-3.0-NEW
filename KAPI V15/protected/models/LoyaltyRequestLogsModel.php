<?php

/**
 * Date Created 04 05, 2013 5:42:58 PM <pre />
 * Description of LoyaltyRequestLogsModel
 * @author aqdepliyan
 */
class LoyaltyRequestLogsModel extends CFormModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LoyaltyRequestLogsModel();
        return self::$_instance;
    }
    
    /**
     * Insert to loyaltyrequestlogs either UB or TB
     * @param int $mid, char $trans_type, int $terminal_id, 
     *                  int $amount, int $trans_details_id, int $paymentType, 
     *                  int $isCreditable
     * @return obj
     */
    public function insert($mid, $trans_type, $terminal_id, $amount, $trans_details_id, $paymentType, $isCreditable="") {
        
                $sql = 'INSERT INTO loyaltyrequestlogs (MID,'.
                                'DateCreated, TransactionType, TransactionOrigin, TerminalID, Amount, TransactionDetailsID,'. 
                                'PaymentType, IsCreditable, Status) VALUES (:mid, NOW(6),'.
                                ':trans_type, :trans_org, :terminal_id, :amount, :transdetailsid,'.
                                ':payment_type, :isCreditable, :trans_status)';
                $smt = $this->_connection->createCommand($sql);
                $param = array(':mid'=>$mid, 'trans_type'=>$trans_type, ':trans_org'=>1,
                    ':terminal_id'=>$terminal_id, ':amount'=>$amount, ':transdetailsid'=>$trans_details_id,
                    ':payment_type'=>$paymentType, ':isCreditable'=>$isCreditable, ':trans_status'=>0);
                $smt->execute($param);
                $transaction_id = $this->_connection->getLastInsertID();
                return $transaction_id;
        
    }
     
    /**
     * Update loyaltyrequestlogs status either UB or TB
     * @param int $trans_details_id, $status
     * @return obj
     */
    public function updateLoyaltyRequestLogs($loyaltyrequestlogID,$status) {
        $sql = 'UPDATE loyaltyrequestlogs SET Status = :trans_status, ' . 
                'DateUpdated = NOW(6) WHERE LoyaltyRequestLogID = :loyaltyrequestlogID';
        $smt = $this->_connection->createCommand($sql);
        $param = array(
            ':trans_status'=> $status,
            ':loyaltyrequestlogID'=>$loyaltyrequestlogID);
        $result = $smt->execute($param);
    
        return $result;
    }
    
}

?>
