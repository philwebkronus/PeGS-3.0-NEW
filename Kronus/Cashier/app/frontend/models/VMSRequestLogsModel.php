<?php

/**
 * Date Created 04 08, 2013 10:44:01 AM <pre />
 * Description of VMSRequestLogsModel
 * @author aqdepliyan
 */
class VMSRequestLogsModel extends MI_Model {
    
    /**
     * Insert to vmsrequestlogs either UB or TB for voucher 
     * @param varchar $vouchercode, int $aid, int $terminalID, 
     *                  varchar $trackingID
     * @return obj
     */
    public function insert($vouchercode, $aid, $terminalID, $trackingID) {
        try{
                $this->beginTransaction();
                $stmt = $this->dbh->prepare('INSERT INTO vmsrequestlogs (VoucherCode,'.
                        'AID, TerminalID, TrackingID, DateCreated, Status) VALUES (:vouchercode, :aid,'.
                        ':terminal_id, :tracking_id, now_usec(), :trans_status)');

                $stmt->bindValue(':vouchercode', $vouchercode);
                $stmt->bindValue(':aid', $aid);
                $stmt->bindValue(':terminal_id', $terminalID);
                $stmt->bindValue(':tracking_id', $trackingID);
                $stmt->bindValue(':trans_status', 0);
                
                $stmt->execute();
                $vmsrequestlogsID = $this->getLastInsertId();
                try {
                    $this->dbh->commit();
                    return $vmsrequestlogsID;
                } catch(Exception $e) {
                    $this->dbh->rollBack();
                    return false;
                }
        } catch (Exception $e) {
            $this->dbh->rollBack();
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
                'DateUpdated = now_usec() WHERE VMSRequestLogID = :vmsrequestlogsID';
        $param = array(
            ':trans_status'=> $status,
            ':vmsrequestlogsID'=>$vmsrequestlogsID);
        return $this->exec($sql,$param);
    }
    
}

?>
