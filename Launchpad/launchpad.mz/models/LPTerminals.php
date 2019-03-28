<?php

/**
 * Description of LPTerminals
 * @package application.modules.launchpad.models
 * @author Bryan Salazar
 */
require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPTerminals extends LPModel
{
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
     * Get instance of LPTerminals
     * @return LPTerminals 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPTerminals();
        return self::$_instance;
    }
    

    public function getTerminalType($terminalCode)
    {
//        $query = "SELECT TerminalType,SiteID FROM terminals WHERE TerminalCode IN ('$terminalCode','$terminalCode"."VIP')";
        $query = "SELECT ts.TerminalType,ts.SiteID,ss.SiteClassificationID FROM terminals ts
                    INNER JOIN sites ss ON ts.SiteID = ss.SiteID
                    WHERE ts.TerminalCode IN ('$terminalCode','$terminalCode"."VIP')";      //dale 01/21/16
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':terminalCode',$terminalCode);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPTerminals, Message: Can't get terminal type");
        }   
        return $result;
    }
    
    
    
    
}