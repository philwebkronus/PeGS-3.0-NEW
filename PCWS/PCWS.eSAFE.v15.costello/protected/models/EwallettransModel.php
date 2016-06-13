<?php

class EwallettransModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
//    public function insertEwallet($idchecked, $csvalidated, $loyaltycardnumber, $siteid, $mid, $amount, $frombalance, $transtype, $serviceid, $usermode, $paymenttype, $aid, $transsumid, $terminalid, $tracenumber, $referencenumber, $paymentTrackingID=null, $couponCode=null){
//        $startTrans = $this->connection->beginTransaction();
//        
//        try {
//            $sql = "INSERT INTO ewallettrans (StartDate, SiteID, MID, LoyaltyCardNumber, Amount, FromBalance, TransType, ServiceID, UserMode, PaymentType, PaymentTrackingID, CreatedByAID, Status, TransactionSummaryID, TerminalID, TraceNumber, ReferenceNumber, Option1,IsPlayerIDValidated, IsCSValidated) VALUES (NOW(6), :siteid, :mid, :loyaltycardnumber, :amount, :frombalance, :transtype, :serviceid, :usermode, :paymenttype, :paymentTrackingID, :aid, 0, :transsumid, :terminalid, :tracenumber, :refnumber, :couponCode, :idchecked, :csvalidated)";
//            $param = array(':idchecked' => $idchecked,  ':csvalidated' => $csvalidated, ':loyaltycardnumber' => $loyaltycardnumber, ':siteid' => $siteid, ':mid' => $mid, ':amount' => $amount, ':frombalance' => $frombalance, ':transtype' => $transtype, ':serviceid' => $serviceid, ':usermode' => $usermode, ':paymenttype' => $paymenttype, ':aid' => $aid, ':transsumid' => $transsumid, ':terminalid' => $terminalid, ':tracenumber' => $tracenumber, ':refnumber' => $referencenumber, ':paymentTrackingID'=>$paymentTrackingID, ':couponCode'=>$couponCode);
//            $command = $this->connection->createCommand($sql);
//            $command->bindValues($param);
//            $command->execute();
//            
//            $lastinserted = $this->connection->getLastInsertID();
//            try {
//                $startTrans->commit();
//                return $lastinserted;
//            } catch (PDOException $e) {
//                $startTrans->rollback();
//                Utilities::log($e->getMessage());
//                return 0;
//            }
//             
//        } catch (Exception $e) {
//            $startTrans->rollback();
//            Utilities::log($e->getMessage());
//            return 0;
//        }
//    }
        public function insertEwallet($loyaltycardnumber, $siteid, $mid, $amount, $frombalance, $transtype, $serviceid, $usermode, $paymenttype, $aid, $transsumid, $terminalid, $tracenumber, $referencenumber, $paymentTrackingID=null, $couponCode=null){
        $startTrans = $this->connection->beginTransaction();
        
        try {
            $sql = "INSERT INTO ewallettrans (StartDate, SiteID, MID, LoyaltyCardNumber, Amount, FromBalance, TransType, ServiceID, UserMode, PaymentType, PaymentTrackingID, CreatedByAID, Status, TransactionSummaryID, TerminalID, TraceNumber, ReferenceNumber, Option1) VALUES (NOW(6), :siteid, :mid, :loyaltycardnumber, :amount, :frombalance, :transtype, :serviceid, :usermode, :paymenttype, :paymentTrackingID, :aid, 0, :transsumid, :terminalid, :tracenumber, :refnumber, :couponCode)";
            $param = array(':loyaltycardnumber' => $loyaltycardnumber, ':siteid' => $siteid, ':mid' => $mid, ':amount' => $amount, ':frombalance' => $frombalance, ':transtype' => $transtype, ':serviceid' => $serviceid, ':usermode' => $usermode, ':paymenttype' => $paymenttype, ':aid' => $aid, ':transsumid' => $transsumid, ':terminalid' => $terminalid, ':tracenumber' => $tracenumber, ':refnumber' => $referencenumber, ':paymentTrackingID'=>$paymentTrackingID, ':couponCode'=>$couponCode);
            $command = $this->connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
            
            $lastinserted = $this->connection->getLastInsertID();
            try {
                $startTrans->commit();
                return $lastinserted;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
             
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
    
    
    public function updateEwallet($tobalance, $servicetransid, $servicetransstatus, $aid, $status, $ewallettransid){
        $startTrans = $this->connection->beginTransaction();
        
        try {
            $sql = "UPDATE ewallettrans SET EndDate = NOW(6), ToBalance = :tobalance, ServiceTransactionID = :servicetransid, 
                ServiceTransactionStatus = :servicetransstatus, UpdatedByAID = :aid, Status = :status WHERE EwalletTransID = :ewallettransid";
            $param = array(':tobalance' => $tobalance, ':servicetransid' => $servicetransid, ':servicetransstatus' => $servicetransstatus, 
                ':aid' => $aid, ':status' => $status, ':ewallettransid'=>$ewallettransid);
            $command = $this->connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
             
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }
}
?>
