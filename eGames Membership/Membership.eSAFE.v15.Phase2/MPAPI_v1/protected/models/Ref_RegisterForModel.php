<?php

/**
 * Date Created 02 13, 18 03:30:00 PM <pre />
 * Description of MembersModel
 * @author John Aaron Vida
 */

class Ref_RegisterForModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new Ref_RegisterForModel();
        return self::$_instance;
    }

    public function getregisterfor() {
        $sql = 'SELECT * FROM ref_registerfor';
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
}
