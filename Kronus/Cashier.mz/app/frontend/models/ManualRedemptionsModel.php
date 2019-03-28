<?php

class ManualRedemptionsModel extends MI_Model {
    
    
    public function getManualRedemptions($date, $enddate, $siteid) {
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $sql = "SELECT SUM(ActualAmount) AS Amount FROM manualredemptions WHERE TransactionDate >= :start_date AND TransactionDate < :end_date AND Status = 1 AND SiteID = :site_id;";
        $param = array(
            ':site_id'=>$siteid,
            ':start_date'=>$date . ' ' .$cutoff_time,
            ':end_date'=>$enddate . ' ' .$cutoff_time,            
        );
        $this->exec($sql,$param);
        return $this->find();
    }
    
    public function getManualRedemptionsPerCashier($date, $enddate, $siteid, $aid) {
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $sql = "SELECT SUM(ActualAmount) AS Amount FROM manualredemptions WHERE TransactionDate >= :start_date AND TransactionDate < :end_date AND Status = 1 AND SiteID = :site_id AND ProcessedByAID=:aid";
        $param = array(
            ':site_id'=>$siteid,
            ':start_date'=>$date . ' ' .$cutoff_time,
            ':end_date'=>$enddate . ' ' .$cutoff_time,  
            ':aid'=>$aid
        );
        $this->exec($sql,$param);
        $result = $this->find();
        return empty($result['Amount'])?0:$result['Amount'];
    }
}
?>
