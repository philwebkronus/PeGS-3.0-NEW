<?php

/**
 * Date Created 10 09, 13 01:00:00 PM <pre />
 * Description of MembersModel
 * @author JunJun S. Hernandez
 */

class Ref_OccupationsModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new Ref_OccupationsModel();
        return self::$_instance;
    }
    
    //@date 07-04-2014
    //@purpose retrieve list of occupation
    public function getOccupationList() {
        $sql = 'SELECT *
                FROM ref_occupations';
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
}