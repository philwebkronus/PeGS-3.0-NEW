<?php

/**
 * @author taalcatara
 * 
 * @date 08-12-2015
 */

class CasinoServicesModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db2;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new CasinoServicesModel();
        return self::$_instance;
    }
    
    /**
     * Get all active casinos
     */
    public function getCasinoServices()
    {
        $query = "SELECT ServiceID, ServiceName, Code, UserMode
                    FROM ref_services WHERE ServiceID IN (8,9,10,11,12)
                        AND Status = 1";
        
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow();
        return $result;
    }
    
    public function getUserBasedCasinoServices()
    {
        $query = "SELECT rs.ServiceID, rs.ServiceGroupID, rsg.ServiceGroupName, rs.ServiceName
                    FROM ref_services rs
                    INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID
                    WHERE rs.Status = 1 AND rs.UserMode = 1";
        
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow();
        return $result;
    }
   /*
    * Added : John Aaron Vida
    * Date : October 9, 2015
    */
    
        public function getUserBasedCasinoDetails($UBserviceID)
    {
        $query = "SELECT rs.ServiceID, rs.ServiceGroupID, rsg.ServiceGroupName, rs.ServiceName
                    FROM ref_services rs
                    INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID
                    WHERE rs.ServiceID = :UBserviceID";
        $param = array(':UBserviceID' => $UBserviceID);
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow(true, $param);
        return $result;
    }
    
    
    
    public function getCasinoServiceName($serviceid)
    {
        $query = "SELECT rs.ServiceName, rsg.ServiceGroupName FROM ref_services rs 
            INNER JOIN ref_servicegroups rsg ON rsg.ServiceGroupID = rs.ServiceGroupID 
            WHERE rs.ServiceID = :ServiceID";
        $param = array(':ServiceID' => $serviceid);
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow(true, $param);
        return $result;
    }
    
    public function generateCasinoAccounts( $MID, $serviceID, $serviceName ,$isVIP = 1 )
    {
        Yii::import('application.components.CasinoProviders');
        Yii::import('application.components.Randomizer');
        
        if(strpos($serviceName, 'RTG2') !== false){
            $isVIP == 0 ? $vipLevel = Yii::app()->params["rtgreg"] : $vipLevel = Yii::app()->params["rtgvip"];
        }
        
        $randomnum = mt_rand(1000,9999); 
        $serviceusername = str_pad($MID, 8, '0', STR_PAD_LEFT);
        
        $services['ServiceID'] = $serviceID;
        $services['MID'] = $MID;
       if(strstr($serviceName, "RTG2")){
            $fullusername = $randomnum.$serviceusername;
            $services['ServiceUsername'] = $fullusername;
       }
               
        $services['ServicePassword'] = strtoupper(Randomizer::GenerateAlphaNumeric(8));  
        $services['HashedServicePassword'] = $services['ServicePassword'];
        $services['UserMode'] = 1;
        $services['DateCreated'] = 'NOW(6)';
        $services['isVIP'] = $isVIP;
        $services['Status'] = 1;
        $services['VIPLevel'] = $vipLevel;
        
        $newservices[] = $services;
        
        return $newservices;
    }
    
    public function getServiceGroupID($serviceid)
    {
        $query = "SELECT ServiceGroupID FROM ref_services WHERE ServiceID = :ServiceID";
        $param = array(':ServiceID' => $serviceid);
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow(true, $param);
        return $result[0]['ServiceGroupID'];
    }
}