<?php

/**
 * Date Created 11 7, 11 11:38:11 AM <pre />
 * Description of TerminalSessionsModel
 * @author Bryan Salazar, elperez
 */
class TerminalSessionsModel extends MI_Model {
    
    public function getServiceId($terminal_id) {
        $sql = 'SELECT ServiceID FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql,$param);
        $result = $this->find();
        if(!isset($result['ServiceID']))
            return false;
        return $result['ServiceID'];
    }
    
    public function getTimePlayingId($terminal_id) {
        $sql = 'SELECT TIMESTAMPDIFF(MINUTE,LastTransactionDate,NOW()) as minutes FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql,$param);
        $result = $this->find();
        if(!isset($result['minutes']))
            return false;
        return getTimePlaying($result['minutes']);
    }
    
    public function getDataById($terminal_id) {
        $sql = 'SELECT TIMESTAMPDIFF(MINUTE,LastTransactionDate,NOW()) as minutes,ServiceID,DateStarted, LastTransactionDate FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql,$param);
        $result = $this->find();
        return $result;
    }

    public function deleteTerminalSessionById($terminal_id) {
        $sql = 'DELETE FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id' => $terminal_id);
        return $this->exec($sql,$param);
    }
    
    public function updateTerminalSessionById($terminal_id,$service_id,$terminal_balance) {
        $sql = 'UPDATE terminalsessions SET ServiceID = :service_id, LastBalance = :terminal_balance, ' . 
                'LastTransactionDate = now_usec() WHERE TerminalID = :terminal_id';
        $param = array(
            ':service_id'=>$service_id,
            ':terminal_balance'=>$terminal_balance,
            ':terminal_id'=>$terminal_id);
        return $this->exec($sql,$param);
    }
    
    public function isSessionActive($terminal_id) {
        $sql = 'SELECT COUNT(TerminalID) AS Cnt FROM terminalsessions WHERE TerminalID = :terminal_id AND DateEnded = 0';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql,$param);
        $result = $this->find();
        if(!isset($result['Cnt']))
            return false;
        return $result['Cnt'];
    }
    
    /**
     * Inserts record in terminalsessions table
     * handles the ff. rules / constraints : 
     * ( 1. same card number and casino)
     * ( 2. same card number and terminal)
     * @modified elperez, 03-22-13
     * @version modified to support Kronus UB
     * @param int $terminal_id
     * @param int $service_id
     * @param str $amount
     * @param int $trans_summary_id
     * @param str $loyalty_card
     * @param int $mid
     * @param int $user_mode
     * @return type
     */
    public function insert($terminal_id,$service_id,$amount,$trans_summary_id, 
                           $loyalty_card, $mid, $user_mode, $casino_login = '', $casino_pwd = '', $casinohashed_pwd = '') {
            try {
                $this->beginTransaction();
                $sql = 'INSERT INTO terminalsessions (TerminalID, ServiceID, DateStarted, 
                    LastBalance, LastTransactionDate, TransactionSummaryID, LoyaltyCardNumber, 
                    MID, UserMode, UBServiceLogin, UBServicePassword, UBHashedServicePassword) 
                    VALUES (:terminal_id, :service_id, now_usec(), :amount, now_usec(), 
                    :trans_summary_id, :loyalty_card, :mid, :user_mode, :casino_login, :casino_pwd, :casinohashed_pwd)';
                
                $stmt = $this->dbh->prepare($sql);

                $stmt->bindValue(':terminal_id', $terminal_id);
                $stmt->bindValue(':service_id', $service_id);
                $stmt->bindValue(':amount', $amount);
                $stmt->bindValue(':trans_summary_id', $trans_summary_id);
                $stmt->bindValue(':loyalty_card', $loyalty_card);
                $stmt->bindValue(':mid', $mid);
                $stmt->bindValue(':user_mode', $user_mode);
                $stmt->bindValue(':casino_login', $casino_login);
                $stmt->bindValue(':casino_pwd', $casino_pwd);
                $stmt->bindValue(':casinohashed_pwd', $casinohashed_pwd);
            
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
    
    public function getTransDateTime() {
        $time =microtime(true);         
        $micro_time=sprintf("%06d",($time - floor($time)) * 1000000);   
        $rawdate = new DateTime( date('Y-m-d H:i:s.'.$micro_time, $time) );    
        return $rawdate->format("Y-m-d H:i:s.u");
    }
    
    /**
     * Get Last Transaction Summary ID in terminal sessions table
     * instead of getting the max summary ID in transaction summary table
     */
    public function getLastSessSummaryID($terminalID){
        $sql = 'SELECT TransactionSummaryID FROM terminalsessions WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminalID);
        $this->exec($sql, $param);
        $result = $this->find();
        if(!isset($result['TransactionSummaryID']))
            return false;
        return $result['TransactionSummaryID'];
    }
    
    /**
     * Get terminal session details either UB or TB
     * @param int $terminalID
     * @return obj
     */
    public function getLastSessionDetails($terminalID){
        
        $sql = "SELECT LoyaltyCardNumber, MID, UserMode, UBServiceLogin, UBServicePassword, 
                ServiceID, UBHashedServicePassword FROM terminalsessions
                WHERE TerminalID = :terminal_id";
        
        $param = array(":terminal_id"=>$terminalID);

        $this->exec($sql, $param);
        
        $result =  $this->findAll();
        
        return $result;
    }
    
}