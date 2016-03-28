<?php

class StackerSummaryModel extends MI_Model {
    
    public function updateStackerSummaryStatus($status, $stackerSummaryid, $aid) {
        $sql = 'UPDATE stackersummary SET Status = :status, UpdatedByAID = :aid, DateUpdated = now(6) WHERE StackerSummaryID = :stackersummaryid AND Status = 0';
        $param = array(':status'=>$status, ':stackersummaryid'=>$stackerSummaryid, ':aid'=>$aid);
        $exec = $this->exec2($sql, $param);
        return $exec;
    }
    
    
    
    
    public function deleteEgmUpdateSSStatus($status, $stackersummaryid, $terminal_id, $aid)
    {
        $beginTrans = $this->beginTransaction2();
        try {
            $stmt = $this->dbh2->prepare('UPDATE stackersummary SET Status = :status, UpdatedByAID = :aid, DateUpdated = now(6) WHERE StackerSummaryID = :stackerbatchid AND Status = 0');
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':aid', $aid);
            $stmt->bindValue(':stackerbatchid', $stackersummaryid);
            
            if($stmt->execute())
            {
                $beginTrans = $this->beginTransaction();
                
                $stmt = $this->dbh->prepare('DELETE FROM egmsessions WHERE TerminalID = :terminal_id');
                
                $stmt->bindValue(':terminal_id',$terminal_id);
                
                if($stmt->execute()) {
                    $this->dbh->commit();
                    $this->dbh2->commit();
                    return true;
                } else {
                    $this->dbh->rollBack();
                    $this->dbh2->rollBack();
                    return false;
                }
            } else {
                $this->dbh2->rollBack();
                return false;
            }
        } catch(Exception $e) {
            $this->dbh->rollBack();
            $this->dbh2->rollBack();
            return false;
        }   
    }
    

    public function getTicketCancelled($date, $enddate){
        
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT TicketCode FROM stackersummary WHERE DateCancelledOn >= :start_date 
            AND DateCancelledOn < :end_date AND Status IN (1,2) AND TicketCode IS NOT NULL;";
        $param = array(':start_date'=>$date . ' ' . $cutoff_time, ':end_date'=>$enddate . ' ' . $cutoff_time);
        $this->exec2($sql, $param);
        $result = $this->findAll2();
        return $result;
    }
}

?>
