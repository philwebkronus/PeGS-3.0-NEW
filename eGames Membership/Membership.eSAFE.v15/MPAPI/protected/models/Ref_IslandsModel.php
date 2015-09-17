<?php

/**
 * @date 08-26-2015
 * @author fdlsison
 */

class Ref_IslandsModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db5;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new Ref_IslandsModel();
        return self::$_instance;
    }
    
    public function getIslandNameUsingIslandID($islandID) {
        $sql = 'SELECT IslandName
                FROM ref_islands
                WHERE IslandID = :IslandID';
        $param = array(':IslandID' => $islandID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
}