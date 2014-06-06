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
}
?>
