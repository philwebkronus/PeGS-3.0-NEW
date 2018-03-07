<?php

/**
 * Date Created 02 13, 18 03:30:00 PM <pre />
 * Description of MembersModel
 * @author John Aaron Vida
 */

class Ref_CivilStatusModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new Ref_CivilStatusModel();
        return self::$_instance;
    }

    public function getcivilstatus() {
        $sql = 'SELECT * FROM ref_civilstatus';
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
}
