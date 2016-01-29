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
    
    /**
     * Get service by ServiceID
     * @param int $serviceID
     * @return bool|array false if no row affected
     */
    public function getServiceInfo($serviceID)
    {
        $query = 'SELECT ServiceName, Alias, Code FROM ref_services WHERE ServiceID = :serviceID';
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':serviceID',$serviceID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPRefServices, Message: Service ID not found");
        }
            
        return $result;
    }
    
    /**
     * Get service by ServiceID including what type define in config [casino_type]
     * @param int $serviceID
     * @return array 
     */
    public function getServiceInfoWithType($serviceID)
    {
        $row = $this->getServiceInfo($serviceID);
        $row['type'] = '';
        $casino = array();
        $casinoType = LPConfig::app()->params['casino_type'];
        foreach($casinoType as $type) {
            if(strpos(strtolower($row['ServiceName']), strtolower($type)) !== false) {
                $row['type'] = $type;
                break;
            }
        }
        return $row;
    }
    
    /**
     * @Description: Get the UserMode of Casino to be transferred.
     * @param int $serviceID
     * @return int
     * @throws CHttpException
     */
    public function getUserMode($serviceID)
    {
        $query = 'SELECT UserMode FROM ref_services WHERE ServiceID = :serviceID';
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':serviceID',$serviceID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPRefServices, Message: Service ID not found");
        }
            
        return $result["UserMode"];
    }
    
    /**
     * @Description: Get the Service Group Name tie up to Service ID
     * @DateCreated: 2014-02-07
     * @param int $serviceID
     * @return string
     * @throws CHttpException
     */
    public function getServiceGroupName($serviceID){
        
        $query = "SELECT rsg.ServiceGroupName FROM ref_servicegroups rsg
                            INNER JOIN ref_services r ON r.ServiceGroupID = rsg.ServiceGroupID
                            WHERE r.ServiceID = :serviceid";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':serviceid',$serviceID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPRefServices, Message: Service Group Name not found");
        }
        
        return $result["ServiceGroupName"];
    }
    
    public function checkSessionMode($serviceID)
    {
        $query = "SELECT COUNT(*) as Count FROM ref_services a 
                  INNER JOIN ref_servicegroups b ON a.ServiceGroupID = b.ServiceGroupID
                  WHERE a.ServiceID = :serviceid
                  AND a.UserMode <> 1
                  AND b.ServiceGroupID <> 4";
        
        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':serviceid',$serviceID);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result) {
            $this->logerror("File: launchpad.models.LPRefServices, Message: Can't get User Mode");
        }
        
        return $result[0];   
    }
    
}
