<?php

/**
 * Description of LPServiceTerminals
 * @package application.modules.launchpad.models
 * @author Bryan Salazar
 */
require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPServiceTerminals extends LPModel
{
    /**
     *
     * @var LPServiceTerminals 
     */
    private static $_instance;
    public $_pdoconn;
    
    private function __construct() 
    {
        $connstring = LPConfig::app()->params["db1"]["connectionString"];
        $username = LPConfig::app()->params["db1"]["username"];
        $password = LPConfig::app()->params["db1"]["password"];
        $this->_pdoconn = $this->setpdoconn($connstring,$username,$password);
    }
    
    /**
     * Get instance of LPServiceTerminals
     * @return LPServiceTerminals 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPServiceTerminals();
        return self::$_instance;
    }
    
    
    
}
