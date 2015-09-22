<?php

/**
 * Log every call of spyder API
 *
 * @author elperez
 * @datecreated 04/26/13
 * 
 */
class SpyderRequestLogsModel extends MI_Model{
    
    /**
     * insert records as pending
     * @param str $terminalCode
     * @param int $commandType
     * @return bool success | failed
     */
    public function insert($terminalCode, $commandType){        
        try{
                $this->beginTransaction();
                $stmt = $this->dbh->prepare('INSERT INTO spyderrequestlogs (TerminalCode, CommandType, DateCreated, 
                Status) VALUES (:terminal_code, :command_type, now(6), 0)');

                $stmt->bindValue(':terminal_code', $terminalCode);
                $stmt->bindValue(':command_type', $commandType);
                
                $stmt->execute();
                $spyderrequestlogsID = $this->getLastInsertId();
                try {
                    $this->dbh->commit();
                    return $spyderrequestlogsID;
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
     * update spyder record as success | failed
     * @param int $status
     * @param int $spyderReqID
     * @return bool success | failed
     */
    public function update($status, $spyderReqID){
        $sql = "UPDATE spyderrequestlogs SET Status = :status, DateUpdated = now(6) 
                WHERE SpyderRequestLogID = :spyder_req_id";
        $param = array(':status'=>$status,
                       ':spyder_req_id'=>$spyderReqID);
        return $this->exec($sql,$param);
    }
    
}

?>
