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
    /**
     * Check if Member/Card is converted into Ewallet
     * @param type $MID
     * @return type
     * @author Ken
     * @date feb 17, 2015
     */
    public function checkIfEwallet($MID)
    {
        $sql = "SELECT IsEwallet FROM members WHERE MID = :mid";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":mid", $MID);
        $result = $command->queryRow();
        
        return $result;
    }
    
    /*
     * @description: check if mid is valid/existing
     * @author: ralph sison
     * @dateadded: 12-22-2015
     */
    public function checkMIDIfExisting($mid)
    {
        $query = "SELECT COUNT(MID) ctrMID FROM members WHERE MID = :mid";
        $param = array(':mid'=>$mid);
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow(true, $param);
        
        if (count($result) > 0)
        {
            return $result['ctrMID'];
        }
        else
        {
            return "";
        }
    }
    
    /*
     * @description: check if player is active
     * @author: ralph sison
     * @dateadded: 12-22-2015
     */
    public function checkIfActive($mid)
    {
        $query = "SELECT COUNT(MID) ctrMID FROM members WHERE MID = :mid AND Status = 1";
        $param = array(':mid'=>$mid);
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow(true, $param);
        
        if (count($result) > 0)
        {
            return $result['ctrMID'];
        }
        else
        {
            return "";
        }
    }
}

