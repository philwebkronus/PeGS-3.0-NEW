<?php

/**
 * Date Created 11 16, 11 3:43:28 PM <pre />
 * Description of AccountSessionsModel
 * @author Bryan Salazar
 */
class AccountSessionsModel extends MI_Model {
    
    public function getSessionId($account_id) {
        $sql = 'SELECT SessionID FROM accountsessions WHERE AID = :account_id';
        $param = array(':account_id'=>$account_id);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['SessionID'];
    }
    
    public function hasSession($zuserid) {
        $sql = 'SELECT AID FROM accountsessions WHERE AID =  :userid';
        $param = array(':userid'=>$zuserid);
        $this->exec($sql, $param);
        $result = $this->find();
        if(isset($result['AID']) && $result['AID'])
            return true;

        return false;
    }
   
    public function deleteSession($aid) {
        $sql = 'DELETE FROM accountsessions WHERE AID = :aid';
        $param = array(':aid'=>$aid);
        $this->exec($sql, $param);
    }
   
    //validation: insert session on accountsessions table
    public function insertSession($aid, $sessionID, $date){
        $sql = 'Insert into accountsessions (AID, SessionID, DateCreated) values(:aid, :sessionid, :date)';
        $param = array(':aid'=>$aid,':sessionid'=>$sessionID,':date'=>$date);
        return $this->exec($sql, $param);
    }    
}

