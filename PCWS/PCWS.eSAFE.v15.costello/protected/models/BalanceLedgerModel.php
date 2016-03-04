<?php
/*
class BalanceLedgerModel extends CFormModel{

    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
    
    public function insert($fromserviceid, $toserviceid, $amount, $frombalance, $mid, $cardnumber, $terminalid, $siteid, $status, $transtype)
    {
        $sql = "INSERT INTO balanceledger (StartDate, FromServiceID, ToServiceID, AmountRequested, FromBalance, MID, CardNumber, TerminalID, SiteID, Status, TransactionType) VALUES (now(6), :fromserviceid, :toserviceid, :amount, :frombalance, :mid, :cardnumber, :terminalid, :siteid, :status, :transtype)";
        
        $param = array(':fromserviceid'=>$fromserviceid, ':toserviceid'=>$toserviceid, ':amount'=>$amount, ':frombalance'=>$frombalance, ':mid'=>$mid, ':cardnumber'=>$cardnumber, ':terminalid'=>$terminalid, ':siteid'=>$siteid, ':status'=>$status, ':transtype'=>$transtype );
        
        $command = $this->connection->createCommand($sql);
        
        $command->execute($param);
        
        return $this->connection->getLastInsertID();
    }
    
    
    public function updatebalanceledger($tobalance, $status, $servicetrans_id, $servicetrans_status, $bledgerid)
    {
        $sql = "UPDATE balanceledger SET EndDate = now(6), ToBalance = :tobalance, Status = :status, ServiceTransactionID = :servicetransid, ServiceTransactionStatus = :servicetransstatus WHERE BalanceLedgerID = :balanceledgerid";
        $param = array(':tobalance'=>$tobalance, ':status'=>$status, ':servicetransid'=>$servicetrans_id, ':servicetransstatus'=>$servicetrans_status, ':balanceledgerid' => $bledgerid);
        $command = $this->connection->createCommand($sql);
        return $command->execute($param);
    }
}*/
?>
