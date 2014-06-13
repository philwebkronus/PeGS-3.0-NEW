<?php

/**
 * Database Service Log In Info
 * date created 10/16/13
 * For EGM Webservice
 * @author JunJun S. Hernandez
 */

class MemberServicesModel{
    
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db3;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new CommonTransactionsModel();
        return self::$_instance;
    }
    
    public function getDetailsByMIDAndCasinoID($MID, $casinoID){
        $sql = "SELECT MemberServiceID, ServiceUsername, ServicePassword, HashedServicePassword
                FROM memberservices e
                WHERE MID = :mid AND ServiceID = :casinoID";
        $param = array(':mid'=>$MID, ':casinoID' => $casinoID);
        $command = $this->_connection->createCommand($sql);
        return $command->queryRow(true, $param);
    }
    
    public function getMatchedTerminalAndServiceID($MID, $ServiceID){
        $sql = "SELECT MID FROM memberservices WHERE MID = :mid AND ServiceID = :service_id";
        $param = array(':mid'=>$MID, ':service_id'=>$ServiceID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        if($result['MID']=="")
            return 0;
        else
            return count($result['MID']);
    }
    
    public function isVip($MID) {
        $sql = "SELECT COUNT(MID) ctrMID FROM memberservices WHERE isVip = 1 AND MID = :mid";
        $param = array(':mid'=>$MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result['ctrMID'];
    }
    /**
     * Get ServiceID by MID to get the Service Details
     * @param int $MID Membership ID
     * @return int ServiceID
     * @author Mark Kenneth Esguerra
     * @date April 16, 2014
     */
    public function getServiceIDByMID($MID) {
        $query = "SELECT ServiceID FROM memberservices WHERE MID = :MID";
        $command = $this->_connection->createCommand($query);
        $command->bindParam(":MID", $MID);
        $result = $command->queryAll();

        if (count($result) > 0) {
            return $result;
        } else {
            return "";
        }
    }
    
}

?>
