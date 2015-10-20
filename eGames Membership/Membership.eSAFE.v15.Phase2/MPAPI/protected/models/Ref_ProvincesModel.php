<?php

/**
 * @date 08-26-2015
 * @author fdlsison
 */

class Ref_ProvincesModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db5;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new Ref_ProvincesModel();
        return self::$_instance;
    }
    
    public function getProvinceList() {
        $sql = 'SELECT *
                FROM ref_provinces  ';
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
    
    //@date 08-26-2015
    //@author fdlsison
    public function getProvinceNameUsingProvinceID($provinceID) {
        $sql = 'SELECT ProvinceName
                FROM ref_provinces
                WHERE ProvinceID = :ProvinceID';
        $param = array(':ProvinceID' => $provinceID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
}