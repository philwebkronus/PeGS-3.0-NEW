<?php

/**
 * Date Created 10 09, 13 01:00:00 PM <pre />
 * Description of MembersModel
 * @author JunJun S. Hernandez
 */

class Ref_RegionsModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db5;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new Ref_RegionsModel();
        return self::$_instance;
    }
    
    //@date 07-04-2014
    //@purpose retrieve list of regions
    public function getRegionList() {
        $sql = 'SELECT *
                FROM ref_regions';
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
    
    //@date 08-26-2015
    //@author fdlsison
    public function getRegionNameUsingRegionID($regionID) {
        $sql = 'SELECT RegionName
                FROM ref_regions
                WHERE RegionID = :RegionID';
        $param = array(':RegionID' => $regionID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
}