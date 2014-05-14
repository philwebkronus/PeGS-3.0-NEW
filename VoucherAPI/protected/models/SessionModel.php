<?php

class SessionModel extends CFormModel{
    public $aid;
    public $sessionid;


    public function addSession($aid, $session_id){
        $query = "INSERT INTO accountsessions (AID, SessionID, DateCreated) VALUES ($aid, '$session_id', NOW())";
        $sql = Yii::app()->db->createCommand($query);
        return $sql->execute();
    }
    
    public function updateSession($aid, $session_id){
        $query = ("UPDATE accountsessions SET SessionID = '$session_id', DateCreated = NOW() WHERE AID = $aid");
        $sql = Yii::app()->db->createCommand($query);
        return $sql->execute();
    }
    
    public function checkSession($aid){
        $query = ("SELECT AID FROM accountsessions where AID = $aid");
        $sql = Yii::app()->db->createCommand($query);
        $result = $sql->queryAll();

        foreach ($result as $row5) {
            $this->aid = $row5['AID'];
        }
        
    }
    
    public function getAID($user){
        $query = ("SELECT AID FROM accounts where UserName = '$user'");
        $sql = Yii::app()->db->createCommand($query);
        $result = $sql->queryAll();

        foreach ($result as $row5) {
            $this->aid = $row5['AID'];
        }
        return $this->aid;
    }


    public function deleteSession($aid){
        $query = ("DELETE FROM accountsessions where AID = $aid");
        $sql = Yii::app()->db->createCommand($query);
        $sql->execute();
    }
    
    public function deleteSessionwithId($sessionid){
        $query = ("DELETE FROM accountsessions where SessionID = '$sessionid'");
        $sql = Yii::app()->db->createCommand($query);
        $sql->execute();
    }
    
    
    public function hasSession($aid){
        
        $query = ("SELECT AID, SessionID FROM accountsessions where AID = $aid");
        $sql = Yii::app()->db->createCommand($query);
        $result = $sql->queryAll();

        foreach ($result as $row5) {
            $this->aid = $row5['AID'];
            $this->sessionid = $row5['SessionID'];
        }
        return $this->aid;
        return $this->sessionid;
    }
    
    public function checkifsessionexist($aid, $sessionid){
        $query = "SELECT COUNT(AID) FROM accountsessions WHERE AID = $aid AND SessionID = '$sessionid'";
        $sql = Yii::app()->db->createCommand($query);
        $result = $sql->queryAll();
        
        foreach ($result as $row) {
            $this->aid = $row['COUNT(AID)'];
        }
        return $this->aid;
    }
}
?>
