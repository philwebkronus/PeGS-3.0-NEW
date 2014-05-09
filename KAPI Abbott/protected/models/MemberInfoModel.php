<?php

/**
 * Date Created 10 09, 13 01:00:00 PM <pre />
 * Description of MembersModel
 * @author JunJun S. Hernandez
 */

class MemberInfoModel {
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
    
    public function getMemberInfoByMID($MID) {
        $sql = 'SELECT FirstName, NickName, Gender FROM memberinfo WHERE MID = :MID';
        $param = array(':MID'=>$MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        return $result;
    }
}