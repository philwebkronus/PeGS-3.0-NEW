<?php

/**
 * Description of LPRefServices
 * @package application.modules.launchpad.models
 * @author Bryan Salazar
 */
require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPRefServices extends LPModel
{
    /**
     *
     * @var LPRefServices 
     */
    private static $_instance = null;
    public $_pdoconn;
    
    private function __construct() 
    {
        $connstring = LPConfig::app()->params["db1"]["connectionString"];
        $username = LPConfig::app()->params["db1"]["username"];
        $password = LPConfig::app()->params["db1"]["password"];
        $this->_pdoconn = $this->setpdoconn($connstring,$username,$password);
    }
    
    /**
     * Get instance of LPRefServices
     * @return LPRefServices 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPRefServices();
        return self::$_instance;
    }    

    
}
