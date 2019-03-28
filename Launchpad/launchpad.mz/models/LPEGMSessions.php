<?php

/**
 * Description of LPEGMSessions
 * @package application.modules.launchpad.models
 * @author Jan Richard Oquendo
 */

require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPEGMSessions extends LPModel
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
    
  
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPEGMSessions();
        return self::$_instance;
    }
    

   public function checkEGMSession($mid)
   {
       
        $query = "SELECT COUNT(*) as Count FROM egmsessions WHERE MID = :mid";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':mid',$mid);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPEGMSessions, Message: Can't get EGM Session");
        }   
        return $result[0]; 
       
   }
  
}