<?php

/**
 * Model for egmrequestlogs
 * @author elperez
 */
class GamingRequestLogs {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new GamingRequestLogs();
        return self::$_instance;
    }
    
    /**
     * Insert EGM request logs 
     * @param int $tracking_id
     * @param str $amount
     * @param str $status
     * @param str $transType
     * @param int $siteID
     * @param int $terminalID
     * @param int $serviceID
     * @return obj | bool 
     */
    public function insertGamingRequestLogs($tracking_id, $amount, $status, $transType, $siteID, $terminalID, $serviceID, $card_number, $mid){
        try{
            
            $beginTrans = $this->_connection->beginTransaction();
            
            $sql = "INSERT INTO egmrequestlogs(TrackingID, ReportedAmount, Status, 
                    TransactionType, SiteID, TerminalID, ServiceID, LoyaltyCardNumber, MID, DateLastUpdated) 
                    VALUES(:tracking_id, :amount, :status, :trans_type, :site_id,
                    :terminal_id, :service_id, :card_number, :mid, NOW(6))";
            
            $command = $this->_connection->createCommand($sql);
            
            $command->bindValues(array(':tracking_id'=>$tracking_id, ':amount'=>$amount, 
                                       ':status'=>$status, ':trans_type'=>$transType,
                                       ':site_id'=>$siteID, ':terminal_id'=>$terminalID,
                                       ':service_id'=>$serviceID,
                                       ':card_number'=>$card_number,
                                       ':mid'=>$mid));
            
            $isrecorded = $command->execute();
            
            $egmRequestLogID = $this->_connection->getLastInsertID();
            
            try {
                $beginTrans->commit();
                return $egmRequestLogID;
            } catch (PDOException $e) {
                $beginTrans->rollback();
                Utilities::log($command->getText().$command->getBound());
                return false;
            }
            
            return $egmRequestLogID;
            
        }catch(CDbException $e){
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }
    
    public function updateGamingLogsStatus($egmRequestLogID, $status, $referenceID = null, $voucherCode = null, $dateExpiry = null){
        try{
            $sql = "UPDATE egmrequestlogs SET Status = :status, TransactionReferenceID = :trans_ref_id, 
                    DateLastUpdated = NOW(6), Option1 = :voucher_code, Option2 = :date_expiry
                    WHERE EGMRequestLogID = :egmRequestLogID";
            
            $command = $this->_connection->createCommand($sql);
            
            $command->bindValues(array(':status'=>$status, ':trans_ref_id'=>$referenceID,
                                       ':voucher_code'=>$voucherCode,':date_expiry'=>$dateExpiry,
                                       ':egmRequestLogID'=>$egmRequestLogID));
            
            return $command->execute();
            
        } catch (CDbException $e){
            Utilities::log($e->getMessage());
            return false;
        }
    }
    
    public function chkTransaction($trackingID){
        $sql = "SELECT ReportedAmount, DateLastUpdated, TransactionReferenceID, 
                Option1, Option2, Status, TransactionType
                FROM egmrequestlogs WHERE TrackingID = :tracking_id";
        $param = array(':tracking_id'=>$trackingID);
        $command = $this->_connection->createCommand($sql);
        return $command->queryRow(true, $param);
    }
    
    public function getMatchedTerminalAndTrackingID($TerminalID, $trackingID){
        $sql = "SELECT TerminalID FROM egmrequestlogs WHERE TerminalID = :terminal_id AND TrackingID = :tracking_id";
        $param = array(':terminal_id'=>$TerminalID, ':tracking_id'=>$trackingID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        if($result['TerminalID']=="")
            return 0;
        else
            return count($result['TerminalID']);
    }
    
    public function getMatchedTerminalAndServiceID($TerminalID, $ServiceID){
        $sql = "SELECT TerminalID FROM egmrequestlogs WHERE TerminalID = :terminal_id AND ServiceID = :service_id";
        $param = array(':terminal_id'=>$TerminalID, ':service_id'=>$ServiceID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        if($result['TerminalID']=="")
            return 0;
        else
            return count($result['TerminalID']);
    }
    
    public function getDetailsByTerminalAndTrackingID($TerminalID, $trackingID){
        $sql = "SELECT e.Status, e.TrackingID, e.TerminalID, t.DateCreated, t.TransactionDetailsID, e.ReportedAmount, e.Option1, e.Option2
                FROM egmrequestlogs e
                LEFT JOIN transactiondetails t ON e.TransactionReferenceID = t.TransactionReferenceID
                WHERE e.TerminalID = :terminal_id AND e.TrackingID = :tracking_id";
        $param = array(':terminal_id'=>$TerminalID, ':tracking_id'=>$trackingID);
        $command = $this->_connection->createCommand($sql);
        return $command->queryRow(true, $param);
    }
}

?>
