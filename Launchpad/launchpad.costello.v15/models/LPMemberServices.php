<?php

/**
 * Description of LPMemberServices
 * @package application.modules.launchpad.models
 * @author aqdepliyan
 */

require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPMemberServices extends LPModel
{
    /**
     *
     * @var LPMemberServices 
     */
    private static $_instance;
    public $_pdoconn;
    
    private function __construct() 
    {
        $connstring = LPConfig::app()->params["db2"]["connectionString"];
        $username = LPConfig::app()->params["db2"]["username"];
        $password = LPConfig::app()->params["db2"]["password"];
        $this->_pdoconn = $this->setpdoconn($connstring,$username,$password);
    }
    
    /**
     * Get instance of LPMemberServices
     * @return LPMemberServices 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPMemberServices();
        return self::$_instance;
    }
    
    
}

?>