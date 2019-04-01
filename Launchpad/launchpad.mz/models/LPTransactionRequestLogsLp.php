<?php

/**
 * Description of LPTransactionRequestLogsLp
 * @package application.modules.launchpad.models
 * @author Bryan Salazar
 */
class LPTransactionRequestLogsLp extends LPModel
{
    
    /**
     *
     * @var LPTransactionRequestLogsLp 
     */
    private static $_instance = null;    
    public $_pdoconn;
    private $_referenceID;
    
    private function __construct() 
    {
        $connstring = LPConfig::app()->params["db1"]["connectionString"];
        $username = LPConfig::app()->params["db1"]["username"];
        $password = LPConfig::app()->params["db1"]["password"];
        $this->setpdoconn($connstring,$username,$password);
    }
    
    /**
     * Get instance of LPTransactionRequestLogsLp
     * @return LPTransactionRequestLogsLp 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPTransactionRequestLogsLp();
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
    
    /**
     * Get reference id
     * @return string 
     */
    public function getReferenceID()
    {
        return $this->_referenceID;
    }
}
