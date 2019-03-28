<?php

/**
 * Description of LPConnection
 * @package application.modules.launchpad.models
 * @author John Aaron Vida
 */
require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPConnection extends LPModel
{
    private static $_instance = null;    
    public $_pdoconn;
    
    private function __construct() 
    {
        $connstring = LPConfig::app()->params["db4"]["connectionString"];
        $username = LPConfig::app()->params["db4"]["username"];
        $password = LPConfig::app()->params["db4"]["password"];
        $this->_pdoconn = $this->setpdoconn($connstring,$username,$password);
    }
    
    /**
     * Get instance of LPTerminals
     * @return LPTerminals 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPConnection();
        return self::$_instance;
    }
    

    public function checkSpyderConnection($terminalCode)
    {
        $terminalCode = substr($terminalCode, 5);
        
        $query = "SELECT * FROM connection WHERE terminalname = '$terminalCode'";  
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPTerminals, Message: Can't get spyder conection");
        }   
        return $result[0];
    }
    
    
    
    
}