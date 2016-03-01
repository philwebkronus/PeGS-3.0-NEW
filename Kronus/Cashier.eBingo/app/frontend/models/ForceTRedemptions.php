<?php

class ForceTRedemptions extends MI_Model {
    
    public function getForcetredemptionsID() {
        $sql = 'SELECT ForceTRedemptionsID FROM forcetredemptions ORDER BY ForceTRedemptionsID DESC';
        $this->exec($sql);
        $result =  $this->find();
        
        return $result['ForceTRedemptionsID'];
    }
    
    
    
    public function insert($siteid, $amount, $serviceid, $mid, $cardnumber, $usermode, $status,$aid) {
            try {
                $this->beginTransaction();
                $sql = 'INSERT INTO forcetredemptions (StartDate, SiteID, Amount, ServiceID, MID, CardNumber, UserMode, Status, CreatedByAID) 
                    VALUES (now(6), :site_id, :amount, :service_id, :mid, :card_number, :user_mode, :status, :aid)';
                
                $stmt = $this->dbh->prepare($sql);

                $stmt->bindValue(':site_id', $siteid);
                $stmt->bindValue(':amount', $amount);
                $stmt->bindValue(':service_id', $serviceid);
                $stmt->bindValue(':mid', $mid);
                $stmt->bindValue(':card_number', $cardnumber);
                $stmt->bindValue(':user_mode', $usermode);
                $stmt->bindValue(':status', $status);
                $stmt->bindValue(':aid', $aid);
            
                if($stmt->execute()){
                    $forcetredemptionID = $this->getLastInsertId();
                    try {
                        $this->dbh->commit();
                        return $forcetredemptionID;
                    } catch(Exception $e) {
                        $this->dbh->rollBack();
                        return false;
                    }
                } else {
                    $this->dbh->rollBack();
                    return false;
                }
            } catch (Exception $e) {
                $this->dbh->rollBack();
                return false;
            }
    }
    
    public function updatePendingTerminalCount($status, $trans_ID, $trans_status, $forcetID){
        $sql = "UPDATE forcetredemptions SET EndDate = now(6), Status = :status, TransactionID = :trans_id, TransactionStatus = :trans_status WHERE ForceTRedemptionsID = :forcetid";
        $param = array(':status'=>$status,':trans_id'=>$trans_ID,':trans_status'=>$trans_status,':forcetid'=>$forcetID);
        return $this->exec($sql,$param);
    }
    
    public function getforcetredemptions($date, $enddate, $siteid, $aid = ''){
        $cutoff_time = Mirage::app()->param['cut_off'];
        if($aid == ''){
            $sql = "SELECT SUM(Amount) AS Amount FROM forcetredemptions WHERE StartDate >= :start_date AND EndDate < :end_date AND Status = 1 AND SiteID = :site_id;";
            $param = array(
                ':site_id'=>$siteid,
                ':start_date'=>$date . ' ' .$cutoff_time,
                ':end_date'=>$enddate . ' ' .$cutoff_time,            
            );
        }
        else{
            $sql = "SELECT SUM(Amount) AS Amount FROM forcetredemptions WHERE StartDate >= :start_date AND EndDate < :end_date AND Status = 1 AND SiteID = :site_id AND CreatedByAID = :aid;";
            $param = array(
                ':site_id'=>$siteid,
                ':start_date'=>$date . ' ' .$cutoff_time,
                ':end_date'=>$enddate . ' ' .$cutoff_time, 
                 ':aid'=>$aid 
            );
        }
        
        $this->exec($sql,$param);
        return $this->find();
    
    }
}
?>
