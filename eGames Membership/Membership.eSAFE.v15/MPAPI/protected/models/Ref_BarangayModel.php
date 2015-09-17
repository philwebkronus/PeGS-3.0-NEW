<?php

/**
 * @date 08-26-2015
 * @author fdlsison
 */

class Ref_BarangayModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db5;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new Ref_BarangayModel();
        return self::$_instance;
    }
    
    public function getBarangayList() {
        $sql = 'SELECT *
                FROM ref_barangay';
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
    
    //@date 08-26-2015
    //@author fdlsison
    public function getBarangayNameUsingBarangayID($barangayID) {
        $sql = 'SELECT BarangayName
                FROM ref_barangay
                WHERE BarangayID = :BarangayID';
        $param = array(':BarangayID' => $barangayID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
}