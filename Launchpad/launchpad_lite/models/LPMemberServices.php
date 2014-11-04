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
    
    
    /**
     * @Description: Get UB Credentials (for User-Based casino)
     * @param int $mid
     * @param int $serviceid
     * @return array
     * @throws CHttpException
     */
    public function GetUBCredentials($serviceID, $UBusername, $MID = '')
    {
        
        if($MID != ''){
            $query = 'SELECT ms.ServiceUsername, ms.HashedServicePassword, ms.ServicePassword 
                                FROM membership.memberservices ms
                                WHERE ms.ServiceID = :serviceid 
                                AND ms.MID = :mid;';
            
            $rqst = $this->_pdoconn->prepare($query);
            $rqst->bindParam(':serviceid',$serviceID);
            $rqst->bindParam(':mid',$MID);
            $rqst->execute();
            $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
            
        } else {
            $query = 'SELECT ms.ServiceUsername, ms.HashedServicePassword, ms.ServicePassword 
                                FROM membership.memberservices ms
                                WHERE ms.ServiceID = :serviceid 
                                AND ms.MID = (SELECT MID FROM membership.memberservices 
                                                        WHERE ServiceUsername = :ubusername LIMIT 1);';
            
            $rqst = $this->_pdoconn->prepare($query);
            $rqst->bindParam(':serviceid',$serviceID);
            $rqst->bindParam(':ubusername',$UBusername);
            $rqst->execute();
            $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        }

        if(count($result) == 0) {
            $this->logerror("File: launchpad.models.LPServiceTerminals, Message: Can't get User Credentials");
        }
        return $result;
    }
}

?>