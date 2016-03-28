<?php

/**
 * Date Created 11 4, 11 1:56:40 PM <pre />
 * Date Modifie 10/12/12
 * Model for ref_denominations
 * @author Bryan Salazar
 * @author Edson Perez
 */
class RefDenominationsModel{
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new RefDenominationsModel();
        return self::$_instance;
    }
    
    public function getAllDenomination() {
        $sql = 'SELECT Amount FROM ref_denominations';
        $command = $this->_connection->createCommand($sql);
        return $command->queryAll();
    }
    
    
    /**
     * Description: convert to singler array and sort denomination
     * @return array 
     */
    public function getAllDenominationInterval() {
        $denominations = $this->getAllDenomination();
        $deno = array();
        foreach($denominations as $val) {
            array_push($deno, $val['Amount']);
        }
        sort($deno);
        return $deno;
    }
}

