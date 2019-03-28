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
    
     /*
     * Added 06-02-2016
     * John Aaron Vida
     */

   public function checkUsermode($serviceID)
   {
       
        $query = "SELECT UserMode FROM ref_services WHERE ServiceID = :serviceID";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':serviceID',$serviceID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPEGMSessions, Message: Can't get Usermode");
        }   
        return $result[0]; 
       
   }

    
}
