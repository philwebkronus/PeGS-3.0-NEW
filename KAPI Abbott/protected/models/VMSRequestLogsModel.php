<?php

class VMSRequestLogsModel extends CFormModel{
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new VMSRequestLogsModel();
        return self::$_instance;
    }
    /**
     * Insert to vmsrequestlogs either UB or TB for voucher 
     * @param varchar $vouchercode, int $aid, int $terminalID, 
     *                  varchar $trackingID
     * @return obj
     */
    public function insert($vouchercode, $aid, $terminalID, $trackingID) {
        $beginTrans = $this->_connection->beginTransaction();
        try{
               
                $sql = 'INSERT INTO vmsrequestlogs (VoucherCode,'.
                        'AID, TerminalID, TrackingID, DateCreated, Status) VALUES (:vouchercode, :aid,'.
                        ':terminal_id, :tracking_id, NOW(6), :trans_status)';
                $param = array(':vouchercode', $vouchercode, ':aid', $aid,
                    ':terminal_id', $terminalID, ':tracking_id', $trackingID,
                    ':trans_status', 0);
                
                $command = $this->_connection->createCommand($sql);
                $command->bindValues($param);
                $command->execute();
                $vmsrequestlogsID = $this->_connection->getLastInsertID();
                try {
                    $beginTrans->commit();
                    return $vmsrequestlogsID;
                } catch(Exception $e) {
                    $beginTrans->rollBack();
                    return false;
                }
        } catch (Exception $e) {
            $beginTrans->rollBack();
            return false;
        }
    }
     
    /**
     * Update vmsrequestlogs status either UB or TB
     * @param varchar $trackingID, $status
     * @return obj
     */
    public function updateVMSRequestLogs($vmsrequestlogsID,$status) {
        $sql = 'UPDATE vmsrequestlogs SET Status = :trans_status, ' . 
                'DateUpdated = NOW(6) WHERE VMSRequestLogID = :vmsrequestlogsID';
        $smt = $this->_connection->createCommand($sql);
        $param = array(
            ':trans_status'=> $status,
            ':vmsrequestlogsID'=>$vmsrequestlogsID);
        $result = $smt->execute($param);
    
        return $result;
    }
}
?>
