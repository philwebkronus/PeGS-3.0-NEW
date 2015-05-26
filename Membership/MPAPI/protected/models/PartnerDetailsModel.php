<?php

/**
 * @author fdlsison
 * 
 * @date 6-26-2014
 */

class PartnerDetailsModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db4;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new PartnerDetailsModel();
        return self::$_instance;
    }           
}