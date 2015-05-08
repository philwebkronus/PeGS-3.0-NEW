<?php

/**
 * Date Created 01 5, 13 10:00:00 AM <pre />
 * Description of EgmSessionsModel
 * @author gvjagolino
 */
class EgmSessionsModel extends MI_Model {
    
    
    public function insert($mid, $terminal_id,$service_id,$aid) {
            try {
                $this->beginTransaction();
                $sql = 'INSERT INTO egmsessions (MID, TerminalID, ServiceID, DateCreated, CreatedByAID) 
                    VALUES (:mid, :terminal_id, :service_id, now(6), :aid)';
                
                $stmt = $this->dbh->prepare($sql);

                $stmt->bindValue(':mid', $mid);
                $stmt->bindValue(':terminal_id', $terminal_id);
                $stmt->bindValue(':service_id', $service_id);
                $stmt->bindValue(':aid', $aid);
            
                if($stmt->execute()){
                    try {
                        $this->dbh->commit();
                        return true;
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
    
    
    
    public function getStackerBtachID($terminal_id, $mid) {
        $sql = 'SELECT * FROM egmsessions WHERE TerminalID = :terminal_id AND MID = :mid';
        $param = array(':terminal_id' => $terminal_id, ':mid' => $mid);
        $this->exec($sql,$param);
        return $this->find();
    }
    
    
    public function deleteEgmSessionById($terminal_id) {
        $sql = 'DELETE FROM egmsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id' => $terminal_id);
        return $this->exec($sql,$param);
    }
    
    public function checkEgmSession($serviceid, $mid) {
        $sql = 'SELECT * FROM egmsessions WHERE MID = :mid AND ServiceID = :serviceid';
        $param = array(':mid' => $mid, ':serviceid' => $serviceid);
        $this->exec($sql,$param);
        return $this->find();
    }
}
?>
