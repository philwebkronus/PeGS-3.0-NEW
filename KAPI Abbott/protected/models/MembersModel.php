<?php

/**
 * Date Created 10 09, 13 01:00:00 PM <pre />
 * Description of MembersModel
 * @author JunJun S. Hernandez
 */

class MembersModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db3;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new MembersModel();
        return self::$_instance;
    }
    
    public function getStatus($MID) {
        $sql = 'SELECT Status FROM members WHERE MID = :MID';
        $param = array(':MID'=>$MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if(!isset($result['Status']))
            return false;
        return $result['Status'];
    }
    
    public function isVip($MID) {
        $sql = "SELECT COUNT(MID) ctrMID FROM members WHERE isVip = 1 AND MID = :mid";
        $param = array(':mid'=>$MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result['ctrMID'];
    }
}

