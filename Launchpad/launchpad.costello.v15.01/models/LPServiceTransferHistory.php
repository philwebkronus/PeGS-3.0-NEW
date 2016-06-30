<?php

/**
 * Description of LPServiceTransferHistory
 * @package application.modules.launchpad.models
 * @author Bryan Salazar
 */
require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPServiceTransferHistory  extends LPModel
{
    
    /**
     *
     * @var LPServiceTransferHistory 
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
     * Get instance of LPServiceTransferHistory
     * @return LPServiceTransferHistory 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPServiceTransferHistory();
        return self::$_instance;
    }
    
    
    /**
     * Get last insert id
     * @return int 
     */
    public function getLastInsertID()
    {
        return $this->_connection->lastInsertID;
    }
    
    
}
