<?php

/**
 * Date Created 10 09, 13 01:00:00 PM <pre />
 * Description of MembersModel
 * @author JunJun S. Hernandez
 */

class Ref_IdentificationsModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new Ref_IdentificationsModel();
        return self::$_instance;
    }
    
    //@date 07-04-2014
    //@purpose retrieve list of ID
    public function getIDPresentedList() {
        $sql = 'SELECT *
                FROM ref_identifications';
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
}