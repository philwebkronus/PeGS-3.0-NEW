<?php

/**
 * Date Created 10 28, 11 1:11:44 PM <pre />
 * Date Modified 10/12/12
 * Description of TerminalsModel
 * @author Bryan Salazar
 * @author Edson Perez
 */
class TerminalsModel{
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new TerminalsModel();
        return self::$_instance;
    }
    /**
     * Description: Terminal code want to start session
     * @var string terminal_code
     */
    public $terminal_code;
    
    public function getDataByTerminalId($terminal_id) {
        $sql = 'SELECT isVIP, TerminalCode FROM terminals WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $command = $this->_connection->createCommand($sql);
        return $command->queryRow(true, $param);
    }
   
    public function getTerminalName($terminal_id) {
        $sql = 'SELECT TerminalCode FROM terminals WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(!isset($result['TerminalCode']))
            return false;
        return $result['TerminalCode'];
    }
    
    public function isPartnerAlreadyStarted($terminal_id, $siteCode) {
        $sql = 'SELECT TerminalCode FROM terminals WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        $terminal_code = $result['TerminalCode'];
        $len = strlen($siteCode);
        $this->terminal_code = substr($terminal_code, $len);
        if(stripos($terminal_code, 'VIP') !== false) {
            $terminal_code = str_replace('VIP', '', $terminal_code);
        } else {
            $terminal_code = $terminal_code."VIP";
        }
        
        $sql1 = "SELECT t.TerminalID FROM terminals t INNER JOIN terminalsessions ts ON ts.TerminalID = t.TerminalID " . 
                "WHERE t.TerminalCode LIKE '%$terminal_code' AND ts.TerminalID != :terminal_id";
        $command = $this->_connection->createCommand($sql1);
        $res = $command->queryRow(true, $param);
        
        if(isset($res['TerminalID']) && $res['TerminalID']) {
            return true;
        }
        return false;
    }
   
    public function getTerminalPassword($terminal_id, $service_id){
        $sql = "SELECT t.ServicePassword FROM terminalservices t WHERE t.TerminalID = :terminal_id AND ServiceID = :service_id AND t.Status = 1";
        $param = array(':terminal_id'=>$terminal_id,':service_id'=>$service_id);
        $command = $this->_connection->createCommand($sql);
        return $command->queryRow(true, $param);
    }
    
    public function insertserviceTransRef($service_id, $origin_id)
    {
        $sql = "INSERT INTO servicetransactionref (ServiceID, TransactionOrigin, DateCreated) VALUES (:service_id, :origin_id, NOW(6))";
        $smt = $this->_connection->createCommand($sql);
        $param = array(':service_id'=>$service_id, ':origin_id'=>$origin_id);
        $smt->execute($param);
        $transaction_id = $this->_connection->getLastInsertID();
        return $transaction_id;
    }
    
    //@author JunJun S. Hernandez
    //Get terminal details
    public function getTerminalDetails($terminalCode, $isVip){
        $sql = "SELECT TerminalID, Status FROM terminals WHERE TerminalCode = :terminal_code AND isVIP = :is_vip";
        $command = $this->_connection->createCommand($sql);
        $param = array(":terminal_code"=>$terminalCode,":is_vip"=>$isVip);
        return $command->queryRow(true, $param);
    }
    
    public function getTerminalInfo($terminalCode) {
        $terminalCodeVip = $terminalCode."VIP";
        $sql = "SELECT TerminalID, Status FROM terminals  WHERE TerminalCode IN (:terminal_code, :terminal_code_vip)";
        $command = $this->_connection->createCommand($sql);
        $param = array(":terminal_code"=>$terminalCode,":terminal_code_vip"=>$terminalCodeVip);
        return $command->queryAll(true, $param);
    }
    
    public function getTerminalIDByCode($terminalCode) {
        $terminalCodeVip = $terminalCode."VIP";
        $sql = 'SELECT TerminalID, Status FROM terminals WHERE TerminalCode IN (:terminal_code, :terminal_code_vip)';
        $param = array(":terminal_code"=>$terminalCode,":terminal_code_vip"=>$terminalCodeVip);
        $command = $this->_connection->createCommand($sql);
        return $command->queryAll(true, $param);
    }
    
    public function getTerminalIDByCodeEGMType($terminalCode) {
        $terminalCodeVip = $terminalCode."VIP";
        $sql = 'SELECT TerminalID, Status FROM terminals WHERE TerminalCode IN (:terminal_code, :terminal_code_vip) AND  TerminalType = 1';
        $param = array(":terminal_code"=>$terminalCode,":terminal_code_vip"=>$terminalCodeVip);
        $command = $this->_connection->createCommand($sql);
        return $command->queryAll(true, $param);
    }
    
    public function getSiteIDByTerminalID($terminalID) {
        $sql = 'SELECT SiteID FROM terminals WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminalID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['SiteID'];
    }
    
    public function checkVIP($terminalID) {
        $sql = 'SELECT isVIP FROM terminals WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminalID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['isVIP'];
    }
    
    public function getTerminalSiteIDSolo($terminal_code) {
        $sql = 'SELECT TerminalID, SiteID, Status FROM terminals WHERE TerminalCode = :terminal_code';
        $param = array(':terminal_code'=>$terminal_code);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(empty($result))
            return false;
        return $result;
    }
    
    /**
    * Description: Get Terminal and Site ID using Terminal Code of Reg and VIP terminal
    * @author gvjagolino
    */
    public function getTerminalSiteID($terminal_code) {
        $terminal_codevip = $terminal_code."VIP";
        $sql = 'SELECT TerminalID, SiteID FROM terminals WHERE TerminalCode IN (:terminal_code, :terminal_codevip)';
        $param = array(':terminal_code'=>$terminal_code, ':terminal_codevip'=>$terminal_codevip);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryAll(true, $param);
        if(empty($result))
            return false;
        return $result;
    }
    
    
    public function checkTerminalType($terminal_id){
        $sql = 'SELECT TerminalType FROM terminals WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result['TerminalType'];
    }
}

